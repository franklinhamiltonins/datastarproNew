<div class="card-body lead-update p-0">
    @if(isset($lead))
    <div class="card card-secondary mb-0 border-0 shadow-none">
        <div class="card-body p-0 pt-2">
            <div class="form-row px-2">
                <div class="form-group col-12 col-md-6 mb-2">
                    <strong> Wind Mitigation Date:</strong>  <span class="small">{{!empty($lead->wind_mitigation_date)?date('m/d/Y',strtotime($lead->wind_mitigation_date)):"N/A"}}</span>
                </div>
                <div class="form-group col-12 col-md-6 mb-2">
                    <strong>Roof Year: </strong> <span class="small">{{!empty($lead->roof_year)?$lead->roof_year:"N/A"}}</span>
                </div>
            </div>

        
            <div class="form-row px-2">
                <div class="form-group col-12 col-md-6 mb-2">
                    <strong>Roof Covering:</strong> <span class="small">{{!empty($lead->roof_covering)?$lead->roof_covering:"N/A"}}</span>
                </div>
                <div class="form-group col-12 col-md-6 mb-2">
                    <strong>Roof Connection:</strong> <span class="small">{{!empty($lead->roof_connection)?$lead->roof_connection:"N/A"}}</span>
                </div>
            </div>
        
            <div class="form-row px-2">
                <div class="form-group col-12 col-md-6 mb-2">
                    <strong>Roof Geometry:</strong> <span class="small">{{!empty($lead->roof_geom)?$lead->roof_geom:"N/A"}}</span>
                </div>
                <div class="form-group col-12 col-md-6 mb-2">
                    <strong> SWR:</strong>  <span class="small">{{!empty($lead->secondary_water_insurance)?$lead->secondary_water_insurance:"No"}}</span>
                </div>
            </div>

            <div class="form-row px-2">
                <div class="form-group col-12 col-md-6 mb-2">
                    <strong>Opening Protection:</strong>  <span class="small">{{!empty($lead->opening_protection)?$lead->opening_protection:"No"}}</span>
                </div>
            </div>

            <div class="form-row px-2">
                <div class="form-group col-12 mb-2">
                    <strong>Report Notes:</strong> <span class="small">{{!empty($lead->other_community_info)?$lead->other_community_info:"N/A"}}</span>
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