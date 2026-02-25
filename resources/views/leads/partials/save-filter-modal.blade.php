<div id="save-filter" class="modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Save Filter</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            
            <div class="modal-body">
                <div class="form-group">
                    <strong>Filter Name:</strong>
                    <input id="filter_save_type" type="text" class="d-none" value="" name="filter_save_type">
                    {!! Form::text('save_filter_name', null, array('placeholder' => 'Filter Name','class' => 'form-control','required')) !!}
                </div>
            </div>
            <div class="modal-footer flex-column">
                <button type="submit" class="btn btn-primary" id="save-filter-button" onclick="saveFilter()">Save Filter</button>
            </div>
        </div>
    </div>
</div>
