<div class="modal fade" id="saveCampaign" tabindex="-1" role="dialog"  aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content p-0">
      <div class="modal-header p-0 p-lg-3 align-items-center">
        <h5 class="modal-title">Save as Marketing Campaign</h5>
        <button type="button" id="close_saveCampaign" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
       
        <div class="modal-body">
            <div class="form-group">
                <strong>Campaign Name:</strong>
                {!! Form::text('name', null, array('placeholder' => 'Campaign Name','class' => 'form-control campName','required')) !!}
            </div>
        </div>
        <div class="modal-footer flex-column">
          <button  class="btn btn-primary" id="saveCampaign-button" onclick="save_campaign(this)">Save Campaign <span class="spinner-border spinner-border-sm navicon d-none" role="status" aria-hidden="true"></span></button>
          <div class="mt-3 text-secondary d-none waitInfo w-100">
            Please wait... <br/>
            Creating CSV file ...<br/>
            Do not close this window.
        </div>
        </div>
        
      </div>
    </div>
  </div>
