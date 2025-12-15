@extends('layouts.app')
@section('pagetitle', 'Import Lead Contacts')
@push('breadcrumbs')
<li class="breadcrumb-item">Business</li>
<li class="breadcrumb-item active">Import Lead Contacts</li>
@endpush
@section('content')
<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12 mb-3">

                <div class="pull-right">
                    <a class="btn btn-info btn-sm px-2" href="{{ route('leads.index') }}"><i
                            class="fas fa-arrow-circle-left"></i> Back</a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-6">
                <div class="card card-secondary">
                    <div class="card-header">
                        <h3 class="card-title">Update Contacts</h3>
                    </div>

                    <div class="card-body p-2 p-lg-3">




                        {{-- {{The custom made errors when an important column is missing in file --}}
                        @if(session('customErrors'))
                        @foreach (session('customErrors') as $error)
                        <span class="text-danger"><small>{!! $error !!}</small></span>
                        @endforeach
                        @endif



                        <form action="{{ route('leads.processContacts') }}" method="POST" enctype="multipart/form-data" id="importForm">
                            @csrf
                            <div class="form-group mb-2">
                                <label for="customFile" class="mb-1">Choose File</label>
                                <div class="d-flex justify-content-between">
                                    <div class="custom-file text-left mr-2">
                                        <label class="custom-file-label" for="customFile">Select file</label>
                                        <input type="file" name="file" class="custom-file-input" id="customFile">
                                    </div>
                                    <button class="btn btn-sm text-nowrap btn-outline-info importDataBtn"><i class="fas fa-file-csv"></i>
                                Import data <span class="spinner-border spinner-border-sm navicon d-none"
                                    role="status" aria-hidden="true"></span>
                                </button>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input class="custom-control-input" type="checkbox" id="customCheckbox2"
                                        name="forceOverwrite">
                                    <label for="customCheckbox2" class="custom-control-label">Force Update</label>
                                </div>
                                <small class="text-muted d-inline-block mt-1">
                                    Overwrite old values with newer ones from the csv (this applies to all columns but
                                    first name, last name and address 1)
                                </small>
                            </div>
                            <div class="mt-3 text-secondary d-none waitInfo">Please wait...This might take a
                                while.<br /> Do not close this window while importing.</div>
                        </form>
                    </div>
                </div>

            </div>
            <div class="col-xl-6">
                <div class="card card-secondary">
                    <div class="card-header">
                        <h3 class="card-title">Importing Information</h3>
                    </div>

                    <div class="card-body text-dark p-2 p-lg-3">
                        <div id="accordion" class="accordian">
                            <div class="">
                                <div class="card-header pt-1 pb-1 px-0" id="headingOne">
                                    <h5 class="mb-0">
                                        <button class="btn px-0" data-toggle="collapse" data-target="#collapseOne"
                                            aria-expanded="true" aria-controls="collapseOne">
                                            Required CSV Table Column Names
                                        </button>
                                    </h5>
                                </div>

                                <div id="collapseOne" class="collapse show" aria-labelledby="headingOne"
                                    data-parent="#accordion">
                                    <div class="card-body p-2">
                                        CSV table column names eligible (case sensitive): <br />
                                        <b> contact_first_name, contact_last_name, contact_address1, contact_address2,
                                            contact_city, contact_state, contact_zip, phone,is_client </b> <br /><br />

                                        if a record is found matching fist name, last name and address 1, any missing
                                        data found in the csv file is updated to the existing record

                                    </div>
                                </div>
                            </div>


                            <div class="">
                                <div class="card-header pt-1 pb-1 px-0" id="headingFour">
                                    <h5 class="mb-0">
                                        <button class="btn px-0 collapsed" data-toggle="collapse" data-target="#collapseFour"
                                            aria-expanded="false" aria-controls="collapseFour">
                                            Sample CSV File
                                        </button>
                                    </h5>
                                </div>
                                <div id="collapseFour" class="collapse" aria-labelledby="headingFour"
                                    data-parent="#accordion">
                                    <div class="card-body p-2">
                                        <button class="btn btn-info"> <a class="text-light "
                                                href="/download/sample-contacts.csv" title="sample CSV file">Download
                                                sample-contacts.csv</a></button>
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
                url: "{{route('leads.import')}}",
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