@extends('layouts.app')
@section('pagetitle', 'Mailer Lead Tracker Form')
@push('breadcrumbs')
<li class="breadcrumb-item active">Mailer Lead</li>
<li class="breadcrumb-item active">Tracker</li>
@endpush
@section('content')
<section class="content">
    <div class="container-fluid dashboard-sec">
        <div class="mt-2 card card-secondary">
            <div class="row">
                <div class="col-12">
                    <div class="card-body lead-update p-0">
                        <div class="p-3 border-bottom">
                            <h6 class="mb-0">Please fill the form to report your numbers daily</h6>
                        </div>
                        <form id="mail_lead_tracker">
                            @csrf
                            <div class="form-row p-3">
                                <input type="hidden" name="mailer_id" value="{{$edit && $mailLead ? $mailLead->id : ''}}">
                                <input type="hidden" name="created_by" value="{{$agentId}}">

                                <div class="form-group col-12 col-md-6 col-lg-4 mb-2">
                                    <strong>Lead Source:</strong>
                                    <select class="form-control" name="lead_source" id="lead_source">
                                        <option value="">Select Lead Source</option>
                                        @foreach($leadSource as $source)
                                            <option value="{{$source->id}}"
                                                @if($edit && $mailLead->lead_source == $source->id) {{'selected'}} @endif
                                            >{{$source->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-12 col-md-6 col-lg-4 mb-2">
                                    <strong>Agent<sup class="mandatoryClass">*</sup>:</strong>
                                    <select class="form-control" name="user_id" id="user_id">
                                        @if($isAdminUser)
                                            <option value="">Select Agent</option>
                                        @endif
                                        @foreach($agentUsers as $agent)
                                            <option value="{{$agent['id']}}"
                                                @if($edit && $mailLead->user_id == $agent['id']) {{'selected'}} @endif
                                            >{{$agent["displayname"]}}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group col-12 col-md-6 col-lg-4 mb-2">
                                    <strong>Date<sup class="mandatoryClass">*</sup>:</strong>
                                    <input id="dateInput" class="form-control" name="date" type="date" value="{{$edit && $mailLead ? $mailLead->date : date('d-m-Y')}}">
                                </div>

                                <div class="col-12 mt-3 mb-1">
                                    <h6 class="text-primary border-bottom pb-1">Business Details</h6>
                                </div>
                                <div class="form-group col-12 col-md-6 col-lg-4 mb-2">
                                    <strong>Business Name<sup class="subMandatoryClass">*</sup>:</strong>
                                    <input placeholder="Business Name" class="form-control" name="business" type="text" value="{{$edit && $mailLead ? $mailLead->business : ''}}">
                                </div>
                                <div class="form-group col-12 col-md-6 col-lg-4 mb-2">
                                    <strong>Business Type<sup class="subMandatoryClass">*</sup>:</strong>
                                    <select class="form-control" name="business_type" id="business_type">
                                        <option value="">Select Type</option>
                                        <option value="Condo" @if($edit && $mailLead->business_type == "Condo") {{'selected'}} @endif>Condo</option>
                                        <option value="HOA" @if($edit && $mailLead->business_type == "HOA") {{'selected'}} @endif>HOA</option>
                                        <option value="Commercial" @if($edit && $mailLead->business_type == "Commercial") {{'selected'}} @endif>Commercial</option>
                                        <option value="Co-Op" @if($edit && $mailLead->business_type == "Co-Op") {{'selected'}} @endif>Co-Op</option>
                                    </select>
                                </div>
                                <div class="form-group col-12 col-md-6 col-lg-4 mb-2">
                                    <strong>Business Address<sup class="subMandatoryClass">*</sup>:</strong>
                                    <input placeholder="Business Address" class="form-control" name="business_address" type="text" value="{{$edit && $mailLead ? $mailLead->business_address : ''}}" pattern="^\d[0-9a-zA-Z\s\/#,._\-:]*$"
                                    title="Address must start with a digit and contain only letters, numbers, spaces, and / # , . _ - :">
                                </div>
                                <div class="form-group col-12 col-md-6 col-lg-4 mb-2">
                                    <strong>Business City<sup class="subMandatoryClass">*</sup>:</strong>
                                    <input placeholder="Business City" class="form-control" name="business_city" type="text" value="{{$edit && $mailLead ? $mailLead->business_city : ''}}">
                                </div>
                                <div class="form-group col-12 col-md-6 col-lg-4 mb-2">
                                    <strong>Business Zip<sup class="subMandatoryClass">*</sup>:</strong>
                                    <input placeholder="Business Zip - 5 digits" class="form-control" name="business_zip" type="text" maxlength="5" value="{{$edit && $mailLead ? $mailLead->business_zip : ''}}">
                                </div>

                                <div class="col-12 mt-3 mb-1">
                                    <h6 class="text-primary border-bottom pb-1">Contact Details</h6>
                                </div>
                                <div class="form-group col-12 col-md-6 col-lg-4 mb-2">
                                    <strong>Contact FirstName<sup class="subMandatoryClass">*</sup>:</strong>
                                    <input placeholder="Contact FirstName" class="form-control " name="contact_firstname" type="text" value="{{$edit && $mailLead ? $mailLead->contact_firstname : ''}}">
                                </div>
                                <div class="form-group col-12 col-md-6 col-lg-4 mb-2">
                                    <strong>Contact LastName<sup class="subMandatoryClass">*</sup>:</strong>
                                    <input placeholder="Contact LastName" class="form-control " name="contact_lastname" type="text" value="{{$edit && $mailLead ? $mailLead->contact_lastname : ''}}">
                                </div>
                                <div class="form-group col-12 col-md-6 col-lg-4 mb-2">
                                    <strong>Phone<sup class="mandatoryClass">*</sup>:</strong>
                                    <input placeholder="Phone" class="form-control " id="phone" name="phone" type="text" value="{{$edit && $mailLead ? $mailLead->phone : ''}}">
                                </div>
                                <div class="form-group col-12 col-md-6 col-lg-4 mb-2">
                                    <strong>Email Address:</strong>
                                    <input placeholder="Email Address" class="form-control" name="email_address" type="text" value="{{$edit && $mailLead ? $mailLead->email_address : ''}}">
                                </div>
                                <div class="form-group col-12 col-md-6 col-lg-4 mb-2">
                                    <strong>Contact Address<sup class="subMandatoryClass">*</sup>:</strong>
                                    <input placeholder="Contact Address" class="form-control" name="contact_address" type="text" value="{{$edit && $mailLead ? $mailLead->contact_address : ''}}" pattern="^\d[0-9a-zA-Z\s\/#,._\-:]*$"
                                    title="Address must start with a digit and contain only letters, numbers, spaces, and / # , . _ - :">
                                </div>
                                <div class="form-group col-12 col-md-6 col-lg-4 mb-2">
                                    <strong>Contact Title:</strong>
                                    <select class="form-control" name="contact_title" id="contact_title">
                                        @foreach($contactsTitle as $key => $title)
                                            <option value="{{$key}}"
                                                @if($edit && $mailLead->contact_title == $key) {{'selected'}} @endif
                                            >{{$title}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-12 col-md-6 col-lg-4 mb-2">
                                    <strong>Contact Status:</strong>
                                    <select class="form-control" name="contact_status" id="contact_status">
                                        <?php
                                            $status_selected = 0;
                                            if($edit && !empty($mailLead->contact_status)){
                                                $selcted_status = $mailLead->contact_status;
                                            }
                                            if(empty($selcted_status)){
                                                $selcted_status = 8;
                                            }
                                        ?>
                                        @foreach($statusOptions as $keyStatus => $statusOption)
                                            @if(!empty($statusOption->false_status) && $statusOption->false_status == 1)
                                                <option value="{{ $statusOption->id }}" @if($statusOption->id == $selcted_status && $status_selected == 0) <?php $status_selected = 1; ?> {{'selected'}} @endif>
                                                    {{ $statusOption->name }}
                                                </option>
                                            @endif
                                        @endforeach

                                        <optgroup label="Prospecting">
                                            @foreach($statusOptions as $keyStatus => $statusOption)
                                                @if($statusOption->false_status != 1 && $statusOption->display_in_pipedrive == null)
                                                    <option value="{{ $statusOption->id }}" @if($statusOption->id == $selcted_status && $status_selected == 0) <?php $status_selected = 1; ?> {{'selected'}} @endif>
                                                        {{ $statusOption->name }}
                                                    </option>
                                                @endif
                                            @endforeach
                                        </optgroup>

                                        <optgroup label="Pipeline">
                                            @foreach($statusOptions as $keyStatus => $statusOption)
                                                @if($statusOption->false_status != 1 && $statusOption->display_in_pipedrive != null)
                                                    <option value="{{ $statusOption->id }}" @if($statusOption->id == $selcted_status && $status_selected == 0) <?php $status_selected = 1; ?> {{'selected'}} @endif>
                                                        {{ $statusOption->name }}
                                                    </option>
                                                @endif
                                            @endforeach
                                        </optgroup>
                                    </select>
                                </div>

                                <div class="form-group col-12 mb-2">
                                    <strong>Status Notes:</strong>
                                    <textarea name="status_note" id="status_note" placeholder="Status Notes" class="form-control" rows="4">{{trim($edit && $mailLead ? $mailLead->status_note : '')}}</textarea>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between px-3 pb-3">
                                <button type="submit" class="btn btn-info btn-sm">
                                    <i class="fas fa-file-alt"></i> @if($edit) Update @else Submit @endif
                                </button>
                            </div>
                        </form>
                        <div class="px-3 pb-4">
                            <div class="alert alert-light border mt-2 small">
                                <p class="mb-1">
                                    <sup class="mandatoryClass">*</sup> — Required field for <strong>Mailer Lead Tracker form submission</strong>
                                </p>
                                <p class="mb-0">
                                    <sup class="subMandatoryClass">*</sup> — Required field for <strong>Business creation</strong> after Mailer Lead Tracker form submission
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
@push('styles')
@endpush
@push('scripts')
<script src="{{ asset('js/ckeditor-reuired-function.js') }}" defer></script>
<script >
    // Get today's date in YYYY-MM-DD format
    const input = document.getElementById("dateInput");
    const today = new Date().toISOString().split("T")[0];
    input.setAttribute("max", today);
    // input.value = today;


    let theEditor;
    $('#mail_lead_tracker').on('submit', function(e) {
        e.preventDefault();

        if(document.getElementById("user_id").value == ""){
            toastr.error("Agent is required");
            return;
        }
        if(document.getElementById("dateInput").value == ""){
            toastr.error("Date is required");
            return;
        }
        if(document.getElementById("phone").value == ""){
            toastr.error("Phone is required");
            return;
        }
        document.getElementById('status_note').value = theEditor.getData();

        // Prepare form data
        let formData = new FormData(this);

        $.ajax({
            url: '{{ route("agentreport.saveMailLeadTracker") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function() {
                $('body').append(`{!! trim(preg_replace('/\s+/', ' ', view('partials.formSubmission_loader')->render())) !!}`);
            },
            success: function(response) {
                if(response.status){
                    toastr.success(response.message);
                    $('#mail_lead_tracker')[0].reset(); // reset form
                    if(response.redirection){
                        setTimeout(function() {
                            window.location.href = '{{ route("agentreport.mailerLeadReport") }}';
                        }, 1500); // 1500 milliseconds = 1.5 seconds
                    }
                    else{
                        setTimeout(function() {
                            window.location.reload();
                        }, 2000);
                    }
                }
                else{
                    toastr.error(response.message);
                }

            },
            error: function(xhr) {
                console.error(xhr);
                toastr.error('Something went wrong.');
                // alert('Something went wrong.');
            },
            complete: function() {
                $('.ajax-loader-wrapper').remove();
            }
        });
    });

    document.addEventListener('DOMContentLoaded', function () {

        ClassicEditor
        .create(document.getElementById('status_note'), {
            extraPlugins: [ MyCustomUploadAdapterPlugin ],
        })
        .then(editor => {
            theEditor = editor;

            @if(trim($edit) && $mailLead && !empty($mailLead->status_note))
                editor.setData(`{!! addslashes($mailLead->status_note) !!}`);
            @endif
        })
        .catch(error => {
            console.error(error);
        });

        // ClassicEditor
        // .create(document.getElementById('status_note'))
        // .then(editor => {
        //     theEditor = editor;

        //     @if(trim($edit) && $mailLead && !empty($mailLead->status_note))
        //         editor.setData(`{!! addslashes($mailLead->status_note) !!}`);
        //     @endif

            
        // })
        // .catch(error => {
        //     console.error(error);
        // });


    });
</script>
@endpush