<div class="card-body lead-update p-0 pt-4">
    @if(isset($lead))
    <div class="card card-secondary mb-0 border-0 shadow-none">
        <div class="card-body p-0">
            <div class="mb-2">
                <div class="form-row">
                    <div class="form-group col-12 col-md-6 col-lg-4 mb-0">
                        <strong> Wind Mitigation Date:</strong>
                        {!! Form::date('wind_mitigation_date', null, array('id' => 'wind_mitigation_date','class' => 'form-control wind_mitigation_date')) !!}
                    </div>
                    <div class="form-group col-12 col-md-6 col-lg-4 mb-0">
                        <strong>Roof Year: </strong>
                        {!! Form::select('roof_year', $years, isset($lead)? $lead->roof_year : [] , array('class'
                        => 'form-control multiple premium_year px-1','id'=>'roof_year')) !!}
                    </div>
                    <div class="form-group col-12 col-md-6 col-lg-4 mb-0">
                        <strong>Roof Covering:</strong>
                        {!! Form::select('roof_covering',$roofcovering,isset($lead) ? $lead->roof_covering : [], array('class'
                        => 'form-control multiple px-1','id'=>'roof_covering')) !!}
                    </div>
                </div>
            </div>

            <div class="mb-2">
                <div class="form-row">
                    <div class="form-group col-12 col-md-6 col-lg-4 mb-0">
                        <strong>Roof Connection:</strong>
                        {!! Form::select('roof_connection', [
                            '' => 'Select Roof Connection',
                            'Hurricane clips' => 'Hurricane clips',
                            'Hurricane straps' => 'Hurricane straps',
                            'Toe Nails' => 'Toe Nails',
                            'Anchor Bolts with Top Plates' => 'Anchor Bolts with Top Plates',
                            'N/A' => 'N/A',
                        ], isset($lead) ? $lead->roof_connection : null, ['class' => 'form-control px-1','id'=>'roof_connection']) !!}
                    </div>
                    <div class="form-group col-12 col-md-6 col-lg-4 mb-0">
                        <strong>Roof Geometry:</strong>
                        {!! Form::select('roof_geom',$roof_geometry,isset($lead) ? $lead->roof_geom : [], array('class' => 'form-control multiple px-1','id'=>'roof_geom')) !!}
                    </div>
                    <div class="form-group col-12 col-md-6 col-lg-4 mb-0">
                        <strong> SWR:</strong>
                        {!! Form::select('secondary_water_insurance',array(
                        'No'=>'No',
                        'Yes'=>'Yes',
                        ),isset($lead) ? $lead->secondary_water_insurance : '', array('id' => 'secondary_water_insurance','class' => 'form-control  px-1')) !!}
                    </div>
                </div>
            </div>

            <div class="mb-2">
                <div class="form-row">
                    <div class="form-group col-12 col-md-6 col-lg-4 mb-0">
                        <strong>Opening Protection:</strong>
                        <!-- list will be shared -->
                        {!! Form::select('opening_protection',array(
                        'No'=>'No',
                        'Yes'=>'Yes',
                        ),isset($lead) ? $lead->opening_protection : '', array('id' => 'opening_protection','class' => 'form-control px-1')) !!}
                    </div>
                </div>
            </div>

            <div class="mb-2">
                <div class="form-row">
                    <div class="form-group col-12 col-md-12 col-lg-12 mb-0">
                        <strong>Report Notes:</strong>
                        {!! Form::textarea('other_community_info', null, array('placeholder' => 'Other Community Info','class' => 'form-control d-block','id'=>'other_community_info','rows' => '3','maxlength'=>'500')) !!}
                    </div>
                </div>
            </div>
            
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>


</script>
@endpush