<div class="fields-container">
    <div class="row or_section">
        <div class="col-md-6 mb-3">
            <strong class="text-info mb-2 d-block">Outbound Count :</strong>
            <div class="row">
                <div class="col-6">
                    <select id="outbound_type_selection" class="form-control">
                        <option value="">Select Sign</option>
                        <option value=">">Greater than</option>
                        <option value="<">Lesser than</option>
                        <option value="=">Equal to</option>
                    </select>
                </div>
                <div class="col-6">
                    <input type="text" maxlength="4" placeholder="Outbound value" class="form-control numeric-input" name="outbound_type_value" id="outbound_type_value">
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <strong class="text-info mb-2 d-block">Inbound Count :</strong>
            <div class="row">
                <div class="col-6">
                    <select id="inbound_type_selection" class="form-control">
                        <option value="">Select Sign</option>
                        <option value=">">Greater than</option>
                        <option value="<">Lesser than</option>
                        <option value="=">Equal to</option>
                    </select>
                </div>
                <div class="col-6">
                    <input type="text" maxlength="4" placeholder="Inbound value" class="form-control numeric-input" name="inbound_type_value" id="inbound_type_value">
                </div>
            </div>
        </div>
    </div>
    <br>
    <div class="row or_section">
        <div class="col-md-6 mb-3">
            <div class="row">
                <div class="col-6">
                    <strong class="text-info mb-2 d-block">Message Type</strong>
                    <select id="message_type_selection" class="form-control" {{$type}}>
                        <option value="">Select Message Type</option>
                        <option @if(!empty($type) && $type == 1) {{'selected'}} @endif value="1">Sms Provider</option>
                        <option @if(!empty($type) && $type == 2) {{'selected'}} @endif value="2">NewsLetter</option>
                    </select>
                </div>
                <div class="col-6">
                    <strong class="text-info mb-2 d-block">User Type</strong>
                    <select id="user_type_selection" class="form-control">
                        <option selected value="1">Processing</option>
                        <option value="2">Complete</option>
                        <option value="3">Stop</option>
                        <option value="4">Archived</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="dropdown-divider mb-2 mt-2"></div>
<div class="row flex-wrap align-items-center justify-content-center justify-content-md-between">
    <div class="col-12 col-md-6 d-flex justify-content-center justify-content-md-start">
        <button type="button" class="btn btn-outline-primary btn-sm mr-3" id="btnFiterSubmitSearch" onclick="reset_table()">Reset</button>
        <button type="button" class="btn btn-outline-primary btn-sm mr-3" id="btnFiterresetSearch" onclick="filter_table()">Filter</button>
    </div>
</div>
