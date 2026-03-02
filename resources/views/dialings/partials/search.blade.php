<div class="fields-container">
    <h4>Search:</h4>
    <div class="default or_section">
        <div class="row flex-wrap align-items-end fieldsRow">
            <div class="col-12 col-md-6 inputForms search-fields">
                <div class="form-row align-items-center inputField">
                    <div class="form-group col-12 col-sm mb-2">
                        <strong>In Column</strong>
                        {!!
                            Form::select(
                                "s_name_1",
                                [
                                    "Frequently Used" => [
                                        "business_name" => "Business Name",
                                    ],
                                    "Other - Lead" => [
                                        "business_city" => "Business City",
                                        "business_county" => "Business County",
                                        "business_unit_count" => "Business Unit Count",
                                    ],
                                ],
                                [],
                                ["class" => "form-control multiple select ", "onchange" => "changeInput(this)"],
                            )
                        !!}
                    </div>
                    <div class="form-group col mb-2" style="max-width: 60px">
                        <strong>op.</strong>
                        {!! Form::select("and_or", ["like" => " = ", "not like" => " != "], [], ["class" => "form-control multiple operator p-0 "]) !!}
                    </div>
                    <div class="form-group col-12 col-sm mb-2">
                        <strong>For</strong>
                        <div class="inputEnter">
                            {!! Form::text("name", null, ["placeholder" => "Enter value", "class" => "form-control input"]) !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <button type="button" class="btn btn-outline-info btn-sm mt-2 btn_add_or" onclick="add_or_condition(this)">
            +
            <b>OR</b>
        </button>
    </div>
</div>
<div class="dropdown-divider mb-2 mt-2"></div>
<div class="row flex-wrap align-items-center justify-content-center justify-content-md-between">
    <div class="col-12 col-md-6 d-flex justify-content-center justify-content-md-start">
        <button type="button" class="btn btn-outline-info btn-sm mr-3" onclick="add_and_condition()">
            +
            <b>AND</b>
        </button>
        <button
            type="submit"
            class="btn btn-outline-primary btn-sm mr-3"
            id="btnFiterSubmitSearch"
            onclick="filter_table()"
        >
            Filter
        </button>
    </div>
    <div class="col-12 col-md-6 d-flex justify-content-center justify-content-md-end align-items-md-center">
        <button type="button" class="btn btn-outline-danger btn-sm andClose" onclick="resetCloseFiltersTab(this)">
            Reset
        </button>
    </div>
</div>
