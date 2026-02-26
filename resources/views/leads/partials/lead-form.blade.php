<div class="card-body lead-update p-0">
    @if(isset($lead))
    <div class="form-group">
        <div class="custom-control custom-switch">
            <input type="checkbox" class="custom-control-input"
                {{ $lead && $lead->is_client == 1 ? 'checked' : '' }}
                name="is_client" value="1" id="clientSwitch_{{ $lead ? $lead->id : 0 }}">
            <label class="custom-control-label" for="clientSwitch_{{ $lead ? $lead->id : 0 }}">
                Current Client
            </label>
        </div>
    </div>
    @endif

    {{-- Business Name --}}
    <div class="form-group">
        <strong>Business Name<sup class="mandatoryClass">*</sup>:</strong>
        {!! Form::text('name', null, ['placeholder' => 'Business Name', 'class' => 'form-control']) !!}
    </div>

    {{-- Business Type and Year Built --}}
    <div class="mb-2">
        <div class="form-row">
            <div class="form-group col mb-0">
                <strong>Business Type<sup class="mandatoryClass">*</sup>:</strong>
                {!! Form::select('type', [
                    '' => 'Select Type',
                    'Condo' => 'Condo',
                    'HOA' => 'HOA',
                    'Commercial' => 'Commercial',
                    'Co-Op' => 'Co-Op',
                ], isset($lead) ? $lead->type : [], ['class' => 'form-control multiple']) !!}
            </div>
            <div class="form-group col mb-0">
                <strong>Year Built:</strong>
                {!! Form::date('creation_date', null, [
                    'class' => 'form-control businessCreationDate thisYearLimitRestriction'
                ]) !!}
            </div>
        </div>
    </div>

    {{-- Address Fields --}}
    <div class="form-group">
        <strong>Business Address 1<sup class="mandatoryClass">*</sup>:</strong>
        {!! Form::text('address1', null, [
            'placeholder' => 'Business Address - must start with a number',
            'class' => 'form-control',
            'pattern' => '^\d[0-9a-zA-Z\s\/#,._-:]*$',
            'title' => 'Chars allowed: # . - _ ,'
        ]) !!}
    </div>
    <div class="form-group">
        <strong>Business Address 2:</strong>
        {!! Form::text('address2', null, ['placeholder' => 'Business Address2', 'class' => 'form-control']) !!}
    </div>

    {{-- City and State --}}
    <div class="mb-2">
        <div class="form-row">
            <div class="form-group col mb-0">
                <strong>City<sup class="mandatoryClass">*</sup>:</strong>
                {!! Form::text('city', null, ['placeholder' => 'City', 'class' => 'form-control']) !!}
            </div>
            <div class="form-group col mb-0">
                <strong>State:</strong>
                {!! Form::select('state', $states, isset($lead) ? $lead->state : 'FL', [
                    'class' => 'form-control multiple USstates'
                ]) !!}
            </div>
        </div>
    </div>

    {{-- Zip and County --}}
    <div class="mb-2">
        <div class="form-row">
            <div class="form-group col mb-0">
                <strong>Zip<sup class="mandatoryClass">*</sup>:</strong>
                {!! Form::text('zip', null, [
                    'placeholder' => 'Zip - 5 digits',
                    'class' => 'form-control integer-only',
                    'maxlength' => '5'
                ]) !!}
            </div>
            <div class="form-group col mb-0">
                <strong>County:</strong>
                {!! Form::select('county', isset($lead) ? array_merge([$lead->county => $lead->county], $counties) : $counties,
                    isset($lead) ? $lead->county : [], [
                        'class' => 'form-control multiple',
                        'onchange' => 'getSetOtherVal(this)'
                    ]
                ) !!}
                <div id="countyOther" class="mt-2 otherInput" style="display:none;text-transform: lowercase;">
                    <input placeholder="Other County" class="form-control capitalize" name="county-other" type="text">
                </div>
            </div>
        </div>
    </div>

    {{-- Coastal and Unit Count --}}
    <div class="mb-2">
        <div class="form-row">
            <div class="form-group col mb-0">
                <strong>Coastal / Non Coastal:</strong>
                <select class="form-control multiple" name="coastal">
                    <option value="0" {{ empty($lead->coastal) ? 'selected' : '' }}>Non Coastal</option>
                    <option value="1" {{ !empty($lead->coastal) ? 'selected' : '' }}>Coastal</option>
                </select>
            </div>
            <div class="form-group col mb-0">
                <strong>Business Unit Count:</strong>
                {!! Form::number('unit_count', null, [
                    'placeholder' => 'Unit Count - max 4 digits',
                    'class' => 'form-control',
                    'max' => 9999
                ]) !!}
            </div>
        </div>
    </div>

    {{-- TIV and Square Footage --}}
    <div class="mb-2">
        <div class="form-row">
            <div class="form-group col mb-0">
                <strong>Total Insured Value:</strong>
                <div class="input-group">
                    <span class="input-group-text rounded-right-0">$</span>
                    {!! Form::number('business_tiv', null, [
                        'id' => 'business_tiv',
                        'placeholder' => 'T.I.V.',
                        'class' => 'form-control rounded-left-0',
                        'step' => 'any',
                        'oninput' => 'restrictInput(this, 10)'
                    ]) !!}
                </div>
            </div>
            <div class="form-group col mb-0">
                <strong>Total Square Footage:</strong>
                {!! Form::number('total_square_footage', null, [
                    'id' => 'total_square_footage',
                    'placeholder' => 'Square Footage - max 7 digits',
                    'class' => 'form-control',
                    'max' => 9999999
                ]) !!}
            </div>
        </div>
    </div>

    {{-- Appraiser Info --}}
    <div class="mb-2">
        <div class="form-row">
            <div class="form-group col mb-0">
                <strong>Appraiser Name:</strong>
                <input placeholder="Appraiser Name" class="form-control" name="appraisal_name" type="text"
                    value="" id="appraisal_name">
            </div>
            <div class="form-group col mb-0">
                <strong>Appraisal Company:</strong>
                <input placeholder="Appraisal Company" class="form-control" name="appraisal_company" type="text"
                    value="" id="appraisal_company">
            </div>
        </div>
    </div>

    {{-- Appraisal Date and Flood Zone --}}
    <div class="mb-2">
        <div class="form-row">
            <div class="form-group col mb-0">
                <strong>Appraisal Date:</strong>
                {!! Form::date('appraisal_date', null, [
                    'id' => 'appraisal_date',
                    'class' => 'form-control appraisal_date'
                ]) !!}
            </div>
            <div class="form-group col mb-0">
                @include('leads.partials.form-yes-no', [
                    'name' => 'ins_flood',
                    'label' => 'Flood Zone',
                    'selected' => $lead->ins_flood ?? 'No',
                    'id' => 'ins_flood',
                    'class' => 'form-control'
                ])
            </div>
        </div>
    </div>

    {{-- Property Floors and Pool --}}
    <div class="mb-2">
        <div class="form-row">
            <div class="form-group col mb-0">
                <strong>Property Floors:</strong>
                {!! Form::select('prop_floor', ['' => 'Select any option'] + array_combine(range(1, 100), range(1, 100)),
                    isset($lead) ? $lead->prop_floor : [], ['class' => 'form-control', 'id' => 'prop_floor']) !!}
            </div>
            @include('leads.partials.form-yes-no', [
                'name' => 'pool',
                'label' => 'Pool',
                'selected' => $lead->pool ?? 'No',
                'id' => 'pool',
                'class' => 'form-control'
            ])
        </div>
    </div>

    {{-- Lakes and Clubhouse --}}
    <div class="mb-2">
        <div class="form-row">
            @include('leads.partials.form-yes-no', [
                'name' => 'lakes',
                'label' => 'Lakes',
                'selected' => $lead->lakes ?? 'No',
                'id' => 'lakes',
                'class' => 'form-control'
            ])
            @include('leads.partials.form-yes-no', [
                'name' => 'clubhouse',
                'label' => 'Clubhouse',
                'selected' => $lead->clubhouse ?? 'No',
                'id' => 'clubhouse',
                'class' => 'form-control'
            ])
        </div>
    </div>

    {{-- Tennis/Basketball and ISO --}}
    <div class="mb-2">
        <div class="form-row">
            @include('leads.partials.form-yes-no', [
                'name' => 'tennis_basketball',
                'label' => 'Tennis/Basketball Court',
                'selected' => $lead->tennis_basketball ?? 'No',
                'id' => 'tennis_basketball',
                'class' => 'form-control'
            ])
            <div class="form-group col mb-0">
                <strong>ISO:</strong>
                {!! Form::number('iso', null, [
                    'placeholder' => 'ISO - max 2 digits',
                    'class' => 'form-control',
                    'id' => 'iso',
                    'max' => 99,
                    'oninput' => 'restrictInput(this, 2)'
                ]) !!}
            </div>
        </div>
    </div>

    {{-- Lead Source --}}
    <div class="mb-2">
        <div class="form-row">
            <div class="form-group col mb-0">
                <strong>Lead Source:</strong>
                <select name="lead_source" id="lead_source" class="form-control input selectboxcarrier"
                    placeholder="Select Lead Source">
                    <option value="">Select Lead Source</option>
                    @foreach($leadSource as $lsource)
                        <option value="{{ $lsource->id }}">{{ $lsource->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- Community Info Section (Only shown when lead exists) --}}
    @if(isset($lead))
    <div class="card card-secondary mt-4 mb-0">
        <div class="card-header">
            <h3 class="card-title">Community Info:</h3>
        </div>
        <div class="card-body p-2 p-lg-3">
            {{-- Community Yes/No Fields --}}
            <div class="mb-2">
                <div class="form-row">
                    @include('leads.partials.form-yes-no', [
                        'name' => 'pool_community',
                        'label' => 'Pool',
                        'selected' => $lead->pool ?? 'No',
                        'class' => 'form-control'
                    ])
                    @include('leads.partials.form-yes-no', [
                        'name' => 'lakes_community',
                        'label' => 'Lakes',
                        'selected' => $lead->lakes ?? 'No',
                        'class' => 'form-control'
                    ])
                </div>
            </div>
            <div class="mb-2">
                <div class="form-row">
                    @include('leads.partials.form-yes-no', [
                        'name' => 'clubhouse_community',
                        'label' => 'Clubhouse',
                        'selected' => $lead->clubhouse ?? 'No',
                        'class' => 'form-control'
                    ])
                    @include('leads.partials.form-yes-no', [
                        'name' => 'tennis_basketball_community',
                        'label' => 'Tennis/Basketball Court',
                        'selected' => $lead->tennis_basketball ?? 'No',
                        'class' => 'form-control'
                    ])
                </div>
            </div>
            <div class="mb-2">
                <div class="form-row">
                    <div class="form-group col mb-0">
                        <strong>Other Community Info:</strong>
                        <input placeholder="Other Community Info" class="form-control" name="other_community_info"
                            type="text" value="{{ $lead->other_community_info ?? '' }}">
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Insurance Information Section --}}
    @if(isset($lead))
    <div class="card card-secondary mt-4 mb-0">
        <div class="card-header">
            <h3 class="card-title">Prospect's Insurance Information</h3>
        </div>
        <div class="card-body p-2 p-lg-3">
            {{-- Renewal Date and Month --}}
            <div class="mb-2">
                <div class="form-row">
                    <div class="form-group col mb-0">
                        <strong>Property Insurance Renewal Date:</strong>
                        {!! Form::date('renewal_date', null, [
                            'class' => 'form-control businessRenewalDate',
                            'onchange' => 'synchronizeRenMonth(this)'
                        ]) !!}
                    </div>
                    <div class="form-group col mb-0">
                        <strong>Property Insurance Renewal Month:</strong>
                        {!! Form::select('renewal_month', $months, isset($lead) ? $lead->renewal_month : [], [
                            'class' => 'form-control multiple businessRenewalMonth'
                        ]) !!}
                    </div>
                </div>
            </div>

            {{-- Premium and Premium Year --}}
            <div class="form-group">
                <div class="row align-items-end">
                    @include('leads.partials.form-premium', [
                        'name' => 'premium',
                        'label' => 'Property Insurance Expiring Premium',
                        'id' => 'premium',
                        'colClass' => 'col-6'
                    ])
                    <div class="col-6">
                        <strong>Expiring Premium Year:</strong>
                        {!! Form::select('premium_year', $years, isset($lead) ? $lead->premium_year : [], [
                            'class' => 'form-control multiple premium_year'
                        ]) !!}
                    </div>
                </div>
            </div>

            {{-- Insured Amount and Year --}}
            <div class="form-group">
                <div class="row">
                    @include('leads.partials.form-premium', [
                        'name' => 'insured_amount',
                        'label' => 'Total Insured Value',
                        'id' => 'insured_amount',
                        'colClass' => 'col-6'
                    ])
                    <div class="col-6">
                        <strong>Total Insured Value - YEAR:</strong>
                        {!! Form::select('insured_year', $years, isset($lead) ? $lead->insured_year : [], [
                            'class' => 'form-control multiple insured_year'
                        ]) !!}
                    </div>
                </div>
            </div>

            {{-- Price Per SqFt and Appraiser --}}
            <div class="mb-2">
                <div class="form-row">
                    <div class="form-group col mb-0">
                        <strong>Price Per SqFt:</strong>
                        <input type="text" disabled name="price_per_sqft" id="price_per_sqft" class="form-control">
                    </div>
                    <div class="form-group col mb-0">
                        <strong>Appraiser Name:</strong>
                        <input placeholder="Appraiser Name" class="form-control" name="appraisal_name"
                            type="text" value="{{ $lead->appraisal_name ?? '' }}">
                    </div>
                </div>
            </div>

            {{-- Appraisal Company and Date --}}
            <div class="mb-2">
                <div class="form-row">
                    <div class="form-group col mb-0">
                        <strong>Appraisal Company:</strong>
                        <input placeholder="Appraisal Company" class="form-control" name="appraisal_company"
                            type="text" value="{{ $lead->appraisal_company ?? '' }}">
                    </div>
                    <div class="form-group col mb-0">
                        <strong>Appraisal Date:</strong>
                        {!! Form::date('appraisal_date', null, ['class' => 'form-control appraisal_date']) !!}
                    </div>
                </div>
            </div>

            {{-- Incumbent Agency and Agent --}}
            <div class="mb-2">
                <div class="form-row">
                    <div class="form-group col mb-0">
                        <strong>Incumbent Agency:</strong>
                        <input placeholder="Incumbent Agency" class="form-control" name="incumbent_agency"
                            type="text" value="{{ $lead->incumbent_agency ?? '' }}">
                    </div>
                    <div class="form-group col mb-0">
                        <strong>Incumbent Agent:</strong>
                        <input placeholder="Incumbent Agent" class="form-control" name="incumbent_agent"
                            type="text" value="{{ $lead->incumbent_agent ?? '' }}">
                    </div>
                </div>
            </div>

            {{-- Policy Renewal and Wind Mitigation Dates --}}
            <div class="mb-2">
                <div class="form-row">
                    <div class="form-group col mb-0">
                        <strong>Policy Renewal Date:</strong>
                        {!! Form::date('policy_renewal_date', null, ['class' => 'form-control policy_renewal_date']) !!}
                    </div>
                    <div class="form-group col mb-0">
                        <strong>Wind Mitigation Date:</strong>
                        {!! Form::date('wind_mitigation_date', null, ['class' => 'form-control wind_mitigation_date']) !!}
                    </div>
                </div>
            </div>

            {{-- Rating and Hurricane Deductible --}}
            <div class="mb-2">
                <div class="form-row">
                    <div class="form-group col mb-0">
                        <strong>Rating:</strong>
                        {!! Form::select('rating', ['' => 'Select Rating'], isset($lead) ? $lead->rating : '', [
                            'class' => 'form-control'
                        ]) !!}
                    </div>
                    <div class="form-group col mb-0">
                        <strong>Hurricane Deductible:</strong>
                        {!! Form::select('hurricane_deductible', [
                            '' => 'Select Hurricane Deductible',
                            '1' => '1%', '3' => '3%', '5' => '5%', '10' => '10%'
                        ], isset($lead) ? $lead->hurricane_deductible : '', ['class' => 'form-control']) !!}
                    </div>
                </div>
            </div>

            {{-- Hurricane Deductible Occurrence and Sinkhole --}}
            <div class="mb-2">
                <div class="form-row">
                    <div class="form-group col mb-0">
                        <strong>Hurricane Deductible (Per Occ or Per Year):</strong>
                        {!! Form::select('hurricane_deductible_occurrence', [
                            '' => 'Select Occurrence',
                            'Per Occurrence' => 'Per Occurrence',
                            'Per Year' => 'Per Year'
                        ], isset($lead) ? $lead->hurricane_deductible_occurrence : '', ['class' => 'form-control']) !!}
                    </div>
                    @include('leads.partials.form-yes-no', [
                        'name' => 'skin_hole',
                        'label' => 'Sinkhole',
                        'selected' => $lead->skin_hole ?? '',
                        'class' => 'form-control'
                    ])
                </div>
            </div>

            {{-- All Other Perils and Ordinance of Law --}}
            <div class="mb-2">
                <div class="form-row">
                    <div class="form-group col mb-0">
                        <strong>All Other Perils:</strong>
                        {!! Form::select('all_other_perils', [
                            '' => 'Select All other Perils',
                            '1' => '1%', '3' => '3%', '5' => '5%', '10' => '10%'
                        ], isset($lead) ? $lead->all_other_perils : '', ['class' => 'form-control']) !!}
                    </div>
                    <div class="form-group col mb-0">
                        <strong>Ordinance of Law:</strong>
                        {!! Form::select('ordinance_of_law', [
                            '' => 'Select Ordinance of Law',
                            '1' => '1%', '3' => '3%', '5' => '5%', '10' => '10%'
                        ], isset($lead) ? $lead->ordinance_of_law : '', ['class' => 'form-control']) !!}
                    </div>
                </div>
            </div>

            {{-- TIV Matches and Secondary Water Insurance --}}
            <div class="mb-2">
                <div class="form-row">
                    @include('leads.partials.form-yes-no', [
                        'name' => 'tiv_matches_appraisal',
                        'label' => 'T.I.V. Matches Appraisal',
                        'selected' => $lead->tiv_matches_appraisal ?? '',
                        'class' => 'form-control'
                    ])
                    @include('leads.partials.form-yes-no', [
                        'name' => 'secondary_water_insurance',
                        'label' => 'Secondary Water Insurance',
                        'selected' => $lead->secondary_water_insurance ?? '',
                        'class' => 'form-control'
                    ])
                </div>
            </div>

            {{-- Opening Protection --}}
            <div class="mb-2">
                <div class="form-row">
                    @include('leads.partials.form-yes-no', [
                        'name' => 'opening_protection',
                        'label' => 'Opening Protection',
                        'selected' => $lead->opening_protection ?? '',
                        'class' => 'form-control'
                    ])
                </div>
            </div>

            {{-- Property Insurance Carrier --}}
            <div class="form-group">
                <strong>Property Insurance Carrier:</strong>
                {!! Form::select('ins_prop_carrier',
                    isset($lead) ? array_merge([$lead->ins_prop_carrier => $lead->ins_prop_carrier], $leadsinsurrance) : $leadsinsurrance,
                    isset($lead) ? $lead->ins_prop_carrier : [], [
                        'class' => 'form-control multiple',
                        'onchange' => 'getSetOtherVal(this)'
                    ]
                ) !!}
                <div id="inssuranceOther" class="mt-2 otherInput" style="display:none">
                    <input placeholder="Other Insurance Property Carrier" class="form-control" name="insurrance-other" type="text">
                </div>
            </div>

            {{-- Carrier Renewal Month --}}
            <div class="form-group">
                <strong>Property Insurance Carrier Renewal Month:</strong>
                {!! Form::select('renewal_carrier_month', $months, isset($lead) ? $lead->renewal_carrier_month : [], [
                    'class' => 'form-control multiple businessRenewalCarrierMonth'
                ]) !!}
            </div>

            {{-- General Liability Section --}}
            @include('leads.partials.insurance-section', [
                'title' => 'General Liability',
                'type' => 'general_liability',
                'carriers' => $carriersWithGeneralLiability ?? collect(),
                'selected' => $lead->general_liability ?? '',
                'renewalMonth' => $lead->GL_ren_month ?? '',
                'expiryPremium' => $lead->gl_expiry_premium ?? '',
                'policyDate' => $lead->gl_policy_renewal_date ?? '',
                'rating' => $lead->gl_rating ?? '',
                'exclusions' => $lead->gl_exclusions ?? '',
                'otherExclusions' => $lead->gl_other_exclusions ?? '',
                'pricePerUnit' => $lead->gl_price_per_unit ?? ''
            ])

            {{-- Crime Insurance Section --}}
            @include('leads.partials.insurance-section', [
                'title' => 'Crime Insurance',
                'type' => 'crime_insurance',
                'carriers' => $carriersWithCrimeInsurance ?? collect(),
                'selected' => $lead->crime_insurance ?? '',
                'renewalMonth' => $lead->CI_ren_month ?? '',
                'expiryPremium' => $lead->ci_expiry_premium ?? '',
                'policyDate' => $lead->ci_policy_renewal_date ?? '',
                'rating' => $lead->ci_rating ?? '',
                'employeeTheft' => $lead->employee_theft ?? '',
                'operatingReserves' => $lead->operating_reserves ?? '',
                'pendingLitigation' => $lead->pending_litigation ?? ''
            ])

            {{-- Directors & Officers Section --}}
            @include('leads.partials.insurance-section', [
                'title' => 'Directors & Officers',
                'type' => 'directors_officers',
                'carriers' => $carriersWithDirectorOfficor ?? collect(),
                'selected' => $lead->directors_officers ?? '',
                'renewalMonth' => $lead->DO_ren_month ?? '',
                'expiryPremium' => $lead->do_expiry_premium ?? '',
                'policyDate' => $lead->do_policy_renewal_date ?? '',
                'rating' => $lead->do_rating ?? '',
                'claimsMade' => $lead->claims_made ?? ''
            ])

            {{-- Umbrella Section --}}
            @include('leads.partials.insurance-section', [
                'title' => 'Umbrella',
                'type' => 'umbrella',
                'carriers' => $carriersWithUnbrella ?? collect(),
                'selected' => $lead->umbrella ?? '',
                'renewalMonth' => $lead->U_ren_month ?? '',
                'expiryPremium' => $lead->umbrella_expiry_premium ?? '',
                'policyDate' => $lead->umbrella_policy_renewal_date ?? '',
                'rating' => $lead->umbrella_rating ?? '',
                'exclusions' => $lead->umbrella_exclusions ?? '',
                'otherExclusions' => $lead->umbrella_other_exclusions ?? ''
            ])

            {{-- Workers Compensation Section --}}
            @include('leads.partials.insurance-section', [
                'title' => 'Workers Compensation',
                'type' => 'workers_compensation',
                'carriers' => $carriersWithWorkCompensation ?? collect(),
                'selected' => $lead->workers_compensation ?? '',
                'renewalMonth' => $lead->WC_ren_month ?? '',
                'expiryPremium' => $lead->wc_expiry_premium ?? '',
                'policyDate' => $lead->wc_policy_renewal_date ?? '',
                'rating' => $lead->wc_rating ?? '',
                'employeeCount' => $lead->employee_count ?? '',
                'employeePayroll' => $lead->employee_payroll ?? ''
            ])

            {{-- Flood Section --}}
            @include('leads.partials.insurance-section', [
                'title' => 'Flood',
                'type' => 'flood',
                'carriers' => $carriersWithFlood ?? collect(),
                'selected' => $lead->flood ?? '',
                'renewalMonth' => $lead->F_ren_month ?? '',
                'expiryPremium' => $lead->flood_expiry_premium ?? '',
                'policyDate' => $lead->flood_policy_renewal_date ?? '',
                'rating' => $lead->flood_rating ?? '',
                'elevationCertificate' => $lead->elevation_certificate ?? '',
                'lomaLetter' => $lead->loma_letter ?? ''
            ])
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
function synchronizeRenMonth(elem) {
    var date = $(elem).val().split("-");
    var months = [];
    $('.businessRenewalMonth option').each(function() {
        months.push($(this).val());
    });
    var monthChoosed = months[parseInt(date[1])];
    $('.businessRenewalMonth').val(monthChoosed);
}

function getSetOtherVal(elem) {
    var inputContainer = $(elem).siblings('.otherInput');
    var input = inputContainer.find('input');
    $(elem).find('option[value="other"]').addClass('other');

    if ($(elem).val() == 'other') {
        inputContainer.fadeIn(500);
    } else {
        inputContainer.fadeOut(500);
    }

    $(input).on('keyup', function() {
        $(elem).find('.other').attr('value', $(input).val());
    });
}

function toggleOtherInput(elem, targetId) {
    if (elem.value == "other") {
        $("#" + targetId).show();
    } else {
        $("#" + targetId).hide();
    }
}

function restrictInput(element, maxLength) {
    if (element.value.length > maxLength) {
        element.value = element.value.slice(0, maxLength);
    }
}

$(document).on('blur', '#total_square_footage', function() {
    calculatePricePerSqFt($('#total_square_footage').val(), $('#insured_amount').val());
});

$(document).on('blur', '#insured_amount', function() {
    calculatePricePerSqFt($('#total_square_footage').val(), $('#insured_amount').val());
});

function calculatePricePerSqFt(totalSquare, totalInsured) {
    if (totalSquare == '' || totalInsured == '') {
        $("#price_per_sqft").val('');
    } else {
        var pricePpt = (parseFloat(totalInsured) / parseFloat(totalSquare)).toFixed(3);
        $("#price_per_sqft").val(pricePpt);
    }
}

// Initialize on page load
calculatePricePerSqFt("{{ $lead->total_square_footage ?? '' }}", "{{ $lead->insured_amount ?? '' }}");
</script>
@endpush
