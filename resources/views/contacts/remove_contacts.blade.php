@extends('layouts.app')
@section('pagetitle', 'Bulk Remove Contacts')
@push('breadcrumbs')
<li class="breadcrumb-item"><a href="{{route('contacts.index')}}">All Contacts</a></li>
<li class="breadcrumb-item active">Bulk Remove Contacts </li>
@endpush
@section('content')
<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12 mb-3">

                <div class="float-right">
                    <a class="btn btn-sm btn-primary" href="{{ route('contacts.index') }}"><i
                            class="fas fa-arrow-circle-left"></i> Back</a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-6">
                <div class="card card-secondary">
                    <div class="card-header bg-info mb-3">
                        <h3 class="card-title">Bulk Remove Contacts</h3>
                    </div>

                    <div class="card-body">




                        {{-- {{The custom made errors when an important column is missing in file --}}
                        @if(session('customErrors'))
                        @foreach (session('customErrors') as $error)
                        <span class="text-danger"><small>{!! $error !!}</small></span>
                        @endforeach
                        @endif



                        <form action="{{ route('contacts.delete_contacts') }}" method="POST"
                            enctype="multipart/form-data" class="mt-2" id="importForm">
                            @csrf
                            <div class="d-flex align-items-start justify-content-between">
                                <div class="form-group">
                                    <div class="custom-file text-left">
                                        <label for="customFile">Select file</label>
                                        <input type="file" name="file" class="form-control-file" id="customFile">
                                    </div>
                                </div>
                                <button class="btn btn-outline-info importDataBtn"><i class="fas fa-file-csv"></i>
                                    Remove Contacts <span class="spinner-border spinner-border-sm navicon d-none"
                                        role="status" aria-hidden="true"></span></button>
                            </div>

                            <div class="mt-3 text-secondary d-none waitInfo">Please wait...This might take a
                                while.<br /> Do not close this window while importing.</div>
                        </form>

                        {{-- errors/success logs --}}
                        @if(session('messages') && !session('customErrors'))



                        {{-- errors log --}}
                        @if(isset(session('messages')['failures']))
                        @php

                        $failure = json_decode(session('messages')['failures']);

                        @endphp
                        @if($failure)
                        <div id="accordionError">
                            <div class="card">
                                <div class=" p-2 d-flex align-items-center justify-content-between">
                                    <h5 class="mb-0">
                                        <button class="btn " data-bs-toggle="collapse" data-bs-target="#error"
                                            aria-expanded="false" aria-controls="error">
                                            <i class="fas fa-exclamation text-danger"></i> Errors happened while
                                            deleting contacts
                                        </button>
                                    </h5>
                                    {{-- <a class ="btn btn-sm btn-outline-secondary"onclick="download_text(this, 'importErrors.txt')" title="Save Errors Log"><i class="fas fa-download"></i></a> --}}
                                </div>

                                <div id="error" class="collapse collapsed contentToSave" aria-labelledby="error"
                                    data-parent="#accordionError">
                                    <div class="card-body p-2 p-lg-3 ">

                                        @foreach ( $failure as $error)

                                        @foreach ( $error as $err)

                                        <span class="text-danger"><small><i class="fas fa-exclamation"></i> Error </b> -
                                                <b>{{$err->errors }}</b> </small></span><br />

                                        @endforeach

                                        @endforeach


                                    </div>

                                </div>
                            </div>
                        </div>
                        @endif
                        @endif

                        @if(isset(session('messages')['success']))

                        {{-- success log --}}
                        @php
                        $success = json_decode(session('messages')['success']);



                        @endphp
                        @if($success)
                        <div id="accordionSuccess">
                            <div class="card">
                                <div class=" p-2 d-flex align-items-center justify-content-between">
                                    <h5 class="mb-0">
                                        <button class="btn " data-bs-toggle="collapse" data-bs-target="#success"
                                            aria-expanded="false" aria-controls="success">
                                            <i class="fas fa-check text-success"></i> Successfull Deletion
                                        </button>
                                    </h5>
                                    {{-- <a class ="btn btn-sm btn-outline-secondary"onclick="download_text(this, 'importSuccess.txt')" title="Save Succesfull Imports Log"><i class="fas fa-download"></i></a> --}}
                                </div>

                                <div id="success" class="collapse collapsed contentToSave" aria-labelledby="headingOne"
                                    data-parent="#accordionSuccess">
                                    <div class="card-body p-2 p-lg-3 ">

                                        @foreach ( $success as $successMessage)


                                        <span class="text-success"><small>{!! $successMessage !!} </small></span><br />


                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        @endif

                        @endif
                    </div>
                </div>

            </div>
            <div class="col-xl-6">
                <div class="card card-secondary">
                    <div class="card-header mb-3">
                        <h3 class="card-title">Removing Contacts - Information</h3>
                    </div>

                    <div class="card-body text-secondary ">

                        <div id="accordion">
                            <div class="">
                                <div class="card-header pt-1 pb-1 px-0" id="headingOne">
                                    <h5 class="mb-0">
                                        <button class="btn" data-bs-toggle="collapse" data-bs-target="#collapseOne"
                                            aria-expanded="true" aria-controls="collapseOne">
                                            Required CSV Table Column Names
                                        </button>
                                    </h5>
                                </div>

                                <div id="collapseOne" class="collapse show" aria-labelledby="headingOne"
                                    data-parent="#accordion">
                                    <div class="card-body pt-3 pb-3 px-2">
                                        CSV table column names eligible (case sensitive): <br />
                                        <b> Contact_First_Name,Contact_Last_Name, Contact_Address1 </b> <br /><br />

                                        if a record is found matching first name, last name and address 1, the record
                                        will be removed

                                    </div>
                                </div>
                            </div>


                            <div class="">
                                <div class="card-header pt-1 pb-1 px-0" id="headingFour">
                                    <h5 class="mb-0">
                                        <button class="btn collapsed" data-bs-toggle="collapse" data-bs-target="#collapseFour"
                                            aria-expanded="false" aria-controls="collapseFour">
                                            Sample CSV File
                                        </button>
                                    </h5>
                                </div>
                                <div id="collapseFour" class="collapse" aria-labelledby="headingFour"
                                    data-parent="#accordion">
                                    <div class="card-body pt-3 pb-3 px-2">
                                        <button class="btn btn-info"> <a class="text-light "
                                                href="/download/sample-remove-contacts.csv"
                                                title="sample CSV file">Download sample-remove-contacts.csv</a></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

        </div><!-- /.container-fluid -->
</section>
<!-- /.content -->
@endsection
@push('styles')
@endpush
@push('scripts')
<script>
function download_text(elem, name) {
    // download logs
    var text = jQuery(elem).parents('.card').children('.contentToSave').children('.card-body').text();
    text = text.replace(/^\s*$[\r\n]*|^[^\S\r\n]+|[^\S\r\n]+$|([^\S\r\n]){2,}|\s+$(?![^])/gm, '');
    var atag = document.createElement("a");
    var file = new Blob([text], {
        type: 'text/plain'
    });
    atag.href = URL.createObjectURL(file);
    atag.download = Date.now() + '-' + name;
    atag.click();
}

$(document).ready(function() {
    $('#create_campaign').prop('checked', false);
    //   $('#importForm .importDataBtn').on('click',function(){
    //     $(this).children('.spinner-border').removeClass('d-none')
    //      $('#importForm .waitInfo').removeClass('d-none')
    //   });
    // });


    //import leads from csv file
    $('.importDataBtn').on('click', function() {
        //check if file input is empty
        // $(this).text('Processing ...').prop('disabled',true);
        var createCamp = false;
        if ($('#create_campaign').is(':checked')) {
            createCamp = true;
            if ($('#campaign_name').val() == "" || $('#campaign_date').val() == "") {
                toastr.error('Campaign Name and Campaign Date are required');
                return false;
            }
        }
        //show loader
        $(this).children('.spinner-border').removeClass('d-none');
        $('#importForm .waitInfo').removeClass('d-none');
        $('#importForm').submit();
        return;

        if (!$('#customFile').val()) {
            toastr.error('Choose a file first !');
            return false;
        }


        var fileData = $('#customFile').prop('files')[0];
        var formData = new FormData();
        formData.append('file', fileData);

        // ajax request
        $.ajax({
                url: "{{route('contacts.delete_contacts')}}",
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                cache: false,
                headers: {
                    'X-CSRF-TOKEN': '{{csrf_token()}}'
                }
            }).always(function() {

            })
            .done(function(response) {
                $('.loader_div.custom').fadeOut();
                $('#import_modal').modal('hide');
                toastr.success('Leads imported successfully !');
                setTimeout(function() {
                    // location.reload();
                }, 3000);
            })
            .fail(function(response) {
                toastr.error(response.responseJSON.message);
            });

    });
});
</script>
@endpush