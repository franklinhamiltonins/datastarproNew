@extends('layouts.app')
@section('pagetitle', 'Import Scrap Bot')
@push('breadcrumbs')
<li class="breadcrumb-item"><a href="{{route('bot.settings')}}">Bot Import</a></li>
<li class="breadcrumb-item active">Import Scrap Bot </li>
@endpush
@section('content')

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12 mb-3">

                <div class="pull-right">
                    <a class="btn btn-primary btn-sm" href="{{ route('bot.settings') }}"><i
                            class="fas fa-arrow-circle-left"></i> Back</a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-6">
                <div class="card card-secondary">
                    <div class="card-header bg-info mb-3">
                        <h3 class="card-title">Import Scrap Bot </h3>
                    </div>

                    <div class="card-body">



                        @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                        @endforeach





                        {{-- {{The custom made errors when an important column is missing in file --}}
                        @if(session('customErrors'))
                        @foreach (session('customErrors') as $error)
                        <span class="text-danger"><small>{!! $error !!}</small></span>
                        @endforeach
                        @endif

                        <form action="{{ route('bot.import_scrap') }}" method="POST" enctype="multipart/form-data"
                            class="mt-2" id="importForm">

                            @csrf

                            <div class="d-flex align-items-center">

                                <div class="form-group mb-4 mr-3">
                                    <div class="custom-file text-left">
                                        <label for="customFile">Select file</label>
                                        <input type="file" name="file" class="form-control-file" id="customFile"
                                            accept=".xls,.xlsx,.csv">
                                    </div>

                                </div>
                                <button class="btn btn-outline-info importDataBtn"><i class="fas fa-file-csv"></i>
                                    Import data <span class="spinner-border spinner-border-sm navicon d-none"
                                        role="status" aria-hidden="true"></span></button>

                            </div>
                            <div class="mt-3 text-secondary d-none waitInfo">Please wait...This might take a
                                while.<br /> Do not close this window while importing.</div>
                            {{-- <a class="btn btn-success" href="{{ route('import_scrap') }}">Export data</a> --}}
                        </form>

                        {{-- errors/success logs --}}
                        @if(session('messages') && !session('customErrors'))

                        {{-- errors log --}}
                        @if(isset(session('messages')['failures']))
                        @php
                        // dd($error['failures']);
                        $failure = json_decode(session('messages')['failures']);
                        // dd($failure);
                        // dd($failure);
                        @endphp
                        @if($failure)
                        <div id="accordionError">
                            <div class="card">
                                <div class=" p-2 d-flex align-items-center justify-content-between">
                                    <h5 class="mb-0">
                                        <button class="btn " data-toggle="collapse" data-target="#error"
                                            aria-expanded="false" aria-controls="error">
                                            <i class="fas fa-exclamation text-danger"></i> Errors happened while
                                            importing the file
                                        </button>
                                    </h5>
                                    <a class="btn btn-sm btn-outline-secondary"
                                        onclick="download_text(this, 'importErrors.txt')" title="Save Errors Log"><i
                                            class="fas fa-download"></i></a>
                                </div>

                                <div id="error" class="collapse collapsed contentToSave" aria-labelledby="error"
                                    data-parent="#accordionError">
                                    <div class="card-body p-2 p-lg-3">

                                        @foreach ( $failure as $error)

                                        @foreach ( $error as $err)

                                        <span class="text-danger"><small><i class="fas fa-exclamation"></i> Error on row
                                                <b>{{$err->row }}</b> - <b>{{$err->errors }}</b> </small></span><br />

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
                                        <button class="btn " data-toggle="collapse" data-target="#success"
                                            aria-expanded="false" aria-controls="success">
                                            <i class="fas fa-check text-success"></i> Successfull Imports
                                        </button>
                                    </h5>
                                    <a class="btn btn-sm btn-outline-secondary"
                                        onclick="download_text(this, 'importSuccess.txt')"
                                        title="Save Succesfull Imports Log"><i class="fas fa-download"></i></a>
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
                        <h3 class="card-title">Importing Information</h3>
                    </div>

                    <div class="card-body text-secondary ">

                        <div id="accordion">
                            <div class="">
                                <div class="card-header pt-1 pb-1 px-0" id="headingOne">
                                    <h5 class="mb-0">
                                        <button class="btn" data-toggle="collapse" data-target="#collapseOne"
                                            aria-expanded="true" aria-controls="collapseOne">
                                            Required CSV Table Column Names
                                        </button>
                                    </h5>
                                </div>

                                <div id="collapseOne" class="collapse show" aria-labelledby="headingOne"
                                    data-parent="#accordion">
                                    <div class="card-body pt-3 pb-3 px-2">
                                        CSV table column names required for a succesfull CSV/XLS/XLSX file import :
                                        <br />
                                        'Search Keyword','City','State','State Code','County',
                                    </div>
                                </div>
                            </div>
                            <!-- <div class="">
                                <div class="card-header pt-1 pb-1 px-0" id="headingTwo">
                                    <h5 class="mb-0">
                                        <button class="btn collapsed" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                            CSV Cell Data Validation Information
                                        </button>
                                    </h5>
                                </div>
                                <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordion">
                                    <div class="card-body pt-3 pb-3 px-2">
                                        The CSV cell data will be validated by the following rules :<br />
                                        <h6> Leads </h6>
                                        <ul>
                                            <li><b>Business_Type</b> - can be empty & text</li>
                                            <li><b>Business_Name</b> - required & text & unique</li>
                                            <li><b>Business_Creation_Date</b> - can be empty / date format shoud be : YYYY/MM/DD or DD/MM/YY or MM/DD <br />
                                                Other formats will be skipped or messed up (eg: if you write 1/1/2021 and refer to format to m/d/y . If the second digit(here day) is greater than 12, will cause error because date will be considered as format d/m/y. </li>
                                            <li><b>Business_Address1</b> - can be empty & text starts with a number</li>
                                            <li><b>Business_Address2</b> - can be empty / text</li>
                                            <li><b>Business_City</b> - can be empty & text</li>
                                            <li><b>Business_State</b> - can be empty & text </li>
                                            <li><b>Business_Zip</b> - can be empty & text max 5 digits</li>
                                            <li><b>Business_County</b> - can be empty & text 2 letters </li>
                                            <li><b>Business_Unit_Count</b> - can be empty & number with maximum 4 digits </li>
                                            <li><b>Property_Insurance_Renewal_Date</b> - can be empty / date format shoud be : YYYY/MM/DD or DD/MM/YY or MM/DD <br />
                                                Other formats will be skipped or messed up (eg: if you write 1/1/2021 and refer to format to m/d/y . If the second digit(here day) is greater than 12, will cause error because date will be considered as format d/m/y. </li>
                                            <li><b>Property_Insurance_Renewal_Month</b> - can be empty / text </li>
                                            <li><b>Business_Premium</b> - can be empty / number with maximum 2 decimals </li>
                                            <li><b>Business_Insured_Amount</b> - can be empty / number with maximum 2 decimals </li>
                                            <li><b>Management_Company</b> - can be empty & text </li>
                                            <li><b>Property_Manager</b> - can be empty & text </li>
                                            <li><b>Current_Agency</b> - can be empty & text </li>
                                            <li><b>Current_Agent</b> - can be empty & text </li>
                                            <li><b>Insurance_Property_Carrier</b> - can be empty & text </li>
                                            <li><b>Insurance_Flood</b> - can be empty ("Yes" or "No")</li>
                                            <li><b>General_Liability</b> - can be empty & text </li>
                                            <li><b>General_Liability_Renewal_Month</b> - can be empty & text </li>
                                            <li><b>Crime_Insurance</b> - can be empty & text </li>
                                            <li><b>Crime_Insurance_Renewal_Month</b> - can be empty & text </li>
                                            <li><b>Directors_Officers</b> - can be empty & text </li>
                                            <li><b>Directors_Officers Renewal Month</b> - can be empty & text </li>
                                            <li><b>Workers_Compensation</b> - can be empty & text </li>
                                            <li><b>Workers_Compensation Renewal Month</b> - can be empty & text </li>
                                            <li><b>Umbrella</b> - can be empty & text </li>
                                            <li><b>Umbrella_Renewal Month</b> - can be empty & text </li>
                                            <li><b>Flood</b> - can be empty & text </li>
                                            <li><b>Flood_General_Liability_Renewal_Month</b> - can be empty & text </li>
                                        </ul>

                                        <br />
                                        <br />
                                        <h6> Contacts </h6>
                                        <ul>
                                            <li><b>Contact_Title</b> - required & text</li>
                                            <li><b>Contact_First_Name</b> - required & text</li>
                                            <li><b>Contact_Last_Name</b> - required & text</li>
                                            <li><b>Contact_Address1</b> - required & text starts with a number</li>
                                            <li><b>Contact_Address2</b> - can be empty / text</li>
                                            <li><b>Contact_City</b> - required & text</li>
                                            <li><b>Contact_State</b> - required & text</li>
                                            <li><b>Contact_Zip</b>- required & text max 5 digits</li>
                                            <li><b>Contact_County</b> - required & text 2 letters </li>
                                            <li><b>Contact_Phone</b> - can be empty / number ,format xxx-xxx-xxxx</li>
                                            <li><b>Contact_Email</b> can be empty / email format</li>
                                        </ul>
                                        <br />
                                        <br />
                                        <h6> Actions </h6>
                                        <ul>
                                            <li><b>Response date</b>- can be empty / date format shoud be : YYYY/MM/DD or DD/MM/YY or MM/DD <br />
                                                Other formats will be skipped or messed up (eg: if you write 1/1/2021 and refer to format to m/d/y . If the second digit(here day) is greater than 12, will cause error because date will be considered as format d/m/y. </li>

                                        </ul>
                                        <br />
                                        <p class="small"><b>Property Insurance Renewal Month</b> : when the CSV contains a Property_Insurance_Renewal_Month with "This Year" value and its corresponding database field value is empty, the Lead Property_Insurance_Renewal_Month will be set to empty. <br />
                                            If the corresponding database field value is filled, nothing will happen.</p>
                                        <p class="small">* If one row contains the same Bussiness Name will not be imported. It will compare the CSV cells values corresponding to the database fields of the existing Bussiness Name lead, and if a database field is empty, will update the field with the corresponding cell value . <br>
                                            Then will look for contact in same row, to import for the existing lead having the same Bussiness Name in database . Else , it gets imported , also the contacts</p>
                                        <p class="small">* If one cell contains a value that doesn't match the specified format (eg: wrong date format ,Unit Count more than 4 digits), the lead will be imported with no value for that field </p>
                                        <p class="small">* If one lead contact contains the same First Name + Last Name + Address1 as one already existing in database for that lead, will not be imported. Else , it gets imported<br />
                                            Contact will be skipped if one of the required cell value is empty.</p>

                                        <p class="small">* If one lead row contains Response_Date, Contact_First_Name and Contact_Last_Name cell values, a new action 'Phone' will be created. <br />
                                            Before adding a new action it will search if the action already exists: same contact name (Contact_First_Name + Contact_Last_Name), Response_Date (as created date) and same action (Phone). If it does, will be attached to the lead if it is not already attached. If not, a new action will be created ,attaching it to the lead <br />
                                        </p>



                                    </div>
                                </div>
                            </div>
                            <div class="">
                                <div class="card-header pt-1 pb-1 px-0" id="headingThree">
                                    <h5 class="mb-0">
                                        <button class="btn collapsed" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                            Leads and Contact CSV table structure
                                        </button>
                                    </h5>
                                </div>
                                <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#accordion">
                                    <div class="card-body pt-3 pb-3 px-2">
                                        For each contact assigned to a lead, the lead row needs to be copied . For 5 contacts will result 5 rows with the same lead data, but different contact data.<br>
                                        <br />
                                        When importing , the lead will be only once imported, and each contact found will be imported .<br />
                                        <br />
                                        <span class="small">
                                            Import logic:
                                            <br />
                                            <b>1.</b> if the lead Bussiness Name doesn't exist => create it -><br />
                                            &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;<b>a.</b> get its row contact <br />
                                            &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; -> if the contact with same first_name & last_name & address1 doesn't exist for that lead => create it <br />
                                            &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; -> if the contact with same first_name & last_name & address1 exists for that lead => if one of these fields title, address2, phone, email is empty in database but contains value in CSV, update it . <br />
                                            &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;<b>b.</b> if it contains Response_Date -> if the action using same contact name (Contact_First_Name + Contact_Last_Name), Response_Date (as created date) and same action (Phone) doesn't exist -> create action & attach to lead <br />
                                            &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;<b>c.</b> if it contains Response_Date -> if the action using same contact name (Contact_First_Name + Contact_Last_Name), Response_Date (as created date) and same action (Phone) already exists -> attach to lead (if it is not already attached)<br /><br />

                                            <b>2.</b> if the lead Bussiness Name exists => compare cells corresponding to database fields of the existing one: if cell contains data but database field doesn't->update that specific field -> <br />
                                            &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;<b>a.</b> look for row contact <br />
                                            &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; -> if the contact with same first_name & last_name & address1 doesn't exist for that lead => create it <br />
                                            &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; -> if the contact with same first_name & last_name & address1 exists for that lead => if one of these fields title, address2, phone, email is empty in database but contains value in CSV, update it . <br />
                                            &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;<b>b.</b> if it contains Response_Date -> if the action using same contact name (Contact_First_Name + Contact_Last_Name), Response_Date (as created date) and same action (Phone) doesn't exist -> create action & attach to lead<br />
                                            &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;<b>c.</b> if it contains Response_Date -> if the action using same contact name (Contact_First_Name + Contact_Last_Name), Response_Date (as created date) and same action (Phone) already exists -> attach to lead (if it is not already attached)
                                        </span>
                                    </div>
                                </div>
                            </div> -->

                            <div class="">
                                <div class="card-header pt-1 pb-1 px-0" id="headingFour">
                                    <h5 class="mb-0">
                                        <button class="btn collapsed" data-toggle="collapse" data-target="#collapseFour"
                                            aria-expanded="false" aria-controls="collapseFour">
                                            Sample CSV File
                                        </button>
                                    </h5>
                                </div>
                                <div id="collapseFour" class="collapse" aria-labelledby="headingFour"
                                    data-parent="#accordion">
                                    <div class="card-body pt-3 pb-3 px-2">
                                        <button class="btn btn-info"> <a class="text-light "
                                                href="/download/sample_county.csv" title="sample CSV file">Download
                                                sample.csv</a></button>
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

window.addEventListener("dragover", function(e) {
    e = e || event;
    e.preventDefault();
}, false);
window.addEventListener("drop", function(e) {
    e = e || event;
    e.preventDefault();
}, false);
</script>
@endpush