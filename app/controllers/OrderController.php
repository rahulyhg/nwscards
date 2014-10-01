<?php

class OrderController extends BaseController {

	public function getAccount()
	{
		$user = Sentry::getUser();
		$user->load('orders');
		return View::make('account', [
			'user' => $user,
			'message' => Session::get('ordermessage'),
			'mostRecentOrder' => $user->orders()->first(),
			]);
	}

	public function postAccount()
	{
		return View::make('account');
	}

	public function getNew()
	{
		return View::make('new', ['stripeKey' => $_ENV['stripe_pub_key']]);
	}

	public function postNew()
	{
	 	$rules = [
				'name'		=> 'required',
				'email'		=> 'required|email|unique:users',
				'phone'		=> 'digits:10',
				'password'	=> 'required',
				'password-repeat'	=> 'required|same:password',
				'address1'	=> 'required_if:deliveyrmethod,mail',
				'city'		=> 'required_if:deliverymethod,mail',
				'postal_code'	=> 'required_if:deliverymethod,mail|regex:/^\w\d\w ?\d\w\d$/',
				'schedule'	=> 'required|in:biweekly,monthly,monthly-second',
				'saveon'	=> 'digits_between:1,2|required_without:coop',
				'coop'		=> 'digits_between:1,2|required_without:saveon',
				'payment'	=> 'required|in:debit,credit',
				'debit-transit'		=> 'required_if:payment,debit|digits_between:5,7',
				'debit-institution'	=> 'required_if:payment,debit|digits:3',
				'debit-account' 	=> 'required_if:payment,debit|digits_between:5,15',
				'debitterms' 	=> 'required_if:payment,debit',
				'mailwaiver'	=>'required_if:deliverymethod,mail',
				'deliverymethod' => 'required',
			];

	 	$messages = [
	 			'debit-transit.required_if' => 'branch number is required.',
	 			'debit-institution.required_if' => 'institution is required.',
	 			'debit-account.required_if' => 'account number is required.',
	 			'debitterms.required_if' => 'You must agree to the terms to pay by pre-authorized debit.',
	 		];
	  	//store phone as a number, but allow people to type ( and ) and - and space in it

	  	$in = Input::all();
	  	$in['phone'] = preg_replace('/[- \\(\\)]*/','',$in['phone']);

		$validator = Validator::make($in, $rules, $messages);

		// process the login
		if ($validator->fails()) {
			return Redirect::to('/new')
				->withErrors($validator)
				->withInput(Input::all());
		} else {
			$user = Sentry::register([
				'email'		=>$in['email'],
				'password'	=>$in['password'],
				'name'		=>$in['name'],
				'phone'		=>$in['phone'],
				'address1'	=>$in['address1'],
				'address2'	=>$in['address2'],
				'city'		=>$in['city'],
				'province'	=>'BC',
				'postal_code'	=>$in['postal_code'],
				'marigold' => array_key_exists('marigold', $in),
				'daisy' => array_key_exists('daisy', $in),
				'sunflower' => array_key_exists('sunflower', $in),
				'bluebell' => array_key_exists('bluebell', $in),
				'class_1' => array_key_exists('class_1', $in),
				'class_2' => array_key_exists('class_2', $in),
				'class_3' => array_key_exists('class_3', $in),
				'class_4' => array_key_exists('class_4', $in),
				'class_5' => array_key_exists('class_5', $in),
				'class_6' => array_key_exists('class_6', $in),
				'class_7' => array_key_exists('class_7', $in),
				'class_8' => array_key_exists('class_8', $in),
				'saveon' => $in['saveon'],
				'coop' => $in['coop'],
				'payment' => $in['payment'] == 'credit',
				'schedule' => $in['schedule'],
				'deliverymethod' => $in['deliverymethod'] == 'mail',
				'referrer' => $in['referrer'],
				'pickupalt' => $in['pickupalt'],
			], true);
			try {
				$plan = null;
				if($in['schedule'] == 'biweekly'){
					$plan = '14days';
				}
				else {
					$plan = '28days';
				}
				$cardToken = null;
				if(isset($in['stripeToken'])){
					$cardToken = $in['stripeToken'];
				}
				$gateway = null;
				if(isset($cardToken)) {
						$chargedate = BaseController::getCutoffs()[$in['schedule']]['charge'];
						$gateway = $user->subscription($plan)->trialFor($chargedate)->quantity($in['saveon']+$in['coop']);
				}
				else {
					$gateway = $user->subscription(null); //just create them with no subs if they don't have a card
				}
				$extras = [
					'email' => $user->email, 
					'description' => $user->name,
				];
				if(!isset($cardToken))
				{
					$extras['metadata'] = [
						'debit-transit'=>$in['debit-transit'],
						'debit-institution'=>$in['debit-institution'],
						'debit-account'=>$in['debit-account'],
					];
				}
			
				$gateway->create($cardToken, $extras);
			}
			catch(\Exception $e) {
				$user->delete();
				throw $e;
			}

			Mail::send('emails.newconfirmation', ['user' => $user], function($message) use ($user){
				$message->subject('Grocery card order confirmation');
				$message->to($user->email, $user->name);
				if(! $user->payment)
				{
					$agreementView = View::make('partial.debitterms');
					$agreement = '<html><body>'.$agreementView->render().'</body></html>';
					$message->attachData($agreement, 'debit-agreement.html', ['mime'=>'text/html', 'as'=>'debit-agreement.html']);
				}
			});
			// redirect
			Session::flash('ordermessage', 'order created');
			Sentry::login($user, false);
			return Redirect::to('/account');			
		}
	}
}
