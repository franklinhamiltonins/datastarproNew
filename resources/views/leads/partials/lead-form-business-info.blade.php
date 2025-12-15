<div class="card-body lead-update p-0">
    @if(isset($lead))
    <div class="form-group">
        <div class="custom-control custom-switch">
            {{-- <input type="checkbox" class="custom-control-input" {{$lead && $lead->is_client == 1 && $lead->contacts()->NotClient()->count() == 0 ? 'checked' : ''}}
            name="c_client" value="1" id="clientSwitch_{{$lead ? $lead->id : 0}}"> --}}
            <input type="checkbox" class="custom-control-input current_clientswitch" {{$lead && $lead->is_client == 1 ? 'checked' : ''}}
                name="is_client" value="1" id="clientSwitch_{{$lead ? $lead->id : 0}}">
            <label class="custom-control-label" for="clientSwitch_{{$lead ? $lead->id : 0}}">Current Client</label>
        </div>
    </div>
    @endif

    <div class="form-row">
        <div class="form-group col-12 col-md-3 mb-0">
            <strong>Business Type<sup class="mandatoryClass">*</sup>:</strong>
            {{-- hardcoded dropdown --}}
            {!! Form::select('type',array(
            ''=>'Select Type',
            'Condo'=>'Condo',
            'HOA'=>'HOA',
            'Commercial'=>'Commercial',
            'Co-Op'=>'Co-Op',
            ),isset($lead) ? $lead->type : [], array('class' => 'form-control multiple px-1','id'=>'type')) !!}
        </div>
        <div class="form-group col-12 col-md-9">
            <strong>Business Name<sup class="mandatoryClass">*</sup>:</strong>
            {!! Form::text('name', null, array('placeholder' => 'Business Name ','class'=> 'form-control','id'=>'name')) !!}
        </div>
    </div>
    

    <div class="mb-2">
        <div class="form-row">
            <div class="form-group col-12 col-md-6 col-lg-4 mb-0">
                <strong>Year Built:</strong>
                {!! Form::date('creation_date', null, array('class' => 'form-control businessCreationDate thisYearLimitRestriction','id'=>'creation_date')) !!}
            </div>
            <div class="form-group col-12 col-md-6 col-lg-4 mb-0">
                <strong>Total Square Footage :</strong>
                {!! Form::number('total_square_footage', null, array('id' => 'total_square_footage' ,'placeholder' => 'Square Footage - max 7 digits','class' =>
                'form-control','max' => 9999999,'oninput' => 'restrictInput(this, 7)')) !!}
            </div>
            <div class="form-group col-12 col-md-6 col-lg-4 mb-0">
                <strong>Business Unit Count :</strong>
                {!! Form::number('unit_count', null, array('placeholder' => 'Unit - max 4 digits','class' =>
                'form-control','max' => 9999,'id'=> 'unit_count' ,'oninput' => 'restrictInput(this, 4)')) !!}
            </div>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group col-12 col-md-6">
            <strong>Business Address 1<sup class="mandatoryClass">*</sup>:</strong>
            {!! Form::textarea('address1', null, array('placeholder' => 'Business Address - must start with a number','class' =>
            'form-control','id'=>'address1','rows' => '4','pattern'=>'^\d[0-9a-zA-Z\s\/#,._-:]*$','title'=>'Chars allowed: # . - _ ,')) !!}
        </div>
        <div class="form-group col-12 col-md-6">
            <strong>Business Address 2:</strong>
            {!! Form::textarea('address2', null, array('placeholder' => 'Business Address2','rows' => '4','class' =>'form-control','id'=>'address2')) !!}
        </div>
    </div>

    <div class="mb-2">
        <div class="form-row">
            <div class="form-group col-12 col-md-6 col-lg-4 mb-0">
                <strong>County:</strong>
                {!! Form::select('county',isset($lead) ?
                array_merge(array($lead->county=>$lead->county),$counties):$counties ,isset($lead) ? $lead->county : [],
                array('class' => 'form-control multiple px-1','id'=>'county','onchange'=>'get_set_other_val(this)')) !!}
                <div id="countyOther" class="mt-2 otherInput" style="display:none;text-transform: lowercase; ">
                    <input placeholder="Other County" class="form-control capitalize" name="county-other" type="text">
                </div>
            </div>
            <div class="form-group col-12 col-md-6 col-lg-4 mb-0">
                <strong>Coastal / Non Coastal:</strong>
                <select class="form-control multiple " name="coastal" id="coastal">
                    <option value="0" @if(empty($lead->coastal)) {{'selected'}} @endif>Non Coastal</option>
                    <option value="1" @if(!empty($lead->coastal)) {{'selected'}} @endif>Coastal</option>
                </select>
            </div>
            <div class="form-group col-12 col-md-6 col-lg-4 mb-0">
                <strong>Total insured value: </strong>
                <div class="input-group">
                    <span class="input-group-text rounded-right-0">$</span>
                    {!! Form::number('business_tiv', null, array('id' => 'business_tiv', 'placeholder' =>
                    'T.I.V.','class' => 'form-control rounded-left-0', 'step'=>'any',
                    'aria-label'=>'Dollar amount (with
                    dot and two decimal places','oninput' => 'restrictInput(this, 10)')) !!}
                </div>
            </div>
        </div>
    </div>

    <div class="mb-2">
        <div class="form-row">
            <div class="form-group col-12 col-md-6 col-lg-4 mb-0">
                <strong>City<sup class="mandatoryClass">*</sup>:</strong>
                {!! Form::text('city', null, array('placeholder' => 'City','class' => 'form-control','id'=>'city')) !!}
            </div>
            <div class="form-group col-12 col-md-6 col-lg-4 mb-0">
                <strong>State:</strong>
                {!! Form::select('state', $states, isset($lead) ? $lead->state : 'FL', array('class' => 'form-control
                multiple USstates px-1','id'=>'state')) !!}
            </div>
            <div class="form-group col-12 col-md-6 col-lg-4 mb-0">
                <strong>Zip<sup class="mandatoryClass">*</sup>:</strong>
                {!! Form::text('zip', null, array('placeholder' => 'Zip - 5 digits ','class'
                =>'form-control integer-only','maxlength' => '5','id'=>'zip')) !!}
            </div>
        </div>
    </div>
    <div class="mb-2">
        <div class="form-row">
            <div class="form-group col-12 col-md-6 col-lg-4 mb-0">
                <strong>Appraiser Name:</strong>
                <input placeholder="Appraiser Name" class="form-control" name="appraisal_name" type="text" value="{{$lead->appraisal_name}}" id="appraisal_name">
            </div>
            <div class="form-group col-12 col-md-6 col-lg-4 mb-0">
                <strong>Appraisal Company:</strong>
                <input placeholder="Appraisal Company" class="form-control" name="appraisal_company" type="text" value="{{$lead->appraisal_company}}" id="appraisal_company">
            </div>
            <div class="form-group col-12 col-md-6 col-lg-4 mb-0">
                <strong>Apraisal Date:</strong>
                {!! Form::date('appraisal_date', null, array('id' => 'appraisal_date','class' => 'form-control appraisal_date')) !!}
            </div>
        </div>
    </div>
    <div class="mb-2">
        <div class="form-row">
            <div class="form-group col-12 col-md-6 col-lg-4 mb-0">
                <strong>Flood Zone:</strong>
                {!! Form::select('ins_flood',array(
                'No'=>'No',
                'Yes'=>'Yes',
                ),isset($lead) ? $lead->ins_flood : [], array('class' => 'form-control multiple px-1','id'=>'ins_flood')) !!}
            </div>
            <div class="form-group col-12 col-md-6 col-lg-4 mb-0">
                <strong>Property Floors:</strong>
                {!! Form::select('prop_floor',[''=>'Select any option'] + array_combine(range(1,100),
                range(1,100)),isset($lead) ? $lead->prop_floor : [], array('class' => 'form-control multiple px-1','id'=>'prop_floor')) !!}
            </div>
            <div class="form-group col-12 col-md-6 col-lg-4 mb-0">
                <strong>Pool:</strong>
                {!! Form::select('pool',array(
                'No'=>'No',
                'Yes'=>'Yes',
                ),isset($lead) ? $lead->pool : [], array('class' => 'form-control px-1','id'=> 'pool')) !!}
            </div>
        </div>
    </div>

    <div class="mb-2">
        <div class="form-row">
            <div class="form-group col-12 col-md-6 col-lg-4 mb-0">
                <strong>Lakes:</strong>
                {!! Form::select('lakes',array(
                'No'=>'No',
                'Yes'=>'Yes',
                ),isset($lead) ? $lead->lakes : [], array('class' => 'form-control px-1','id'=> 'lakes')) !!}
            </div>
            <div class="form-group col-12 col-md-6 col-lg-4 mb-0">
                <strong>Clubhouse:</strong>
                {!! Form::select('clubhouse',array(
                'No'=>'No',
                'Yes'=>'Yes',
                ),isset($lead) ? $lead->clubhouse : [], array('class' => 'form-control px-1','id'=> 'clubhouse')) !!}
            </div>
            <div class="form-group col-12 col-md-6 col-lg-4 mb-0">
                <strong>Tennis/Basketball Court:</strong>
                {!! Form::select('tennis_basketball',array(
                'No'=>'No',
                'Yes'=>'Yes',
                ),isset($lead) ? $lead->tennis_basketball : [], array('class' => 'form-control px-1','id'=> 'tennis_basketball')) !!}
            </div>
        </div>
    </div>
    <div class="mb-2">
        <div class="form-row">
            <div class="form-group col-12 col-md-6 col-lg-4 mb-0">
                <strong>ISO:</strong>
                {!! Form::number('iso', null, array('placeholder' => 'ISO - max 2 digits','class' =>
                'form-control','id'=> 'iso','max' => 99,'oninput' => 'restrictInput(this, 2)' )) !!}
            </div>
            <div class="form-group col-12 col-md-6 col-lg-4 mb-0">
                <strong>Lead Source:</strong>
                <select name="lead_source" id="lead_source" class="form-control input selectboxcarrier px-1" placeholder="Select Lead Source" >
                    <option value="" @if($lead->lead_source == "") {{'selected'}} @endif>Select Lead Source</option>
                    @foreach($leadSource as $lsource)
                        <option value="{{$lsource->id}}" @if($lead->lead_source == $lsource->id) {{'selected'}} @endif >{{ $lsource->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const currentYear = new Date().getFullYear();
    const startYear = 1970; // Starting year
    const dropdown = document.getElementById('roof_year');

    // Populate the dropdown with years
    for (let year = currentYear; year >= startYear; year--) {
        const option = document.createElement('option');
        option.value = year;
        option.textContent = year;
        dropdown.appendChild(option);
    }

    if("{{$lead->roof_year}}" != ""){
        dropdown.value = "{{$lead->roof_year}}";
    }

    // console.log("{{$lead->roof_year}}");

</script>
@endpush