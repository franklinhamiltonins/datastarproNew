<div class="card-body lead-update p-0">
    @if(isset($lead))
    <div class="form-group">
        <div class="custom-control custom-switch">
            {{-- <input type="checkbox" class="custom-control-input" {{$lead && $lead->is_client == 1 && $lead->contacts()->NotClient()->count() == 0 ? 'checked' : ''}}
            name="c_client" value="1" id="clientSwitch_{{$lead ? $lead->id : 0}}"> --}}
            <input type="checkbox" class="custom-control-input" {{$lead && $lead->is_client == 1 ? 'checked' : ''}}
                name="is_client" value="1" id="clientSwitch_{{$lead ? $lead->id : 0}}">
            <label class="custom-control-label" for="clientSwitch_{{$lead ? $lead->id : 0}}">Current Client</label>
        </div>
    </div>
    @endif

    <div class="form-group">
        <strong>Business Name<sup class="mandatoryClass">*</sup>:</strong>
        {!! Form::text('name', null, array('placeholder' => 'Business Name ','class'=> 'form-control')) !!}
    </div>

    <div class="mb-2">
        <div class="form-row">
            <div class="form-group col mb-0">
                <strong>Business Type<sup class="mandatoryClass">*</sup>:</strong>
                {{-- hardcoded dropdown --}}
                {!! Form::select('type',array(
                ''=>'Select Type',
                'Condo'=>'Condo',
                'HOA'=>'HOA',
                'Commercial'=>'Commercial',
                'Co-Op'=>'Co-Op',
                ),isset($lead) ? $lead->type : [], array('class' => 'form-control multiple ')) !!}
            </div>
            <div class="form-group col mb-0">
                <strong>Year Built:</strong>
                {!! Form::date('creation_date', null, array('class' => 'form-control businessCreationDate thisYearLimitRestriction')) !!}
            </div>
        </div>
    </div>

    <div class="form-group">
        <strong>Business Address 1<sup class="mandatoryClass">*</sup>:</strong>
        {!! Form::text('address1', null, array('placeholder' => 'Business Address - must start with a number','class' =>
        'form-control','pattern'=>'^\d[0-9a-zA-Z\s\/#,._-:]*$','title'=>'Chars allowed: # . - _ ,')) !!}
    </div>
    <div class="form-group">
        <strong>Business Address 2:</strong>
        {!! Form::text('address2', null, array('placeholder' => 'Business Address2','class' =>'form-control')) !!}
    </div>

    <div class="mb-2">
        <div class="form-row">
            <div class="form-group col mb-0">
                <strong>City<sup class="mandatoryClass">*</sup>:</strong>
                {!! Form::text('city', null, array('placeholder' => 'City','class' => 'form-control')) !!}
            </div>
            <div class="form-group col mb-0">
                <strong>State:</strong>
                {!! Form::select('state', $states, isset($lead) ? $lead->state : 'FL', array('class' => 'form-control
                multiple USstates')) !!}
            </div>
        </div>
    </div>

    <div class="mb-2">
        <div class="form-row">
            <div class="form-group col mb-0">
                <strong>Zip<sup class="mandatoryClass">*</sup>:</strong>
                {!! Form::text('zip', null, array('placeholder' => 'Zip - 5 digits ','class'
                =>'form-control integer-only','maxlength' => '5')) !!}
            </div>
            <div class="form-group col mb-0">
                <strong>County :</strong>
                {!! Form::select('county',isset($lead) ?
                array_merge(array($lead->county=>$lead->county),$counties):$counties ,isset($lead) ? $lead->county : [],
                array('class' => 'form-control multiple','onchange'=>'get_set_other_val(this)')) !!}
                <div id="countyOther" class="mt-2 otherInput" style="display:none;text-transform: lowercase; ">
                    <input placeholder="Other County" class="form-control capitalize" name="county-other" type="text">
                </div>
            </div>
        </div>
    </div>

    <div class="mb-2">
        <div class="form-row">
            <div class="form-group col mb-0">
                <strong>Coastal / Non Coastal :</strong>
                <select class="form-control multiple " name="coastal">
                    <option value="0" @if(empty($lead->coastal)) {{'selected'}} @endif>Non Coastal</option>
                    <option value="1" @if(!empty($lead->coastal)) {{'selected'}} @endif>Coastal</option>
                </select>
            </div>
            <div class="form-group col mb-0">
                <strong>Business Unit Count :</strong>
                {!! Form::number('unit_count', null, array('placeholder' => 'Unit Count - max 4 digits','class' =>
                'form-control','max' => 9999 )) !!}
            </div>
        </div>
    </div>
    <div class="mb-2">
        <div class="form-row">
            <div class="form-group col mb-0">
                <strong>Total insured value : </strong>
                <div class="input-group">
                    <span class="input-group-text rounded-right-0">$</span>
                    {!! Form::number('business_tiv', null, array('id' => 'business_tiv', 'placeholder' =>
                    'T.I.V.','class' => 'form-control rounded-left-0', 'step'=>'any',
                    'aria-label'=>'Dollar amount (with
                    dot and two decimal places','oninput' => 'restrictInput(this, 10)')) !!}
                </div>
            </div>
            <div class="form-group col mb-0">
                <strong>Total Square Footage :</strong>
                {!! Form::number('total_square_footage', null, array('id' => 'total_square_footage' ,'placeholder' => 'Total Square Footage - max 7 digits','class' =>
                'form-control','max' => 9999999 )) !!}
            </div>
        </div>
    </div>

    <div class="mb-2">
        <div class="form-row">
            <div class="form-group col mb-0">
                <strong>Appraiser Name :</strong>
                <input placeholder="Appraiser Name" class="form-control" name="appraisal_name" type="text" value="" id="appraisal_name">
            </div>
            <div class="form-group col mb-0">
                <strong>Appraisal Company :</strong>
                <input placeholder="Appraisal Company" class="form-control" name="appraisal_company" type="text" value="" id="appraisal_company">
            </div>
        </div>
    </div>

    <div class="mb-2">
        <div class="form-row">
            <div class="form-group col mb-0">
                <strong>Apraisal Date :</strong>
                {!! Form::date('appraisal_date', null, array('id' => 'appraisal_date','class' => 'form-control appraisal_date')) !!}
            </div>
            <div class="form-group col mb-0">
                <strong>Flood Zone :</strong>
                {!! Form::select('ins_flood',array(
                'No'=>'No',
                'Yes'=>'Yes',
                ),isset($lead) ? $lead->ins_flood : [], array('class' => 'form-control multiple px-1','id'=>'ins_flood')) !!}
            </div>
        </div>
    </div>

    <div class="mb-2">
        <div class="form-row">
            <div class="form-group col mb-0">
                <strong>Property Floors :</strong>
                {!! Form::select('prop_floor',[''=>'Select any option'] + array_combine(range(1,100),
                range(1,100)),isset($lead) ? $lead->prop_floor : [], array('class' => 'form-control multiple px-1','id'=>'prop_floor')) !!}
            </div>
            <div class="form-group col mb-0">
                <strong>Pool :</strong>
                {!! Form::select('pool',array(
                'No'=>'No',
                'Yes'=>'Yes',
                ),isset($lead) ? $lead->pool : [], array('class' => 'form-control px-1','id'=> 'pool')) !!}
            </div>
        </div>
    </div>

    <div class="mb-2">
        <div class="form-row">
            <div class="form-group col mb-0">
                <strong>Lakes :</strong>
                {!! Form::select('lakes',array(
                'No'=>'No',
                'Yes'=>'Yes',
                ),isset($lead) ? $lead->lakes : [], array('class' => 'form-control px-1','id'=> 'lakes')) !!}
            </div>
            <div class="form-group col mb-0">
                <strong>Clubhouse :</strong>
                {!! Form::select('clubhouse',array(
                'No'=>'No',
                'Yes'=>'Yes',
                ),isset($lead) ? $lead->clubhouse : [], array('class' => 'form-control px-1','id'=> 'clubhouse')) !!}
            </div>
        </div>
    </div>

    <div class="mb-2">
        <div class="form-row">
            <div class="form-group col mb-0">
                <strong>Tennis/Basketball Court :</strong>
                {!! Form::select('tennis_basketball',array(
                'No'=>'No',
                'Yes'=>'Yes',
                ),isset($lead) ? $lead->tennis_basketball : [], array('class' => 'form-control px-1','id'=> 'tennis_basketball')) !!}
            </div>
            <div class="form-group col mb-0">
                <strong>ISO :</strong>
                {!! Form::number('iso', null, array('placeholder' => 'ISO - max 2 digits','class' =>
                'form-control','id'=> 'iso','max' => 99,'oninput' => 'restrictInput(this, 2)' )) !!}
            </div>
        </div>
    </div>

    <div class="mb-2">
        <div class="form-row">
            <div class="form-group col mb-0">
                <strong>Tennis/Basketball Court :</strong>
                {!! Form::select('tennis_basketball',array(
                'No'=>'No',
                'Yes'=>'Yes',
                ),isset($lead) ? $lead->tennis_basketball : [], array('class' => 'form-control px-1','id'=> 'tennis_basketball')) !!}
            </div>
            <div class="form-group col mb-0">
                <strong>Lead Source :</strong>
                <select name="lead_source" id="lead_source" class="form-control input selectboxcarrier px-1" placeholder="Select Lead Source" >
                    <option value="" >Select Lead Source</option>
                    @foreach($leadSource as $lsource)
                        <option  value="{{ $lsource->id }}">{{ $lsource->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    @if(isset($lead))
    <div class="card card-secondary mt-4 mb-0">
        <div class="card-header">
            <h3 class="card-title"> Community Info: </h3>
        </div>

        <div class="card-body p-2 p-lg-3">
            <div class="mb-2">
                <div class="form-row">
                    <div class="form-group col mb-0">
                        <strong>Pool :</strong>
                        {!! Form::select('pool',array(
                        'No'=>'No',
                        'Yes'=>'Yes',
                        ),isset($lead) ? $lead->pool : [], array('class' => 'form-control ')) !!}
                    </div>
                    <div class="form-group col mb-0">
                        <strong>Lakes :</strong>
                        {!! Form::select('lakes',array(
                        'No'=>'No',
                        'Yes'=>'Yes',
                        ),isset($lead) ? $lead->lakes : [], array('class' => 'form-control ')) !!}
                    </div>
                </div>
            </div>

            <div class="mb-2">
                <div class="form-row">
                    <div class="form-group col mb-0">
                        <strong>Clubhouse :</strong>
                        {!! Form::select('clubhouse',array(
                        'No'=>'No',
                        'Yes'=>'Yes',
                        ),isset($lead) ? $lead->clubhouse : [], array('class' => 'form-control ')) !!}
                    </div>
                    <div class="form-group col mb-0">
                        <strong>Tennis/Basketball Court :</strong>
                        {!! Form::select('tennis_basketball',array(
                        'No'=>'No',
                        'Yes'=>'Yes',
                        ),isset($lead) ? $lead->tennis_basketball : [], array('class' => 'form-control ')) !!}
                    </div>
                </div>
            </div>

            <div class="mb-2">
                <div class="form-row">
                    <div class="form-group col mb-0">
                        <strong>Other Community Info :</strong>
                        <input placeholder="Other Community Info" class="form-control" name="other_community_info"
                                type="text" value="{{$lead->other_community_info}}">
                    </div>
                    <div class="form-group col mb-0">
                        <strong>ISO :</strong>
                        {!! Form::number('iso', null, array('placeholder' => 'ISO - max 2 digits','class' =>
                'form-control','max' => 99 )) !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif



    @if(isset($lead))
    <div class="card card-secondary mt-4 mb-0">
        <div class="card-header">
            <h3 class="card-title"> Prospect’s Insurance Information </h3>
        </div>

        <div class="card-body p-2 p-lg-3">
            <div class="mb-2">
                <div class="form-row">
                    <div class="form-group col mb-0">
                        <strong>Property Insurance Renewal Date :</strong>
                        {!! Form::date('renewal_date', null, array('class' => 'form-control
                        businessRenewalDate','onchange'=>'sincronize_renMonth(this)')) !!}
                    </div>
                    <div class="form-group col mb-0">
                        <strong>Property Insurance Renewal Month :</strong>
                        {{-- hardcoded months dropdown--}}
                        {!! Form::select('renewal_month',$months,isset($lead)? $lead->renewal_month : [] , array('class' =>
                        'form-control multiple businessRenewalMonth')) !!}

                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="row align-items-end">
                    <div class="col-6">
                        <strong>Property Insurance Expiring Premium : </strong>
                        <div class="input-group">
                            <span class="input-group-text rounded-right-0">$</span>
                            {!! Form::number('premium', null, array('placeholder' => 'Enter price ','class' =>
                            'form-control rounded-left-0','step'=>'any','aria-label'=>'Dollar amount (with dot and two
                            decimal
                            places')) !!}
                        </div>
                    </div>
                    <div class="col-6">
                        <strong>Expiring Premium Year : </strong>
                        {!! Form::select('premium_year', $years, isset($lead)? $lead->premium_year : [] , array('class'
                        => 'form-control multiple premium_year')) !!}
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="row">
                    <div class="col-6">
                        <strong>Total insured value : </strong>
                        <div class="input-group">
                            <span class="input-group-text rounded-right-0">$</span>
                            {!! Form::number('insured_amount', null, array('id' => 'insured_amount', 'placeholder' =>
                            'Enter price ','class' => 'form-control rounded-left-0', 'step'=>'any',
                            'aria-label'=>'Dollar amount (with
                            dot and two decimal places')) !!}
                        </div>
                    </div>
                    <div class="col-6">
                        <strong>Total Insured Value – YEAR : </strong>
                        {!! Form::select('insured_year', $years, isset($lead)? $lead->insured_year : [] , array('class'
                        => 'form-control multiple insured_year')) !!}
                    </div>
                </div>
            </div>

            <div class="mb-2">
                <div class="form-row">
                    <div class="form-group col mb-0">
                        <strong>Price Per SqFt
                         <!-- (Total insured value / Total Square Footage)  -->
                        : </strong>
                        <input type="text" disabled name="price_per_sqft" id="price_per_sqft" class="form-control">
                    </div>
                    <div class="form-group col mb-0">
                        <strong>Appraiser Name :</strong>
                        <input placeholder="Other Community Info" class="form-control" name="appraisal_name"
                                type="text" value="{{$lead->appraisal_name}}">
                    </div>
                </div>
            </div>
            <div class="mb-2">
                <div class="form-row">
                    <div class="form-group col mb-0">
                        <strong>Appraisal Company :</strong>
                        <input placeholder="Other Community Info" class="form-control" name="appraisal_company"
                                type="text" value="{{$lead->appraisal_company}}">
                    </div>
                    <div class="form-group col mb-0">
                        <strong>Apraisal Date :</strong>
                        {!! Form::date('appraisal_date', null, array('class' => 'form-control appraisal_date')) !!}
                    </div>
                </div>
            </div>
            <div class="mb-2">
                <div class="form-row">
                    <div class="form-group col mb-0">
                        <strong>Incumbent Agency :</strong>
                        <input placeholder="Other Community Info" class="form-control" name="incumbent_agency"
                                type="text" value="{{$lead->incumbent_agency}}">
                    </div>
                    <div class="form-group col mb-0">
                        <strong>Incumbent Agent :</strong>
                        <input placeholder="Other Community Info" class="form-control" name="incumbent_agent"
                                type="text" value="{{$lead->incumbent_agent}}">
                    </div>
                </div>
            </div>
            <!--  -->
            <div class="mb-2">
                <div class="form-row">
                    <div class="form-group col mb-0">
                        <strong>Policy Renewal Date :</strong>
                        {!! Form::date('policy_renewal_date', null, array('class' => 'form-control policy_renewal_date')) !!}
                    </div>
                    <div class="form-group col mb-0">
                        <strong> Wind Mitigation Date :</strong>
                        {!! Form::date('wind_mitigation_date', null, array('class' => 'form-control wind_mitigation_date')) !!}
                    </div>
                </div>
            </div>
            <div class="mb-2">
                <div class="form-row">
                    <div class="form-group col mb-0">
                        <strong>Rating :</strong>
                        <!-- list will be shared -->
                        {!! Form::select('rating',array(
                        ''=>'Select Rating',
                        ),isset($lead) ? $lead->rating : '', array('class' => 'form-control ')) !!}
                    </div>
                    <div class="form-group col mb-0">
                        <strong> Hurricane Deductible :</strong>
                        {!! Form::select('hurricane_deductible',array(
                        ''=>'Select Hurricane Deductible',
                        '1'=>'1%',
                        '3'=>'3%',
                        '5'=>'5%',
                        '10'=>'10%',
                        ),isset($lead) ? $lead->hurricane_deductible : '', array('class' => 'form-control ')) !!}
                    </div>
                </div>
            </div>
            <div class="mb-2">
                <div class="form-row">
                    <div class="form-group col mb-0">
                        <strong>Hurricane Deductible (Per Occ or Per Year) :</strong>
                        <!-- list will be shared -->
                        {!! Form::select('hurricane_deductible_occurrence',array(
                        ''=>'Select Occurrence',
                        'Per Occurrence'=>'Per Occurrence',
                        'Per Year'=>'Per Year',
                        ),isset($lead) ? $lead->hurricane_deductible_occurrence : '', array('class' => 'form-control ')) !!}
                    </div>
                    <div class="form-group col mb-0">
                        <strong> Sinkhole :</strong>
                        {!! Form::select('skin_hole',array(
                        'No'=>'No',
                        'Yes'=>'Yes',
                        ),isset($lead) ? $lead->skin_hole : '', array('class' => 'form-control ')) !!}
                    </div>
                </div>
            </div>
            <div class="mb-2">
                <div class="form-row">
                    <div class="form-group col mb-0">
                        <strong>All other Perils :</strong>
                        <!-- list will be shared -->
                        {!! Form::select('all_other_perils',array(
                        ''=>'Select All other Perils',
                        '1'=>'1%',
                        '3'=>'3%',
                        '5'=>'5%',
                        '10'=>'10%',
                        ),isset($lead) ? $lead->all_other_perils : '', array('class' => 'form-control ')) !!}
                    </div>
                    <div class="form-group col mb-0">
                        <strong> Ordinance of Law :</strong>
                        {!! Form::select('ordinance_of_law',array(
                        ''=>'Select Ordinance of Law',
                        '1'=>'1%',
                        '3'=>'3%',
                        '5'=>'5%',
                        '10'=>'10%',
                        ),isset($lead) ? $lead->ordinance_of_law : '', array('class' => 'form-control ')) !!}
                    </div>
                </div>
            </div>
            <div class="mb-2">
                <div class="form-row">
                    <div class="form-group col mb-0">
                        <strong>T.I.V. Matches Appraisal :</strong>
                        <!-- list will be shared -->
                        {!! Form::select('tiv_matches_appraisal',array(
                        'No'=>'No',
                        'Yes'=>'Yes',
                        ),isset($lead) ? $lead->tiv_matches_appraisal : '', array('class' => 'form-control ')) !!}
                    </div>
                    <div class="form-group col mb-0">
                        <strong> Secondary Water Insurance :</strong>
                        {!! Form::select('secondary_water_insurance',array(
                        'No'=>'No',
                        'Yes'=>'Yes',
                        ),isset($lead) ? $lead->secondary_water_insurance : '', array('class' => 'form-control ')) !!}
                    </div>
                </div>
            </div>
            <div class="mb-2">
                <div class="form-row">
                    <div class="form-group col mb-0">
                        <strong>Opening Protection :</strong>
                        <!-- list will be shared -->
                        {!! Form::select('opening_protection',array(
                        'No'=>'No',
                        'Yes'=>'Yes',
                        ),isset($lead) ? $lead->opening_protection : '', array('class' => 'form-control ')) !!}
                    </div>
                </div>
            </div>
            <!--  -->
            <div class="form-group">
                <strong>Property Insurance Carrier : </strong>
                {!! Form::select('ins_prop_carrier',isset($lead) ?
                array_merge(array($lead->ins_prop_carrier=>$lead->ins_prop_carrier),$leadsinsurrance):$leadsinsurrance
                ,isset($lead)? $lead->ins_prop_carrier : [], array('class' => 'form-control multiple '
                ,'onchange'=>'get_set_other_val(this)')) !!}
                <div id="inssuranceOther" class="mt-2 otherInput" style="display:none">
                    <input placeholder="Other Insurance Property Carrier" class="form-control" name="insurrance-other"
                        type="text">
                </div>
            </div>
            <div class="form-group">
                <strong>Property Insurance Carrier Renewal Month : </strong>
                {{-- hardcoded months dropdown--}}
                {!! Form::select('renewal_carrier_month',$months,isset($lead)? $lead->renewal_carrier_month : [] , array('class' =>
                'form-control multiple businessRenewalCarrierMonth')) !!}
            </div>
            <!-- General Liability  -->
            <div class="mb-2 form-group">
                <strong>General Liability : </strong>
                <div class="form-row">
                    <!-- <div class="form-group col mb-0">
                        {!! Form::text('general_liability', null, array('placeholder' => 'Carrier:','class' =>
                        'form-control currentInsurance','maxlength'=>'191')) !!}
                    </div> -->
                    <?php $gl_selected = 0 ?>
                    <div class="form-group col mb-0">
                        <select name="general_liability" class="form-control input selectboxcarrier" placeholder="Select Carrier" onchange="get_set_othercarrier_val(this,'general_liability')">
                            <option value="" @if($lead->general_liability == "") <?php $gl_selected = 1 ?> {{'selected'}} @endif>Select Carrier</option>
                            @foreach($carriersWithGeneralLiability as $carrier)
                                <option @if($lead->general_liability == $carrier->name) <?php $gl_selected = 1 ?> {{'selected'}} @endif>{{ $carrier->name }}</option>
                            @endforeach
                            <option value="other" @if($gl_selected == 0) {{'selected'}} @endif>Others</option>
                        </select>
                    </div>
                    <div class="form-group col mb-0">
                        {!! Form::select('GL_ren_month',$months, $lead->GL_ren_month, array('class' =>
                        'form-control multiple ')) !!}
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col mb-0">
                        <div id="general_liability" class="mt-2 otherInput" @if($gl_selected == 1) style="display:none;" @endif >
                            <input placeholder="General Liability Carrier" class="form-control" name="general_liability-other"
                                type="text" value="{{$lead->general_liability}}">
                        </div>
                    </div>
                </div>
            </div>
            <div class="mb-2">
                <div class="form-row">
                    <div class="form-group col mb-0">
                        <strong>General Liability Expiring Premium : </strong>
                        <div class="input-group">
                            <span class="input-group-text rounded-right-0">$</span>
                            {!! Form::number('gl_expiry_premium', null, array('placeholder' => 'Enter Premium ','class' =>
                            'form-control rounded-left-0','step'=>'any','aria-label'=>'Dollar amount (with dot and two
                            decimal
                            places')) !!}
                        </div>
                    </div>
                    <div class="form-group col mb-0">
                        <strong>General Liability Policy Renewal Date : </strong>
                        {!! Form::date('gl_policy_renewal_date', null, array('class' => 'form-control gl_policy_renewal_date')) !!}
                    </div>
                </div>
            </div>
            <div class="mb-2">
                <div class="form-row">
                    <div class="form-group col mb-0">
                        <strong>General Liability Insurance Rating:</strong>
                        <!-- list will be shared -->
                        {!! Form::select('gl_rating',array(
                            '' => "Select Rating"
                        ),isset($lead) ? $lead->gl_rating : '', array('class' => 'form-control ')) !!}
                    </div>
                    <div class="form-group col mb-0">
                        <strong>Exclusions :</strong>
                        {!! Form::select('gl_exclusions',array(
                        ''=>'select Exclusions'
                        ),isset($lead) ? $lead->gl_exclusions : '', array('class' => 'form-control ')) !!}
                    </div>
                </div>
            </div>
            <div class="mb-2">
                <div class="form-row">
                    <div class="form-group col mb-0">
                        <strong> Other Exclusions :</strong>
                        <input placeholder="Other Exclusions" class="form-control" name="gl_other_exclusions"
                                type="text" value="{{$lead->gl_other_exclusions}}">
                    </div>
                    <div class="form-group col mb-0">
                        <strong>Price Per Unit :</strong>
                        <input placeholder="Price Per Unit " class="form-control" name="gl_price_per_unit"
                                type="text" value="{{$lead->gl_price_per_unit}}">
                    </div>
                </div>
            </div>
            <!-- Crime Insurance -->
            <div class="mb-2 form-group">
                <strong>Crime Insurance : </strong>
                <div class="form-row">
                    <!-- <div class="form-group col mb-0">
                        {!! Form::text('general_liability', null, array('placeholder' => 'Carrier:','class' =>
                        'form-control currentInsurance','maxlength'=>'191')) !!}
                    </div> -->
                    <?php $ci_selected = 0 ?>
                    <div class="form-group col mb-0">
                        <select name="crime_insurance" class="form-control input selectboxcarrier" placeholder="Select Carrier" onchange="get_set_othercarrier_val(this,'crime_insurance')">
                            <option value="" @if($lead->crime_insurance == "") <?php $ci_selected = 1 ?> {{'selected'}} @endif>Select Carrier</option>
                            @foreach($carriersWithCrimeInsurance as $carrier)
                                <option @if($lead->crime_insurance == $carrier->name) <?php $ci_selected = 1 ?> {{'selected'}} @endif>{{ $carrier->name }}</option>
                            @endforeach
                            <option value="other" @if($ci_selected == 0) {{'selected'}} @endif>Others</option>
                        </select>
                    </div>
                    <div class="form-group col mb-0">
                        {!! Form::select('CI_ren_month',$months, $lead->CI_ren_month, array('class' =>
                        'form-control multiple ')) !!}
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col mb-0">
                        <div id="crime_insurance" class="mt-2 otherInput" @if($ci_selected == 1) style="display:none;" @endif>
                            <input placeholder="Crime Insurance Carrier" class="form-control" name="crime_insurance-other"
                                type="text" value="{{$lead->crime_insurance}}">
                        </div>
                    </div>
                </div>
            </div>
            <div class="mb-2">
                <div class="form-row">
                    <div class="form-group col mb-0">
                        <strong>Crime Insurance Expiring Premium : </strong>
                        <div class="input-group">
                            <span class="input-group-text rounded-right-0">$</span>
                            {!! Form::number('ci_expiry_premium', null, array('placeholder' => 'Enter Premium ','class' =>
                            'form-control rounded-left-0','step'=>'any','aria-label'=>'Dollar amount (with dot and two
                            decimal
                            places')) !!}
                        </div>
                    </div>
                    <div class="form-group col mb-0">
                        <strong>Crime Insurance Policy Renewal Date : </strong>
                        {!! Form::date('ci_policy_renewal_date', null, array('class' => 'form-control ci_policy_renewal_date')) !!}
                    </div>
                </div>
            </div>
            <div class="mb-2">
                <div class="form-row">
                    <div class="form-group col mb-0">
                        <strong>Crime Insurance Rating:</strong>
                        <!-- list will be shared -->
                        {!! Form::select('ci_rating',array(
                            '' => "Select Rating"
                        ),isset($lead) ? $lead->ci_rating : '', array('class' => 'form-control ')) !!}
                    </div>
                    <div class="form-group col mb-0">
                        <strong>Employee Theft :</strong>
                        {!! Form::number('employee_theft', null, array('placeholder' => 'Enter Theft ','class' =>
                            'form-control rounded-left-0','step'=>'any','aria-label'=>'Dollar amount (with dot and two
                            decimal places')) !!}
                    </div>
                </div>
            </div>
            <div class="mb-2">
                <div class="form-row">
                    <div class="form-group col mb-0">
                        <strong>Operating Reserves :</strong>
                        <!-- list will be shared -->
                        {!! Form::number('operating_reserves', null, array('placeholder' => 'Enter Reserves ','class' =>
                            'form-control rounded-left-0','step'=>'any','aria-label'=>'Dollar amount (with dot and two
                            decimal places')) !!}
                    </div>
                    <div class="form-group col mb-0">
                        <strong>Pending Litigation :</strong>
                        {!! Form::select('pending_litigation',array(
                        'No'=>'No',
                        'Yes'=>'Yes',
                        ),isset($lead) ? $lead->pending_litigation : '', array('class' => 'form-control ')) !!}
                    </div>
                </div>
            </div>
            <div class="mb-2">
                <div class="form-row">
                    <div class="form-group col mb-0">
                        <strong> Litigation Date :</strong>
                        {!! Form::date('litigation_date', null, array('class' => 'form-control litigation_date')) !!}
                    </div>
                </div>
            </div>
            <!-- Directors & Officers -->
            <div class="mb-2 form-group">
                <strong>Directors & Officers : </strong>
                <div class="form-row">
                    <!-- <div class="form-group col mb-0">
                        {!! Form::text('general_liability', null, array('placeholder' => 'Carrier:','class' =>
                        'form-control currentInsurance','maxlength'=>'191')) !!}
                    </div> -->
                    <?php $do_selected = 0 ?>
                    <div class="form-group col mb-0">
                        <select name="directors_officers" class="form-control input selectboxcarrier" placeholder="Select Carrier" onchange="get_set_othercarrier_val(this,'directors_officers')">
                            <option value="" @if($lead->directors_officers == "") <?php $do_selected = 1 ?> {{'selected'}} @endif>Select Carrier</option>
                            @foreach($carriersWithDirectorOfficor as $carrier)
                                <option @if($lead->directors_officers == $carrier->name) <?php $do_selected = 1 ?> {{'selected'}} @endif>{{ $carrier->name }}</option>
                            @endforeach
                            <option value="other" @if($do_selected == 0) {{'selected'}} @endif>Others</option>
                        </select>
                    </div>
                    <div class="form-group col mb-0">
                        {!! Form::select('DO_ren_month',$months, $lead->DO_ren_month, array('class' =>
                        'form-control multiple ')) !!}
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col mb-0">
                        <div id="directors_officers" class="mt-2 otherInput" @if($do_selected == 1) style="display:none;" @endif>
                            <input placeholder="Directors & Officers Carrier" class="form-control" name="directors_officers-other"
                                type="text" value="{{$lead->directors_officers}}">
                        </div>
                    </div>
                </div>
            </div>
            <div class="mb-2">
                <div class="form-row">
                    <div class="form-group col mb-0">
                        <strong>Directors & Officers Expiring Premium : </strong>
                        <div class="input-group">
                            <span class="input-group-text rounded-right-0">$</span>
                            {!! Form::number('do_expiry_premium', null, array('placeholder' => 'Enter price ','class' =>
                            'form-control rounded-left-0','step'=>'any','aria-label'=>'Dollar amount (with dot and two
                            decimal
                            places')) !!}
                        </div>
                    </div>
                    <div class="form-group col mb-0">
                        <strong>Directors & Officers Policy Renewal Date : </strong>
                        {!! Form::date('do_policy_renewal_date', null, array('class' => 'form-control do_policy_renewal_date')) !!}
                    </div>
                </div>
            </div>
            <div class="mb-2">
                <div class="form-row">
                    <div class="form-group col mb-0">
                        <strong>Directors & Officers Rating:</strong>
                        <!-- list will be shared -->
                        {!! Form::select('do_rating',array(
                            '' => "Select Rating"
                        ),isset($lead) ? $lead->do_rating : '', array('class' => 'form-control ')) !!}
                    </div>
                    <div class="form-group col mb-0">
                        <strong>Claims Made :</strong>
                        {!! Form::select('claims_made',array(
                        'No'=>'No',
                        'Yes'=>'Yes',
                        ),isset($lead) ? $lead->claims_made : '', array('class' => 'form-control ')) !!}
                    </div>
                </div>
            </div>
            <!-- Umbrella -->
            <div class="mb-2 form-group">
                <strong>Umbrella : </strong>
                <div class="form-row">
                    <!-- <div class="form-group col mb-0">
                        {!! Form::text('general_liability', null, array('placeholder' => 'Carrier:','class' =>
                        'form-control currentInsurance','maxlength'=>'191')) !!}
                    </div> -->
                    <?php $u_selected = 0 ?>
                    <div class="form-group col mb-0">
                        <select name="umbrella" class="form-control input selectboxcarrier" placeholder="Select Carrier" onchange="get_set_othercarrier_val(this,'umbrella')">
                            <option value="" @if($lead->umbrella == "") <?php $u_selected = 1 ?> {{'selected'}} @endif>Select Carrier</option>
                            @foreach($carriersWithUnbrella as $carrier)
                                <option @if($lead->umbrella == $carrier->name) <?php $u_selected = 1 ?> {{'selected'}} @endif>{{ $carrier->name }}</option>
                            @endforeach
                            <option value="other" @if($u_selected == 0) {{'selected'}} @endif>Others</option>
                        </select>
                    </div>
                    <div class="form-group col mb-0">
                        {!! Form::select('U_ren_month',$months, $lead->U_ren_month, array('class' =>
                        'form-control multiple ')) !!}
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col mb-0">
                        <div id="umbrella" class="mt-2 otherInput" @if($u_selected == 1) style="display:none;" @endif>
                            <input placeholder="Umbrella Carrier" class="form-control" name="umbrella-other"
                                type="text" value="{{$lead->umbrella}}">
                        </div>
                    </div>
                </div>
            </div>
            <div class="mb-2">
                <div class="form-row">
                    <div class="form-group col mb-0">
                        <strong>Umbrella Expiring Premium : </strong>
                        <div class="input-group">
                            <span class="input-group-text rounded-right-0">$</span>
                            {!! Form::number('umbrella_expiry_premium', null, array('placeholder' => 'Enter price ','class' =>
                            'form-control rounded-left-0','step'=>'any','aria-label'=>'Dollar amount (with dot and two
                            decimal
                            places')) !!}
                        </div>
                    </div>
                    <div class="form-group col mb-0">
                        <strong>Umbrella Policy Renewal Date : </strong>
                        {!! Form::date('umbrella_policy_renewal_date', null, array('class' => 'form-control umbrella_policy_renewal_date')) !!}
                    </div>
                </div>
            </div>
            <div class="mb-2">
                <div class="form-row">
                    <div class="form-group col mb-0">
                        <strong>Umbrella Rating:</strong>
                        <!-- list will be shared -->
                        {!! Form::select('umbrella_rating',array(
                            '' => "Select Rating"
                        ),isset($lead) ? $lead->umbrella_rating : '', array('class' => 'form-control ')) !!}
                    </div>
                    <div class="form-group col mb-0">
                        <strong>Exclusions :</strong>
                        {!! Form::select('umbrella_exclusions',array(
                        ''=>'select Exclusions'
                        ),isset($lead) ? $lead->umbrella_exclusions : '', array('class' => 'form-control ')) !!}
                    </div>
                </div>
            </div>
            <div class="mb-2">
                <div class="form-row">
                    <div class="form-group col mb-0">
                        <strong> Other Exclusions :</strong>
                        <input placeholder="Other Exclusions" class="form-control" name="umbrella_other_exclusions"
                                type="text" value="{{$lead->umbrella_other_exclusions}}">
                    </div>
                </div>
            </div>
            <!-- Workers Compensation  -->
            <div class="mb-2 form-group">
                <strong>Workers Compensation : </strong>
                <div class="form-row">
                    <!-- <div class="form-group col mb-0">
                        {!! Form::text('general_liability', null, array('placeholder' => 'Carrier:','class' =>
                        'form-control currentInsurance','maxlength'=>'191')) !!}
                    </div> -->
                    <?php $wc_selected = 0 ?>
                    <div class="form-group col mb-0">
                        <select name="workers_compensation" class="form-control input selectboxcarrier" placeholder="Select Carrier" onchange="get_set_othercarrier_val(this,'workers_compensation')">
                            <option value="" @if($lead->workers_compensation == "") <?php $wc_selected = 1 ?> {{'selected'}} @endif>Select Carrier</option>
                            @foreach($carriersWithWorkCompensation as $carrier)
                                <option @if($lead->workers_compensation == $carrier->name) <?php $wc_selected = 1 ?> {{'selected'}} @endif>{{ $carrier->name }}</option>
                            @endforeach
                            <option value="other" @if($wc_selected == 0) {{'selected'}} @endif>Others</option>
                        </select>
                    </div>
                    <div class="form-group col mb-0">
                        {!! Form::select('WC_ren_month',$months, $lead->WC_ren_month, array('class' =>
                        'form-control multiple ')) !!}
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col mb-0">
                        <div id="workers_compensation" class="mt-2 otherInput" @if($wc_selected == 1) style="display:none;" @endif>
                            <input placeholder="Workers Compensation Carrier" class="form-control" name="workers_compensation-other"
                                type="text" value="{{$lead->workers_compensation}}">
                        </div>
                    </div>
                </div>
            </div>
            <div class="mb-2">
                <div class="form-row">
                    <div class="form-group col mb-0">
                        <strong>Workers Compensation Expiring Premium : </strong>
                        <div class="input-group">
                            <span class="input-group-text rounded-right-0">$</span>
                            {!! Form::number('wc_expiry_premium', null, array('placeholder' => 'Enter price ','class' =>
                            'form-control rounded-left-0','step'=>'any','aria-label'=>'Dollar amount (with dot and two
                            decimal
                            places')) !!}
                        </div>
                    </div>
                    <div class="form-group col mb-0">
                        <strong>Workers Compensation Policy Renewal Date : </strong>
                        {!! Form::date('wc_policy_renewal_date', null, array('class' => 'form-control wc_policy_renewal_date')) !!}
                    </div>
                </div>
            </div>
            <div class="mb-2">
                <div class="form-row">
                    <div class="form-group col mb-0">
                        <strong>Workers Compensation Rating:</strong>
                        <!-- list will be shared -->
                        {!! Form::select('wc_rating',array(
                            '' => "Select Rating"
                        ),isset($lead) ? $lead->wc_rating : '', array('class' => 'form-control ')) !!}
                    </div>
                    <div class="form-group col mb-0">
                        <strong>Employee Count :</strong>
                        {!! Form::number('employee_count', null, array('placeholder' => 'Enter Count ','class' =>
                            'form-control rounded-left-0','step'=>'any')) !!}
                    </div>
                    <div class="form-group col mb-0">
                        <strong> Employee Payroll :</strong>
                        <div class="input-group">
                            <span class="input-group-text rounded-right-0">$</span>
                            {!! Form::number('employee_payroll', null, array('placeholder' => 'Enter Payroll ','class' =>
                            'form-control rounded-left-0')) !!}
                        </div>
                    </div>
                </div>
            </div>
            <div class="mb-2">
                <div class="form-row">
                    <div class="form-group col mb-0">
                        <strong> Employee Payroll :</strong>
                        <div class="input-group">
                            <span class="input-group-text rounded-right-0">$</span>
                            {!! Form::number('employee_payroll', null, array('placeholder' => 'Enter Payroll ','class' =>
                            'form-control rounded-left-0')) !!}
                        </div>
                    </div>
                </div>
            </div>
            <!-- Flood -->
            <div class="mb-2 form-group">
                <strong>Flood : </strong>
                <div class="form-row">
                    <!-- <div class="form-group col mb-0">
                        {!! Form::text('general_liability', null, array('placeholder' => 'Carrier:','class' =>
                        'form-control currentInsurance','maxlength'=>'191')) !!}
                    </div> -->
                    <?php $f_selected = 0 ?>
                    <div class="form-group col mb-0">
                        <select name="flood" class="form-control input selectboxcarrier" placeholder="Select Carrier" onchange="get_set_othercarrier_val(this,'flood')">
                            <option value="" @if($lead->flood == "") <?php $f_selected = 1 ?> {{'selected'}} @endif>Select Carrier</option>
                            @foreach($carriersWithFlood as $carrier)
                                <option @if($lead->flood == $carrier->name) <?php $f_selected = 1 ?> {{'selected'}} @endif>{{ $carrier->name }}</option>
                            @endforeach
                            <option value="other" @if($f_selected == 0) {{'selected'}} @endif>Others</option>
                        </select>
                    </div>
                    <div class="form-group col mb-0">
                        {!! Form::select('F_ren_month',$months, $lead->F_ren_month, array('class' =>
                        'form-control multiple ')) !!}
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col mb-0">
                        <div id="flood" class="mt-2 otherInput" @if($f_selected == 1) style="display:none;" @endif>
                            <input placeholder="Flood Carrier" class="form-control" name="flood-other"
                                type="text" value="{{$lead->flood}}">
                        </div>
                    </div>
                </div>
            </div>
            <div class="mb-2">
                <div class="form-row">
                    <div class="form-group col mb-0">
                        <strong>Flood Expiring Premium : </strong>
                        <div class="input-group">
                            <span class="input-group-text rounded-right-0">$</span>
                            {!! Form::number('flood_expiry_premium', null, array('placeholder' => 'Enter price ','class' =>
                            'form-control rounded-left-0','step'=>'any','aria-label'=>'Dollar amount (with dot and two
                            decimal
                            places')) !!}
                        </div>
                    </div>
                    <div class="form-group col mb-0">
                        <strong>Flood Policy Renewal Date : </strong>
                        {!! Form::date('flood_policy_renewal_date', null, array('class' => 'form-control flood_policy_renewal_date')) !!}
                    </div>
                </div>
            </div>
            <div class="mb-2">
                <div class="form-row">
                    <div class="form-group col mb-0">
                        <strong>Flood Rating:</strong>
                        <!-- list will be shared -->
                        {!! Form::select('flood_rating',array(
                            '' => "Select Rating"
                        ),isset($lead) ? $lead->flood_rating : '', array('class' => 'form-control ')) !!}
                    </div>
                    <div class="form-group col mb-0">
                        <strong>Elevation Certificate :</strong>
                        {!! Form::select('elevation_certificate',array(
                        'No'=>'No',
                        'Yes'=>'Yes',
                        ),isset($lead) ? $lead->elevation_certificate : '', array('class' => 'form-control ')) !!}
                    </div>
                </div>
            </div>
            <div class="mb-2">
                <div class="form-row">
                    <div class="form-group col mb-0">
                        <strong> Loma Letter :</strong>
                        {!! Form::select('loma_letter',array(
                        'No'=>'No',
                        'Yes'=>'Yes',
                        ),isset($lead) ? $lead->loma_letter : '', array('class' => 'form-control ')) !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
function sincronize_renMonth(elem) {
    //sincronize date selected with the month dropdown

    //select the dropdown
    var date = $(elem).val();
    //split the numbers to get the month number
    date = date.split("-");
    // create the month names array ,getting them from the month dropdown
    var months = [];

    $('.businessRenewalMonth option').each(function() {
        months.push($(this).val());
    });
    console.log(months);
    //get the chosen month name
    var monthChoosed = months[parseInt(date[1])];
    //set the dropdown to the selected month
    $('.businessRenewalMonth').val(monthChoosed);


}
// get the value of the input and set it for "other" option in the dropdown
function get_set_other_val(elem) {
    var inputContainer = $(elem).siblings('.otherInput') //get the container element
    var input = $(elem).siblings('.otherInput').find('input'); //get the input element
    $(elem).find('option[value="other"]').addClass('other'); //add class to "other" option
    //when user selects an option

    //if the option is "other"
    if ($(elem).val() == $(elem).find('.other').val()) {
        //show the input
        $(inputContainer).fadeIn(500);
    } else {
        //hide the input
        $(inputContainer).fadeOut(500);
    }
    //when input value changes
    $(input).on('keyup', function() {
        console.log($(input).val());
        //add the value to the "other option in the dropdown"
        $(elem).find('.other').attr('value', $(input).val());
    });
}

function get_set_othercarrier_val(elem,targetElement) {
    if(elem.value == "other"){
        $("#"+targetElement).show();
    }
    else{
        $("#"+targetElement).hide();
    }
}

$(document).on('blur','#total_square_footage',function (){
    pricepersquarefootcalculation($('#total_square_footage').val(),$('#insured_amount').val());
});

$(document).on('blur','#insured_amount',function (){
    pricepersquarefootcalculation($('#total_square_footage').val(),$('#insured_amount').val());
});

function pricepersquarefootcalculation(total_square,toal_insured) {
    if(total_square == '' || toal_insured == ''){
        $("#price_per_sqft").val('');
    }
    else{
        total_square = parseFloat(total_square);
        toal_insured = parseFloat(toal_insured);

        const price_ppt = (toal_insured/total_square).toFixed(3);

        $("#price_per_sqft").val(price_ppt);
    }

}

pricepersquarefootcalculation("{{ $lead->total_square_footage ?? '' }}", "{{ $lead->insured_amount ?? '' }}");

</script>
@endpush