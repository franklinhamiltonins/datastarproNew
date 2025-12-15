<div class="fields-container">
    <div class="default or_section">
        <div class="row flex-wrap align-items-end distance_input">
            <div class="col-12">
                <strong class="text-info mb-2 d-inline-block">Distance Query :</strong>
                <div class="d-flex m-0">
                    <label>Address</label>
                    <div class="custom-control custom-switch ml-2">
                        <input type="checkbox" class="custom-control-input" id="distance_query_selection_checkbox">
                        <label class="custom-control-label" for="distance_query_selection_checkbox">Business
                            Name</label>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="row">
                    <div class="col-12 col-md-6">
                        <div class="form-group mb-0">
                            <span id="disctance_query_selection_span"><strong>Address :</strong></span>
                            {!! Form::text('address_text', null, array("placeholder" => "Enter address", 'class'=>
                            'form-control input', 'id'=> 'address_text_search')) !!}
                            {!! Form::text('lead_business_names_search', null, array("placeholder" => "Enter business name",
                            'class'=> 'form-control input d-none', 'id'=> 'lead_business_names_search', 'data-filter'=>
                            '&q=#QUERY#')) !!}
                            <input type="text" id="lead_business_name_search_id" class="d-none" value="0">
                        </div>
                    </div>
                    <div class="col-12 col-md-6 p-0">
                        <div class="d-flex">
                            <div class="form-group col mb-0" style="max-width:60px;">
                                <strong>OP.</strong>
                                {!! Form::select('distance_op', array( '=' => '=', '<'=> '<', '>'=> '>', '>=' => '>=', '
                                        <='=> '<='),[], array('class'=> 'form-control multiple operator p-0 ml-0')) !!}
                            </div>
                            <div class="form-group col mb-0">
                                <strong>Distance</strong>
                                {!! Form::text('distance', null, array("placeholder" => "Enter distance",'class'=>
                                'form-control input integer-only')) !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row flex-wrap align-items-end fieldsRow mt-3">
            <div class="col-12 col-md-6 inputForms search-fields">
                <div class="form-row align-items-center inputField ">
                    <div class="form-group col-12 col-sm mb-2">
                        <strong>In Column</strong>
                        {!! Form::select('s_name_1', $tableHeadingName,null, array('class' => 'form-control multiple select ', 'id'=>'s_name_1','onchange'=>'changeInput(this)'))!!}
                    </div>
                    <div class="form-group col mb-2" style="max-width:60px;">
                        <strong>op.</strong>
                        {!! Form::select('and_or',array('like'=>' = ','not like'=>' != '),[], array('class' =>
                        'form-control multiple operator p-0 ','id'=>'and_or_1')) !!}
                    </div>
                    <div class="form-group col-12 col-sm mb-2">
                        <strong>For</strong>
                        <div class="inputEnter">
                            {!! Form::text('name', null, [
                                'placeholder' => 'Enter value',
                                'class' => 'form-control input value',
                                'id' => 'name_1'
                            ]) !!}

                        </div>
                    </div>
                </div>
            </div>
        </div>
        <button type="button" class="btn btn-outline-info btn-sm mt-2 btn_add_or" onclick="add_or_condition(this)">+
            <b>OR</b></button>
        <!-- <div class="btn btn-outline-info btn-sm mt-2 btn_add_or" onclick="add_or_condition(this)">+ <b>OR</b></div> -->
    </div>
</div>
<div class="dropdown-divider mb-2 mt-2"></div>
<div class="row flex-wrap align-items-center justify-content-center justify-content-md-between">
    <div class="col-12 col-md-6 d-flex justify-content-center justify-content-md-start">
        <button type="button" class="d-inline-flex align-items-center btn btn-outline-info btn-sm mr-3" onclick="add_and_condition()">
            <i class="fa fa-plus d-inline-block mr-2"> </i>
            <b>AND</b>
        </button>
        <!-- <div class="btn btn-outline-info btn-sm btn_add_and mr-3" onclick="add_and_condition()">+ <b>AND</b></div> -->
        <button type="submit" class="btn btn-outline-primary btn-sm mr-3" id="btnFiterSubmitSearch"
            onclick="filter_table()">Filter</button>
        <button type="submit" class="btn btn-outline-primary text-nowrap btn-sm mr-3" id="btnFiterDialingSubmitSearch"
            onclick="filter_table('dialing')">Filter for Dialing List</button>
        <button type="submit" class="btn btn-outline-primary text-nowrap btn-sm mr-3" id="btn_save_filter"
            onclick="openSaveFilterModal(event, 'save_new', 'save-filter')">Save Filter</button>
        <button type="submit" class="btn btn-outline-primary text-nowrap btn-sm mr-3 d-none" id="btn_save_as_filter"
            onclick="openSaveFilterModal(event, 'save_as', 'save-filter')">Save as Filter</button>

        <button type="submit" class="btn btn-outline-primary text-nowrap btn-sm mr-3" id="btn_dailing_list" data-toggle="modal" data-target="#saveagentlist">Create Dialing List</button>
        <button type="submit" class="btn btn-outline-primary text-nowrap btn-sm mr-3" id="btn_dailing_list" data-toggle="modal" data-target="#saveCampaign">Create Mailing List</button>
    </div>
    <div class="col-12 col-md-6 d-flex justify-content-center justify-content-md-end  align-items-md-center">
        <button type="button" class="btn btn-outline-danger btn-sm andClose"
            onclick="resetCloseFiltersTab(this)">Reset</button>
        <!-- <div class="btn btn-outline-danger andClose" onclick="resetCloseFiltersTab(this)">Reset Filters and Close</div> -->
    </div>
</div>