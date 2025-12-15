<div class="fields-container">
    <h4>Search: </h4>

    <div class="default or_section">

        <div class=" row flex-wrap align-items-end fieldsRow">
            <div class=" col-12 col-md-6 inputForms search-fields">
                <div class="form-row align-items-center inputField ">
                    <div class="form-group col-md-6">
                        <h6>Start Date <span class="text-danger"></span></h6>
                        <div class="controls">
                            <input type="date" name="start_date" id="start_date" class="form-control datepicker-autoclose" placeholder="Please select start date"> <div class="help-block"></div></div>
                        </div>
                        <div class="form-group col-md-6">
                        <h6>End Date <span class="text-danger"></span></h6>
                        <div class="controls">
                            <input type="date" name="end_date" id="end_date" class="form-control datepicker-autoclose" placeholder="Please select end date"> <div class="help-block"></div></div>
                        </div>
                        <div class="text-left" style="
                        margin-left: 15px;
                        ">
                        </div>

                </div>
            </div>
        </div>



    </div>
</div>
<div class="dropdown-divider mb-4 mt-4"></div>
<div class="row flex-wrap align-items-center justify-content-center justify-content-md-between">
    <div class="col-12 col-md-6 d-flex justify-content-center justify-content-md-start">
        <button type="submit " id="btnFiterSubmitSearch" onclick="filter_table()" class="btn btn-primary  mb-2">Filter</button>
    </div>
    <div class="col-12 col-md-6 d-flex justify-content-center justify-content-md-end  align-items-md-center">
        <div class=" btn btn-danger text-light andClose  mb-2" onclick="resetCloseFiltersTab(this)">Reset Filters and Close</div>
    </div>
</div>
