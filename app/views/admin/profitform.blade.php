<div class="modal-header">
  <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
  <h4 class="modal-title" id="profitLabel">Set Order Profits</h4>
</div>
<div class="modal-body">
  {{Form::model($cutoff, ['route'=>['admin-postprofit', $cutoff->id], 'method'=>'POST', 'class'=>'form-horizontal new-order'])}}
    <div class="form-group">
      <label for='saveon_cheque_value' class='col-sm-6 text-right'>Save-On Cheque:</label>
      <div class='col-sm-6'>
        <div class="input-group">
          <span class="input-group-addon">$</span>
          <input type="number" min="0" step="0.01" class="form-control" placeholder="" id="saveon_cheque_value" name="saveon_cheque_value" value="{{Form::getValueAttribute('saveon_cheque_value', $cutoff->saveon_cheque_value)}}"/>
        </div>
      </div>
    </div>
    <div class="form-group">
      <label for='saveon_card_value' class='col-sm-6 text-right'>Save-On Card Value:</label>
      <div class='col-sm-6'>
        <div class="input-group">
          <span class="input-group-addon">$</span>
          <input type="number" min="0" step="0.01" class="form-control" placeholder="" id="saveon_card_value" name="saveon_card_value" value="{{Form::getValueAttribute('saveon_card_value', $cutoff->saveon_card_value)}}"/>
        </div>
      </div>
    </div>
    <div class="form-group" style="margin-top:3em;">
      <label for='coop_cheque_value' class='col-sm-6 text-right'>Co-op Cheque:</label>
      <div class='col-sm-6'>
        <div class="input-group">
          <span class="input-group-addon">$</span>
          <input type="number" min="0" step="0.01" class="form-control" placeholder="" id="coop_cheque_value" name="coop_cheque_value" value="{{Form::getValueAttribute('coop_cheque_value', $cutoff->coop_cheque_value)}}"/>
        </div>
      </div>
    </div>
    <div class="form-group">
      <label for='coop_card_value' class='col-sm-6 text-right'>Co-op Card Value:</label>
      <div class='col-sm-6'>
        <div class="input-group">
          <span class="input-group-addon">$</span>
          <input type="number" min="0" step="0.01" class="form-control" placeholder="" id="coop_card_value" name="coop_card_value" value="{{Form::getValueAttribute('coop_card_value', $cutoff->coop_card_value)}}"/>
        </div>
      </div>
    </div>
    <div class="form-group text-right">
      <div class="col-sm-12">
        <button type='submit' class='btn btn-danger btn-lg'>Calculate order profits</button>
      </div>
    </div>
  {{Form::close()}}
</div>
<div class="modal-footer">
  <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
</div>