<div class="card-body lead-update p-0 pt-4">
    @if (isset($lead))
        <div class="card card-secondary mt-0 mb-0 border-0 shadow-none">
            <div class="card-body p-0">
                <div id="property_accordion">
                    <h3 class="px-1">Property</h3>
                    <div class="wrapper_content">
                        <div class="form-row">
                            <div class="form-group col-12 col-md-6 mb-2">
                                <strong>Carrier:</strong>

                                <?php $pSelected = 0; ?>

                                <select
                                    name="ins_prop_carrier"
                                    class="form-control input selectboxcarrier px-1"
                                    placeholder="Select Carrier"
                                    onchange="getSetOtherCarrierVal(this, 'ins_prop_carrier')"
                                    id="ins_prop_carrier"
                                >
                                    <option
                                        value=""
                                        @if ($lead->ins_prop_carrier == "")
                                            <?php $pSelected = 1; ?>
                                            {{ "selected" }}
                                        @endif
                                    >
                                        Select Carrier
                                    </option>
                                    @foreach ($carriersWithProperty as $carrier)
                                        <option
                                            value="{{ $carrier->id }}"
                                            @if ($lead->ins_prop_carrier == $carrier->id)
                                                <?php $pSelected = 1; ?>
                                                {{ "selected" }}
                                            @endif
                                        >
                                            {{ $carrier->name }}
                                        </option>
                                    @endforeach

                                    <option value="other" @if($pSelected == 0) {{ 'selected' }} @endif>Others</option>
                                </select>
                            </div>
                            <div class="form-group col-12 col-md-6 mb-2">
                                <strong>Month:</strong>
                                {{-- hardcoded months dropdown --}}
                                {!!
                                    Form::select("renewal_carrier_month", $months, isset($lead) ? $lead->renewal_carrier_month : [], [
                                        "id" => "renewal_carrier_month",
                                        "class" => "form-control multiple businessRenewalCarrierMonth px-1",
                                    ])
                                !!}
                            </div>
                            <div class="form-group col-12 mb-2">
                                <div
                                    id="ins_prop_carrier_div"
                                    class="mt-2 otherInput"
                                    @if($pSelected == 1) style="display:none;" @endif
                                >
                                    <input
                                        placeholder="Other Insurance Property Carrier"
                                        class="form-control"
                                        name="ins_prop_carrier-other"
                                        type="text"
                                        value="{{ ! empty($lead->propertyCarrier->name) && $pSelected == 0 ? $lead->propertyCarrier->name : "" }}"
                                        id="ins_prop_carrier-other"
                                    />
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row align-items-end">
                                <div class="col-12 col-md-6">
                                    <strong>Expiring Premium:</strong>
                                    <div class="input-group">
                                        <span class="input-group-text rounded-right-0">$</span>
                                        {!!
                                            Form::number("premium", null, [
                                                "placeholder" => "Expiring Premium ",
                                                "class" => "form-control rounded-left-0",
                                                "step" => "any",
                                                "aria-label" => 'Dollar amount (with dot and two
                                                                                    decimal places',
                                                "id" => "premium",
                                                "maxlength" => 10,
                                                "oninput" => "restrictInput(this, 10)",
                                            ])
                                        !!}
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <strong>Expiring Premium Year:</strong>
                                    {!!
                                        Form::select("premium_year", $years, isset($lead) ? $lead->premium_year : [], [
                                            "class" => "form-control multiple premium_year px-1",
                                            "id" => "premium_year",
                                        ])
                                    !!}
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-12 col-md-6">
                                    <strong>Total insured value:</strong>
                                    <div class="input-group">
                                        <span class="input-group-text rounded-right-0">$</span>
                                        {!!
                                            Form::number("insured_amount", null, [
                                                "id" => "insured_amount",
                                                "placeholder" => "Total insured value",
                                                "class" => "form-control rounded-left-0",
                                                "step" => "any",
                                                "aria-label" => 'Dollar amount (with
                                                                                    dot and two decimal places',
                                                "oninput" => "restrictInput(this, 10)",
                                            ])
                                        !!}
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <strong>Total Insured Value – Year:</strong>
                                    {!!
                                        Form::select("insured_year", $years, isset($lead) ? $lead->insured_year : [], [
                                            "id" => "insured_year",
                                            "class" => "form-control multiple insured_year px-1",
                                        ])
                                    !!}
                                </div>
                            </div>
                        </div>

                        <div class="mb-2">
                            <div class="form-row">
                                <div class="form-group col-12 col-md-6 mb-0">
                                    <strong>Price Per SqFt:</strong>
                                    <div class="input-group">
                                        <span class="input-group-text rounded-right-0">$</span>
                                        {!!
                                            Form::text("price_per_sqft", null, [
                                                "placeholder" => "Price Per SqFt",
                                                "class" => "form-control rounded-left-0",
                                                "step" => "any",
                                                "id" => "price_per_sqft",
                                                "disabled" => "disabled",
                                            ])
                                        !!}
                                    </div>
                                </div>
                                <div class="form-group col-12 col-md-6 mb-0">
                                    <strong>Policy Renewal Date:</strong>
                                    {!! Form::date("policy_renewal_date", null, ["id" => "policy_renewal_date", "class" => "form-control policy_renewal_date"]) !!}
                                </div>
                            </div>
                        </div>
                        <div class="mb-2">
                            <div class="form-row">
                                <div class="form-group col-12 col-md-6 mb-0">
                                    <strong>Incumbent Agency:</strong>
                                    <input
                                        placeholder="Incumbent Agency"
                                        class="form-control"
                                        name="incumbent_agency"
                                        type="text"
                                        value="{{ $lead->incumbent_agency }}"
                                        id="incumbent_agency"
                                    />
                                </div>
                                <div class="form-group col-12 col-md-6 mb-0">
                                    <strong>Incumbent Agent:</strong>
                                    <input
                                        placeholder="Incumbent Agent"
                                        class="form-control"
                                        name="incumbent_agent"
                                        type="text"
                                        value="{{ $lead->incumbent_agent }}"
                                        id="incumbent_agent"
                                    />
                                </div>
                            </div>
                        </div>
                        <!--  -->
                        <div class="form-row">
                            <div class="form-group col-12 col-md-6 mb-2">
                                <strong>Carrier Rating:</strong>

                                <?php $pSelectedR = 0; ?>

                                <select
                                    name="rating"
                                    class="form-control input selectboxcarrier px-1"
                                    placeholder="Select Rating"
                                    onchange="getSetOtherCarrierVal(this, 'rating')"
                                    id="rating"
                                >
                                    <option
                                        value=""
                                        @if($lead->rating == "") <?php $pSelectedR = 1 ?> {{ 'selected' }} @endif
                                    >
                                        Select Rating
                                    </option>
                                    @foreach ($ratingsWithProperty as $rating)
                                        <option
                                            value="{{ $rating->id }}"
                                            @if ($lead->rating == $rating->id)
                                                <?php $pSelectedR = 1; ?>
                                                {{ "selected" }}
                                            @endif
                                        >
                                            {{ $rating->name }}
                                        </option>
                                    @endforeach

                                    <option value="other" @if($pSelectedR == 0) {{ 'selected' }} @endif>Others</option>
                                </select>
                            </div>
                            <div class="form-group col-12 col-md-6 mb-2">
                                <strong>Sinkhole:</strong>
                                {!!
                                    Form::select(
                                        "skin_hole",
                                        [
                                            "No" => "No",
                                            "Yes" => "Yes",
                                        ],
                                        isset($lead) ? $lead->skin_hole : "",
                                        ["id" => "skin_hole", "class" => "form-control px-1"],
                                    )
                                !!}
                            </div>
                            <div class="form-group col-12 mb-2">
                                <div
                                    id="rating_div"
                                    class="mt-2 otherInput"
                                    @if($pSelectedR == 1) style="display:none;" @endif
                                >
                                    <input
                                        placeholder="Other Insurance Property Rating"
                                        class="form-control"
                                        name="rating-other"
                                        type="text"
                                        value="{{ ! empty($lead->propertyRating->name) && $pSelectedR == 0 ? $lead->propertyRating->name : "" }}"
                                        id="rating-other"
                                    />
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-12 col-md-6 mb-2">
                                <strong>Hurricane Deductible:</strong>
                                {!!
                                    Form::select(
                                        "hurricane_deductible",
                                        [
                                            "" => "Select Hurricane Deductible",
                                            "1" => "1%",
                                            "3" => "3%",
                                            "5" => "5%",
                                            "10" => "10%",
                                        ],
                                        isset($lead) ? $lead->hurricane_deductible : "",
                                        ["id" => "hurricane_deductible", "class" => "form-control px-1"],
                                    )
                                !!}
                            </div>
                            <div class="form-group col-12 col-md-6 mb-2">
                                <strong>Hurricane Deductible (Per Occ/Year):</strong>
                                <!-- list will be shared -->
                                {!!
                                    Form::select(
                                        "hurricane_deductible_occurrence",
                                        [
                                            "" => "Select Occurrence",
                                            "Per Occurrence" => "Per Occurrence",
                                            "Per Year" => "Per Year",
                                        ],
                                        isset($lead) ? $lead->hurricane_deductible_occurrence : "",
                                        ["id" => "hurricane_deductible_occurrence", "class" => "form-control px-1"],
                                    )
                                !!}
                            </div>
                        </div>
                        <div class="mb-2">
                            <div class="form-row">
                                <div class="form-group col-12 col-md-6 mb-0">
                                    <strong>All other Perils:</strong>
                                    <div class="input-group">
                                        <span class="input-group-text rounded-right-0">$</span>
                                        {!!
                                            Form::number("all_other_perils", null, [
                                                "id" => "all_other_perils",
                                                "placeholder" => "All other Perils",
                                                "class" => "form-control rounded-left-0",
                                                "step" => "any",
                                                "aria-label" => 'Dollar amount (with
                                                                                    dot and two decimal places',
                                                "oninput" => "restrictInput(this, 10)",
                                            ])
                                        !!}
                                    </div>
                                </div>
                                <div class="form-group col-12 col-md-6 mb-0">
                                    <strong>Ordinance of Law:</strong>

                                    <?php $oflSelected = 0; ?>

                                    <select
                                        id="ordinance_of_law"
                                        class="form-control px-1"
                                        name="ordinance_of_law"
                                        onchange="getSetOtherCarrierVal(this, 'ordinance_of_law')"
                                    >
                                        <option
                                            value=""
                                            @if ($lead->ordinance_of_law === "" || $lead->ordinance_of_law === null)
                                                <?php $oflSelected = 1; ?>
                                                {{ "selected" }}
                                            @endif
                                        >
                                            Select Ordinance of Law
                                        </option>
                                        <option
                                            value="0"
                                            @if ($lead->ordinance_of_law == 0 && $oflSelected == 0)
                                                <?php $oflSelected = 1; ?>
                                                {{ "selected" }}
                                            @endif
                                        >
                                            0%
                                        </option>
                                        <option
                                            value="1"
                                            @if ($lead->ordinance_of_law == 1)
                                                <?php $oflSelected = 1; ?>
                                                {{ "selected" }}
                                            @endif
                                        >
                                            1%
                                        </option>
                                        <option
                                            value="2.5"
                                            @if ($lead->ordinance_of_law == 2.5)
                                                <?php $oflSelected = 1; ?>
                                                {{ "selected" }}
                                            @endif
                                        >
                                            2.5%
                                        </option>
                                        <option
                                            value="3"
                                            @if ($lead->ordinance_of_law == 3)
                                                <?php $oflSelected = 1; ?>
                                                {{ "selected" }}
                                            @endif
                                        >
                                            3%
                                        </option>
                                        <option
                                            value="5"
                                            @if ($lead->ordinance_of_law == 5)
                                                <?php $oflSelected = 1; ?>
                                                {{ "selected" }}
                                            @endif
                                        >
                                            5%
                                        </option>
                                        <option
                                            value="10"
                                            @if ($lead->ordinance_of_law == 10)
                                                <?php $oflSelected = 1; ?>
                                                {{ "selected" }}
                                            @endif
                                        >
                                            10%
                                        </option>
                                        <option value="other" @if($oflSelected == 0) {{ 'selected' }} @endif>
                                            Others
                                        </option>
                                    </select>
                                </div>
                                <div class="form-group col-12 mb-2">
                                    <div
                                        id="ordinance_of_law_div"
                                        class="mt-2 otherInput"
                                        @if($oflSelected == 1) style="display:none;" @endif
                                    >
                                        <input
                                            placeholder="Other Ordinance of Law"
                                            class="form-control"
                                            name="ordinance_of_law-other"
                                            type="number"
                                            step="any"
                                            value="{{ $lead->ordinance_of_law }}"
                                            id="ordinance_of_law-other"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-2">
                            <div class="form-row">
                                <div class="form-group col-12 col-md-6 mb-0">
                                    <strong>T.I.V. Matches Appraisal:</strong>
                                    <!-- list will be shared -->
                                    {!!
                                        Form::select(
                                            "tiv_matches_appraisal",
                                            [
                                                "No" => "No",
                                                "Yes" => "Yes",
                                            ],
                                            isset($lead) ? $lead->tiv_matches_appraisal : "",
                                            ["id" => "tiv_matches_appraisal", "class" => "form-control px-1"],
                                        )
                                    !!}
                                </div>
                            </div>
                        </div>
                        <div class="mb-0">
                            <div class="form-row">
                                <div class="form-group col mb-0">
                                    <strong>Property Notes:</strong>
                                    {!!
                                        Form::textarea("property_insurance_coverage", null, [
                                            "placeholder" => "Property Notes",
                                            "class" => "form-control d-block",
                                            "id" => "property_insurance_coverage",
                                            "rows" => "4",
                                            "maxlength" => "1000",
                                        ])
                                    !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="accordion">
                    <h3 class="px-1 mt-2">General Liability</h3>
                    <div class="wrapper_content">
                        <div class="mb-2 form-group">
                            <div class="form-row">
                                <?php $glSelected = 0; ?>

                                <div class="form-group col mb-0">
                                    <select
                                        name="general_liability"
                                        id="general_liability"
                                        class="form-control input selectboxcarrier px-1"
                                        placeholder="Select Carrier"
                                        onchange="getSetOtherCarrierVal(this, 'general_liability')"
                                    >
                                        <option
                                            value=""
                                            @if ($lead->general_liability == "")
                                                <?php $glSelected = 1; ?>
                                                {{ "selected" }}
                                            @endif
                                        >
                                            Select Carrier
                                        </option>
                                        @foreach ($carriersWithGeneralLiability as $carrier)
                                            <option
                                                value="{{ $carrier->id }}"
                                                @if ($lead->general_liability == $carrier->id)
                                                    <?php $glSelected = 1; ?>
                                                    {{ "selected" }}
                                                @endif
                                            >
                                                {{ $carrier->name }}
                                            </option>
                                        @endforeach

                                        <option value="other" @if($glSelected == 0) {{ 'selected' }} @endif>
                                            Others
                                        </option>
                                    </select>
                                </div>
                                <div class="form-group col mb-0">
                                    {!!
                                        Form::select("GL_ren_month", $months, $lead->GL_ren_month, [
                                            "class" => "form-control multiple px-1",
                                            "id" => "GL_ren_month",
                                        ])
                                    !!}
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col mb-0">
                                    <div
                                        id="general_liability_div"
                                        class="mt-2 otherInput"
                                        @if($glSelected == 1) style="display:none;" @endif
                                    >
                                        <input
                                            placeholder="General Liability Carrier"
                                            class="form-control"
                                            name="general_liability-other"
                                            type="text"
                                            value="{{ ! empty($lead->glCarrier->name) && $glSelected == 0 ? $lead->glCarrier->name : "" }}"
                                            id="general_liability-other"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-2">
                            <div class="form-row">
                                <div class="form-group col mb-0">
                                    <strong>Expiring Premium:</strong>
                                    <div class="input-group">
                                        <span class="input-group-text rounded-right-0">$</span>
                                        {!!
                                            Form::number("gl_expiry_premium", null, [
                                                "placeholder" => "Expiring Premium ",
                                                "class" => "form-control rounded-left-0",
                                                "step" => "any",
                                                "aria-label" => 'Dollar amount (with dot and two
                                                                                    decimal places',
                                                "id" => "gl_expiry_premium",
                                                "oninput" => "restrictInput(this, 8)",
                                            ])
                                        !!}
                                    </div>
                                </div>
                                <div class="form-group col mb-0">
                                    <strong>Policy Renewal Date:</strong>
                                    {!!
                                        Form::date("gl_policy_renewal_date", null, ["class" => "form-control gl_policy_renewal_date", "id" => "gl_policy_renewal_date"])
                                    !!}
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-12 col-md-6 mb-2">
                                <strong>Carrier Rating:</strong>

                                <?php $glSelectedR = 0; ?>

                                <select
                                    name="gl_rating"
                                    class="form-control input selectboxcarrier px-1"
                                    placeholder="Select Rating"
                                    onchange="getSetOtherCarrierVal(this, 'gl_rating')"
                                    id="gl_rating"
                                >
                                    <option
                                        value=""
                                        @if ($lead->gl_rating == "")
                                            <?php $glSelectedR = 1; ?>
                                            {{ "selected" }}
                                        @endif
                                    >
                                        Select Rating
                                    </option>
                                    @foreach ($ratingsWithGeneralLiability as $rating)
                                        <option
                                            value="{{ $rating->id }}"
                                            @if ($lead->gl_rating == $rating->id)
                                                <?php $glSelectedR = 1; ?>
                                                {{ "selected" }}
                                            @endif
                                        >
                                            {{ $rating->name }}
                                        </option>
                                    @endforeach

                                    <option value="other" @if($glSelectedR == 0) {{ 'selected' }} @endif>
                                        Others
                                    </option>
                                </select>
                            </div>
                            <div class="form-group col-12 col-md-6 mb-2">
                                <strong>Price Per Unit:</strong>
                                <div class="input-group">
                                    <span class="input-group-text rounded-right-0">$</span>
                                    {!!
                                        Form::text("gl_price_per_unit", null, [
                                            "placeholder" => "Price Per Unit",
                                            "class" => "form-control rounded-left-0",
                                            "step" => "any",
                                            "id" => "gl_price_per_unit",
                                            "disabled" => "disabled",
                                        ])
                                    !!}
                                </div>
                            </div>
                            <div class="form-group col-12 mb-2">
                                <div
                                    id="gl_rating_div"
                                    class="mt-2 otherInput"
                                    @if($glSelectedR == 1) style="display:none;" @endif
                                >
                                    <input
                                        placeholder="Other General Liability Rating"
                                        class="form-control"
                                        name="gl_rating-other"
                                        type="text"
                                        value="{{ ! empty($lead->generaLiablityRating->name) && $glSelectedR == 0 ? $lead->generaLiablityRating->name : "" }}"
                                        id="gl_rating-other"
                                    />
                                </div>
                            </div>
                        </div>
                        <div class="mb-2">
                            <div class="form-row">
                                <div class="form-group col mb-0">
                                    <strong>Exclusions:</strong>
                                    {!!
                                        Form::select(
                                            "gl_exclusions[]",
                                            [
                                                "animal" => "Animal",
                                                "assault_battery" => "Assault and Battery",
                                                "cross_suit" => "Cross Suit",
                                                "insured_vs_insured" => "Insured Vs. Insured",
                                                "liquor" => "Liquor",
                                                "pool" => "Pool",
                                                "firearm" => "Firearm",
                                            ],
                                            isset($lead) ? explode(",", $lead->gl_exclusions) : [],
                                            ["id" => "gl_exclusions", "class" => "form-control  px-1", "multiple" => true, "size" => 1, "style" => "height: 2rem"],
                                        )
                                    !!}
                                </div>
                                <div class="form-group col mb-0">
                                    <strong>Other Exclusions:</strong>
                                    <input
                                        placeholder="Other Exclusions"
                                        id="gl_other_exclusions"
                                        class="form-control"
                                        name="gl_other_exclusions"
                                        type="text"
                                        value="{{ $lead->gl_other_exclusions }}"
                                    />
                                </div>
                            </div>
                        </div>
                        <div class="mb-0">
                            <div class="form-row">
                                <div class="form-group col mb-0">
                                    <strong>General Liability Notes:</strong>
                                    {!!
                                        Form::textarea("gl_insurance_coverage", null, [
                                            "placeholder" => "General Liability Notes",
                                            "class" => "form-control d-block",
                                            "id" => "gl_insurance_coverage",
                                            "rows" => "4",
                                            "maxlength" => "1000",
                                        ])
                                    !!}
                                </div>
                            </div>
                        </div>
                    </div>

                    <h3 class="mt-2 px-1">Crime Insurance</h3>
                    <div class="wrapper_content">
                        <div class="mb-2 form-group">
                            <div class="form-row">
                                <?php $ciSelected = 0; ?>

                                <div class="form-group col mb-0">
                                    <select
                                        name="crime_insurance"
                                        id="crime_insurance"
                                        class="form-control input selectboxcarrier px-1"
                                        placeholder="Select Carrier"
                                        onchange="getSetOtherCarrierVal(this, 'crime_insurance')"
                                    >
                                        <option
                                            value=""
                                            @if ($lead->crime_insurance == "")
                                                <?php $ciSelected = 1; ?>
                                                {{ "selected" }}
                                            @endif
                                        >
                                            Select Carrier
                                        </option>
                                        @foreach ($carriersWithCrimeInsurance as $carrier)
                                            <option
                                                value="{{ $carrier->id }}"
                                                @if ($lead->crime_insurance == $carrier->id)
                                                    <?php $ciSelected = 1; ?>
                                                    {{ "selected" }}
                                                @endif
                                            >
                                                {{ $carrier->name }}
                                            </option>
                                        @endforeach

                                        <option value="other" @if($ciSelected == 0) {{ 'selected' }} @endif>
                                            Others
                                        </option>
                                    </select>
                                </div>
                                <div class="form-group col mb-0">
                                    {!!
                                        Form::select("CI_ren_month", $months, $lead->CI_ren_month, [
                                            "class" => "form-control multiple px-1",
                                            "id" => "CI_ren_month",
                                        ])
                                    !!}
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col mb-0">
                                    <div
                                        id="crime_insurance_div"
                                        class="mt-2 otherInput"
                                        @if($ciSelected == 1) style="display:none;" @endif
                                    >
                                        <input
                                            placeholder="Crime Insurance Carrier"
                                            class="form-control"
                                            name="crime_insurance-other"
                                            type="text"
                                            value="{{ ! empty($lead->ciCarrier->name) && $ciSelected == 0 ? $lead->ciCarrier->name : "" }}"
                                            id="crime_insurance-other"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-2">
                            <div class="form-row">
                                <div class="form-group col mb-0">
                                    <strong>Expiring Premium:</strong>
                                    <div class="input-group">
                                        <span class="input-group-text rounded-right-0">$</span>
                                        {!!
                                            Form::number("ci_expiry_premium", null, [
                                                "placeholder" => "Expiring Premium ",
                                                "class" => "form-control rounded-left-0",
                                                "step" => "any",
                                                "aria-label" => 'Dollar amount (with dot and two
                                                                                    decimal places',
                                                "id" => "ci_expiry_premium",
                                                "oninput" => "restrictInput(this, 8)",
                                            ])
                                        !!}
                                    </div>
                                </div>
                                <div class="form-group col mb-0">
                                    <strong>Policy Renewal Date:</strong>
                                    {!!
                                        Form::date("ci_policy_renewal_date", null, ["class" => "form-control ci_policy_renewal_date", "id" => "ci_policy_renewal_date"])
                                    !!}
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-12 col-md-6 mb-2">
                                <strong>Rating :</strong>

                                <?php $ciSelectedR = 0; ?>

                                <select
                                    name="ci_rating"
                                    class="form-control input selectboxcarrier px-1"
                                    placeholder="Select Rating"
                                    onchange="getSetOtherCarrierVal(this, 'ci_rating')"
                                    id="ci_rating"
                                >
                                    <option
                                        value=""
                                        @if ($lead->ci_rating == "")
                                            <?php $ciSelectedR = 1; ?>
                                            {{ "selected" }}
                                        @endif
                                    >
                                        Select Rating
                                    </option>
                                    @foreach ($ratingsWithCrimeInsurance as $rating)
                                        <option
                                            value="{{ $rating->id }}"
                                            @if ($lead->ci_rating == $rating->id)
                                                <?php $ciSelectedR = 1; ?>
                                                {{ "selected" }}
                                            @endif
                                        >
                                            {{ $rating->name }}
                                        </option>
                                    @endforeach

                                    <option value="other" @if($ciSelectedR == 0) {{ 'selected' }} @endif>
                                        Others
                                    </option>
                                </select>
                            </div>
                            <div class="form-group col-12 col-md-6 mb-2">
                                <strong>Employee Theft:</strong>
                                {!!
                                    Form::number("employee_theft", null, [
                                        "placeholder" => "Employee Theft ",
                                        "id" => "employee_theft",
                                        "class" => "form-control rounded-left-0",
                                        "step" => "any",
                                        "aria-label" => "Dollar amount (with dot and two decimal places",
                                        "oninput" => "restrictInput(this, 7)",
                                    ])
                                !!}
                            </div>
                            <div class="form-group col-12 mb-2">
                                <div
                                    id="ci_rating_div"
                                    class="mt-2 otherInput"
                                    @if($ciSelectedR == 1) style="display:none;" @endif
                                >
                                    <input
                                        placeholder="Other Crime Insurance Rating"
                                        class="form-control"
                                        name="ci_rating-other"
                                        type="text"
                                        value="{{ ! empty($lead->crimeInsuranceRating->name) && $ciSelectedR == 0 ? $lead->crimeInsuranceRating->name : "" }}"
                                        id="ci_rating-other"
                                    />
                                </div>
                            </div>
                        </div>
                        <div class="mb-2">
                            <div class="form-row">
                                <div class="form-group col-12 col-md-6 mb-0">
                                    <strong>Operating Reserves:</strong>
                                    <!-- list will be shared -->
                                    {!!
                                        Form::number("operating_reserves", null, [
                                            "placeholder" => "Operating Reserves ",
                                            "id" => "operating_reserves",
                                            "class" => "form-control rounded-left-0",
                                            "step" => "any",
                                            "aria-label" => 'Dollar amount (with dot and two
                                                                            decimal places',
                                            "oninput" => "restrictInput(this, 7)",
                                        ])
                                    !!}
                                </div>
                            </div>
                        </div>
                        <div class="mb-0">
                            <div class="form-row">
                                <div class="form-group col mb-0">
                                    <strong>Crime Insurance Notes:</strong>
                                    {!!
                                        Form::textarea("ci_insurance_coverage", null, [
                                            "placeholder" => "Crime Insurance Notes",
                                            "class" => "form-control d-block",
                                            "id" => "ci_insurance_coverage",
                                            "rows" => "4",
                                            "maxlength" => "1000",
                                        ])
                                    !!}
                                </div>
                            </div>
                        </div>
                    </div>

                    <h3 class="mt-2 px-1">Directors & Officers</h3>
                    <div class="wrapper_content">
                        <div class="mb-2 form-group">
                            <div class="form-row">
                                <?php $doSelected = 0; ?>

                                <div class="form-group col mb-0">
                                    <select
                                        name="directors_officers"
                                        id="directors_officers"
                                        class="form-control input selectboxcarrier px-1"
                                        placeholder="Select Carrier"
                                        onchange="getSetOtherCarrierVal(this, 'directors_officers')"
                                    >
                                        <option
                                            value=""
                                            @if ($lead->directors_officers == "")
                                                <?php $doSelected = 1; ?>
                                                {{ "selected" }}
                                            @endif
                                        >
                                            Select Carrier
                                        </option>
                                        @foreach ($carriersWithDC as $carrier)
                                            <option
                                                value="{{ $carrier->id }}"
                                                @if ($lead->directors_officers == $carrier->id)
                                                    <?php $doSelected = 1; ?>
                                                    {{ "selected" }}
                                                @endif
                                            >
                                                {{ $carrier->name }}
                                            </option>
                                        @endforeach

                                        <option value="other" @if($doSelected == 0) {{ 'selected' }} @endif>
                                            Others
                                        </option>
                                    </select>
                                </div>
                                <div class="form-group col mb-0">
                                    {!!
                                        Form::select("DO_ren_month", $months, $lead->DO_ren_month, [
                                            "class" => "form-control multiple px-1",
                                            "id" => "DO_ren_month",
                                        ])
                                    !!}
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col mb-0">
                                    <div
                                        id="directors_officers_div"
                                        class="mt-2 otherInput"
                                        @if($doSelected == 1) style="display:none;" @endif
                                    >
                                        <input
                                            placeholder="Directors & Officers Carrier"
                                            class="form-control"
                                            name="directors_officers-other"
                                            type="text"
                                            value="{{ ! empty($lead->doCarrier->name) && $doSelected == 0 ? $lead->doCarrier->name : "" }}"
                                            id="directors_officers-other"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-2">
                            <div class="form-row">
                                <div class="form-group col mb-0">
                                    <strong>Expiring Premium:</strong>
                                    <div class="input-group">
                                        <span class="input-group-text rounded-right-0">$</span>
                                        {!!
                                            Form::number("do_expiry_premium", null, [
                                                "placeholder" => "Expiring Premium ",
                                                "class" => "form-control rounded-left-0",
                                                "step" => "any",
                                                "aria-label" => 'Dollar amount (with dot and two
                                                                                    decimal places',
                                                "id" => "do_expiry_premium",
                                                "oninput" => "restrictInput(this, 8)",
                                            ])
                                        !!}
                                    </div>
                                </div>
                                <div class="form-group col mb-0">
                                    <strong>Policy Renewal Date:</strong>
                                    {!!
                                        Form::date("do_policy_renewal_date", null, ["class" => "form-control do_policy_renewal_date", "id" => "do_policy_renewal_date"])
                                    !!}
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-12 col-md-6 mb-2">
                                <strong>Carrier Rating:</strong>

                                <?php $doSelectedR = 0; ?>

                                <select
                                    name="do_rating"
                                    class="form-control input selectboxcarrier px-1"
                                    placeholder="Select Rating"
                                    onchange="getSetOtherCarrierVal(this, 'do_rating')"
                                    id="do_rating"
                                >
                                    <option
                                        value=""
                                        @if ($lead->do_rating == "")
                                            <?php $doSelectedR = 1; ?>
                                            {{ "selected" }}
                                        @endif
                                    >
                                        Select Rating
                                    </option>
                                    @foreach ($ratingsWithDirectorOfficor as $rating)
                                        <option
                                            value="{{ $rating->id }}"
                                            @if ($lead->do_rating == $rating->id)
                                                <?php $doSelectedR = 1; ?>
                                                {{ "selected" }}
                                            @endif
                                        >
                                            {{ $rating->name }}
                                        </option>
                                    @endforeach

                                    <option value="other" @if($doSelectedR == 0) {{ 'selected' }} @endif>
                                        Others
                                    </option>
                                </select>
                            </div>
                            <div class="form-group col-12 col-md-6 mb-2">
                                <strong>Claims Made:</strong>
                                {!!
                                    Form::select(
                                        "claims_made",
                                        [
                                            "No" => "No",
                                            "Yes" => "Yes",
                                        ],
                                        isset($lead) ? $lead->claims_made : "",
                                        ["class" => "form-control px-1", "id" => "claims_made"],
                                    )
                                !!}
                            </div>
                            <div class="form-group col-12 mb-2">
                                <div
                                    id="do_rating_div"
                                    class="mt-2 otherInput"
                                    @if($doSelectedR == 1) style="display:none;" @endif
                                >
                                    <input
                                        placeholder="Other Directors & Officers Rating"
                                        class="form-control"
                                        name="do_rating-other"
                                        type="text"
                                        value="{{ ! empty($lead->directorOfficerRating->name) && $doSelectedR == 0 ? $lead->directorOfficerRating->name : "" }}"
                                        id="do_rating-other"
                                    />
                                </div>
                            </div>
                        </div>
                        <div class="mb-2">
                            <div class="form-row">
                                <div class="form-group col-12 col-md-6 mb-2">
                                    <strong>Pending Litigation:</strong>
                                    {!!
                                        Form::select(
                                            "pending_litigation",
                                            [
                                                "No" => "No",
                                                "Yes" => "Yes",
                                            ],
                                            isset($lead) ? $lead->pending_litigation : "",
                                            ["id" => "pending_litigation", "class" => "form-control px-1"],
                                        )
                                    !!}
                                </div>
                                <div class="form-group col-12 col-md-6 mb-2">
                                    <strong>Litigation Date:</strong>
                                    {!! Form::date("litigation_date", null, ["id" => "litigation_date", "class" => "form-control litigation_date"]) !!}
                                </div>
                            </div>
                        </div>
                        <div class="mb-0">
                            <div class="form-row">
                                <div class="form-group col mb-0">
                                    <strong>Directors & Officers Notes:</strong>
                                    {!!
                                        Form::textarea("do_insurance_coverage", null, [
                                            "placeholder" => "Directors & Officers Notes",
                                            "class" => "form-control d-block",
                                            "id" => "do_insurance_coverage",
                                            "rows" => "4",
                                            "maxlength" => "1000",
                                        ])
                                    !!}
                                </div>
                            </div>
                        </div>
                    </div>

                    <h3 class="mt-2 px-1">Umbrella</h3>
                    <div class="wrapper_content">
                        <div class="mb-2 form-group">
                            <div class="form-row">
                                <?php $uSelected = 0; ?>

                                <div class="form-group col mb-0">
                                    <select
                                        name="umbrella"
                                        id="umbrella"
                                        class="form-control input selectboxcarrier px-1"
                                        placeholder="Select Carrier"
                                        onchange="getSetOtherCarrierVal(this, 'umbrella')"
                                    >
                                        <option
                                            value=""
                                            @if ($lead->umbrella == "")
                                                <?php $uSelected = 1; ?>
                                                {{ "selected" }}
                                            @endif
                                        >
                                            Select Carrier
                                        </option>
                                        @foreach ($carriersWithUnbrella as $carrier)
                                            <option
                                                value="{{ $carrier->id }}"
                                                @if ($lead->umbrella == $carrier->id)
                                                    <?php $uSelected = 1; ?>
                                                    {{ "selected" }}
                                                @endif
                                            >
                                                {{ $carrier->name }}
                                            </option>
                                        @endforeach

                                        <option value="other" @if($uSelected == 0) {{ 'selected' }} @endif>
                                            Others
                                        </option>
                                    </select>
                                </div>
                                <div class="form-group col mb-0">
                                    {!!
                                        Form::select("U_ren_month", $months, $lead->U_ren_month, [
                                            "class" => "form-control multiple px-1",
                                            "id" => "U_ren_month",
                                        ])
                                    !!}
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col mb-0">
                                    <div
                                        id="umbrella_div"
                                        class="mt-2 otherInput"
                                        @if($uSelected == 1) style="display:none;" @endif
                                    >
                                        <input
                                            placeholder="Umbrella Carrier"
                                            class="form-control"
                                            name="umbrella-other"
                                            type="text"
                                            value="{{ ! empty($lead->umbrellaCarrier->name) && $uSelected == 0 ? $lead->umbrellaCarrier->name : "" }}"
                                            id="umbrella-other"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-2">
                            <div class="form-row">
                                <div class="form-group col mb-0">
                                    <strong>Expiring Premium:</strong>
                                    <div class="input-group">
                                        <span class="input-group-text rounded-right-0">$</span>
                                        {!!
                                            Form::number("umbrella_expiry_premium", null, [
                                                "placeholder" => "Expiring Premium",
                                                "class" => "form-control rounded-left-0",
                                                "step" => "any",
                                                "aria-label" => 'Dollar amount (with dot and two
                                                                                    decimal places',
                                                "id" => "umbrella_expiry_premium",
                                                "oninput" => "restrictInput(this, 8)",
                                            ])
                                        !!}
                                    </div>
                                </div>
                                <div class="form-group col mb-0">
                                    <strong>Policy Renewal Date:</strong>
                                    {!!
                                        Form::date("umbrella_policy_renewal_date", null, ["class" => "form-control umbrella_policy_renewal_date", "id" => "umbrella_policy_renewal_date"])
                                    !!}
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-12 col-md-6 mb-2">
                                <strong>Exclusions:</strong>
                                {!!
                                    Form::select(
                                        "umbrella_exclusions[]",
                                        [
                                            "animal" => "Animal",
                                            "assault_battery" => "Assault and Battery",
                                            "cross_suit" => "Cross Suit",
                                            "insured_vs_insured" => "Insured Vs. Insured",
                                            "liquor" => "Liquor",
                                            "pool" => "Pool",
                                            "firearm" => "Firearm",
                                        ],
                                        isset($lead) ? explode(",", $lead->umbrella_exclusions) : [],
                                        ["id" => "umbrella_exclusions", "class" => "form-control px-1", "multiple" => true, "size" => 1, "style" => "height: 2rem"],
                                    )
                                !!}
                            </div>
                            <div class="form-group col-12 col-md-6 mb-2">
                                <strong>Other Exclusions:</strong>
                                <input
                                    placeholder="Other Exclusions"
                                    id="umbrella_other_exclusions"
                                    class="form-control"
                                    name="umbrella_other_exclusions"
                                    type="text"
                                    value="{{ $lead->umbrella_other_exclusions }}"
                                />
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-12 col-md-6 mb-2">
                                <strong>Carrier Rating:</strong>

                                <?php $uSelectedR = 0; ?>

                                <select
                                    name="umbrella_rating"
                                    class="form-control input selectboxcarrier px-1"
                                    placeholder="Select Rating"
                                    onchange="getSetOtherCarrierVal(this, 'umbrella_rating')"
                                    id="umbrella_rating"
                                >
                                    <option
                                        value=""
                                        @if ($lead->umbrella_rating == "")
                                            <?php $uSelectedR = 1; ?>
                                            {{ "selected" }}
                                        @endif
                                    >
                                        Select Rating
                                    </option>
                                    @foreach ($ratingsWithUnbrella as $rating)
                                        <option
                                            value="{{ $rating->id }}"
                                            @if ($lead->umbrella_rating == $rating->id)
                                                <?php $uSelectedR = 1; ?>
                                                {{ "selected" }}
                                            @endif
                                        >
                                            {{ $rating->name }}
                                        </option>
                                    @endforeach

                                    <option value="other" @if($uSelectedR == 0) {{ 'selected' }} @endif>Others</option>
                                </select>
                            </div>
                            <div class="form-group col-12 col-md-6 mb-2">
                                <strong>Correct Underlying:</strong>
                                {!!
                                    Form::select(
                                        "correct_underlying",
                                        [
                                            "No" => "No",
                                            "Yes" => "Yes",
                                        ],
                                        isset($lead) ? $lead->correct_underlying : "",
                                        ["class" => "form-control px-1", "id" => "correct_underlying"],
                                    )
                                !!}
                            </div>
                            <div class="form-group col-12 mb-2">
                                <div
                                    id="umbrella_rating_div"
                                    class="mt-2 otherInput"
                                    @if($uSelectedR == 1) style="display:none;" @endif
                                >
                                    <input
                                        placeholder="Other Umbrella Rating"
                                        class="form-control"
                                        name="umbrella_rating-other"
                                        type="text"
                                        value="{{ ! empty($lead->uRating->name) && $uSelectedR == 0 ? $lead->uRating->name : "" }}"
                                        id="umbrella_rating-other"
                                    />
                                </div>
                            </div>
                        </div>
                        <div class="mb-0">
                            <div class="form-row">
                                <div class="form-group col mb-0">
                                    <strong>Umbrella Notes:</strong>
                                    {!!
                                        Form::textarea("u_insurance_coverage", null, [
                                            "placeholder" => "Umbrella Notes",
                                            "class" => "form-control d-block",
                                            "id" => "u_insurance_coverage",
                                            "rows" => "4",
                                            "maxlength" => "1000",
                                        ])
                                    !!}
                                </div>
                            </div>
                        </div>
                    </div>

                    <h3 class="mt-2 px-1">Workers Compensation</h3>
                    <div class="wrapper_content">
                        <div class="mb-2 form-group">
                            <div class="form-row">
                                <?php $wcSelected = 0; ?>

                                <div class="form-group col mb-0">
                                    <select
                                        name="workers_compensation"
                                        id="workers_compensation"
                                        class="form-control input selectboxcarrier px-1"
                                        placeholder="Select Carrier"
                                        onchange="getSetOtherCarrierVal(this, 'workers_compensation')"
                                    >
                                        <option
                                            value=""
                                            @if ($lead->workers_compensation == "")
                                                <?php $wcSelected = 1; ?>
                                                {{ "selected" }}
                                            @endif
                                        >
                                            Select Carrier
                                        </option>
                                        @foreach ($carriersWithWorkCompensation as $carrier)
                                            <option
                                                value="{{ $carrier->id }}"
                                                @if ($lead->workers_compensation == $carrier->id)
                                                    <?php $wcSelected = 1; ?>
                                                    {{ "selected" }}
                                                @endif
                                            >
                                                {{ $carrier->name }}
                                            </option>
                                        @endforeach

                                        <option value="other" @if($wcSelected == 0) {{ 'selected' }} @endif>
                                            Others
                                        </option>
                                    </select>
                                </div>
                                <div class="form-group col mb-0">
                                    {!!
                                        Form::select("WC_ren_month", $months, $lead->WC_ren_month, [
                                            "class" => "form-control multiple px-1",
                                            "id" => "WC_ren_month",
                                        ])
                                    !!}
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col mb-0">
                                    <div
                                        id="workers_compensation_div"
                                        class="mt-2 otherInput"
                                        @if($wcSelected == 1) style="display:none;" @endif
                                    >
                                        <input
                                            placeholder="Workers Compensation Carrier"
                                            class="form-control"
                                            name="workers_compensation-other"
                                            type="text"
                                            value="{{ ! empty($lead->wcCarrier->name) && $wcSelected == 0 ? $lead->wcCarrier->name : "" }}"
                                            id="workers_compensation-other"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-2">
                            <div class="form-row">
                                <div class="form-group col mb-0">
                                    <strong>Expiring Premium:</strong>
                                    <div class="input-group">
                                        <span class="input-group-text rounded-right-0">$</span>
                                        {!!
                                            Form::number("wc_expiry_premium", null, [
                                                "placeholder" => "Expiring Premium",
                                                "class" => "form-control rounded-left-0",
                                                "step" => "any",
                                                "aria-label" => 'Dollar amount (with dot and two
                                                                                    decimal places',
                                                "id" => "wc_expiry_premium",
                                                "oninput" => "restrictInput(this, 8)",
                                            ])
                                        !!}
                                    </div>
                                </div>
                                <div class="form-group col mb-0">
                                    <strong>Policy Renewal Date:</strong>
                                    {!!
                                        Form::date("wc_policy_renewal_date", null, ["class" => "form-control wc_policy_renewal_date", "id" => "wc_policy_renewal_date"])
                                    !!}
                                </div>
                            </div>
                        </div>
                        <div class="mb-2">
                            <div class="form-row">
                                <div class="form-group col-12 col-md-6 col-lg-4 mb-0">
                                    <strong>Carrier Rating:</strong>

                                    <?php $wcSelectedR = 0; ?>

                                    <select
                                        name="wc_rating"
                                        class="form-control input selectboxcarrier px-1"
                                        placeholder="Select Rating"
                                        onchange="getSetOtherCarrierVal(this, 'wc_rating')"
                                        id="wc_rating"
                                    >
                                        <option
                                            value=""
                                            @if ($lead->wc_rating == "")
                                                <?php $wcSelectedR = 1; ?>
                                                {{ "selected" }}
                                            @endif
                                        >
                                            Select Rating
                                        </option>
                                        @foreach ($ratingsWithWorkCompensation as $rating)
                                            <option
                                                value="{{ $rating->id }}"
                                                @if ($lead->wc_rating == $rating->id)
                                                    <?php $wcSelectedR = 1; ?>
                                                    {{ "selected" }}
                                                @endif
                                            >
                                                {{ $rating->name }}
                                            </option>
                                        @endforeach

                                        <option value="other" @if($wcSelectedR == 0) {{ 'selected' }} @endif>
                                            Others
                                        </option>
                                    </select>
                                </div>
                                <div class="form-group col-12 col-md-6 col-lg-4 mb-0">
                                    <strong>Employee Count:</strong>
                                    {!!
                                        Form::number("employee_count", null, [
                                            "placeholder" => "Enter Count ",
                                            "class" => "form-control rounded-left-0",
                                            "id" => "employee_count",
                                            "step" => "any",
                                            "oninput" => "restrictInput(this, 3)",
                                        ])
                                    !!}
                                </div>
                                <div class="form-group col-12 col-md-6 col-lg-4 mb-0">
                                    <strong>Employee Payroll:</strong>
                                    <div class="input-group">
                                        <span class="input-group-text rounded-right-0">$</span>
                                        {!!
                                            Form::number("employee_payroll", null, [
                                                "placeholder" => "Enter Payroll ",
                                                "class" => "form-control rounded-left-0",
                                                "id" => "employee_payroll",
                                                "oninput" => "restrictInput(this, 6)",
                                            ])
                                        !!}
                                    </div>
                                </div>
                                <div class="form-group col-12">
                                    <div
                                        id="wc_rating_div"
                                        class="mt-2 otherInput"
                                        @if($wcSelectedR == 1) style="display:none;" @endif
                                    >
                                        <input
                                            placeholder="Other Workers Compensation Rating"
                                            class="form-control"
                                            name="wc_rating-other"
                                            type="text"
                                            value="{{ ! empty($lead->workerCompansestionRating->name) && $wcSelectedR == 0 ? $lead->workerCompansestionRating->name : "" }}"
                                            id="wc_rating-other"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-0">
                            <div class="form-row">
                                <div class="form-group col mb-0">
                                    <strong>Workers Compensation Notes:</strong>
                                    {!!
                                        Form::textarea("wc_insurance_coverage", null, [
                                            "placeholder" => "Workers Compensation Notes",
                                            "class" => "form-control d-block",
                                            "id" => "wc_insurance_coverage",
                                            "rows" => "4",
                                            "maxlength" => "1000",
                                        ])
                                    !!}
                                </div>
                            </div>
                        </div>
                    </div>

                    <h3 class="mt-2 px-1">Flood</h3>
                    <div class="wrapper_content">
                        <div class="mb-2 form-group">
                            <div class="form-row">
                                <?php $fSelected = 0; ?>

                                <div class="form-group col mb-0">
                                    <select
                                        name="flood"
                                        id="flood"
                                        class="form-control input selectboxcarrier px-1"
                                        placeholder="Select Carrier"
                                        onchange="getSetOtherCarrierVal(this, 'flood')"
                                    >
                                        <option
                                            value=""
                                            @if ($lead->flood == "")
                                                <?php $fSelected = 1; ?>
                                                {{ "selected" }}
                                            @endif
                                        >
                                            Select Carrier
                                        </option>
                                        @foreach ($carriersWithFlood as $carrier)
                                            <option
                                                value="{{ $carrier->id }}"
                                                @if ($lead->flood == $carrier->id)
                                                    <?php $fSelected = 1; ?>
                                                    {{ "selected" }}
                                                @endif
                                            >
                                                {{ $carrier->name }}
                                            </option>
                                        @endforeach

                                        <option value="other" @if($fSelected == 0) {{ 'selected' }} @endif>
                                            Others
                                        </option>
                                    </select>
                                </div>
                                <div class="form-group col mb-0">
                                    {!!
                                        Form::select("F_ren_month", $months, $lead->F_ren_month, [
                                            "class" => "form-control multiple px-1",
                                            "id" => "F_ren_month",
                                        ])
                                    !!}
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col mb-0">
                                    <div
                                        id="flood_div"
                                        class="mt-2 otherInput"
                                        @if($fSelected == 1) style="display:none;" @endif
                                    >
                                        <input
                                            placeholder="Flood Carrier"
                                            class="form-control"
                                            name="flood-other"
                                            type="text"
                                            value="{{ ! empty($lead->floodCarrier->name) && $fSelected == 0 ? $lead->floodCarrier->name : "" }}"
                                            id="flood-other"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-2">
                            <div class="form-row">
                                <div class="form-group col mb-0">
                                    <strong>Expiring Premium:</strong>
                                    <div class="input-group">
                                        <span class="input-group-text rounded-right-0">$</span>
                                        {!!
                                            Form::number("flood_expiry_premium", null, [
                                                "placeholder" => "Expiring Premium",
                                                "class" => "form-control rounded-left-0",
                                                "step" => "any",
                                                "aria-label" => 'Dollar amount (with dot and two
                                                                                    decimal places',
                                                "id" => "flood_expiry_premium",
                                                "oninput" => "restrictInput(this, 8)",
                                            ])
                                        !!}
                                    </div>
                                </div>
                                <div class="form-group col mb-0">
                                    <strong>Policy Renewal Date:</strong>
                                    {!!
                                        Form::date("flood_policy_renewal_date", null, ["class" => "form-control flood_policy_renewal_date", "id" => "flood_policy_renewal_date"])
                                    !!}
                                </div>
                            </div>
                        </div>
                        <div class="mb-2">
                            <div class="form-row">
                                <div class="form-group col-12 col-md-6 col-lg-4 mb-0">
                                    <strong>Carrier Rating:</strong>

                                    <?php $fSelectedR = 0; ?>

                                    <select
                                        name="flood_rating"
                                        class="form-control input selectboxcarrier px-1"
                                        placeholder="Select Rating"
                                        onchange="getSetOtherCarrierVal(this, 'flood_rating')"
                                        id="flood_rating"
                                    >
                                        <option
                                            value=""
                                            @if ($lead->flood_rating == "")
                                                <?php $fSelectedR = 1; ?>
                                                {{ "selected" }}
                                            @endif
                                        >
                                            Select Rating
                                        </option>
                                        @foreach ($ratingsWithFlood as $rating)
                                            <option
                                                value="{{ $rating->id }}"
                                                @if ($lead->flood_rating == $rating->id)
                                                    <?php $fSelectedR = 1; ?>
                                                    {{ "selected" }}
                                                @endif
                                            >
                                                {{ $rating->name }}
                                            </option>
                                        @endforeach

                                        <option value="other" @if($fSelectedR == 0) {{ 'selected' }} @endif>
                                            Others
                                        </option>
                                    </select>
                                </div>
                                <div class="form-group col-12 col-md-6 col-lg-4 mb-0">
                                    <strong>Elevation Certificate:</strong>
                                    {!!
                                        Form::select(
                                            "elevation_certificate",
                                            [
                                                "No" => "No",
                                                "Yes" => "Yes",
                                            ],
                                            isset($lead) ? $lead->elevation_certificate : "",
                                            ["class" => "form-control px-1", "id" => "elevation_certificate"],
                                        )
                                    !!}
                                </div>
                                <div class="form-group col-12 col-md-6 col-lg-4 mb-0">
                                    <strong>Loma Letter :</strong>
                                    {!!
                                        Form::select(
                                            "loma_letter",
                                            [
                                                "No" => "No",
                                                "Yes" => "Yes",
                                            ],
                                            isset($lead) ? $lead->loma_letter : "",
                                            ["class" => "form-control px-1", "id" => "loma_letter"],
                                        )
                                    !!}
                                </div>
                                <div class="form-group col-12 mb-0">
                                    <div
                                        id="flood_rating_div"
                                        class="mt-2 otherInput"
                                        @if($fSelectedR == 1) style="display:none;" @endif
                                    >
                                        <input
                                            placeholder="Other Flood Rating"
                                            class="form-control"
                                            name="flood_rating-other"
                                            type="text"
                                            value="{{ ! empty($lead->fRating->name) && $fSelectedR == 0 ? $lead->fRating->name : "" }}"
                                            id="flood_rating-other"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-0">
                            <div class="form-row">
                                <div class="form-group col mb-0">
                                    <strong>Flood Notes:</strong>
                                    {!!
                                        Form::textarea("f_insurance_coverage", null, [
                                            "placeholder" => "Flood Notes",
                                            "class" => "form-control d-block",
                                            "id" => "f_insurance_coverage",
                                            "rows" => "4",
                                            "maxlength" => "1000",
                                        ])
                                    !!}
                                </div>
                            </div>
                        </div>
                    </div>

                    <h3 class="mt-2 px-1">Difference In Conditions</h3>
                    <div class="wrapper_content">
                        <div class="mb-2 form-group">
                            <div class="form-row">
                                <?php $dicSelected = 0; ?>

                                <div class="form-group col mb-0">
                                    <select
                                        name="difference_in_condition"
                                        id="difference_in_condition"
                                        class="form-control input selectboxcarrier px-1"
                                        placeholder="Select Carrier"
                                        onchange="getSetOtherCarrierVal(this, 'difference_in_condition')"
                                    >
                                        <option
                                            value=""
                                            @if ($lead->difference_in_condition == "")
                                                <?php $dicSelected = 1; ?>
                                                {{ "selected" }}
                                            @endif
                                        >
                                            Select Carrier
                                        </option>
                                        @foreach ($carriersWithDC as $carrier)
                                            <option
                                                value="{{ $carrier->id }}"
                                                @if ($lead->difference_in_condition == $carrier->id)
                                                    <?php $dicSelected = 1; ?>
                                                    {{ "selected" }}
                                                @endif
                                            >
                                                {{ $carrier->name }}
                                            </option>
                                        @endforeach

                                        <option value="other" @if($dicSelected == 0) {{ 'selected' }} @endif>
                                            Others
                                        </option>
                                    </select>
                                </div>
                                <div class="form-group col mb-0">
                                    {!!
                                        Form::select("dic_ren_month", $months, $lead->dic_ren_month, [
                                            "class" => "form-control multiple px-1",
                                            "id" => "dic_ren_month",
                                        ])
                                    !!}
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col mb-0">
                                    <div
                                        id="difference_in_condition_div"
                                        class="mt-2 otherInput"
                                        @if($dicSelected == 1) style="display:none;" @endif
                                    >
                                        <input
                                            placeholder="Difference In Conditions Carrier"
                                            class="form-control"
                                            name="difference_in_condition-other"
                                            type="text"
                                            value="{{ ! empty($lead->dcCarrier->name) && $dicSelected == 0 ? $lead->dcCarrier->name : "" }}"
                                            id="difference_in_condition-other"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-2">
                            <div class="form-row">
                                <div class="form-group col mb-0">
                                    <strong>Expiring Premium:</strong>
                                    <div class="input-group">
                                        <span class="input-group-text rounded-right-0">$</span>
                                        {!!
                                            Form::number("dic_expiry_premium", null, [
                                                "placeholder" => "Expiring Premium",
                                                "class" => "form-control rounded-left-0",
                                                "step" => "any",
                                                "aria-label" => 'Dollar amount (with dot and two
                                                                                    decimal places',
                                                "id" => "dic_expiry_premium",
                                                "oninput" => "restrictInput(this, 8)",
                                            ])
                                        !!}
                                    </div>
                                </div>
                                <div class="form-group col mb-0">
                                    <strong>Policy Renewal Date:</strong>
                                    {!!
                                        Form::date("dic_policy_renewal_date", null, ["class" => "form-control dic_policy_renewal_date", "id" => "dic_policy_renewal_date"])
                                    !!}
                                </div>
                            </div>
                        </div>
                        <div class="mb-2">
                            <div class="form-row">
                                <div class="form-group col-12 col-md-6 mb-2">
                                    <strong>Hurricane Deductible:</strong>
                                    <select
                                        id="dic_hurricane_deductible"
                                        class="form-control px-1"
                                        name="dic_hurricane_deductible"
                                    >
                                        <option value="">Select Hurricane Deductible</option>
                                        <option
                                            value="1"
                                            @if($lead->dic_hurricane_deductible == 1) {{ 'selected' }} @endif
                                        >
                                            1%
                                        </option>
                                        <option
                                            value="3"
                                            @if($lead->dic_hurricane_deductible == 3) {{ 'selected' }} @endif
                                        >
                                            3%
                                        </option>
                                        <option
                                            value="5"
                                            @if($lead->dic_hurricane_deductible == 5) {{ 'selected' }} @endif
                                        >
                                            5%
                                        </option>
                                        <option
                                            value="10"
                                            @if($lead->dic_hurricane_deductible == 10) {{ 'selected' }} @endif
                                        >
                                            10%
                                        </option>
                                    </select>
                                </div>
                                <div class="form-group col-12 col-md-6 mb-2">
                                    <strong>All Other Perils Deductible:</strong>
                                    <input
                                        placeholder="All Other Perils Deductible"
                                        class="form-control"
                                        name="dic_all_other_perils"
                                        type="text"
                                        value="{{ $lead->dic_all_other_perils }}"
                                        id="dic_all_other_perils"
                                    />
                                </div>
                            </div>
                        </div>
                        <div class="mb-0">
                            <div class="form-row">
                                <div class="form-group col mb-0">
                                    <strong>Difference In Conditions Notes:</strong>
                                    {!!
                                        Form::textarea("dic_insurance_coverage", null, [
                                            "placeholder" => "Difference In Conditions Notes",
                                            "class" => "form-control d-block",
                                            "id" => "dic_insurance_coverage",
                                            "rows" => "4",
                                            "maxlength" => "1000",
                                        ])
                                    !!}
                                </div>
                            </div>
                        </div>
                    </div>

                    <h3 class="mt-2 px-1">X-Wind</h3>
                    <div class="wrapper_content">
                        <div class="mb-2 form-group">
                            <div class="form-row">
                                <?php $xwSelected = 0; ?>

                                <div class="form-group col mb-0">
                                    <select
                                        name="x_wind"
                                        id="x_wind"
                                        class="form-control input selectboxcarrier px-1"
                                        placeholder="Select Carrier"
                                        onchange="getSetOtherCarrierVal(this, 'x_wind')"
                                    >
                                        <option
                                            value=""
                                            @if ($lead->x_wind == "")
                                                <?php $xwSelected = 1; ?>
                                                {{ "selected" }}
                                            @endif
                                        >
                                            Select Carrier
                                        </option>
                                        @foreach ($carriersWithXW as $carrier)
                                            <option
                                                value="{{ $carrier->id }}"
                                                @if ($lead->x_wind == $carrier->id)
                                                    <?php $xwSelected = 1; ?>
                                                    {{ "selected" }}
                                                @endif
                                            >
                                                {{ $carrier->name }}
                                            </option>
                                        @endforeach

                                        <option value="other" @if($xwSelected == 0) {{ 'selected' }} @endif>
                                            Others
                                        </option>
                                    </select>
                                </div>
                                <div class="form-group col mb-0">
                                    {!!
                                        Form::select("xw_ren_month", $months, $lead->xw_ren_month, [
                                            "class" => "form-control multiple px-1",
                                            "id" => "xw_ren_month",
                                        ])
                                    !!}
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col mb-0">
                                    <div
                                        id="x_wind_div"
                                        class="mt-2 otherInput"
                                        @if($xwSelected == 1) style="display:none;" @endif
                                    >
                                        <input
                                            placeholder="X-Wind Carrier"
                                            class="form-control"
                                            name="x_wind-other"
                                            type="text"
                                            value="{{ ! empty($lead->xwindCarrier->name) && $xwSelected == 0 ? $lead->xwindCarrier->name : "" }}"
                                            id="x_wind-other"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-2">
                            <div class="form-row">
                                <div class="form-group col mb-0">
                                    <strong>Expiring Premium:</strong>
                                    <div class="input-group">
                                        <span class="input-group-text rounded-right-0">$</span>
                                        {!!
                                            Form::number("xw_expiry_premium", null, [
                                                "placeholder" => "Expiring Premium",
                                                "class" => "form-control rounded-left-0",
                                                "step" => "any",
                                                "aria-label" => 'Dollar amount (with dot and two
                                                                                    decimal places',
                                                "id" => "xw_expiry_premium",
                                                "oninput" => "restrictInput(this, 8)",
                                            ])
                                        !!}
                                    </div>
                                </div>
                                <div class="form-group col mb-0">
                                    <strong>Policy Renewal Date:</strong>
                                    {!!
                                        Form::date("xw_policy_renewal_date", null, ["class" => "form-control xw_policy_renewal_date", "id" => "xw_policy_renewal_date"])
                                    !!}
                                </div>
                            </div>
                        </div>
                        <div class="mb-2">
                            <div class="form-row">
                                <div class="form-group col-12 col-md-6 mb-2">
                                    <strong>Hurricane Deductible:</strong>
                                    <select
                                        id="xw_hurricane_deductible"
                                        class="form-control px-1"
                                        name="xw_hurricane_deductible"
                                    >
                                        <option value="">Select Hurricane Deductible</option>
                                        <option
                                            value="1"
                                            @if($lead->xw_hurricane_deductible == 1) {{ 'selected' }} @endif
                                        >
                                            1%
                                        </option>
                                        <option
                                            value="3"
                                            @if($lead->xw_hurricane_deductible == 3) {{ 'selected' }} @endif
                                        >
                                            3%
                                        </option>
                                        <option
                                            value="5"
                                            @if($lead->xw_hurricane_deductible == 5) {{ 'selected' }} @endif
                                        >
                                            5%
                                        </option>
                                        <option
                                            value="10"
                                            @if($lead->xw_hurricane_deductible == 10) {{ 'selected' }} @endif
                                        >
                                            10%
                                        </option>
                                    </select>
                                </div>
                                <div class="form-group col-12 col-md-6 mb-2">
                                    <strong>All Other Perils Deductible:</strong>
                                    <input
                                        placeholder="All Other Perils Deductible"
                                        class="form-control"
                                        name="xw_all_other_perils"
                                        type="text"
                                        value="{{ $lead->xw_all_other_perils }}"
                                        id="xw_all_other_perils"
                                    />
                                </div>
                            </div>
                        </div>
                        <div class="mb-0">
                            <div class="form-row">
                                <div class="form-group col mb-0">
                                    <strong>X-Wind Notes:</strong>
                                    {!!
                                        Form::textarea("xw_insurance_coverage", null, [
                                            "placeholder" => "X-Wind Notes",
                                            "class" => "form-control d-block",
                                            "id" => "xw_insurance_coverage",
                                            "rows" => "4",
                                            "maxlength" => "1000",
                                        ])
                                    !!}
                                </div>
                            </div>
                        </div>
                    </div>

                    <h3 class="mt-2 px-1">Equipment Breakdown</h3>
                    <div class="wrapper_content">
                        <div class="mb-2 form-group">
                            <div class="form-row">
                                <?php $ebSelected = 0; ?>

                                <div class="form-group col mb-0">
                                    <select
                                        name="equipment_breakdown"
                                        id="equipment_breakdown"
                                        class="form-control input selectboxcarrier px-1"
                                        placeholder="Select Carrier"
                                        onchange="getSetOtherCarrierVal(this, 'equipment_breakdown')"
                                    >
                                        <option
                                            value=""
                                            @if ($lead->equipment_breakdown == "")
                                                <?php $ebSelected = 1; ?>
                                                {{ "selected" }}
                                            @endif
                                        >
                                            Select Carrier
                                        </option>
                                        @foreach ($carriersWithEB as $carrier)
                                            <option
                                                value="{{ $carrier->id }}"
                                                @if ($lead->equipment_breakdown == $carrier->id)
                                                    <?php $ebSelected = 1; ?>
                                                    {{ "selected" }}
                                                @endif
                                            >
                                                {{ $carrier->name }}
                                            </option>
                                        @endforeach

                                        <option value="other" @if($ebSelected == 0) {{ 'selected' }} @endif>
                                            Others
                                        </option>
                                    </select>
                                </div>
                                <div class="form-group col mb-0">
                                    {!!
                                        Form::select("eb_ren_month", $months, $lead->eb_ren_month, [
                                            "class" => "form-control multiple px-1",
                                            "id" => "eb_ren_month",
                                        ])
                                    !!}
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col mb-0">
                                    <div
                                        id="equipment_breakdown_div"
                                        class="mt-2 otherInput"
                                        @if($ebSelected == 1) style="display:none;" @endif
                                    >
                                        <input
                                            placeholder="Equipment Breakdown Carrier"
                                            class="form-control"
                                            name="equipment_breakdown-other"
                                            type="text"
                                            value="{{ ! empty($lead->ebCarrier->name) && $ebSelected == 0 ? $lead->ebCarrier->name : "" }}"
                                            id="equipment_breakdown-other"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-2">
                            <div class="form-row">
                                <div class="form-group col mb-0">
                                    <strong>Expiring Premium:</strong>
                                    <div class="input-group">
                                        <span class="input-group-text rounded-right-0">$</span>
                                        {!!
                                            Form::number("eb_expiry_premium", null, [
                                                "placeholder" => "Expiring Premium",
                                                "class" => "form-control rounded-left-0",
                                                "step" => "any",
                                                "aria-label" => 'Dollar amount (with dot and two
                                                                                    decimal places',
                                                "id" => "eb_expiry_premium",
                                                "oninput" => "restrictInput(this, 8)",
                                            ])
                                        !!}
                                    </div>
                                </div>
                                <div class="form-group col mb-0">
                                    <strong>Policy Renewal Date:</strong>
                                    {!!
                                        Form::date("eb_policy_renewal_date", null, ["class" => "form-control eb_policy_renewal_date", "id" => "eb_policy_renewal_date"])
                                    !!}
                                </div>
                            </div>
                        </div>
                        <div class="mb-2">
                            <div class="form-row">
                                <div class="form-group col-12 col-md-6 mb-2">
                                    <strong>Hurricane Deductible:</strong>
                                    <select
                                        id="eb_hurricane_deductible"
                                        class="form-control px-1"
                                        name="eb_hurricane_deductible"
                                    >
                                        <option value="">Select Hurricane Deductible</option>
                                        <option
                                            value="1"
                                            @if($lead->eb_hurricane_deductible == 1) {{ 'selected' }} @endif
                                        >
                                            1%
                                        </option>
                                        <option
                                            value="3"
                                            @if($lead->eb_hurricane_deductible == 3) {{ 'selected' }} @endif
                                        >
                                            3%
                                        </option>
                                        <option
                                            value="5"
                                            @if($lead->eb_hurricane_deductible == 5) {{ 'selected' }} @endif
                                        >
                                            5%
                                        </option>
                                        <option
                                            value="10"
                                            @if($lead->eb_hurricane_deductible == 10) {{ 'selected' }} @endif
                                        >
                                            10%
                                        </option>
                                    </select>
                                </div>
                                <div class="form-group col-12 col-md-6 mb-2">
                                    <strong>All Other Perils Deductible:</strong>
                                    <input
                                        placeholder="All Other Perils Deductible"
                                        class="form-control"
                                        name="eb_all_other_perils"
                                        type="text"
                                        value="{{ $lead->eb_all_other_perils }}"
                                        id="eb_all_other_perils"
                                    />
                                </div>
                            </div>
                        </div>
                        <div class="mb-0">
                            <div class="form-row">
                                <div class="form-group col mb-0">
                                    <strong>Equipment Breakdown Notes:</strong>
                                    {!!
                                        Form::textarea("eb_insurance_coverage", null, [
                                            "placeholder" => "Equipment Breakdown Notes",
                                            "class" => "form-control d-block",
                                            "id" => "eb_insurance_coverage",
                                            "rows" => "4",
                                            "maxlength" => "1000",
                                        ])
                                    !!}
                                </div>
                            </div>
                        </div>
                    </div>

                    <h3 class="mt-2 px-1">Commercial AutoMobile</h3>
                    <div class="wrapper_content">
                        <div class="mb-2 form-group">
                            <div class="form-row">
                                <?php $caSelected = 0; ?>

                                <div class="form-group col mb-0">
                                    <select
                                        name="commercial_automobiles"
                                        id="commercial_automobiles"
                                        class="form-control input selectboxcarrier px-1"
                                        placeholder="Select Carrier"
                                        onchange="getSetOtherCarrierVal(this, 'commercial_automobiles')"
                                    >
                                        <option
                                            value=""
                                            @if ($lead->commercial_automobiles == "")
                                                <?php $caSelected = 1; ?>
                                                {{ "selected" }}
                                            @endif
                                        >
                                            Select Carrier
                                        </option>
                                        @foreach ($carriersWithCA as $carrier)
                                            <option
                                                value="{{ $carrier->id }}"
                                                @if ($lead->commercial_automobiles == $carrier->id)
                                                    <?php $caSelected = 1; ?>
                                                    {{ "selected" }}
                                                @endif
                                            >
                                                {{ $carrier->name }}
                                            </option>
                                        @endforeach

                                        <option value="other" @if($caSelected == 0) {{ 'selected' }} @endif>
                                            Others
                                        </option>
                                    </select>
                                </div>
                                <div class="form-group col mb-0">
                                    {!!
                                        Form::select("ca_ren_month", $months, $lead->ca_ren_month, [
                                            "class" => "form-control multiple px-1",
                                            "id" => "ca_ren_month",
                                        ])
                                    !!}
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col mb-0">
                                    <div
                                        id="commercial_automobiles_div"
                                        class="mt-2 otherInput"
                                        @if($caSelected == 1) style="display:none;" @endif
                                    >
                                        <input
                                            placeholder="Commercial AutoMobile Carrier"
                                            class="form-control"
                                            name="commercial_automobiles-other"
                                            type="text"
                                            value="{{ ! empty($lead->caCarrier->name) && $caSelected == 0 ? $lead->caCarrier->name : "" }}"
                                            id="commercial_automobiles-other"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-2">
                            <div class="form-row">
                                <div class="form-group col mb-0">
                                    <strong>Expiring Premium:</strong>
                                    <div class="input-group">
                                        <span class="input-group-text rounded-right-0">$</span>
                                        {!!
                                            Form::number("ca_expiry_premium", null, [
                                                "placeholder" => "Expiring Premium",
                                                "class" => "form-control rounded-left-0",
                                                "step" => "any",
                                                "aria-label" => 'Dollar amount (with dot and two
                                                                                    decimal places',
                                                "id" => "ca_expiry_premium",
                                                "oninput" => "restrictInput(this, 8)",
                                            ])
                                        !!}
                                    </div>
                                </div>
                                <div class="form-group col mb-0">
                                    <strong>Policy Renewal Date:</strong>
                                    {!!
                                        Form::date("ca_policy_renewal_date", null, ["class" => "form-control ca_policy_renewal_date", "id" => "ca_policy_renewal_date"])
                                    !!}
                                </div>
                            </div>
                        </div>
                        <div class="mb-2">
                            <div class="form-row">
                                <div class="form-group col-12 col-md-6 mb-2">
                                    <strong>Hurricane Deductible:</strong>
                                    <select
                                        id="ca_hurricane_deductible"
                                        class="form-control px-1"
                                        name="ca_hurricane_deductible"
                                    >
                                        <option value="">Select Hurricane Deductible</option>
                                        <option
                                            value="1"
                                            @if($lead->ca_hurricane_deductible == 1) {{ 'selected' }} @endif
                                        >
                                            1%
                                        </option>
                                        <option
                                            value="3"
                                            @if($lead->ca_hurricane_deductible == 3) {{ 'selected' }} @endif
                                        >
                                            3%
                                        </option>
                                        <option
                                            value="5"
                                            @if($lead->ca_hurricane_deductible == 5) {{ 'selected' }} @endif
                                        >
                                            5%
                                        </option>
                                        <option
                                            value="10"
                                            @if($lead->ca_hurricane_deductible == 10) {{ 'selected' }} @endif
                                        >
                                            10%
                                        </option>
                                    </select>
                                </div>
                                <div class="form-group col-12 col-md-6 mb-2">
                                    <strong>All Other Perils Deductible:</strong>
                                    <input
                                        placeholder="All Other Perils Deductible"
                                        class="form-control"
                                        name="ca_all_other_perils"
                                        type="text"
                                        value="{{ $lead->ca_all_other_perils }}"
                                        id="ca_all_other_perils"
                                    />
                                </div>
                            </div>
                        </div>
                        <div class="mb-0">
                            <div class="form-row">
                                <div class="form-group col mb-0">
                                    <strong>Commercial AutoMobile Notes:</strong>
                                    {!!
                                        Form::textarea("ca_insurance_coverage", null, [
                                            "placeholder" => "Commercial AutoMobiles  Notes",
                                            "class" => "form-control d-block",
                                            "id" => "ca_insurance_coverage",
                                            "rows" => "4",
                                            "maxlength" => "1000",
                                        ])
                                    !!}
                                </div>
                            </div>
                        </div>
                    </div>

                    <h3 class="mt-2 px-1">Marina</h3>
                    <div class="wrapper_content">
                        <div class="mb-2 form-group">
                            <div class="form-row">
                                <?php $mSelected = 0; ?>

                                <div class="form-group col mb-0">
                                    <select
                                        name="marina"
                                        id="marina"
                                        class="form-control input selectboxcarrier px-1"
                                        placeholder="Select Carrier"
                                        onchange="getSetOtherCarrierVal(this, 'marina')"
                                    >
                                        <option
                                            value=""
                                            @if ($lead->marina == "")
                                                <?php $mSelected = 1; ?>
                                                {{ "selected" }}
                                            @endif
                                        >
                                            Select Carrier
                                        </option>
                                        @foreach ($carriersWithMarina as $carrier)
                                            <option
                                                value="{{ $carrier->id }}"
                                                @if ($lead->marina == $carrier->id)
                                                    <?php $mSelected = 1; ?>
                                                    {{ "selected" }}
                                                @endif
                                            >
                                                {{ $carrier->name }}
                                            </option>
                                        @endforeach

                                        <option value="other" @if($mSelected == 0) {{ 'selected' }} @endif>
                                            Others
                                        </option>
                                    </select>
                                </div>
                                <div class="form-group col mb-0">
                                    {!!
                                        Form::select("m_ren_month", $months, $lead->m_ren_month, [
                                            "class" => "form-control multiple px-1",
                                            "id" => "m_ren_month",
                                        ])
                                    !!}
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col mb-0">
                                    <div
                                        id="marina_div"
                                        class="mt-2 otherInput"
                                        @if($mSelected == 1) style="display:none;" @endif
                                    >
                                        <input
                                            placeholder="Marina Carrier"
                                            class="form-control"
                                            name="marina-other"
                                            type="text"
                                            value="{{ ! empty($lead->marinaCarrier->name) && $mSelected == 0 ? $lead->marinaCarrier->name : "" }}"
                                            id="marina-other"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-2">
                            <div class="form-row">
                                <div class="form-group col mb-0">
                                    <strong>Expiring Premium:</strong>
                                    <div class="input-group">
                                        <span class="input-group-text rounded-right-0">$</span>
                                        {!!
                                            Form::number("m_expiry_premium", null, [
                                                "placeholder" => "Expiring Premium",
                                                "class" => "form-control rounded-left-0",
                                                "step" => "any",
                                                "aria-label" => 'Dollar amount (with dot and two
                                                                                    decimal places',
                                                "id" => "m_expiry_premium",
                                                "oninput" => "restrictInput(this, 8)",
                                            ])
                                        !!}
                                    </div>
                                </div>
                                <div class="form-group col mb-0">
                                    <strong>Policy Renewal Date:</strong>
                                    {!!
                                        Form::date("m_policy_renewal_date", null, ["class" => "form-control m_policy_renewal_date", "id" => "m_policy_renewal_date"])
                                    !!}
                                </div>
                            </div>
                        </div>
                        <div class="mb-2">
                            <div class="form-row">
                                <div class="form-group col-12 col-md-6 mb-2">
                                    <strong>Hurricane Deductible:</strong>
                                    <select
                                        id="m_hurricane_deductible"
                                        class="form-control px-1"
                                        name="m_hurricane_deductible"
                                    >
                                        <option value="">Select Hurricane Deductible</option>
                                        <option
                                            value="1"
                                            @if($lead->m_hurricane_deductible == 1) {{ 'selected' }} @endif
                                        >
                                            1%
                                        </option>
                                        <option
                                            value="3"
                                            @if($lead->m_hurricane_deductible == 3) {{ 'selected' }} @endif
                                        >
                                            3%
                                        </option>
                                        <option
                                            value="5"
                                            @if($lead->m_hurricane_deductible == 5) {{ 'selected' }} @endif
                                        >
                                            5%
                                        </option>
                                        <option
                                            value="10"
                                            @if($lead->m_hurricane_deductible == 10) {{ 'selected' }} @endif
                                        >
                                            10%
                                        </option>
                                    </select>
                                </div>
                                <div class="form-group col-12 col-md-6 mb-2">
                                    <strong>All Other Perils Deductible:</strong>
                                    <input
                                        placeholder="All Other Perils Deductible"
                                        class="form-control"
                                        name="m_all_other_perils"
                                        type="text"
                                        value="{{ $lead->m_all_other_perils }}"
                                        id="m_all_other_perils"
                                    />
                                </div>
                            </div>
                        </div>
                        <div class="mb-0">
                            <div class="form-row">
                                <div class="form-group col mb-0">
                                    <strong>Marina Notes:</strong>
                                    {!!
                                        Form::textarea("m_insurance_coverage", null, [
                                            "placeholder" => "Marina Notes",
                                            "class" => "form-control d-block",
                                            "id" => "m_insurance_coverage",
                                            "rows" => "4",
                                            "maxlength" => "1000",
                                        ])
                                    !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="additonal_accordion">
                    <?php $iloop = 0; ?>

                    @foreach ($additonalPolicy as $adpolicy)
                        <h3 class="px-1 mt-2 position-relative addition_policy_selection_{{ $iloop }}">
                            <span class="addition_policy_h3_{{ $iloop }}">
                                Additional Policy ({{ $adpolicy->policy_type }})
                            </span>
                            <button
                                type="button"
                                class="close_area border-0 position-absolute end-5"
                                data-id="{{ $iloop }}"
                            >
                                <i class="fa fa-trash"></i>
                            </button>
                        </h3>
                        <div
                            class="wrapper_content additional_policy addition_policy_selection_{{ $iloop }}"
                            data-id="{{ $iloop }}"
                        >
                            <div class="form-row">
                                <div class="form-group col-12 col-md-6 mb-2">
                                    <strong>Policy Type:</strong>
                                    <select
                                        id="policy_type{{ $iloop }}"
                                        class="form-control px-1"
                                        name="policy_type[]"
                                        onchange="getPolicyBasedCarrier(this, 'carrier{{ $iloop }}', '{{ $iloop }}')"
                                        id="carrier{{ $iloop }}"
                                    >
                                        <option value="">Select Policy Type</option>
                                        @foreach ($additionalPoliciesCarrier as $key => $policy)
                                            <option
                                                value="{{ $key }}"
                                                @if($adpolicy->policy_type == $key) {{ 'selected' }} @endif
                                            >
                                                {{ $key }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-12 col-md-6 mb-2">
                                    <input
                                        value="{{ $adpolicy->id }}"
                                        name="policy_id[]"
                                        type="hidden"
                                        id="policy_id{{ $iloop }}"
                                    />
                                    <strong>Carrier:</strong>

                                    <?php $pSelected = 0; ?>

                                    <select
                                        name="carrier[]"
                                        class="form-control input selectboxcarrier px-1"
                                        placeholder="Select Carrier"
                                        onchange="getSetOtherCarrierVal(this, 'carrier{{ $iloop }}')"
                                        id="carrier{{ $iloop }}"
                                    >
                                        <option
                                            value=""
                                            @if ($adpolicy->carrier == "")
                                                <?php $pSelected = 1; ?>
                                                {{ "selected" }}
                                            @endif
                                        >
                                            Select Carrier
                                        </option>
                                        <option value="other" @if($pSelected == 0) {{ 'selected' }} @endif>
                                            Others
                                        </option>
                                    </select>
                                </div>
                                <div class="form-group col-12 mb-2">
                                    <div
                                        id="carrier{{ $iloop }}_div"
                                        class="mt-2 otherInput"
                                        @if($pSelected == 1) style="display:none;" @endif
                                    >
                                        <input
                                            placeholder="Other Additional Policy Carrier"
                                            class="form-control"
                                            name="carrier{{ $iloop }}-other"
                                            type="text"
                                            value="{{ ! empty($adpolicy->listCarrier->name) ? $adpolicy->listCarrier->name : "" }}"
                                            id="carrier{{ $iloop }}-other"
                                        />
                                    </div>
                                </div>
                                <script>
                                    document.addEventListener('DOMContentLoaded', function () {
                                        let selectElem = document.getElementById('policy_type{{ $iloop }}');
                                        if (selectElem) {
                                            getPolicyBasedCarrier(
                                                selectElem,
                                                'carrier{{ $iloop }}',
                                                '{{ $iloop }}',
                                                '{{ $adpolicy->carrier }}',
                                            );
                                        }
                                    });
                                </script>
                            </div>
                            <div class="mb-2">
                                <div class="form-row">
                                    <div class="form-group col mb-0">
                                        <strong>Expiring Premium:</strong>
                                        <div class="input-group">
                                            <span class="input-group-text rounded-right-0">$</span>
                                            <input
                                                placeholder="Expiring Premium"
                                                class="form-control expiry_premium_input"
                                                name="a_expiry_premium[]"
                                                type="number"
                                                value="{{ $adpolicy->expiry_premium }}"
                                                id="a_expiry_premium{{ $iloop }}"
                                                oninput="restrictInput(this, 8)"
                                            />
                                        </div>
                                    </div>
                                    <div class="form-group col mb-0">
                                        <strong>Policy Renewal Date:</strong>
                                        <input
                                            placeholder="Policy Renewal Date"
                                            class="form-control"
                                            name="a_policy_renewal_date[]"
                                            type="date"
                                            value="{{ $adpolicy->policy_renewal_date }}"
                                            id="a_policy_renewal_date{{ $iloop }}"
                                        />
                                    </div>
                                </div>
                            </div>
                            <div class="mb-2">
                                <div class="form-row">
                                    <div class="form-group col-12 col-md-6 mb-2">
                                        <strong>Hurricane Deductible:</strong>
                                        <select
                                            id="a_hurricane_deductible{{ $iloop }}"
                                            class="form-control px-1"
                                            name="a_hurricane_deductible[]"
                                        >
                                            <option value="">Select Hurricane Deductible</option>
                                            <option
                                                value="1"
                                                @if($adpolicy->hurricane_deductible == 1) {{ 'selected' }} @endif
                                            >
                                                1%
                                            </option>
                                            <option
                                                value="3"
                                                @if($adpolicy->hurricane_deductible == 3) {{ 'selected' }} @endif
                                            >
                                                3%
                                            </option>
                                            <option
                                                value="5"
                                                @if($adpolicy->hurricane_deductible == 5) {{ 'selected' }} @endif
                                            >
                                                5%
                                            </option>
                                            <option
                                                value="10"
                                                @if($adpolicy->hurricane_deductible == 10) {{ 'selected' }} @endif
                                            >
                                                10%
                                            </option>
                                        </select>
                                    </div>
                                    <div class="form-group col-12 col-md-6 mb-2">
                                        <strong>All Other Perils Deductible:</strong>
                                        <input
                                            placeholder="All Other Perils Deductible"
                                            class="form-control"
                                            name="a_all_other_perils[]"
                                            type="text"
                                            value="{{ $adpolicy->all_other_perils }}"
                                            id="a_all_other_perils{{ $iloop }}"
                                        />
                                    </div>
                                </div>
                            </div>
                            <div class="mb-0">
                                <div class="form-row">
                                    <div class="form-group col mb-0">
                                        <strong>Notes:</strong>
                                        <textarea
                                            placeholder="Notes"
                                            class="form-control d-block"
                                            id="insurance_coverage{{ $iloop }}"
                                            rows="4"
                                            maxlength="1000"
                                            name="insurance_coverage[]"
                                            cols="50"
                                        >
{{ $adpolicy->insurance_coverage }}</textarea
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php $iloop++; ?>
                    @endforeach
                </div>

                <button class="btn btn-sm btn-primary mt-2 add_additional_policy cursor-pointer">
                    <i class="fa fa-plus"></i>
                    Add Policy
                </button>

                <div class="my-2">
                    <div class="form-row">
                        <div class="form-group col-12 mb-0">
                            <strong class="text-success">Total Premium:</strong>
                            <div class="input-group">
                                <span class="input-group-text rounded-right-0">$</span>
                                {!!
                                    Form::text("total_premium", null, [
                                        "placeholder" => "Sum of All Premium",
                                        "class" => "form-control rounded-left-0",
                                        "step" => "any",
                                        "aria-label" => "Dollar amount (with dot and two decimal places)",
                                        "id" => "total_premium_sum",
                                        "disabled" => "disabled",
                                    ])
                                !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@push("scripts")
    <script src="{{ asset("js/custom-helper.js") }}"></script>
    <script>
        $( "#property_accordion" ).accordion({
              heightStyle: "content",
              collapsible: true,
              active: false
            });
            $( "#accordion" ).accordion({
              heightStyle: "content",
              collapsible: true,
              active: false
            });
            $( "#additonal_accordion" ).accordion({
              heightStyle: "content",
              collapsible: true,
              active: false
            });

            var countOfEntry = parseInt('{{ $iloop }}');

            $(".add_additional_policy").on("click", function (e) {
                e.preventDefault();
                var maxEntry = parseInt("{{ count($additionalPoliciesCarrier) }}");
                var lengthEntry = parseInt($(".additional_policy").length) || 0;
                if (lengthEntry >= maxEntry) {
                    toastr.error(`You can't add more than ${maxEntry} additional policies.`);
                    return;
                }

                var newPolicyHtml = `
                    <h3 class="px-1 mt-2 position-relative addition_policy_selection_${countOfEntry}" >
                        <span class="addition_policy_h3_${countOfEntry}">Additional Policy ${countOfEntry + 1}</span>
                        <button type="button" class="close_area border-0 position-absolute end-5" data-id="${countOfEntry}">
                            <i class="fa fa-trash"></i>
                        </button>
                    </h3>
                <div class="wrapper_content additional_policy addition_policy_selection_${countOfEntry}" data-id="${countOfEntry}">
                    <div class="form-row">
                        <div class="form-group col-12 col-md-6 mb-2">
                            <strong>Policy Type: </strong>
                            <select id="policy_type${countOfEntry}" class="form-control px-1" name="policy_type[]"
                                    onchange="getPolicyBasedCarrier(this,'carrier${countOfEntry}','${countOfEntry}')"
                                    id="carrier${countOfEntry}">
                                <option value="">Select Policy Type</option>
                                @foreach($additionalPoliciesCarrier as $key => $policy)
                                    <option value="{{ $key }}" >{{ $key }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-12 col-md-6 mb-2">
                            <strong>Carrier:</strong>
                            <input  name="policy_id[]" type="hidden" id="policy_id${countOfEntry}">
                            <select name="carrier[]" class="form-control input selectboxcarrier px-1"
                                    placeholder="Select Carrier"
                                    onchange="getSetOtherCarrierVal(this,'carrier${countOfEntry}')"
                                    id="carrier${countOfEntry}">
                                <option value="">Select Carrier</option>
                            </select>
                        </div>
                        <div class="form-group col-12 mb-2">
                            <div id="carrier${countOfEntry}_div" class="mt-2 otherInput" style="display:none;">
                                <input placeholder="Other Additional Policy Carrier" class="form-control"
                                       name="carrier${countOfEntry}-other" type="text" id="carrier${countOfEntry}-other">
                            </div>
                        </div>
                    </div>
                    <div class="mb-2">
                        <div class="form-row">
                            <div class="form-group col mb-0">
                                <strong>Expiring Premium: </strong>
                                <div class="input-group">
                                    <span class="input-group-text rounded-right-0">$</span>
                                    <input placeholder="Expiring Premium" class="form-control expiry_premium_input"
                                           name="a_expiry_premium[]" type="number"
                                           id="a_expiry_premium${countOfEntry}" oninput="restrictInput(this, 8)">
                                </div>
                            </div>
                            <div class="form-group col mb-0">
                                <strong>Policy Renewal Date: </strong>
                                <input placeholder="Policy Renewal Date" class="form-control" name="a_policy_renewal_date[]"
                                       type="date" id="a_policy_renewal_date${countOfEntry}">
                            </div>
                        </div>
                    </div>
                    <div class="mb-2">
                        <div class="form-row">
                            <div class="form-group col-12 col-md-6 mb-2">
                                <strong> Hurricane Deductible:</strong>
                                <select id="a_hurricane_deductible${countOfEntry}" class="form-control px-1"
                                        name="a_hurricane_deductible[]" >
                                    <option value="">Select Hurricane Deductible</option>
                                    <option value="1">1%</option>
                                    <option value="3">3%</option>
                                    <option value="5">5%</option>
                                    <option value="10">10%</option>
                                </select>
                            </div>
                            <div class="form-group col-12 col-md-6 mb-2">
                                <strong>All Other Perils Deductible: </strong>
                                <input placeholder="All Other Perils Deductible" class="form-control"
                                       name="a_all_other_perils[]" type="text" id="a_all_other_perils${countOfEntry}">
                            </div>
                        </div>
                    </div>
                    <div class="mb-0">
                        <div class="form-row">
                            <div class="form-group col mb-0">
                                <strong>Notes:</strong>
                                <textarea placeholder="Notes" class="form-control d-block" id="insurance_coverage${countOfEntry}"
                                          rows="4" maxlength="1000" name="insurance_coverage[]" cols="50"></textarea>
                            </div>
                        </div>
                    </div>
                </div>`;

                $("#additonal_accordion").append(newPolicyHtml);
                $("#additonal_accordion").accordion("refresh");
                countOfEntry++;
            });

            $(document).on("click", ".close_area",function (e) {
                e.preventDefault();
                const elementId = $(this).data("id");
                $(".addition_policy_selection_"+elementId).remove();
                totalpermiumsum();
            });


            document.addEventListener('DOMContentLoaded', function () {
                const glExclusions = document.getElementById('gl_exclusions');
                const glChoices = new Choices(glExclusions, {
                    removeItemButton: true,
                    placeholder: true,
                    placeholderValue: 'Select Exclusions'
                });

                const umbrellaExclusions = document.getElementById('umbrella_exclusions');
                const uChoices = new Choices(umbrellaExclusions, {
                    removeItemButton: true,
                    placeholder: true,
                    placeholderValue: 'Select Exclusions'
                });
            });

            function getSetOtherVal(elem) {
                const inputContainer = $(elem).siblings('.otherInput');
                const input = $(elem).siblings('.otherInput').find('input');
                $(elem).find('option[value="other"]').addClass('other');

                if ($(elem).val() == $(elem).find('.other').val()) {
                    $(inputContainer).fadeIn(500);
                } else {
                    $(inputContainer).fadeOut(500);
                }
                $(input).on('keyup', function() {
                    console.log($(input).val());
                    $(elem).find('.other').attr('value', $(input).val());
                });
            }

            function getSetOtherCarrierVal(elem, targetElement) {
                if (elem.value == "other") {
                    $("#"+targetElement+"_div").show();
                } else {
                    $("#"+targetElement+"_div").hide();
                }
            }

            function getPolicyBasedCarrier(elem, targetElement, elementId, value) {
                $.ajax({
                    type: 'GET',
                    url: "{{ url('/leads/carrierList') }}",
                    data: {
                        name: elem.value
                    },
                    success: function(response) {
                        updateCarrierDropdown(targetElement, response.carriers, value);
                        if (elem.value == "") {
                            const el = document.querySelector(".addition_policy_h3_" + elementId);
                            if (el) {
                                el.textContent = `Additional Policy ${elementId + 1}`;
                            }
                        } else {
                            const el = document.querySelector(".addition_policy_h3_" + elementId);
                            if (el) {
                                el.textContent = `Additional Policy (${elem.options[elem.selectedIndex].text})`;
                            }
                        }
                    },
                    error: function(error) {
                    }
                });
            }

            function updateCarrierDropdown(targetElement, carriers, value) {
                const selectBox = $("#" + targetElement);
                $("#"+targetElement+"_div").hide();
                selectBox.empty();
                selectBox.append('<option value="">Select Carrier</option>');

                let isMatchFound = false;

                carriers.forEach(function(carrier) {
                    var isSelected = carrier.id == value ? 'selected' : '';
                    if (isSelected) {
                        isMatchFound = true;
                    }
                    selectBox.append(`<option value="${carrier.id}" ${isSelected}>${carrier.name}</option>`);
                });

                var otherSelected = (!isMatchFound && value !== "" ? 'selected' : '');
                selectBox.append(`<option value="other" ${otherSelected}>Others</option>`);
                if (otherSelected) {
                    $("#"+targetElement+"_div").show();
                } else {
                    $("#"+targetElement+"-other").val('');
                }
            }


            $(document).on('blur','#total_square_footage',function (){
                pricepersquarefootcalculation($('#total_square_footage').val(), $('#insured_amount').val());
            });

            $(document).on('blur','#insured_amount',function (){
                pricepersquarefootcalculation($('#total_square_footage').val(), $('#insured_amount').val());
            });

            $(document).on('blur','#gl_expiry_premium',function (){
                priceperunitcalculation($('#gl_expiry_premium').val(), $('#unit_count').val());
            });

            $(document).on('blur','#unit_count',function (){
                priceperunitcalculation($('#gl_expiry_premium').val(), $('#unit_count').val());
            });


            function pricepersquarefootcalculation(totalSquare, totalInsured) {
                if (totalSquare == '' || totalInsured == '') {
                    $("#price_per_sqft").val('');
                } else {
                    totalSquare = parseFloat(totalSquare);
                    totalInsured = parseFloat(totalInsured);
                    const pricePerSqFt = (totalInsured / totalSquare).toFixed(2);
                    document.getElementById('price_per_sqft').value = formatUSNumberJs(pricePerSqFt);
                    const previewEl = document.getElementById('price_per_sqft_preview');
                    if (previewEl) {
                        previewEl.textContent = '$' + formatUSNumberJs(pricePerSqFt);
                    }
                }
            }

            function priceperunitcalculation(expiryPremium, totalUnits) {
                if (expiryPremium == '' || totalUnits == '') {
                    $("#gl_price_per_unit").val('');
                } else {
                    expiryPremium = parseFloat(expiryPremium);
                    totalUnits = parseFloat(totalUnits);
                    const pricePerUnit = (expiryPremium / totalUnits).toFixed(2);
                    document.getElementById('gl_price_per_unit').value = formatUSNumberJs(pricePerUnit);
                    const previewEl = document.getElementById('gl_price_per_unit_preview');
                    if (previewEl) {
                        previewEl.textContent = '$' + formatUSNumberJs(pricePerUnit);
                    }
                }
            }

            function totalpermiumsum() {
                let sum = 0;
                const premiumFields = [
                    "premium", "gl_expiry_premium", "ci_expiry_premium",
                    "do_expiry_premium", "umbrella_expiry_premium", "wc_expiry_premium",
                    "flood_expiry_premium", "dic_expiry_premium", "xw_expiry_premium",
                    "eb_expiry_premium", "ca_expiry_premium", "m_expiry_premium"
                ];

                premiumFields.forEach(function(id) {
                    const value = parseFloat($("#" + id).val()) || 0;
                    sum += value;
                });

                $(".additional_policy").each(function(index) {
                    const value = parseFloat($("#a_expiry_premium" + index).val()) || 0;
                    sum += value;
                });

                document.getElementById('total_premium_sum').value = formatUSNumberJs(sum);
                const previewEl = document.getElementById('total_premium_sum_preview');
                if (previewEl) {
                    previewEl.textContent = '$' + formatUSNumberJs(sum);
                }
            }


            const inputIds = [
                '#premium',
                '#gl_expiry_premium',
                '#ci_expiry_premium',
                '#do_expiry_premium',
                '#umbrella_expiry_premium',
                '#wc_expiry_premium',
                '#flood_expiry_premium',
                '#dic_expiry_premium',
                '#xw_expiry_premium',
                '#eb_expiry_premium',
                '#ca_expiry_premium',
                '#m_expiry_premium',
                '.expiry_premium_input'
            ];

            $(document).on('blur', inputIds.join(', '), totalpermiumsum);

            pricepersquarefootcalculation("{{ $lead->total_square_footage }}", "{{ $lead->insured_amount }}");
            priceperunitcalculation("{{ $lead->gl_expiry_premium }}", "{{ $lead->unit_count }}");

            totalpermiumsum();
    </script>
@endpush
