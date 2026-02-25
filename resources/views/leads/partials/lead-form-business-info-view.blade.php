<div class="card-body lead-update p-0 pt-2">
    @if(isset($lead))
    <div class="form-group mb-0">
        <div class="custom-control custom-switch pl-2 pb-2">
             @if($lead && $lead->is_client == 1 && $lead->contacts()->NotClient()->count() == 0)
                <p class="m-0">Current Client</p>
             @else
             @endif
        </div>
    </div>
    @endif
    <div class="form-row m-0 mb-2">
        <div class="form-group col-12 col-md-4 mb-0 p-2 border-top border-bottom">
            <strong class="mb-0">Type:</strong>
            <span class="small">
                {{!empty($lead->type)?$lead->type:"N/A"}}
            </span>
        </div>
        <div class="form-group col-12 col-md-4 mb-0 p-2 border-top border-bottom">
            <strong class="mb-0">Year Built:</strong>
            <span class="small">
                {{!empty($lead->creation_date)?date('m/d/Y',strtotime($lead->creation_date)):"N/A"}}
            </span>
        </div>
        <div class="form-group col-12 col-lg-4 mb-0 p-2 border-top border-bottom">
            <strong class="mb-0">Unit Count:</strong>
            <span class="small">
                {{!empty($lead->unit_count)?$lead->unit_count:"N/A"}}
            </span>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group col-12 px-3">
            <strong>Business Address 1:</strong>
            <span class="small">
                {{!empty($lead->address1)?$lead->address1:"N/A"}}
            </span>
        </div>
        <div class="form-group col-12 px-3">
            <strong>Business Address 2:</strong>
            <span class="small">
                {{!empty($lead->address2)?$lead->address2:"N/A"}}
            </span>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group col-12 col-md-6 col-lg-4 mb-2 px-3">
            <strong>City:</strong>
            <span class="small">
                {{!empty($lead->city)?$lead->city:"N/A"}}
            </span>
        </div>
        <div class="form-group col-12 col-md-6 col-lg-3 mb-2 px-3">
            <strong>County :</strong>
            <span class="small">
                {{!empty($lead->county)?$lead->county:"N/A"}}
            </span>
        </div>
        <div class="form-group col-12 col-md-6 col-lg-5 mb-2 px-3">
            <strong>Coastal / Non Coastal:</strong>
            <span class="small">
                {{$lead->coastal ? 'Coastal' : 'Non Coastal'}}
            </span>
        </div>
    </div>
    <div class="form-row">
        <div class="form-group col-12 col-md-6 col-lg-4 mb-2 px-3">
            <strong>State:</strong>
            <span class="small">
                {{!empty($lead->state)?$lead->state:"N/A"}}
            </span>
        </div>
        <div class="form-group col-12 col-md-6 col-lg-3 mb-2 px-3">
            <strong>Zip:</strong>
            <span class="small">
                {{!empty($lead->zip)?$lead->zip:"N/A"}}
            </span>
        </div>
        <div class="form-group col-12 col-md-6 col-lg-5 mb-2 px-3">
            <strong>Total Square Footage:</strong>
            <span class="small">
                {{!empty($lead->total_square_footage)?$lead->total_square_footage:"N/A"}}
            </span>
        </div>
    </div>
    <div class="form-row">
        <div class="form-group col-12 col-md-6 col-lg-4 mb-2 px-3">
            <strong>Total insured value: </strong>
            <span class="small">
                {{!empty($lead->business_tiv)?'$'.formatUSNumber($lead->business_tiv,2):"N/A"}}
            </span>
        </div>
        <div class="form-group col-12 col-md-6 col-lg-3 mb-2 px-3">
            <strong>Appraiser Name:</strong>
            <span class="small">
                {{!empty($lead->appraisal_name)?$lead->appraisal_name:"N/A"}}
            </span>
        </div>
        <div class="form-group col-12 col-md-6 col-lg-5 mb-2 px-3">
            <strong>Appraisal Company:</strong>
            <span class="small">
                {{!empty($lead->appraisal_company)?$lead->appraisal_company:"N/A"}}
            </span>
        </div>
    </div>
    <div class="form-row ">
        <div class="form-group col-12 col-md-7 mb-2 px-3">
            <strong>Appraisal Date:</strong>
            <span class="small">
                {{!empty($lead->appraisal_date)?date('m/d/Y',strtotime($lead->appraisal_date)):"N/A"}}
            </span>
        </div>
        <div class="form-group col-12 col-md-5 mb-2 px-3">
            <strong>Flood Zone:</strong>
            <span class="small">
                {{!empty($lead->ins_flood)?$lead->ins_flood:"No"}}
            </span>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group col-12 col-md-7 mb-2 px-3">
            <strong>Property Floors:</strong>
            <span class="small">
                {{!empty($lead->prop_floor)?$lead->prop_floor:"N/A"}}
            </span>
        </div>
        <div class="form-group col-12 col-md-5 mb-2 px-3">
            <strong>Pool: </strong>
            <span class="small">
                {{!empty($lead->pool)?$lead->pool:"No"}}
            </span>
        </div>
    </div>
    <div class="form-row">
        <div class="form-group col-12 col-md-7 mb-2 px-3">
            <strong>Lakes:</strong>
            <span class="small">
                {{!empty($lead->lakes)?$lead->lakes:"No"}}
            </span>
        </div>
        <div class="form-group col-12 col-md-5 mb-2 px-3">
            <strong>Clubhouse:</strong>
            <span class="small">
                {{!empty($lead->clubhouse)?$lead->clubhouse:"No"}}
            </span>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group col-12 col-md-7 mb-2 px-3">
            <strong>Tennis/Basketball Court:</strong>
            <span class="small">
                {{!empty($lead->tennis_basketball)?$lead->tennis_basketball:"No"}}
            </span>
        </div>
        <div class="form-group col-12 col-md-5 mb-2 px-3">
            <strong>ISO:</strong>
            <span class="small">
                {{!empty($lead->iso)?$lead->iso:"N/A"}}
            </span>
        </div>
    </div>
    <div class="form-row">
        <div class="form-group col-12 col-md-7 mb-2 px-3">
            <strong>Lead Source:</strong>
            <span class="small">
                {{!empty($lead->leadSource->name)?$lead->leadSource->name:"N/A"}}
            </span>
        </div>
    </div>
</div>

@push('scripts')
<script>
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
            $("#"+targetElement).show();
        } else {
            $("#"+targetElement).hide();
        }
    }
</script>
@endpush
