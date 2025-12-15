<div class="card-body lead-update p-0 pt-4">
    @if(isset($lead))
    <div class="card card-secondary mt-0 mb-0 border-0 shadow-none">
        
            <!-- <h3 class="card-title fs-2 mb-3 pb-2 border-bottom"> Prospect’s Insurance Information </h3> -->
       

        <div class="card-body p-0">
            
            <div id="property_accordion">
                <h3 class="px-1">Property</h3>
                <div class="wrapper_content">
                    <div class="form-row">
                        <div class="form-group col-12 col-md-6 mb-2">
                            <strong>Carrier:</strong>
                            <?php $p_selected = 0 ?>
                            <select name="ins_prop_carrier" class="form-control input selectboxcarrier px-1" placeholder="Select Carrier" onchange="get_set_othercarrier_val(this,'ins_prop_carrier')" id="ins_prop_carrier">
                                <option value="" @if($lead->ins_prop_carrier == "") <?php $p_selected = 1 ?> {{'selected'}} @endif>Select Carrier</option>
                                @foreach($carriersWithProperty as $carrier)
                                    <option value="{{$carrier->id}}" @if($lead->ins_prop_carrier == $carrier->id) <?php $p_selected = 1 ?> {{'selected'}} @endif>{{ $carrier->name }}</option>
                                @endforeach
                                <option value="other" @if($p_selected == 0) {{'selected'}} @endif>Others</option>
                            </select>
                        </div>
                        <div class="form-group col-12 col-md-6 mb-2">
                            <strong>Month: </strong>
                            {{-- hardcoded months dropdown--}}
                            {!! Form::select('renewal_carrier_month',$months,isset($lead)? $lead->renewal_carrier_month : [] , array('id' => 'renewal_carrier_month','class' =>
                            'form-control multiple businessRenewalCarrierMonth px-1')) !!}
                        </div>
                        <div class="form-group col-12 mb-2">
                            <div id="ins_prop_carrier_div" class="mt-2 otherInput" @if($p_selected == 1) style="display:none;" @endif>
                                <input placeholder="Other Insurance Property Carrier" class="form-control" name="ins_prop_carrier-other"
                                    type="text" value="{{(!empty($lead->propertyCarrier->name) && $p_selected == 0)?$lead->propertyCarrier->name:''}}" id="ins_prop_carrier-other">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row align-items-end">
                            <div class="col-12 col-md-6">
                                <strong>Expiring Premium: </strong>
                                <div class="input-group">
                                    <span class="input-group-text rounded-right-0">$</span>
                                    {!! Form::number('premium', null, array('placeholder' => 'Expiring Premium ','class' =>
                                    'form-control rounded-left-0','step'=>'any','aria-label'=>'Dollar amount (with dot and two
                                    decimal
                                    places','id'=>'premium','maxlength' => 10,'oninput' => 'restrictInput(this, 10)')) !!}
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <strong>Expiring Premium Year: </strong>
                                {!! Form::select('premium_year', $years, isset($lead)? $lead->premium_year : [] , array('class'
                                => 'form-control multiple premium_year px-1','id'=>'premium_year')) !!}
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <strong>Total insured value: </strong>
                                <div class="input-group">
                                    <span class="input-group-text rounded-right-0">$</span>
                                    {!! Form::number('insured_amount', null, array('id' => 'insured_amount', 'placeholder' =>
                                    'Total insured value','class' => 'form-control rounded-left-0', 'step'=>'any',
                                    'aria-label'=>'Dollar amount (with
                                    dot and two decimal places','oninput' => 'restrictInput(this, 10)')) !!}
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <strong>Total Insured Value – Year: </strong>
                                {!! Form::select('insured_year', $years, isset($lead)? $lead->insured_year : [] , array('id' => 'insured_year','class'
                                => 'form-control multiple insured_year px-1')) !!}
                            </div>
                        </div>
                    </div>

                    <div class="mb-2">
                        <div class="form-row">
                            <div class="form-group col-12 col-md-6 mb-0">
                                <strong>Price Per SqFt: </strong>
                                <div class="input-group">
                                    <span class="input-group-text rounded-right-0">$</span>
                                    {!! Form::text('price_per_sqft', null, array(
                                        'placeholder' => 'Price Per SqFt',
                                        'class' => 'form-control rounded-left-0',
                                        'step' => 'any',
                                        'id' => 'price_per_sqft',
                                        'disabled'=>'disabled'
                                    )) !!}

                                </div>
                            </div>
                            <div class="form-group col-12 col-md-6 mb-0">
                                <strong>Policy Renewal Date:</strong>
                                {!! Form::date('policy_renewal_date', null, array('id' => 'policy_renewal_date','class' => 'form-control policy_renewal_date')) !!}
                            </div>
                        </div>
                    </div>
                    <div class="mb-2">
                        <div class="form-row">
                            <div class="form-group col-12 col-md-6 mb-0">
                                <strong>Incumbent Agency:</strong>
                                <input placeholder="Incumbent Agency" class="form-control" name="incumbent_agency"
                                        type="text" value="{{$lead->incumbent_agency}}" id="incumbent_agency">
                            </div>
                            <div class="form-group col-12 col-md-6 mb-0">
                                <strong>Incumbent Agent:</strong>
                                <input placeholder="Incumbent Agent" class="form-control" name="incumbent_agent"
                                        type="text" value="{{$lead->incumbent_agent}}" id="incumbent_agent">
                            </div>
                        </div>
                    </div>
                    <!--  -->
                    <div class="form-row">
                        <div class="form-group col-12 col-md-6 mb-2">
                            <strong>Carrier Rating:</strong>
                            <?php $p_selected_r = 0 ?>
                            <select name="rating" class="form-control input selectboxcarrier px-1" placeholder="Select Rating" onchange="get_set_othercarrier_val(this,'rating')" id="rating">
                                <option value="" @if($lead->rating == "") <?php $p_selected_r = 1 ?> {{'selected'}} @endif>Select Rating</option>
                                @foreach($ratingsWithProperty as $rating)
                                    <option value="{{$rating->id}}" @if($lead->rating == $rating->id) <?php $p_selected_r = 1 ?> {{'selected'}} @endif>{{ $rating->name }}</option>
                                @endforeach
                                <option value="other" @if($p_selected_r == 0) {{'selected'}} @endif>Others</option>
                            </select>
                        </div>
                        <div class="form-group col-12 col-md-6 mb-2">
                            <strong> Sinkhole:</strong>
                            {!! Form::select('skin_hole',array(
                            'No'=>'No',
                            'Yes'=>'Yes',
                            ),isset($lead) ? $lead->skin_hole : '', array('id' => 'skin_hole','class' => 'form-control px-1')) !!}
                        </div>
                        <div class="form-group col-12 mb-2">
                            <div id="rating_div" class="mt-2 otherInput" @if($p_selected_r == 1) style="display:none;" @endif>
                                <input placeholder="Other Insurance Property Rating" class="form-control" name="rating-other"
                                    type="text" value="{{(!empty($lead->propertyRating->name) && $p_selected_r == 0)?$lead->propertyRating->name:''}}" id="rating-other">
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-12 col-md-6 mb-2">
                            <strong> Hurricane Deductible:</strong>
                            {!! Form::select('hurricane_deductible',array(
                            ''=>'Select Hurricane Deductible',
                            '1'=>'1%',
                            '3'=>'3%',
                            '5'=>'5%',
                            '10'=>'10%',
                            ),isset($lead) ? $lead->hurricane_deductible : '', array('id' => 'hurricane_deductible','class' => 'form-control px-1')) !!}
                        </div>
                        <div class="form-group col-12 col-md-6 mb-2">
                            <strong>Hurricane Deductible (Per Occ/Year):</strong>
                            <!-- list will be shared -->
                            {!! Form::select('hurricane_deductible_occurrence',array(
                            ''=>'Select Occurrence',
                            'Per Occurrence'=>'Per Occurrence',
                            'Per Year'=>'Per Year',
                            ),isset($lead) ? $lead->hurricane_deductible_occurrence : '', array('id' => 'hurricane_deductible_occurrence','class' => 'form-control px-1')) !!}
                        </div>
                    </div>
                    <div class="mb-2">
                        <div class="form-row">
                            <div class="form-group col-12 col-md-6 mb-0">
                                <strong>All other Perils:</strong>
                                <div class="input-group">
                                    <span class="input-group-text rounded-right-0">$</span>
                                    {!! Form::number('all_other_perils', null, array('id' => 'all_other_perils', 'placeholder' =>
                                    'All other Perils','class' => 'form-control rounded-left-0', 'step'=>'any',
                                    'aria-label'=>'Dollar amount (with
                                    dot and two decimal places','oninput' => 'restrictInput(this, 10)')) !!}
                                </div>
                            </div>
                            <div class="form-group col-12 col-md-6 mb-0">
                                <strong>Ordinance of Law:</strong>
                                <?php $ofl_selected = 0 ?>
                                <select id="ordinance_of_law" class="form-control px-1" name="ordinance_of_law"  onchange="get_set_othercarrier_val(this,'ordinance_of_law')">
                                    <option value="" @if($lead->ordinance_of_law === "" || $lead->ordinance_of_law === null) <?php $ofl_selected = 1 ?> {{'selected'}} @endif>Select Ordinance of Law</option>
                                    <option value="0" @if($lead->ordinance_of_law == 0 && $ofl_selected == 0) <?php $ofl_selected = 1 ?> {{'selected'}} @endif>0%</option>
                                    <option value="1" @if($lead->ordinance_of_law == 1) <?php $ofl_selected = 1 ?> {{'selected'}} @endif>1%</option>
                                    <option value="2.5" @if($lead->ordinance_of_law == 2.5) <?php $ofl_selected = 1 ?> {{'selected'}} @endif>2.5%</option>
                                    <option value="3" @if($lead->ordinance_of_law == 3) <?php $ofl_selected = 1 ?> {{'selected'}} @endif>3%</option>
                                    <option value="5" @if($lead->ordinance_of_law == 5) <?php $ofl_selected = 1 ?> {{'selected'}} @endif>5%</option>
                                    <option value="10" @if($lead->ordinance_of_law == 10) <?php $ofl_selected = 1 ?> {{'selected'}} @endif>10%</option>
                                    <option value="other" @if($ofl_selected == 0) {{'selected'}} @endif>Others</option>
                                </select>
                            </div>
                            <div class="form-group col-12 mb-2">
                                <div id="ordinance_of_law_div" class="mt-2 otherInput" @if($ofl_selected == 1) style="display:none;" @endif>
                                    <input placeholder="Other Ordinance of Law" class="form-control" name="ordinance_of_law-other"
                                        type="number" step="any" value="{{$lead->ordinance_of_law}}" id="ordinance_of_law-other">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-2">
                        <div class="form-row">
                            <div class="form-group col-12 col-md-6 mb-0">
                                <strong>T.I.V. Matches Appraisal:</strong>
                                <!-- list will be shared -->
                                {!! Form::select('tiv_matches_appraisal',array(
                                'No'=>'No',
                                'Yes'=>'Yes',
                                ),isset($lead) ? $lead->tiv_matches_appraisal : '', array('id' => 'tiv_matches_appraisal','class' => 'form-control px-1')) !!}
                            </div>
                        </div>
                    </div>
                    <div class="mb-0">
                        <div class="form-row">
                            <div class="form-group col mb-0">
                                <strong>Property Notes:</strong>
                                {!! Form::textarea('property_insurance_coverage', null, array('placeholder' => 'Property Notes','class' => 'form-control d-block','id'=>'property_insurance_coverage','rows' => '4','maxlength'=>'1000')) !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="accordion">
                <h3 class="px-1 mt-2">General Liability</h3>
                <div class="wrapper_content">
                    <div class="mb-2 form-group">
                        <!-- <strong>General Liability : </strong> -->
                        <div class="form-row">
                            <!-- <div class="form-group col mb-0">
                                {!! Form::text('general_liability', null, array('placeholder' => 'Carrier:','class' =>
                                'form-control currentInsurance','maxlength'=>'191')) !!}
                            </div> -->
                            <?php $gl_selected = 0 ?>
                            <div class="form-group col mb-0">
                                <select name="general_liability" id="general_liability" class="form-control input selectboxcarrier px-1" placeholder="Select Carrier" onchange="get_set_othercarrier_val(this,'general_liability')" >
                                    <option value="" @if($lead->general_liability == "") <?php $gl_selected = 1 ?> {{'selected'}} @endif>Select Carrier</option>
                                    @foreach($carriersWithGeneralLiability as $carrier)
                                        <option value="{{$carrier->id}}" @if($lead->general_liability == $carrier->id) <?php $gl_selected = 1 ?> {{'selected'}} @endif>{{ $carrier->name }}</option>
                                    @endforeach
                                    <option value="other" @if($gl_selected == 0) {{'selected'}} @endif>Others</option>
                                </select>
                            </div>
                            <div class="form-group col mb-0">
                                {!! Form::select('GL_ren_month',$months, $lead->GL_ren_month, array('class' =>
                                'form-control multiple px-1','id'=>'GL_ren_month')) !!}
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col mb-0">
                                <div id="general_liability_div" class="mt-2 otherInput" @if($gl_selected == 1) style="display:none;" @endif >
                                    <input placeholder="General Liability Carrier" class="form-control" name="general_liability-other"
                                        type="text" value="{{(!empty($lead->glCarrier->name) && $gl_selected == 0)?$lead->glCarrier->name:''}}" id="general_liability-other">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-2">
                        <div class="form-row">
                            <div class="form-group col mb-0">
                                <strong>Expiring Premium: </strong>
                                <div class="input-group">
                                    <span class="input-group-text rounded-right-0">$</span>
                                    {!! Form::number('gl_expiry_premium', null, array('placeholder' => 'Expiring Premium ','class' =>
                                    'form-control rounded-left-0','step'=>'any','aria-label'=>'Dollar amount (with dot and two
                                    decimal
                                    places','id'=>'gl_expiry_premium','oninput' => 'restrictInput(this, 8)')) !!}
                                </div>
                            </div>
                            <div class="form-group col mb-0">
                                <strong>Policy Renewal Date: </strong>
                                {!! Form::date('gl_policy_renewal_date', null, array('class' => 'form-control gl_policy_renewal_date','id'=>'gl_policy_renewal_date')) !!}
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-12 col-md-6 mb-2">
                            <strong>Carrier Rating:</strong>
                            <?php $gl_selected_r = 0 ?>
                            <select name="gl_rating" class="form-control input selectboxcarrier px-1" placeholder="Select Rating" onchange="get_set_othercarrier_val(this,'gl_rating')" id="gl_rating">
                                <option value="" @if($lead->gl_rating == "") <?php $gl_selected_r = 1 ?> {{'selected'}} @endif>Select Rating</option>
                                @foreach($ratingsWithGeneralLiability as $rating)
                                    <option value="{{$rating->id}}" @if($lead->gl_rating == $rating->id) <?php $gl_selected_r = 1 ?> {{'selected'}} @endif>{{ $rating->name }}</option>
                                @endforeach
                                <option value="other" @if($gl_selected_r == 0) {{'selected'}} @endif>Others</option>
                            </select>
                        </div>
                        <div class="form-group col-12 col-md-6 mb-2">
                            <strong>Price Per Unit:</strong>
                            <div class="input-group">
                            <span class="input-group-text rounded-right-0">$</span>
                            {!! Form::text('gl_price_per_unit', null, array(
                                'placeholder' => 'Price Per Unit',
                                'class' => 'form-control rounded-left-0',
                                'step' => 'any',
                                'id' => 'gl_price_per_unit',
                                'disabled'=>'disabled'
                            )) !!}

                        </div>
                        </div>
                        <div class="form-group col-12 mb-2">
                            <div id="gl_rating_div" class="mt-2 otherInput" @if($gl_selected_r == 1) style="display:none;" @endif>
                                <input placeholder="Other General Liability Rating" class="form-control" name="gl_rating-other"
                                    type="text" value="{{(!empty($lead->generaLiablityRating->name) && $gl_selected_r == 0)?$lead->generaLiablityRating->name:''}}" id="gl_rating-other">
                            </div>
                        </div>
                    </div>
                    <div class="mb-2">
                        <div class="form-row">
                            <div class="form-group col mb-0">
                                <strong>Exclusions:</strong>
                                {!! Form::select('gl_exclusions[]', [
                                    'animal' => 'Animal',
                                    'assault_battery' => 'Assault and Battery',
                                    'cross_suit' => 'Cross Suit',
                                    'insured_vs_insured' => 'Insured Vs. Insured',
                                    'liquor' => 'Liquor',
                                    'pool' => 'Pool',
                                    'firearm' => 'Firearm',
                                ], isset($lead) ? explode(',', $lead->gl_exclusions) : [], ['id'=>'gl_exclusions','class' => 'form-control  px-1', 'multiple' => true, 'size' => 1, 'style' => 'height: 2rem']) !!}
                            </div>
                            <div class="form-group col mb-0">
                                <strong>Other Exclusions:</strong>
                                <input placeholder="Other Exclusions" id="gl_other_exclusions" class="form-control" name="gl_other_exclusions" type="text" value="{{ $lead->gl_other_exclusions }}">
                            </div>
                        </div>
                    </div>
                    <div class="mb-0">
                        <div class="form-row">
                            <div class="form-group col mb-0">
                                <strong>General Liability Notes:</strong>
                                {!! Form::textarea('gl_insurance_coverage', null, array('placeholder' => 'General Liability Notes','class' => 'form-control d-block','id'=>'gl_insurance_coverage','rows' => '4','maxlength'=>'1000')) !!}
                            </div>
                        </div>
                    </div>
                </div>

                <h3 class="mt-2 px-1">Crime Insurance</h3>
                <div class="wrapper_content">
                    <div class="mb-2 form-group">
                        <div class="form-row">
                            <!-- <div class="form-group col mb-0">
                                {!! Form::text('general_liability', null, array('placeholder' => 'Carrier:','class' =>
                                'form-control currentInsurance','maxlength'=>'191')) !!}
                            </div> -->
                            <?php $ci_selected = 0 ?>
                            <div class="form-group col mb-0">
                                <select name="crime_insurance" id="crime_insurance" class="form-control input selectboxcarrier px-1" placeholder="Select Carrier" onchange="get_set_othercarrier_val(this,'crime_insurance')">
                                    <option value="" @if($lead->crime_insurance == "") <?php $ci_selected = 1 ?> {{'selected'}} @endif>Select Carrier</option>
                                    @foreach($carriersWithCrimeInsurance as $carrier)
                                        <option value="{{$carrier->id}}" @if($lead->crime_insurance == $carrier->id) <?php $ci_selected = 1 ?> {{'selected'}} @endif>{{ $carrier->name }}</option>
                                    @endforeach
                                    <option value="other" @if($ci_selected == 0) {{'selected'}} @endif>Others</option>
                                </select>
                            </div>
                            <div class="form-group col mb-0">
                                {!! Form::select('CI_ren_month',$months, $lead->CI_ren_month, array('class' =>
                                'form-control multiple px-1','id'=>'CI_ren_month')) !!}
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col mb-0">
                                <div id="crime_insurance_div" class="mt-2 otherInput" @if($ci_selected == 1) style="display:none;" @endif>
                                    <input placeholder="Crime Insurance Carrier" class="form-control" name="crime_insurance-other"
                                        type="text" value="{{(!empty($lead->ciCarrier->name) && $ci_selected == 0)?$lead->ciCarrier->name:''}}" id="crime_insurance-other">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-2">
                        <div class="form-row">
                            <div class="form-group col mb-0">
                                <strong>Expiring Premium: </strong>
                                <div class="input-group">
                                    <span class="input-group-text rounded-right-0">$</span>
                                    {!! Form::number('ci_expiry_premium', null, array('placeholder' => 'Expiring Premium ','class' =>
                                    'form-control rounded-left-0','step'=>'any','aria-label'=>'Dollar amount (with dot and two
                                    decimal
                                    places','id'=>'ci_expiry_premium','oninput' => 'restrictInput(this, 8)')) !!}
                                </div>
                            </div>
                            <div class="form-group col mb-0">
                                <strong>Policy Renewal Date: </strong>
                                {!! Form::date('ci_policy_renewal_date', null, array('class' => 'form-control ci_policy_renewal_date','id'=>'ci_policy_renewal_date')) !!}
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-12 col-md-6 mb-2">
                            <strong>Rating :</strong>
                            <?php $ci_selected_r = 0 ?>
                            <select name="ci_rating" class="form-control input selectboxcarrier px-1" placeholder="Select Rating" onchange="get_set_othercarrier_val(this,'ci_rating')" id="ci_rating">
                                <option value="" @if($lead->ci_rating == "") <?php $ci_selected_r = 1 ?> {{'selected'}} @endif>Select Rating</option>
                                @foreach($ratingsWithCrimeInsurance as $rating)
                                    <option value="{{$rating->id}}" @if($lead->ci_rating == $rating->id) <?php $ci_selected_r = 1 ?> {{'selected'}} @endif>{{ $rating->name }}</option>
                                @endforeach
                                <option value="other" @if($ci_selected_r == 0) {{'selected'}} @endif>Others</option>
                            </select>
                        </div>
                        <div class="form-group col-12 col-md-6 mb-2">
                            <strong>Employee Theft:</strong>
                            {!! Form::number('employee_theft', null, array('placeholder' => 'Employee Theft ','id'=>'employee_theft','class' =>
                                'form-control rounded-left-0','step'=>'any','aria-label'=>'Dollar amount (with dot and two decimal places','oninput' => 'restrictInput(this, 7)')) !!}
                        </div>
                        <div class="form-group col-12 mb-2">
                            <div id="ci_rating_div" class="mt-2 otherInput" @if($ci_selected_r == 1) style="display:none;" @endif>
                                <input placeholder="Other Crime Insurance Rating" class="form-control" name="ci_rating-other"
                                    type="text" value="{{(!empty($lead->crimeInsuranceRating->name) && $ci_selected_r == 0)?$lead->crimeInsuranceRating->name:''}}" id="ci_rating-other">
                            </div>
                        </div>
                    </div>
                    <div class="mb-2">
                        <div class="form-row">
                            <div class="form-group col-12 col-md-6 mb-0">
                                <strong>Operating Reserves:</strong>
                                <!-- list will be shared -->
                                {!! Form::number('operating_reserves', null, array('placeholder' => 'Operating Reserves ','id'=>'operating_reserves','class' =>
                                    'form-control rounded-left-0','step'=>'any','aria-label'=>'Dollar amount (with dot and two
                                    decimal places','oninput' => 'restrictInput(this, 7)')) !!}
                            </div>
                        </div>
                    </div>
                    <div class="mb-0">
                        <div class="form-row">
                            <div class="form-group col mb-0">
                                <strong>Crime Insurance Notes:</strong>
                                {!! Form::textarea('ci_insurance_coverage', null, array('placeholder' => 'Crime Insurance Notes','class' => 'form-control d-block','id'=>'ci_insurance_coverage','rows' => '4','maxlength'=>'1000')) !!}
                            </div>
                        </div>
                    </div>
                </div>

                <h3 class="mt-2 px-1">Directors & Officers</h3>
                <div class="wrapper_content">
                    <div class="mb-2 form-group">
                        <div class="form-row">
                            <!-- <div class="form-group col mb-0">
                                {!! Form::text('general_liability', null, array('placeholder' => 'Carrier:','class' =>
                                'form-control currentInsurance','maxlength'=>'191')) !!}
                            </div> -->
                            <?php $do_selected = 0 ?>
                            <div class="form-group col mb-0">
                                <select name="directors_officers" id="directors_officers" class="form-control input selectboxcarrier px-1" placeholder="Select Carrier" onchange="get_set_othercarrier_val(this,'directors_officers')">
                                    <option value="" @if($lead->directors_officers == "") <?php $do_selected = 1 ?> {{'selected'}} @endif>Select Carrier</option>
                                    @foreach($carriersWithDirectorOfficor as $carrier)
                                        <option value="{{$carrier->id}}" @if($lead->directors_officers == $carrier->id) <?php $do_selected = 1 ?> {{'selected'}} @endif>{{ $carrier->name }}</option>
                                    @endforeach
                                    <option value="other" @if($do_selected == 0) {{'selected'}} @endif>Others</option>
                                </select>
                            </div>
                            <div class="form-group col mb-0">
                                {!! Form::select('DO_ren_month',$months, $lead->DO_ren_month, array('class' =>
                                'form-control multiple px-1','id'=>'DO_ren_month')) !!}
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col mb-0">
                                <div id="directors_officers_div" class="mt-2 otherInput" @if($do_selected == 1) style="display:none;" @endif>
                                    <input placeholder="Directors & Officers Carrier" class="form-control" name="directors_officers-other"
                                        type="text" value="{{(!empty($lead->doCarrier->name) && $do_selected == 0)?$lead->doCarrier->name:''}}" id="directors_officers-other">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-2">
                        <div class="form-row">
                            <div class="form-group col mb-0">
                                <strong>Expiring Premium: </strong>
                                <div class="input-group">
                                    <span class="input-group-text rounded-right-0">$</span>
                                    {!! Form::number('do_expiry_premium', null, array('placeholder' => 'Expiring Premium ','class' =>
                                    'form-control rounded-left-0','step'=>'any','aria-label'=>'Dollar amount (with dot and two
                                    decimal
                                    places','id'=>'do_expiry_premium','oninput' => 'restrictInput(this, 8)')) !!}
                                </div>
                            </div>
                            <div class="form-group col mb-0">
                                <strong>Policy Renewal Date: </strong>
                                {!! Form::date('do_policy_renewal_date', null, array('class' => 'form-control do_policy_renewal_date','id'=>'do_policy_renewal_date')) !!}
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-12 col-md-6 mb-2">
                            <strong>Carrier Rating:</strong>
                            <?php $do_selected_r = 0 ?>
                            <select name="do_rating" class="form-control input selectboxcarrier px-1" placeholder="Select Rating" onchange="get_set_othercarrier_val(this,'do_rating')" id="do_rating">
                                <option value="" @if($lead->do_rating == "") <?php $do_selected_r = 1 ?> {{'selected'}} @endif>Select Rating</option>
                                @foreach($ratingsWithDirectorOfficor as $rating)
                                    <option value="{{$rating->id}}" @if($lead->do_rating == $rating->id) <?php $do_selected_r = 1 ?> {{'selected'}} @endif>{{ $rating->name }}</option>
                                @endforeach
                                <option value="other" @if($do_selected_r == 0) {{'selected'}} @endif>Others</option>
                            </select>
                        </div>
                        <div class="form-group col-12 col-md-6 mb-2">
                            <strong>Claims Made:</strong>
                            {!! Form::select('claims_made',array(
                            'No'=>'No',
                            'Yes'=>'Yes',
                            ),isset($lead) ? $lead->claims_made : '', array('class' => 'form-control px-1','id'=>'claims_made')) !!}
                        </div>
                        <div class="form-group col-12 mb-2">
                            <div id="do_rating_div" class="mt-2 otherInput" @if($do_selected_r == 1) style="display:none;" @endif>
                                <input placeholder="Other Directors & Officers Rating" class="form-control" name="do_rating-other"
                                    type="text" value="{{(!empty($lead->directorOfficerRating->name)&& $do_selected_r == 0)?$lead->directorOfficerRating->name:''}}" id="do_rating-other">
                            </div>
                        </div>
                    </div>
                    <div class="mb-2">
                        <div class="form-row">
                            <div class="form-group col-12 col-md-6 mb-2">
                                <strong>Pending Litigation:</strong>
                                {!! Form::select('pending_litigation',array(
                                'No'=>'No',
                                'Yes'=>'Yes',
                                ),isset($lead) ? $lead->pending_litigation : '', array('id'=>'pending_litigation','class' => 'form-control px-1')) !!}
                            </div>
                            <div class="form-group col-12 col-md-6 mb-2">
                                <strong> Litigation Date:</strong>
                                {!! Form::date('litigation_date', null, array('id'=>'litigation_date','class' => 'form-control litigation_date')) !!}
                            </div>
                        </div>
                    </div>
                    <div class="mb-0">
                        <div class="form-row">
                            <div class="form-group col mb-0">
                                <strong>Directors & Officers Notes:</strong>
                                {!! Form::textarea('do_insurance_coverage', null, array('placeholder' => 'Directors & Officers Notes','class' => 'form-control d-block','id'=>'do_insurance_coverage','rows' => '4','maxlength'=>'1000')) !!}
                            </div>
                        </div>
                    </div>
                </div>

                <h3 class="mt-2 px-1">Umbrella</h3>
                <div class="wrapper_content">
                    <div class="mb-2 form-group">
                        <div class="form-row">
                            <!-- <div class="form-group col mb-0">
                                {!! Form::text('general_liability', null, array('placeholder' => 'Carrier:','class' =>
                                'form-control currentInsurance','maxlength'=>'191')) !!}
                            </div> -->
                            <?php $u_selected = 0 ?>
                            <div class="form-group col mb-0">
                                <select name="umbrella" id="umbrella" class="form-control input selectboxcarrier px-1" placeholder="Select Carrier" onchange="get_set_othercarrier_val(this,'umbrella')">
                                    <option value="" @if($lead->umbrella == "") <?php $u_selected = 1 ?> {{'selected'}} @endif>Select Carrier</option>
                                    @foreach($carriersWithUnbrella as $carrier)
                                        <option value="{{$carrier->id}}" @if($lead->umbrella == $carrier->id) <?php $u_selected = 1 ?> {{'selected'}} @endif>{{ $carrier->name }}</option>
                                    @endforeach
                                    <option value="other" @if($u_selected == 0) {{'selected'}} @endif>Others</option>
                                </select>
                            </div>
                            <div class="form-group col mb-0">
                                {!! Form::select('U_ren_month',$months, $lead->U_ren_month, array('class' =>
                                'form-control multiple px-1','id'=>'U_ren_month')) !!}
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col mb-0">
                                <div id="umbrella_div" class="mt-2 otherInput" @if($u_selected == 1) style="display:none;" @endif>
                                    <input placeholder="Umbrella Carrier" class="form-control" name="umbrella-other"
                                        type="text" value="{{(!empty($lead->umbrellaCarrier->name) && $u_selected == 0)?$lead->umbrellaCarrier->name:''}}" id="umbrella-other">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-2">
                        <div class="form-row">
                            <div class="form-group col mb-0">
                                <strong>Expiring Premium: </strong>
                                <div class="input-group">
                                    <span class="input-group-text rounded-right-0">$</span>
                                    {!! Form::number('umbrella_expiry_premium', null, array('placeholder' => 'Expiring Premium','class' =>
                                    'form-control rounded-left-0','step'=>'any','aria-label'=>'Dollar amount (with dot and two
                                    decimal
                                    places','id'=>'umbrella_expiry_premium','oninput' => 'restrictInput(this, 8)')) !!}
                                </div>
                            </div>
                            <div class="form-group col mb-0">
                                <strong>Policy Renewal Date: </strong>
                                {!! Form::date('umbrella_policy_renewal_date', null, array('class' => 'form-control umbrella_policy_renewal_date','id'=>'umbrella_policy_renewal_date')) !!}
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-12 col-md-6 mb-2">
                            <strong>Exclusions:</strong>
                            {!! Form::select('umbrella_exclusions[]', [
                                'animal' => 'Animal',
                                'assault_battery' => 'Assault and Battery',
                                'cross_suit' => 'Cross Suit',
                                'insured_vs_insured' => 'Insured Vs. Insured',
                                'liquor' => 'Liquor',
                                'pool' => 'Pool',
                                'firearm' => 'Firearm',
                            ], isset($lead) ? explode(',', $lead->umbrella_exclusions) : [], ['id'=>'umbrella_exclusions','class' => 'form-control px-1', 'multiple' => true, 'size' => 1, 'style' => 'height: 2rem']) !!}
                        </div>
                        <div class="form-group col-12 col-md-6 mb-2">
                            <strong>Other Exclusions:</strong>
                            <input placeholder="Other Exclusions" id="umbrella_other_exclusions" class="form-control" name="umbrella_other_exclusions" type="text" value="{{ $lead->umbrella_other_exclusions }}">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-12 col-md-6 mb-2">
                            <strong>Carrier Rating:</strong>
                            <?php $u_selected_r = 0 ?>
                            <select name="umbrella_rating" class="form-control input selectboxcarrier px-1" placeholder="Select Rating" onchange="get_set_othercarrier_val(this,'umbrella_rating')" id="umbrella_rating">
                                <option value="" @if($lead->umbrella_rating == "") <?php $u_selected_r = 1 ?> {{'selected'}} @endif>Select Rating</option>
                                @foreach($ratingsWithUnbrella as $rating)
                                    <option value="{{$rating->id}}" @if($lead->umbrella_rating == $rating->id) <?php $u_selected_r = 1 ?> {{'selected'}} @endif>{{ $rating->name }}</option>
                                @endforeach
                                <option value="other" @if($u_selected_r == 0) {{'selected'}} @endif>Others</option>
                            </select>
                        </div>
                        <div class="form-group col-12 col-md-6 mb-2">
                            <strong>Correct Underlying:</strong>
                            {!! Form::select('correct_underlying',array(
                            'No'=>'No',
                            'Yes'=>'Yes',
                            ),isset($lead) ? $lead->correct_underlying : '', array('class' => 'form-control px-1','id'=>'correct_underlying')) !!}
                        </div>
                        <div class="form-group col-12 mb-2">
                            <div id="umbrella_rating_div" class="mt-2 otherInput" @if($u_selected_r == 1) style="display:none;" @endif>
                                <input placeholder="Other Umbrella Rating" class="form-control" name="umbrella_rating-other"
                                    type="text" value="{{(!empty($lead->uRating->name) && $u_selected_r == 0)?$lead->uRating->name:''}}" id="umbrella_rating-other">
                            </div>
                        </div>
                    </div>
                    <div class="mb-0">
                        <div class="form-row">
                            <div class="form-group col mb-0">
                                <strong>Umbrella Notes:</strong>
                                {!! Form::textarea('u_insurance_coverage', null, array('placeholder' => 'Umbrella Notes','class' => 'form-control d-block','id'=>'u_insurance_coverage','rows' => '4','maxlength'=>'1000')) !!}
                            </div>
                        </div>
                    </div>
                </div>

                <h3 class="mt-2 px-1">Workers Compensation</h3>
                <div class="wrapper_content">
                    <div class="mb-2 form-group">
                        <div class="form-row">
                            <!-- <div class="form-group col mb-0">
                                {!! Form::text('general_liability', null, array('placeholder' => 'Carrier:','class' =>
                                'form-control currentInsurance','maxlength'=>'191')) !!}
                            </div> -->
                            <?php $wc_selected = 0 ?>
                            <div class="form-group col mb-0">
                                <select name="workers_compensation" id="workers_compensation" class="form-control input selectboxcarrier px-1" placeholder="Select Carrier" onchange="get_set_othercarrier_val(this,'workers_compensation')">
                                    <option value="" @if($lead->workers_compensation == "") <?php $wc_selected = 1 ?> {{'selected'}} @endif>Select Carrier</option>
                                    @foreach($carriersWithWorkCompensation as $carrier)
                                        <option value="{{$carrier->id}}" @if($lead->workers_compensation == $carrier->id) <?php $wc_selected = 1 ?> {{'selected'}} @endif>{{ $carrier->name }}</option>
                                    @endforeach
                                    <option value="other" @if($wc_selected == 0) {{'selected'}} @endif>Others</option>
                                </select>
                            </div>
                            <div class="form-group col mb-0">
                                {!! Form::select('WC_ren_month',$months, $lead->WC_ren_month, array('class' =>
                                'form-control multiple px-1','id'=>'WC_ren_month')) !!}
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col mb-0">
                                <div id="workers_compensation_div" class="mt-2 otherInput" @if($wc_selected == 1) style="display:none;" @endif>
                                    <input placeholder="Workers Compensation Carrier" class="form-control" name="workers_compensation-other"
                                        type="text" value="{{(!empty($lead->wcCarrier->name) && $wc_selected == 0)?$lead->wcCarrier->name:''}}" id="workers_compensation-other">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-2">
                        <div class="form-row">
                            <div class="form-group col mb-0">
                                <strong>Expiring Premium: </strong>
                                <div class="input-group">
                                    <span class="input-group-text rounded-right-0">$</span>
                                    {!! Form::number('wc_expiry_premium', null, array('placeholder' => 'Expiring Premium','class' =>
                                    'form-control rounded-left-0','step'=>'any','aria-label'=>'Dollar amount (with dot and two
                                    decimal
                                    places','id'=>'wc_expiry_premium','oninput' => 'restrictInput(this, 8)')) !!}
                                </div>
                            </div>
                            <div class="form-group col mb-0">
                                <strong>Policy Renewal Date: </strong>
                                {!! Form::date('wc_policy_renewal_date', null, array('class' => 'form-control wc_policy_renewal_date','id'=>'wc_policy_renewal_date')) !!}
                            </div>
                        </div>
                    </div>
                    <div class="mb-2">
                        <div class="form-row">
                            <div class="form-group col-12 col-md-6 col-lg-4 mb-0">
                                <strong>Carrier Rating:</strong>
                                <?php $wc_selected_r = 0 ?>
                                <select name="wc_rating" class="form-control input selectboxcarrier px-1" placeholder="Select Rating" onchange="get_set_othercarrier_val(this,'wc_rating')" id="wc_rating">
                                    <option value="" @if($lead->wc_rating == "") <?php $wc_selected_r = 1 ?> {{'selected'}} @endif>Select Rating</option>
                                    @foreach($ratingsWithWorkCompensation as $rating)
                                        <option value="{{$rating->id}}" @if($lead->wc_rating == $rating->id) <?php $wc_selected_r = 1 ?> {{'selected'}} @endif>{{ $rating->name }}</option>
                                    @endforeach
                                    <option value="other" @if($wc_selected_r == 0) {{'selected'}} @endif>Others</option>
                                </select>
                            </div>
                            <div class="form-group col-12 col-md-6 col-lg-4 mb-0">
                                <strong>Employee Count:</strong>
                                {!! Form::number('employee_count', null, array('placeholder' => 'Enter Count ','class' =>
                                    'form-control rounded-left-0','id'=>'employee_count','step'=>'any','oninput' => 'restrictInput(this, 3)')) !!}
                            </div>
                            <div class="form-group col-12 col-md-6 col-lg-4 mb-0">
                                <strong> Employee Payroll:</strong>
                                <div class="input-group">
                                    <span class="input-group-text rounded-right-0">$</span>
                                    {!! Form::number('employee_payroll', null, array('placeholder' => 'Enter Payroll ','class' =>
                                    'form-control rounded-left-0','id'=>'employee_payroll','oninput' => 'restrictInput(this, 6)')) !!}
                                </div>
                            </div>
                            <div class="form-group col-12">
                                <div id="wc_rating_div" class="mt-2 otherInput" @if($wc_selected_r == 1) style="display:none;" @endif>
                                    <input placeholder="Other Workers Compensation Rating" class="form-control" name="wc_rating-other"
                                        type="text" value="{{(!empty($lead->workerCompansestionRating->name) && $wc_selected_r == 0)?$lead->workerCompansestionRating->name:''}}" id="wc_rating-other">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-0">
                        <div class="form-row">
                            <div class="form-group col mb-0">
                                <strong>Workers Compensation Notes:</strong>
                                {!! Form::textarea('wc_insurance_coverage', null, array('placeholder' => 'Workers Compensation Notes','class' => 'form-control d-block','id'=>'wc_insurance_coverage','rows' => '4','maxlength'=>'1000')) !!}
                            </div>
                        </div>
                    </div>
                </div>

                <h3 class="mt-2 px-1">Flood</h3>
                <div class="wrapper_content">
                    <div class="mb-2 form-group">
                        <div class="form-row">
                            <!-- <div class="form-group col mb-0">
                                {!! Form::text('general_liability', null, array('placeholder' => 'Carrier:','class' =>
                                'form-control currentInsurance','maxlength'=>'191')) !!}
                            </div> -->
                            <?php $f_selected = 0 ?>
                            <div class="form-group col mb-0">
                                <select name="flood" id="flood" class="form-control input selectboxcarrier px-1" placeholder="Select Carrier" onchange="get_set_othercarrier_val(this,'flood')">
                                    <option value="" @if($lead->flood == "") <?php $f_selected = 1 ?> {{'selected'}} @endif>Select Carrier</option>
                                    @foreach($carriersWithFlood as $carrier)
                                        <option value="{{$carrier->id}}" @if($lead->flood == $carrier->id) <?php $f_selected = 1 ?> {{'selected'}} @endif>{{ $carrier->name }}</option>
                                    @endforeach
                                    <option value="other" @if($f_selected == 0) {{'selected'}} @endif>Others</option>
                                </select>
                            </div>
                            <div class="form-group col mb-0">
                                {!! Form::select('F_ren_month',$months, $lead->F_ren_month, array('class' =>
                                'form-control multiple px-1','id'=>'F_ren_month')) !!}
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col mb-0">
                                <div id="flood_div" class="mt-2 otherInput" @if($f_selected == 1) style="display:none;" @endif>
                                    <input placeholder="Flood Carrier" class="form-control" name="flood-other"
                                        type="text" value="{{(!empty($lead->floodCarrier->name) && $f_selected == 0)?$lead->floodCarrier->name:''}}" id="flood-other">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-2">
                        <div class="form-row">
                            <div class="form-group col mb-0">
                                <strong>Expiring Premium: </strong>
                                <div class="input-group">
                                    <span class="input-group-text rounded-right-0">$</span>
                                    {!! Form::number('flood_expiry_premium', null, array('placeholder' => 'Expiring Premium','class' =>
                                    'form-control rounded-left-0','step'=>'any','aria-label'=>'Dollar amount (with dot and two
                                    decimal
                                    places','id'=>'flood_expiry_premium','oninput' => 'restrictInput(this, 8)')) !!}
                                </div>
                            </div>
                            <div class="form-group col mb-0">
                                <strong>Policy Renewal Date: </strong>
                                {!! Form::date('flood_policy_renewal_date', null, array('class' => 'form-control flood_policy_renewal_date','id'=>'flood_policy_renewal_date')) !!}
                            </div>
                        </div>
                    </div>
                    <div class="mb-2">
                        <div class="form-row">
                            <div class="form-group col-12 col-md-6 col-lg-4 mb-0">
                                <strong>Carrier Rating:</strong>
                                <?php $f_selected_r = 0 ?>
                                <select name="flood_rating" class="form-control input selectboxcarrier px-1" placeholder="Select Rating" onchange="get_set_othercarrier_val(this,'flood_rating')" id="flood_rating">
                                    <option value="" @if($lead->flood_rating == "") <?php $f_selected_r = 1 ?> {{'selected'}} @endif>Select Rating</option>
                                    @foreach($ratingsWithFlood as $rating)
                                        <option value="{{$rating->id}}" @if($lead->flood_rating == $rating->id) <?php $f_selected_r = 1 ?> {{'selected'}} @endif>{{ $rating->name }}</option>
                                    @endforeach
                                    <option value="other" @if($f_selected_r == 0) {{'selected'}} @endif>Others</option>
                                </select>
                            </div>
                            <div class="form-group col-12 col-md-6 col-lg-4 mb-0">
                                <strong>Elevation Certificate:</strong>
                                {!! Form::select('elevation_certificate',array(
                                'No'=>'No',
                                'Yes'=>'Yes',
                                ),isset($lead) ? $lead->elevation_certificate : '', array('class' => 'form-control px-1','id'=>'elevation_certificate')) !!}
                            </div>
                            <div class="form-group col-12 col-md-6 col-lg-4 mb-0">
                                <strong>Loma Letter :</strong>
                                {!! Form::select('loma_letter',array(
                                'No'=>'No',
                                'Yes'=>'Yes',
                                ),isset($lead) ? $lead->loma_letter : '', array('class' => 'form-control px-1','id'=>'loma_letter')) !!}
                            </div>
                            <div class="form-group col-12 mb-0">
                                <div id="flood_rating_div" class="mt-2 otherInput" @if($f_selected_r == 1) style="display:none;" @endif>
                                    <input placeholder="Other Flood Rating" class="form-control" name="flood_rating-other"
                                        type="text" value="{{(!empty($lead->fRating->name) && $f_selected_r == 0)?$lead->fRating->name:''}}" id="flood_rating-other">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-0">
                        <div class="form-row">
                            <div class="form-group col mb-0">
                                <strong>Flood Notes:</strong>
                                {!! Form::textarea('f_insurance_coverage', null, array('placeholder' => 'Flood Notes','class' => 'form-control d-block','id'=>'f_insurance_coverage','rows' => '4','maxlength'=>'1000')) !!}
                            </div>
                        </div>
                    </div>
                </div>

                <h3 class="mt-2 px-1">Difference In Conditions</h3>
                <div class="wrapper_content">
                    <div class="mb-2 form-group">
                        <div class="form-row">
                            <?php $dic_selected = 0 ?>
                            <div class="form-group col mb-0">
                                <select name="difference_in_condition" id="difference_in_condition" class="form-control input selectboxcarrier px-1" placeholder="Select Carrier" onchange="get_set_othercarrier_val(this,'difference_in_condition')">
                                    <option value="" @if($lead->difference_in_condition == "") <?php $dic_selected = 1 ?> {{'selected'}} @endif>Select Carrier</option>
                                    @foreach($carriersWithDC as $carrier)
                                        <option value="{{$carrier->id}}" @if($lead->difference_in_condition == $carrier->id) <?php $dic_selected = 1 ?> {{'selected'}} @endif>{{ $carrier->name }}</option>
                                    @endforeach
                                    <option value="other" @if($dic_selected == 0) {{'selected'}} @endif>Others</option>
                                </select>
                            </div>
                            <div class="form-group col mb-0">
                                {!! Form::select('dic_ren_month',$months, $lead->dic_ren_month, array('class' =>
                                'form-control multiple px-1','id'=>'dic_ren_month')) !!}
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col mb-0">
                                <div id="difference_in_condition_div" class="mt-2 otherInput" @if($dic_selected == 1) style="display:none;" @endif>
                                    <input placeholder="Difference In Conditions Carrier" class="form-control" name="difference_in_condition-other"
                                        type="text" value="{{(!empty($lead->dcCarrier->name) && $dic_selected == 0)?$lead->dcCarrier->name:''}}" id="difference_in_condition-other">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-2">
                        <div class="form-row">
                            <div class="form-group col mb-0">
                                <strong>Expiring Premium: </strong>
                                <div class="input-group">
                                    <span class="input-group-text rounded-right-0">$</span>
                                    {!! Form::number('dic_expiry_premium', null, array('placeholder' => 'Expiring Premium','class' =>
                                    'form-control rounded-left-0','step'=>'any','aria-label'=>'Dollar amount (with dot and two
                                    decimal
                                    places','id'=>'dic_expiry_premium','oninput' => 'restrictInput(this, 8)')) !!}
                                </div>
                            </div>
                            <div class="form-group col mb-0">
                                <strong>Policy Renewal Date: </strong>
                                {!! Form::date('dic_policy_renewal_date', null, array('class' => 'form-control dic_policy_renewal_date','id'=>'dic_policy_renewal_date')) !!}
                            </div>
                        </div>
                    </div>
                    <div class="mb-2">
                        <div class="form-row">
                            <div class="form-group col-12 col-md-6 mb-2">
                                <strong> Hurricane Deductible:</strong>
                                <select id="dic_hurricane_deductible" class="form-control px-1" name="dic_hurricane_deductible" >
                                    <option value="">Select Hurricane Deductible</option>
                                    <option value="1" @if($lead->dic_hurricane_deductible == 1) {{'selected'}} @endif >1%</option>
                                    <option value="3" @if($lead->dic_hurricane_deductible == 3) {{'selected'}} @endif >3%</option>
                                    <option value="5" @if($lead->dic_hurricane_deductible == 5) {{'selected'}} @endif >5%</option>
                                    <option value="10" @if($lead->dic_hurricane_deductible == 10) {{'selected'}} @endif>10%</option>
                                </select>
                            </div>
                            <div class="form-group col-12 col-md-6 mb-2">
                                <strong>All Other Perils Deductible: </strong>
                                <input placeholder="All Other Perils Deductible" class="form-control" name="dic_all_other_perils" type="text" value="{{$lead->dic_all_other_perils}}" id="dic_all_other_perils">
                            </div>
                        </div>
                    </div>
                    <div class="mb-0">
                        <div class="form-row">
                            <div class="form-group col mb-0">
                                <strong>Difference In Conditions Notes:</strong>
                                {!! Form::textarea('dic_insurance_coverage', null, array('placeholder' => 'Difference In Conditions Notes','class' => 'form-control d-block','id'=>'dic_insurance_coverage','rows' => '4','maxlength'=>'1000')) !!}
                            </div>
                        </div>
                    </div>
                </div>

                <h3 class="mt-2 px-1">X-Wind</h3>
                <div class="wrapper_content">
                    <div class="mb-2 form-group">
                        <div class="form-row">
                            <?php $xw_selected = 0 ?>
                            <div class="form-group col mb-0">
                                <select name="x_wind" id="x_wind" class="form-control input selectboxcarrier px-1" placeholder="Select Carrier" onchange="get_set_othercarrier_val(this,'x_wind')">
                                    <option value="" @if($lead->x_wind == "") <?php $xw_selected = 1 ?> {{'selected'}} @endif>Select Carrier</option>
                                    @foreach($carriersWithXW as $carrier)
                                        <option value="{{$carrier->id}}" @if($lead->x_wind == $carrier->id) <?php $xw_selected = 1 ?> {{'selected'}} @endif>{{ $carrier->name }}</option>
                                    @endforeach
                                    <option value="other" @if($xw_selected == 0) {{'selected'}} @endif>Others</option>
                                </select>
                            </div>
                            <div class="form-group col mb-0">
                                {!! Form::select('xw_ren_month',$months, $lead->xw_ren_month, array('class' =>
                                'form-control multiple px-1','id'=>'xw_ren_month')) !!}
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col mb-0">
                                <div id="x_wind_div" class="mt-2 otherInput" @if($xw_selected == 1) style="display:none;" @endif>
                                    <input placeholder="X-Wind Carrier" class="form-control" name="x_wind-other"
                                        type="text" value="{{(!empty($lead->xwindCarrier->name) && $xw_selected == 0)?$lead->xwindCarrier->name:''}}" id="x_wind-other">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-2">
                        <div class="form-row">
                            <div class="form-group col mb-0">
                                <strong>Expiring Premium: </strong>
                                <div class="input-group">
                                    <span class="input-group-text rounded-right-0">$</span>
                                    {!! Form::number('xw_expiry_premium', null, array('placeholder' => 'Expiring Premium','class' =>
                                    'form-control rounded-left-0','step'=>'any','aria-label'=>'Dollar amount (with dot and two
                                    decimal
                                    places','id'=>'xw_expiry_premium','oninput' => 'restrictInput(this, 8)')) !!}
                                </div>
                            </div>
                            <div class="form-group col mb-0">
                                <strong>Policy Renewal Date: </strong>
                                {!! Form::date('xw_policy_renewal_date', null, array('class' => 'form-control xw_policy_renewal_date','id'=>'xw_policy_renewal_date')) !!}
                            </div>
                        </div>
                    </div>
                    <div class="mb-2">
                        <div class="form-row">
                            <div class="form-group col-12 col-md-6 mb-2">
                                <strong> Hurricane Deductible:</strong>
                                <select id="xw_hurricane_deductible" class="form-control px-1" name="xw_hurricane_deductible" >
                                    <option value="">Select Hurricane Deductible</option>
                                    <option value="1" @if($lead->xw_hurricane_deductible == 1) {{'selected'}} @endif >1%</option>
                                    <option value="3" @if($lead->xw_hurricane_deductible == 3) {{'selected'}} @endif >3%</option>
                                    <option value="5" @if($lead->xw_hurricane_deductible == 5) {{'selected'}} @endif >5%</option>
                                    <option value="10" @if($lead->xw_hurricane_deductible == 10) {{'selected'}} @endif>10%</option>
                                </select>
                            </div>
                            <div class="form-group col-12 col-md-6 mb-2">
                                <strong>All Other Perils Deductible: </strong>
                                <input placeholder="All Other Perils Deductible" class="form-control" name="xw_all_other_perils" type="text" value="{{$lead->xw_all_other_perils}}" id="xw_all_other_perils">
                            </div>
                        </div>
                    </div>
                    <div class="mb-0">
                        <div class="form-row">
                            <div class="form-group col mb-0">
                                <strong>X-Wind Notes:</strong>
                                {!! Form::textarea('xw_insurance_coverage', null, array('placeholder' => 'X-Wind Notes','class' => 'form-control d-block','id'=>'xw_insurance_coverage','rows' => '4','maxlength'=>'1000')) !!}
                            </div>
                        </div>
                    </div>
                </div>

                <h3 class="mt-2 px-1">Equipment Breakdown</h3>
                <div class="wrapper_content">
                    <div class="mb-2 form-group">
                        <div class="form-row">
                            <?php $eb_selected = 0 ?>
                            <div class="form-group col mb-0">
                                <select name="equipment_breakdown" id="equipment_breakdown" class="form-control input selectboxcarrier px-1" placeholder="Select Carrier" onchange="get_set_othercarrier_val(this,'equipment_breakdown')">
                                    <option value="" @if($lead->equipment_breakdown == "") <?php $eb_selected = 1 ?> {{'selected'}} @endif>Select Carrier</option>
                                    @foreach($carriersWithEB as $carrier)
                                        <option value="{{$carrier->id}}" @if($lead->equipment_breakdown == $carrier->id) <?php $eb_selected = 1 ?> {{'selected'}} @endif>{{ $carrier->name }}</option>
                                    @endforeach
                                    <option value="other" @if($eb_selected == 0) {{'selected'}} @endif>Others</option>
                                </select>
                            </div>
                            <div class="form-group col mb-0">
                                {!! Form::select('eb_ren_month',$months, $lead->eb_ren_month, array('class' =>
                                'form-control multiple px-1','id'=>'eb_ren_month')) !!}
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col mb-0">
                                <div id="equipment_breakdown_div" class="mt-2 otherInput" @if($eb_selected == 1) style="display:none;" @endif>
                                    <input placeholder="Equipment Breakdown Carrier" class="form-control" name="equipment_breakdown-other"
                                        type="text" value="{{(!empty($lead->ebCarrier->name) && $eb_selected == 0)?$lead->ebCarrier->name:''}}" id="equipment_breakdown-other">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-2">
                        <div class="form-row">
                            <div class="form-group col mb-0">
                                <strong>Expiring Premium: </strong>
                                <div class="input-group">
                                    <span class="input-group-text rounded-right-0">$</span>
                                    {!! Form::number('eb_expiry_premium', null, array('placeholder' => 'Expiring Premium','class' =>
                                    'form-control rounded-left-0','step'=>'any','aria-label'=>'Dollar amount (with dot and two
                                    decimal
                                    places','id'=>'eb_expiry_premium','oninput' => 'restrictInput(this, 8)')) !!}
                                </div>
                            </div>
                            <div class="form-group col mb-0">
                                <strong>Policy Renewal Date: </strong>
                                {!! Form::date('eb_policy_renewal_date', null, array('class' => 'form-control eb_policy_renewal_date','id'=>'eb_policy_renewal_date')) !!}
                            </div>
                        </div>
                    </div>
                    <div class="mb-2">
                        <div class="form-row">
                            <div class="form-group col-12 col-md-6 mb-2">
                                <strong> Hurricane Deductible:</strong>
                                <select id="eb_hurricane_deductible" class="form-control px-1" name="eb_hurricane_deductible" >
                                    <option value="">Select Hurricane Deductible</option>
                                    <option value="1" @if($lead->eb_hurricane_deductible == 1) {{'selected'}} @endif >1%</option>
                                    <option value="3" @if($lead->eb_hurricane_deductible == 3) {{'selected'}} @endif >3%</option>
                                    <option value="5" @if($lead->eb_hurricane_deductible == 5) {{'selected'}} @endif >5%</option>
                                    <option value="10" @if($lead->eb_hurricane_deductible == 10) {{'selected'}} @endif>10%</option>
                                </select>
                            </div>
                            <div class="form-group col-12 col-md-6 mb-2">
                                <strong>All Other Perils Deductible: </strong>
                                <input placeholder="All Other Perils Deductible" class="form-control" name="eb_all_other_perils" type="text" value="{{$lead->eb_all_other_perils}}" id="eb_all_other_perils">
                            </div>
                        </div>
                    </div>
                    <div class="mb-0">
                        <div class="form-row">
                            <div class="form-group col mb-0">
                                <strong>Equipment Breakdown Notes:</strong>
                                {!! Form::textarea('eb_insurance_coverage', null, array('placeholder' => 'Equipment Breakdown Notes','class' => 'form-control d-block','id'=>'eb_insurance_coverage','rows' => '4','maxlength'=>'1000')) !!}
                            </div>
                        </div>
                    </div>
                </div>

                <h3 class="mt-2 px-1">Commercial AutoMobile</h3>
                <div class="wrapper_content">
                    <div class="mb-2 form-group">
                        <div class="form-row">
                            <?php $ca_selected = 0 ?>
                            <div class="form-group col mb-0">
                                <select name="commercial_automobiles" id="commercial_automobiles" class="form-control input selectboxcarrier px-1" placeholder="Select Carrier" onchange="get_set_othercarrier_val(this,'commercial_automobiles')">
                                    <option value="" @if($lead->commercial_automobiles == "") <?php $ca_selected = 1 ?> {{'selected'}} @endif>Select Carrier</option>
                                    @foreach($carriersWithCA as $carrier)
                                        <option value="{{$carrier->id}}" @if($lead->commercial_automobiles == $carrier->id) <?php $ca_selected = 1 ?> {{'selected'}} @endif>{{ $carrier->name }}</option>
                                    @endforeach
                                    <option value="other" @if($ca_selected == 0) {{'selected'}} @endif>Others</option>
                                </select>
                            </div>
                            <div class="form-group col mb-0">
                                {!! Form::select('ca_ren_month',$months, $lead->ca_ren_month, array('class' =>
                                'form-control multiple px-1','id'=>'ca_ren_month')) !!}
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col mb-0">
                                <div id="commercial_automobiles_div" class="mt-2 otherInput" @if($ca_selected == 1) style="display:none;" @endif>
                                    <input placeholder="Commercial AutoMobile Carrier" class="form-control" name="commercial_automobiles-other"
                                        type="text" value="{{(!empty($lead->caCarrier->name) && $ca_selected == 0)?$lead->caCarrier->name:''}}" id="commercial_automobiles-other">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-2">
                        <div class="form-row">
                            <div class="form-group col mb-0">
                                <strong>Expiring Premium: </strong>
                                <div class="input-group">
                                    <span class="input-group-text rounded-right-0">$</span>
                                    {!! Form::number('ca_expiry_premium', null, array('placeholder' => 'Expiring Premium','class' =>
                                    'form-control rounded-left-0','step'=>'any','aria-label'=>'Dollar amount (with dot and two
                                    decimal
                                    places','id'=>'ca_expiry_premium','oninput' => 'restrictInput(this, 8)')) !!}
                                </div>
                            </div>
                            <div class="form-group col mb-0">
                                <strong>Policy Renewal Date: </strong>
                                {!! Form::date('ca_policy_renewal_date', null, array('class' => 'form-control ca_policy_renewal_date','id'=>'ca_policy_renewal_date')) !!}
                            </div>
                        </div>
                    </div>
                    <div class="mb-2">
                        <div class="form-row">
                            <div class="form-group col-12 col-md-6 mb-2">
                                <strong> Hurricane Deductible:</strong>
                                <select id="ca_hurricane_deductible" class="form-control px-1" name="ca_hurricane_deductible" >
                                    <option value="">Select Hurricane Deductible</option>
                                    <option value="1" @if($lead->ca_hurricane_deductible == 1) {{'selected'}} @endif >1%</option>
                                    <option value="3" @if($lead->ca_hurricane_deductible == 3) {{'selected'}} @endif >3%</option>
                                    <option value="5" @if($lead->ca_hurricane_deductible == 5) {{'selected'}} @endif >5%</option>
                                    <option value="10" @if($lead->ca_hurricane_deductible == 10) {{'selected'}} @endif>10%</option>
                                </select>
                            </div>
                            <div class="form-group col-12 col-md-6 mb-2">
                                <strong>All Other Perils Deductible: </strong>
                                <input placeholder="All Other Perils Deductible" class="form-control" name="ca_all_other_perils" type="text" value="{{$lead->ca_all_other_perils}}" id="ca_all_other_perils">
                            </div>
                        </div>
                    </div>
                    <div class="mb-0">
                        <div class="form-row">
                            <div class="form-group col mb-0">
                                <strong>Commercial AutoMobile Notes:</strong>
                                {!! Form::textarea('ca_insurance_coverage', null, array('placeholder' => 'Commercial AutoMobiles  Notes','class' => 'form-control d-block','id'=>'ca_insurance_coverage','rows' => '4','maxlength'=>'1000')) !!}
                            </div>
                        </div>
                    </div>
                </div>

                <h3 class="mt-2 px-1">Marina</h3>
                <div class="wrapper_content">
                    <div class="mb-2 form-group">
                        <div class="form-row">
                            <?php $m_selected = 0 ?>
                            <div class="form-group col mb-0">
                                <select name="marina" id="marina" class="form-control input selectboxcarrier px-1" placeholder="Select Carrier" onchange="get_set_othercarrier_val(this,'marina')">
                                    <option value="" @if($lead->marina == "") <?php $m_selected = 1 ?> {{'selected'}} @endif>Select Carrier</option>
                                    @foreach($carriersWithMarina as $carrier)
                                        <option  value="{{$carrier->id}}" @if($lead->marina == $carrier->id) <?php $m_selected = 1 ?> {{'selected'}} @endif>{{ $carrier->name }}</option>
                                    @endforeach
                                    <option value="other" @if($m_selected == 0) {{'selected'}} @endif>Others</option>
                                </select>
                            </div>
                            <div class="form-group col mb-0">
                                {!! Form::select('m_ren_month',$months, $lead->m_ren_month, array('class' =>
                                'form-control multiple px-1','id'=>'m_ren_month')) !!}
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col mb-0">
                                <div id="marina_div" class="mt-2 otherInput" @if($m_selected == 1) style="display:none;" @endif>
                                    <input placeholder="Marina Carrier" class="form-control" name="marina-other"
                                        type="text" value="{{(!empty($lead->marinaCarrier->name) && $m_selected == 0)?$lead->marinaCarrier->name:''}}" id="marina-other">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-2">
                        <div class="form-row">
                            <div class="form-group col mb-0">
                                <strong>Expiring Premium: </strong>
                                <div class="input-group">
                                    <span class="input-group-text rounded-right-0">$</span>
                                    {!! Form::number('m_expiry_premium', null, array('placeholder' => 'Expiring Premium','class' =>
                                    'form-control rounded-left-0','step'=>'any','aria-label'=>'Dollar amount (with dot and two
                                    decimal
                                    places','id'=>'m_expiry_premium','oninput' => 'restrictInput(this, 8)')) !!}
                                </div>
                            </div>
                            <div class="form-group col mb-0">
                                <strong>Policy Renewal Date: </strong>
                                {!! Form::date('m_policy_renewal_date', null, array('class' => 'form-control m_policy_renewal_date','id'=>'m_policy_renewal_date')) !!}
                            </div>
                        </div>
                    </div>
                    <div class="mb-2">
                        <div class="form-row">
                            <div class="form-group col-12 col-md-6 mb-2">
                                <strong> Hurricane Deductible:</strong>
                                <select id="m_hurricane_deductible" class="form-control px-1" name="m_hurricane_deductible" >
                                    <option value="">Select Hurricane Deductible</option>
                                    <option value="1" @if($lead->m_hurricane_deductible == 1) {{'selected'}} @endif >1%</option>
                                    <option value="3" @if($lead->m_hurricane_deductible == 3) {{'selected'}} @endif >3%</option>
                                    <option value="5" @if($lead->m_hurricane_deductible == 5) {{'selected'}} @endif >5%</option>
                                    <option value="10" @if($lead->m_hurricane_deductible == 10) {{'selected'}} @endif>10%</option>
                                </select>
                            </div>
                            <div class="form-group col-12 col-md-6 mb-2">
                                <strong>All Other Perils Deductible: </strong>
                                <input placeholder="All Other Perils Deductible" class="form-control" name="m_all_other_perils" type="text" value="{{$lead->m_all_other_perils}}" id="m_all_other_perils">
                            </div>
                        </div>
                    </div>
                    <div class="mb-0">
                        <div class="form-row">
                            <div class="form-group col mb-0">
                                <strong>Marina Notes:</strong>
                                {!! Form::textarea('m_insurance_coverage', null, array('placeholder' => 'Marina Notes','class' => 'form-control d-block','id'=>'m_insurance_coverage','rows' => '4','maxlength'=>'1000')) !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="additonal_accordion">
                <?php $iloop = 0; ?>
                @foreach($additonalPolicy as $adpolicy)
                    <h3 class="px-1 mt-2 position-relative addition_policy_selection_{{$iloop}}" >
                        <span class="addition_policy_h3_{{$iloop}}">Additional Policy ({{$adpolicy->policy_type}})</span>
                        <button type="button" class="close_area border-0 position-absolute end-5" data-id="{{$iloop}}">
                            <i class="fa fa-trash"></i>
                        </button>
                    </h3>
                    <div class="wrapper_content additional_policy addition_policy_selection_{{$iloop}}" data-id="{{$iloop}}">
                        <div class="form-row">
                            <div class="form-group col-12 col-md-6 mb-2">
                                <strong>Policy Type: </strong>
                                <select id="policy_type{{$iloop}}" class="form-control px-1" name="policy_type[]" onchange="getPolicyBasedCarrier(this,'carrier{{$iloop}}','{{$iloop}}')" id="carrier{{$iloop}}">
                                    <option value="">Select Policy Type</option>
                                    @foreach($additionalPoliciesCarrier as $key => $policy)
                                        <option value="{{$key}}" @if($adpolicy->policy_type == $key) {{'selected'}} @endif >{{$key}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-12 col-md-6 mb-2">
                                <input value="{{$adpolicy->id}}" name="policy_id[]" type="hidden" id="policy_id{{$iloop}}">
                                <strong>Carrier:</strong>
                                <?php $p_selected = 0 ?>
                                <select name="carrier[]" class="form-control input selectboxcarrier px-1" placeholder="Select Carrier" onchange="get_set_othercarrier_val(this,'carrier{{$iloop}}')" id="carrier{{$iloop}}">
                                    <option value="" @if($adpolicy->carrier == "") <?php $p_selected = 1 ?> {{'selected'}} @endif>Select Carrier</option>
                                    <option value="other" @if($p_selected == 0) {{'selected'}} @endif>Others</option>
                                </select>
                            </div>
                            <div class="form-group col-12 mb-2">
                                <div id="carrier{{$iloop}}_div" class="mt-2 otherInput" @if($p_selected == 1) style="display:none;" @endif>
                                    <input placeholder="Other Additional Policy Carrier" class="form-control" name="carrier{{$iloop}}-other"
                                        type="text" value="{{!empty($adpolicy->listCarrier->name)?$adpolicy->listCarrier->name:''}}" id="carrier{{$iloop}}-other">
                                </div>
                            </div>
                            <script>
                                document.addEventListener("DOMContentLoaded", function() {
                                    // Get the select element by its ID
                                    let selectElem = document.getElementById("policy_type{{ $iloop }}");

                                    // Ensure element exists before calling function
                                    if (selectElem) {
                                        getPolicyBasedCarrier(selectElem, "carrier{{ $iloop }}",'{{$iloop}}', "{{ $adpolicy->carrier }}");
                                    }
                                });
                            </script>
                        </div>
                        <div class="mb-2">
                            <div class="form-row">
                                <div class="form-group col mb-0">
                                    <strong>Expiring Premium: </strong>
                                    <div class="input-group">
                                        <span class="input-group-text rounded-right-0">$</span>
                                        <input placeholder="Expiring Premium" class="form-control expiry_premium_input" name="a_expiry_premium[]"
                                        type="number" value="{{$adpolicy->expiry_premium}}" id="a_expiry_premium{{$iloop}}" oninput="restrictInput(this, 8)">
                                    </div>
                                </div>
                                <div class="form-group col mb-0">
                                    <strong>Policy Renewal Date: </strong>
                                    <input placeholder="Policy Renewal Date" class="form-control" name="a_policy_renewal_date[]"
                                        type="date" value="{{$adpolicy->policy_renewal_date}}" id="a_policy_renewal_date{{$iloop}}">
                                </div>
                            </div>
                        </div>
                        <div class="mb-2">
                            <div class="form-row">
                                <div class="form-group col-12 col-md-6 mb-2">
                                    <strong> Hurricane Deductible:</strong>
                                    <select id="a_hurricane_deductible{{$iloop}}" class="form-control px-1" name="a_hurricane_deductible[]" >
                                        <option value="">Select Hurricane Deductible</option>
                                        <option value="1" @if($adpolicy->hurricane_deductible == 1) {{'selected'}} @endif >1%</option>
                                        <option value="3" @if($adpolicy->hurricane_deductible == 3) {{'selected'}} @endif >3%</option>
                                        <option value="5" @if($adpolicy->hurricane_deductible == 5) {{'selected'}} @endif >5%</option>
                                        <option value="10" @if($adpolicy->hurricane_deductible == 10) {{'selected'}} @endif>10%</option>
                                    </select>
                                </div>
                                <div class="form-group col-12 col-md-6 mb-2">
                                    <strong>All Other Perils Deductible: </strong>
                                    <input placeholder="All Other Perils Deductible" class="form-control" name="a_all_other_perils[]" type="text" value="{{$adpolicy->all_other_perils}}" id="a_all_other_perils{{$iloop}}">
                                </div>
                            </div>
                        </div>
                        <div class="mb-0">
                            <div class="form-row">
                                <div class="form-group col mb-0">
                                    <strong>Notes:</strong>
                                    <textarea placeholder="Notes" class="form-control d-block" id="insurance_coverage{{$iloop}}" rows="4" maxlength="1000" name="insurance_coverage[]" cols="50" >{{$adpolicy->insurance_coverage}}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php $iloop++; ?>
                @endforeach
            </div>

            <button class="btn btn-sm btn-primary mt-2 add_additional_policy cursor-pointer">
                <i class="fa fa-plus"></i> Add Policy
            </button>

            <div class="my-2">
                <div class="form-row">
                    <div class="form-group col-12 mb-0">
                        <strong  class="text-success">Total Premium: </strong>
                        <div class="input-group">
                            <span class="input-group-text rounded-right-0">$</span>
                            {!! Form::text('total_premium', null, array(
                                'placeholder' => 'Sum of All Premium',
                                'class' => 'form-control rounded-left-0',
                                'step' => 'any',
                                'aria-label' => 'Dollar amount (with dot and two decimal places)',
                                'id' => 'total_premium_sum',
                                'disabled'=>'disabled'
                            )) !!}

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/js/bootstrap.min.js"></script> -->
<script src="{{ asset('js/custom-helper.js') }}"></script>
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

    var count_ofentry = parseInt('{{$iloop}}');

    $(".add_additional_policy").on("click", function (e) {
        e.preventDefault();
        const max_entry = parseInt("{{count($additionalPoliciesCarrier)}}")
        const lenght_entry = parseInt($(".additional_policy").length) || 0;
        // console.log(lenght_entry);
        if (lenght_entry >= max_entry) {
            toastr.error(`You can't add more than ${max_entry} additional policies.`);
            return;
        }

        let newPolicyHtml = `
            <h3 class="px-1 mt-2 position-relative addition_policy_selection_${count_ofentry}" >
                <span class="addition_policy_h3_${count_ofentry}">Additional Policy ${count_ofentry + 1}</span>
                <button type="button" class="close_area border-0 position-absolute end-5" data-id="${count_ofentry}">
                    <i class="fa fa-trash"></i>
                </button>
            </h3>
        <div class="wrapper_content additional_policy addition_policy_selection_${count_ofentry}" data-id="${count_ofentry}">
            <div class="form-row">
                <div class="form-group col-12 col-md-6 mb-2">
                    <strong>Policy Type: </strong>
                    <select id="policy_type${count_ofentry}" class="form-control px-1" name="policy_type[]" onchange="getPolicyBasedCarrier(this,'carrier${count_ofentry}','${count_ofentry}')" id="carrier${count_ofentry}">
                        <option value="">Select Policy Type</option>
                        @foreach($additionalPoliciesCarrier as $key => $policy)
                            <option value="{{$key}}" >{{$key}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-12 col-md-6 mb-2">
                    <strong>Carrier:</strong>
                    <input  name="policy_id[]" type="hidden" id="policy_id${count_ofentry}">
                    <select name="carrier[]" class="form-control input selectboxcarrier px-1" 
                            placeholder="Select Carrier" 
                            onchange="get_set_othercarrier_val(this,'carrier${count_ofentry}')" 
                            id="carrier${count_ofentry}">
                        <option value="">Select Carrier</option>
                    </select>
                </div>
                <div class="form-group col-12 mb-2">
                    <div id="carrier${count_ofentry}_div" class="mt-2 otherInput" style="display:none;">
                        <input placeholder="Other Additional Policy Carrier" class="form-control" 
                               name="carrier${count_ofentry}-other" type="text" id="carrier${count_ofentry}-other">
                    </div>
                </div>
            </div>
            <div class="mb-2">
                <div class="form-row">
                    <div class="form-group col mb-0">
                        <strong>Expiring Premium: </strong>
                        <div class="input-group">
                            <span class="input-group-text rounded-right-0">$</span>
                            <input placeholder="Expiring Premium" class="form-control expiry_premium_input" name="a_expiry_premium[]"
                                   type="number" id="a_expiry_premium${count_ofentry}" oninput="restrictInput(this, 8)">
                        </div>
                    </div>
                    <div class="form-group col mb-0">
                        <strong>Policy Renewal Date: </strong>
                        <input placeholder="Policy Renewal Date" class="form-control" name="a_policy_renewal_date[]"
                               type="date" id="a_policy_renewal_date${count_ofentry}">
                    </div>
                </div>
            </div>
            <div class="mb-2">
                <div class="form-row">
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong> Hurricane Deductible:</strong>
                        <select id="a_hurricane_deductible${count_ofentry}" class="form-control px-1" name="a_hurricane_deductible[]" >
                            <option value="">Select Hurricane Deductible</option>
                            <option value="1">1%</option>
                            <option value="3">3%</option>
                            <option value="5">5%</option>
                            <option value="10">10%</option>
                        </select>
                    </div>
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>All Other Perils Deductible: </strong>
                        <input placeholder="All Other Perils Deductible" class="form-control" name="a_all_other_perils[]" 
                               type="text" id="a_all_other_perils${count_ofentry}">
                    </div>
                </div>
            </div>
            <div class="mb-0">
                <div class="form-row">
                    <div class="form-group col mb-0">
                        <strong>Notes:</strong>
                        <textarea placeholder="Notes" class="form-control d-block" id="insurance_coverage${count_ofentry}" 
                                  rows="4" maxlength="1000" name="insurance_coverage[]" cols="50"></textarea>
                    </div>
                </div>
            </div>
        </div>`;

        // Append new policy section inside #additonal_accordion
        $("#additonal_accordion").append(newPolicyHtml);

        $("#additonal_accordion").accordion("refresh");
        count_ofentry++; // Increment counter
    });

    $(document).on("click", ".close_area",function (e) {
        e.preventDefault();
        const element_id = $(this).data("id");
        // console.log(element_id);
        $(".addition_policy_selection_"+element_id).remove();
        totalpermiumsum();
    });


    document.addEventListener('DOMContentLoaded', function () {
        var gl_exclusions = document.getElementById('gl_exclusions');
        var gl_choices = new Choices(gl_exclusions, {
            removeItemButton: true,  // Show remove button for selected items
            placeholder: true,  // Show placeholder text
            placeholderValue: 'Select Exclusions'
        });

        var umbrella_exclusions = document.getElementById('umbrella_exclusions');
        var u_choices = new Choices(umbrella_exclusions, {
            removeItemButton: true,  // Show remove button for selected items
            placeholder: true,  // Show placeholder text
            placeholderValue: 'Select Exclusions'
        });
    });
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
    // console.log(elem.value,targetElement);
    if(elem.value == "other"){
        $("#"+targetElement+"_div").show();
    }
    else{
        $("#"+targetElement+"_div").hide();
    }
}

function getPolicyBasedCarrier(elem,targetElement,elementId,value="") {
    // console.log(elem,targetElement,value);
    $.ajax({
        type: 'GET', //THIS NEEDS TO BE GET
        url: "{{ url('/leads/carrierList')}}",
        //dataType: 'json',
        data: {
            name: elem.value
        },
        success: function(response) {
            updateCarrierDropdown(targetElement, response.carriers,value);
            if (elem.value == "") {
                document.querySelector(".addition_policy_h3_" + elementId).textContent = `Additional Policy ${elementId + 1}`;
            } else {
                document.querySelector(".addition_policy_h3_" + elementId).textContent = `Additional Policy (${elem.options[elem.selectedIndex].text})`;
            }

        },
        error: function(error) {
        }

    });
}
function updateCarrierDropdown(targetElement, carriers, value) {
    let selectBox = $("#" + targetElement);
    $("#"+targetElement+"_div").hide();

    // Clear existing options
    selectBox.empty();

    // Add default option
    selectBox.append('<option value="">Select Carrier</option>');

    let isMatchFound = false;

    // Append new carrier options and check if value exists
    carriers.forEach(carrier => {
        let isSelected = carrier.id == value ? 'selected' : '';
        if (isSelected) {
            isMatchFound = true;
        }
        selectBox.append(`<option value="${carrier.id}" ${isSelected}>${carrier.name}</option>`);
    });

    // Append "Others" option
    let otherSelected = (!isMatchFound && value !== "" ? 'selected' : '');
    selectBox.append(`<option value="other" ${otherSelected}>Others</option>`);
    if(otherSelected){
        $("#"+targetElement+"_div").show();
    }
    else{
        $("#"+targetElement+"-other").val('');
    }
}


$(document).on('blur','#total_square_footage',function (){
    pricepersquarefootcalculation($('#total_square_footage').val(),$('#insured_amount').val());
});

$(document).on('blur','#insured_amount',function (){
    pricepersquarefootcalculation($('#total_square_footage').val(),$('#insured_amount').val());
});

$(document).on('blur','#gl_expiry_premium',function (){
    priceperunitcalculation($('#gl_expiry_premium').val(),$('#unit_count').val());
});

$(document).on('blur','#unit_count',function (){
    priceperunitcalculation($('#gl_expiry_premium').val(),$('#unit_count').val());
});


function pricepersquarefootcalculation(total_square,toal_insured) {
    if(total_square == '' || toal_insured == ''){
        $("#price_per_sqft").val('');
    }
    else{
        total_square = parseFloat(total_square);
        toal_insured = parseFloat(toal_insured);

        const price_ppt = (toal_insured/total_square).toFixed(2);

        // $("#price_per_sqft").val(formatUSNumberJs(price_ppt));

        document.getElementById('price_per_sqft').value = formatUSNumberJs(price_ppt);
        document.getElementById('price_per_sqft_preview').textContent = '$'+formatUSNumberJs(price_ppt);
    }

}
function priceperunitcalculation(expiry,total) {
    if(expiry == '' || total == ''){
        $("#gl_price_per_unit").val('');
    }
    else{
        expiry = parseFloat(expiry);
        total = parseFloat(total);

        const price = (expiry/total).toFixed(2);

        // console.log(formatUSNumberJs("888908567"));

        document.getElementById('gl_price_per_unit').value = formatUSNumberJs(price);
        document.getElementById('gl_price_per_unit_preview').textContent = '$'+formatUSNumberJs(price);

        // $("#gl_price_per_unit").val(formatUSNumberJs(price));
    }

}

function totalpermiumsum() {
    let sum = 0;

    // Define all premium input field IDs
    const premiumFields = [
        "premium", "gl_expiry_premium", "ci_expiry_premium",
        "do_expiry_premium", "umbrella_expiry_premium", "wc_expiry_premium",
        "flood_expiry_premium", "dic_expiry_premium", "xw_expiry_premium",
        "eb_expiry_premium", "ca_expiry_premium", "m_expiry_premium"
    ];

    // Iterate through fields and sum values
    premiumFields.forEach(id => {
        let value = parseFloat($("#" + id).val()) || 0;
        sum += value;
    });

    // Handle additional policy fields
    $(".additional_policy").each(function(index) {
        let value = parseFloat($("#a_expiry_premium" + index).val()) || 0;
        sum += value;
    });

    // Update UI
    document.getElementById('total_premium_sum').value = formatUSNumberJs(sum);
    document.getElementById('total_premium_sum_preview').textContent = '$' + formatUSNumberJs(sum);

    // return sum;
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

// Attach blur event to all specified inputs
$(document).on('blur', inputIds.join(', '), totalpermiumsum);

pricepersquarefootcalculation("{{ $lead->total_square_footage}}","{{ $lead->insured_amount}}");
priceperunitcalculation("{{ $lead->gl_expiry_premium}}","{{ $lead->unit_count}}");

totalpermiumsum();



</script>
@endpush