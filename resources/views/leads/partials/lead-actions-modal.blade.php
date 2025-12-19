{{-- Actions Modal --}}
<div class="modal fade" id="userLeadActions" data-source="" style="display: none;">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content p-0">
            <div class="modal-header p-2 p-lg-3 align-items-center">
                <h5 class="modal-title">Set action for: {{$lead->name}} </h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            {{-- {!! Form::open(array('route' =>  ['leads.actions', $lead->id])) !!} --}}
            <form action="{{ route('leads.actions', $lead->id) }}" method="POST">
                @csrf

                <input type="hidden" name="contact_name" />
                <input type="hidden" name="user_id" value="{{auth()->user()->id}}" />
                <div class="modal-body p-2 p-lg-3">


                    <div class="form-group mb-3">
                        <strong>Action:</strong>


                        <select class="form-control multiple " name="action">
                            <option value="E-mail">E-mail</option>
                            <option value="SMS">SMS</option>
                            <option value="Phone">Phone</option>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <strong>Initiated by Contact:</strong>


                        <select class="form-control multiple ana" name="contact_id"
                            onchange="get_set_other_val_actions(this)" required>

                            @foreach ($contactsFullNames as $key=>$contactName)
                            <option value="{{ $key }}" @if (app('request')->input('contact_id') == $key)
                                {{ 'selected' }} @endif: {{ ''}}>{{ $contactName }}
                                @endforeach

                        </select>

                        <div id="countyOther" class="mt-2 otherInput" style="display:none;text-transform: lowercase; ">
                            <input placeholder="Other Contact" class="form-control capitalize" name="county-other"
                                type="text">
                        </div>
                    </div>
                    <div class="form-group mb-3">


                        <select class="form-control multiple " name="campaign_id" @if (empty($campaigns) ||
                            $campaigns->isEmpty()) disabled @endif>
                            @foreach ($campaigns as $campaign)

                            <option value="{{ $campaign->id }}" @if (app('request')->input('contact_id') == $key)
                                {{ 'selected' }} @endif: {{ 'diable'}}>{{ $campaign->name }}
                                @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-0">
                        <input type="date" name="contact_date" class="form-control " required />

                    </div>
                </div>


                <div class="modal-footer justify-content-between p-2 p-lg-3">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="confirm" class="btn btn-info">Add Action</button>
                </div>
            </form>

        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

@push('scripts')
<script>
$(document).ready(function() {

    if ($('select[name="contact_id"]').find(":selected").text() != null && $('select[name="contact_id"]').find(
            ":selected").val() != "") {
        $('input[name="contact_name"]').val($('select[name="contact_id"]').find(":selected").text());
    }
});

// get the value of the input and set it for "other" option in the dropdown
function get_set_other_val_actions(elem) {


    var inputContainer = $(elem).siblings('.otherInput') //get the container element
    var input = $(elem).siblings('.otherInput').find('input'); //get the input element
    $(elem).find('option[value="other"]').addClass('other'); //add class to "other" option
    //when user selects an option
    var elemVal = $(elem).val();
    //if the option is "other"
    if ($(elem).val() == $(elem).find('.other').val() && $(elem).find('.other').prop('selected') == true) {
        //show the input
        $(inputContainer).fadeIn(500);

    } else {
        //hide the input
        $(inputContainer).fadeOut(500);
        //reset val
        $(inputContainer).find('input').val('');
    }

    if ($(elem).val() != 'other' && $('select[name="contact_id"]').find(":selected").val() != "") {

        $(elem).parents('form').find('input[name="contact_name"]').val($(elem).children('option:selected').text());

    } else if ($('select[name="contact_id"').find(":selected").val() == "") {

        $(elem).parents('form').find('input[name="contact_name"]').val('');

    }



    //when input value changes
    $(input).on('keyup', function() {
        // console.log($(input).val());
        //add the value to the "other option in the dropdown"
        $(elem).parents('form').find('input[name="contact_name"]').val($(input).val() ? $(input).val() : $(elem)
            .val());
        $(elem).find('.other').attr('value', '');

    });

}
</script>
@endpush