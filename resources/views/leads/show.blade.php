@extends('layouts.app')
@section('pagetitle', 'View Lead')
@push('breadcrumbs')
<li class="breadcrumb-item"><a href="{{route('leads.index')}}">All Leads</a></li>

<li class="breadcrumb-item active">View Lead</li>
@endpush
@section('content')
<link href="/css/jquery.dataTables.min.css" rel="stylesheet">
<script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js" defer></script>
<!-- Main content -->
<section class="content">
    <div class="container-fluid pb-3">
        <div class="row">
            <div class="col-lg-12 margin-tb mb-3">
                <div class="pull-right d-flex flex-wrap flex-nowrap-md align-items-center justify-content-between">
                    <a class="btn btn-info btn-sm px-2 mb-3 mb-md-0"
                        href="{{ route('leads.index', ['id' => $lead->id]) }}"
                        onclick="redirectToLastLeadsManagementUrl(event)"><i class="fas fa-arrow-circle-left"></i>
                        Back</a>
                    <div>
                        <button class="btn btn-sm btn-secondary m-0" type="button" data-toggle="modal"
                            data-target="#logModal">
                            <i class="fa fa-comment-dots"></i>
                            <span class="d-none d-lg-inline"> Lead Log</span>
                        </button>
                        @can('lead-action')
                        <button class="btn btn-sm btn-warning action-btn m-0" data-toggle="modal"
                            data-target="#userLeadActions">
                            <i class="fas fa-mouse-pointer"></i>
                            <span class="d-none d-lg-inline"> Add Lead Actions</span>
                        </button>
                        @endcan
                        @can('lead-edit')
                        <a class="btn btn-success btn-sm action-btn m-0" href="{{ route('leads.edit',base64_encode($lead->id)) }}"><i
                                class="fa fa-edit"></i>
                            <span class="d-none d-lg-inline"> Edit Business</span>
                        </a>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
        <h5 class="text-dark mb-3 pb-1 border-0">
            {{ $lead->name }}

            <span class="badge badge-secondary ml-2 badgeLabel">
                {{ $lead->leadStatus->name }}
            </span>

            @if(!empty($lead->ownedAgent->name))
                <span class="badge badge-secondary ml-2 badgeLabel" >
                    {{ $lead->ownedAgent->name }}
                </span>
            @endif
        </h5>
        <ul class="nav nav-tabs nav-justified " id="pills-tab" role="tablist">
            <li class="nav-item">
                <a class="nav-link active upperpaneltab_leads" id="pills-Lead-tab" data-toggle="pill" href="#pills-Lead" role="tab"
                    aria-controls="pills-Lead" aria-selected="true">Lead</a>
            </li>
            <li class="nav-item">
                <a class="nav-link upperpaneltab_leads" id="pills-client-insurance-info-tab" data-toggle="pill"
                    href="#pills-client-insurance-info" role="tab" aria-controls="pills-client-insurance-info"
                    aria-selected="true">Client Insurance Info</a>
            </li>
            <li class="nav-item">
                <a class="nav-link upperpaneltab_leads" id="pills-lead-actions-tab" data-toggle="pill" href="#pills-lead-actions" role="tab"
                    aria-controls="pills-lead-actions" aria-selected="true"> Lead Actions</a>
            </li>
            <li class="nav-item">
                <a class="nav-link upperpaneltab_leads" id="pills-lead-campaigns-tab" data-toggle="pill" href="#pills-lead-campaigns"
                    role="tab" aria-controls="pills-lead-campaigns" aria-selected="true"> Lead Campaigns</a>
            </li>
            @can('lead-file-list')
            <li class="nav-item">
                <a class="nav-link t upperpaneltab_leads" id="pills-File-tab" data-toggle="pill" href="#pills-File" role="tab"
                    aria-controls="pills-File" aria-selected="false">Uploaded Files</a>
            </li>
            @endcan
        </ul>
        <div class="tab-content pt-3 bg-white p-2 view-section border border-top-0 rounded rounded-top-0"
            id="pills-tabContent">
            <div class="tab-pane show active" id="pills-Lead" role="tabpanel" aria-labelledby="pills-Lead-tab">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-xl-7">
                            <ul class="nav nav-tabs nav-justified" id="pills-tab-2" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active small px-1 lowerpaneltab_leads " id="pills-Lead-tab-2" data-toggle="pill" href="#pills-Lead-2" role="tab"
                                        aria-controls="pills-Lead-2" aria-selected="true">Appraisal</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link small px-1 lowerpaneltab_leads" id="pills-lead-actions-tab-2" data-toggle="pill" href="#pills-lead-actions-2" role="tab"
                                        aria-controls="pills-lead-actions-2" aria-selected="true">Wind Mitigation</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link small px-1 lowerpaneltab_leads" id="pills-lead-campaigns-tab-2" data-toggle="pill" href="#pills-lead-campaigns-2"
                                        role="tab" aria-controls="pills-lead-campaigns-2" aria-selected="true">Prospect’s Insurance</a>
                                </li>
                            </ul>
                            <div class="tab-content bg-white border border-top-0 rounded rounded-top-0 edit-section" id="pills-tabContent-2" bis_skin_checked="1">
                                <div class="tab-pane show active" id="pills-Lead-2" role="tabpanel" aria-labelledby="pills-Lead-tab-2" bis_skin_checked="1">
                                    <div class="card card-secondary p-0 rounded-top-0 mb-0 shadow-none">
                                        <!-- <h3 class="card-title fs-2 mb-0 pb-2 border-bottom px-2 pt-2">Business</h3> -->
                                        
                                        <div class="lead-edit-form">
                                            <!-- lead-edit-form-scroll-->
                                            <div class="showViewLeadsArea">
                                                @include('leads.partials.lead-form-business-info-view')
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane" id="pills-lead-actions-2" role="tabpanel" aria-labelledby="pills-lead-actions-tab-2" bis_skin_checked="1">
                                    <div class="showViewLeadsArea">
                                        @include('leads.partials.lead-form-community-info-view')
                                    </div>
                                </div>
                                <div class="tab-pane" id="pills-lead-campaigns-2" role="tabpanel" aria-labelledby="pills-lead-campaigns-tab-2" bis_skin_checked="1">
                                    <div class="showViewLeadsArea">
                                        @include('leads.partials.lead-form-property-info-view')
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-5">
                            <div class="card card-secondary">
                                <div class="card-header px-3 py-2">
                                    <h3 class="card-title py-1">Contacts Information</h3>
                                </div>
                                <div class="card-body p-2 px-3">
                                    <div id="contactsAccordion">
                                        @php
                                        $i = 0
                                        @endphp
                                        @if (count($contacts) <1 ) <div class="card-body p-2 p-lg-3 text-center">
                                            The Lead does not have any contacts yet .
                                    </div>
                                    @else
                                    @foreach ($contacts as $contact )
                                    @php
                                    $i++
                                    @endphp
                                    <div class=""> 
                                        <div class="card-header pt-1 pb-1 px-0" id="heading{{$i}}">
                                            <h3 class="position-relative mt-2 accordian-header-light @if($contact->fake_address == 1 || $contact->fake_address == 2) {{'fakeClass'}} @endif">
                                                <div class="custom_contact_class p-0 rounded">
                                                    <div class="card-header pt-1 pb-1 px-0 border-0">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <button class="btn w-100 p-0 pl-2 text-left" data-toggle="collapse"
                                                                data-target="#collapse{{$i}}" aria-expanded="true"
                                                                aria-controls="collapse{{$i}}">

                                                                <div class="d-flex justify-content-between align-items-center">
                                                                    <h6 class="text-left mb-1">
                                                                        <strong>{{$contact->c_first_name}} {{$contact->c_last_name}}</strong>
                                                                    </h6>
                                                                    <div>
                                                                        @php
                                                                            $statusMap = [
                                                                                1 => ['text' => 'Already Exist', 'class' => 'badge-success'],
                                                                                2 => ['text' => 'Newly Added', 'class' => 'badge-warning'],
                                                                                3 => ['text' => 'Not Associated', 'class' => 'badge-danger'],
                                                                            ];
                                                                        @endphp

                                                                        @if(isset($statusMap[$contact->new_scrap_status]))
                                                                            <span class="badge {{ $statusMap[$contact->new_scrap_status]['class'] }}">
                                                                                {{ $statusMap[$contact->new_scrap_status]['text'] }}
                                                                            </span>
                                                                        @endif

                                                                        <span class="badge badge-{{$contact->c_is_client ? 'success' : 'danger'}}">{{$contact->c_is_client ? 'Current Client' : 'Not a client'}}</span>
                                                                    </div>
                                                                </div>
                                                                @if($contact->c_title)
                                                                    <div class="designation"><em>{{$contact->c_title}}</em></div>
                                                                @endif

                                                                <div class="d-flex justify-content-between align-items-center cntct-info-action">
                                                                    <span class="phne-no">
                                                                        @if($contact->c_phone)
                                                                            {{$contact->c_phone}}
                                                                        @endif
                                                                        @if($contact->contactStatus->name)
                                                                            <span style="font-size: 12px;color: black;background: #dfd6d6;padding: 2px 7px;border-radius: 10px;">
                                                                                {{$contact->contactStatus->name}}
                                                                            </span>
                                                                        @endif
                                                                    </span>
                                                                    <div class="d-flex justify-content-center action-btns">
                                                                    </div>
                                                                </div>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </h3>
                                        </div>
                                        <div id="collapse{{$i}}" class="collapse" aria-labelledby="heading{{$i}}"
                                            data-parent="#contactsAccordion">
                                            <div class="card-body bg-light p-3">
                                                <div class="form-group ">
                                                    <strong>First Name:</strong> <span class="small"> {{ $contact->c_first_name }}</span>
                                                </div>
                                                <div class="form-group ">
                                                    <strong>Last Name:</strong> <span class="small"> {{ $contact->c_last_name }}</span>
                                                </div>
                                                <div class="form-group">
                                                    <strong>Title:</strong> <span class="small"> {{ $contact->c_title }}</span>
                                                </div>
                                                <div class="form-group">
                                                    <strong>Address 1:</strong> <span class="small"> {{ $contact->c_address1 }}</span>
                                                </div>
                                                <div class="form-group">
                                                    <strong>Address 2:</strong> <span class="small"> {{ $contact->c_address2 }}</span>
                                                </div>
                                                <div class="form-group">
                                                    <strong>City:</strong> <span class="small"> {{ $contact->c_city }}</span>
                                                </div>
                                                <div class="form-group">
                                                    <strong>State:</strong> <span class="small"> {{ $contact->c_state }}</span>
                                                </div>
                                                <div class="form-group">
                                                    <strong>Zip:</strong> <span class="small"> {{ $contact->c_zip }}</span>
                                                </div>
                                                <div class="form-group">
                                                    <strong>County:</strong> <span class="small"> {{ $contact->c_county }}</span>
                                                </div>
                                                <div class="form-group">
                                                    <strong>Phone:</strong> <span class="small"> {{ $contact->c_phone }}</span>
                                                </div>
                                                <div class="form-group">
                                                    <strong>Email:</strong> <span class="small"> {{ $contact->c_email }}</span>
                                                </div>
                                                <div class="form-group">
                                                    <strong>Status:</strong> <span class="small"> {{ optional($contact->contactStatus)->name ?? '' }}</span>
                                                </div>
                                                <div class="form-group">
                                                    <strong>Assign Agent:</strong> <span class="small"> {{ optional($contact->assignedAgent)->name ?? '' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="card card-secondary">
                            <div class="card-header">
                                <h3 class="card-title">Registered Agent Name & Address (SunBiz)</h5>
                            </div>

                            @if(!empty($lead->sunbiz_registered_name) || !empty($lead->sunbiz_registered_address))
                                <div class="card-body p-3">
                                    <div class="form-group bg-light p-lg-3">
                                        <span class="small"><strong class="mb-0">Name :&nbsp;</strong>{{ $lead->sunbiz_registered_name }}</span>
                                    </div>
                                    <div class="form-group bg-light p-lg-3">
                                        <span class="small"><strong class="mb-0">Address :&nbsp;</strong>{{ $lead->sunbiz_registered_address }}</span>
                                    </div>
                                </div>
                            @else
                                <div class="card-body p-2 p-lg-3 text-center">
                                    Data from Sunbiz not found.
                                </div>
                            @endif
                        </div>

                        {{-- Lead Notes --}}
                        <div class="card card-secondary">
                            <div class="card-header px-3 py-2">
                                <h3 class="card-title py-1">Lead Notes</h3>
                            </div>
                            @if (count($notes) <1 )
                                <div class="card-body p-2 p-lg-3 text-center">
                                    The Lead does not have any notes yet .
                                </div>
                            @else
                                <div class="card-body" style="overflow: auto;max-height: 64vh;">
                                    <div id="notesAccordion">
                                        @php
                                        $i = 0
                                        @endphp
                                        @foreach ($notes as $note )
                                        @php
                                        $i++
                                        @endphp
                                        <div class="">{{--card --}}
                                            <div class="card-header pt-1 pb-1 px-0" id="heading{{$i}}">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <h5 class="mb-0">
                                                        <button class="btn btn-sm text-left" data-toggle="collapse"
                                                            data-target="#notecollapse{{$i}}" aria-expanded="true"
                                                            aria-controls="collapse{{$i}}">
                                                            @if($note->contact_name)
                                                            <strong>{{$note->contact_name}} - </strong>
                                                            @endif
                                                            @if($note->created_at)
                                                            {{date("m/d/Y-H:m", strtotime($note->created_at))}}
                                                            @endif
                                                            <strong> - {!! $note->description !!}</strong>
                                                        </button>
                                                    </h5>


                                                </div>
                                            </div>
                                            <div id="notecollapse{{$i}}" class="collapse" aria-labelledby="heading{{$i}}"
                                                data-parent="#notesAccordion">
                                                <div class="card-body  bg-light">
                                                    <p>{{$note->description}}</p>
                                                </div>
                                            </div>
                                        </div>

                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                        {{-- end of lead notes --}}
                    </div>
                </div>
        </div>
    </div> {{-- Lead Tab --}}
    @can('lead-file-list')
    {{-- File Tab --}}
    <div class="tab-pane " id="pills-File" role="tabpanel" aria-labelledby="pills-File-tab">
        <div class="container-fluid">
            @can('lead-file-upload')
            <div class="row">
                <div class="col-12">
                    {{-- upload files form --}}
                    <div class="card card-secondary">
                        <div class="card-header">
                            <h3 class="card-title ">Upload File</h3>
                        </div>
                        @include('leads.partials.upload-files-form')
                    </div> {{-- upload files form --}}
                </div>
            </div>
            @endcan
            {{-- uploaded files table --}}
            <div class="card card-secondary mt-0">
                <div class="card-header">
                    <h3 class="card-title ">Uploaded Files</h3>
                </div>
                <div class="card-body filesUploadedTable p-2 p-lg-3">
                    <div class="d-flex align-items-center justify-content-between action-dropdown mb-3">
                        <div class="custom_search_page d-flex align-items-center justify-content-between">
                            <div id="custom_length_menu">
                                <label class="d-flex align-items-center justify-content-between mb-0">Show
                                    <select id="customPageLength"
                                        class="form-control form-control-sm mx-1 px-0 bg-transparent"
                                        aria-controls="leads_datatable">
                                        <option value="10">10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                    </select>
                                    entries
                                </label>
                            </div>
                        </div>
                        <div id="leads_datatable_filter" class="dataTables_filter search-sec mb-0">
                            <label class="d-flex align-items-center justify-content-end mb-0 position-relative"><input
                                    type="search" id="customSearchBox" placeholder="Search for Entries"
                                    aria-controls="leads_datatable" class="form-control" val="">
                                <i class="fas fa-search position-absolute"></i>
                            </label>
                        </div>
                    </div>
                    <div class="table-container">
                        <table class="table table-bordered table-hover table-sm table-striped" id="files_datatable">
                            <thead>
                                <tr>
                                    <th style="width:56px;" scope="col">No</th>
                                    <th></th>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th style="width: 181px">Date</th>
                                    <th style="width: 54px">Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>{{-- File Tab --}}
    @endcan

    <div class="tab-pane" id="pills-client-insurance-info" role="tabpanel"
        aria-labelledby="pills-client-insurance-info-tab">
        <div class="container-fluid">
            <div class="row">
                {{-- lead form --}}
                <div class="col-12">
                    <div class="card card-secondary actionTable">
                        <div class="card-header">
                            <h3 class="card-title">Insurence info</h3>
                        </div>
                        <div class="card-body p-2 p-lg-3">
                            @if($renewed_lead == 1)
                                @include('leads.partials.leads-previous-filled-data-modal')
                            @else
                                <div class="card-body p-2 p-lg-3 text-center">
                                    No information
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                {{-- lead form ends --}}
            </div>

        </div>
    </div>

    <div class="tab-pane" id="pills-lead-actions" role="tabpanel" aria-labelledby="pills-lead-actions-tab">
        <div class="container-fluid">
            {{-- Actions Table --}}
            <div class="row">
                <div class="col-12">
                    <div class="card card-secondary actionTable">
                        <div class="card-header ">
                            <h3 class="card-title">Lead Actions </h3>
                        </div>
                        <div class="card-body p-2 p-lg-3">
                            @if(count($actions) == 0)
                            <div class="text-center">
                                No actions
                            </div>
                            @else
                            <div class="table-container ">
                                <table class="order-column compact hover searchHighlight dataTable no-footer">
                                    @php
                                    $a = 0;
                                    @endphp
                                    <tr>
                                        <th style="width:56px;">No</th>
                                        <!-- <th>User</th> -->
                                        <th>Action</th>
                                        <th>Contact Name</th>
                                        <th>Campaign</th>
                                        <th style="width: 138px">Date of Contact</th>
                                        <th style="width: 138px">Created At</th>
                                    </tr>
                                    @foreach ($actions as $key => $action)
                                    <tr>
                                        <td style="text-align: center;">{{ ++ $a }}</td>
                                        <td>{{ $action->action}}</td>
                                        <td> {{ $action->contact_name}}</td>
                                        <td>
                                            @can ('campaign-list')
                                            <a
                                                href="{{ $action->campaigns? route('campaigns.show',$action->campaigns->id) : ''}}">
                                                @endcan
                                                {{ $action->campaigns? $action->campaigns->name : ''}}
                                                @cannot ('campaign-list')
                                            </a>
                                            @endcan
                                        </td>
                                        <td> {{ $action->contact_date}}</td>
                                        <td> {{ $action->created_at}}</td>
                                    </tr>
                                    @endforeach
                                </table>
                            </div>
                            @endif
                            {{ $actions->links() }}
                        </div>
                    </div>
                </div>

            </div>
            {{-- Actions Table --}}
        </div>
    </div>

    <div class="tab-pane" id="pills-lead-campaigns" role="tabpanel" aria-labelledby="pills-lead-campaigns-tab">
        <div class="container-fluid">
            {{-- Campaigns Table --}}
            @can('campaign-list')
            <div class="row">
                <div class="col-12">
                    <div class="card card-secondary campaignTable">
                        <div class="card-header ">
                            <h3 class="card-title">Lead Campaigns </h3>
                        </div>
                        <div class="card-body p-2 p-lg-3">

                            @if(count($data) == 0)
                            <div class="text-center">
                                No campaigns
                            </div>
                            @else
                            <div class="table-container ">
                                <table class="order-column compact hover searchHighlight dataTable no-footer">
                                    @php
                                    $c = 0;
                                    @endphp
                                    <tr>
                                        <th style="width:56px;">No</th>
                                        <th>Name</th>
                                        <th>Status</th>
                                        <th style="width: 138px">Campaign Date</th>
                                        <th style="width: 50px; text-align: center;">View</th>
                                    </tr>
                                    @foreach ($data as $key => $campaign)
                                    <tr>
                                        <td>{{ ++$c }}</td>
                                        <td>{{ $campaign->name }}</td>
                                        <td
                                            class="@if ($campaign->status == 'PENDING') text-info @else text-success @endif">
                                            {{ $campaign->status }}</td>
                                        <td> @if($campaign->campaign_date)
                                            {{date("m/d/Y", strtotime($campaign->campaign_date))}}
                                            @endif
                                        </td>
                                        <td align="center">
                                            <a class="btn btn-sm btn-info action-btn" target="_blank"
                                                title="View Campaign"
                                                href="{{ route('campaigns.show',$campaign->id) }}"><i
                                                    class="fa fa-eye"></i></a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </table>
                            </div>
                            @endif

                            {!! $data->links() !!}

                        </div>
                    </div>
                </div>
            </div>
            @endcan
            {{-- Campaigns Table --}}
        </div>
    </div>

    </div>{{-- end of tab-content --}}


    </div><!-- /.container-fluid -->
    @include('partials.delete-modal')
    @include('leads.partials.log-modal')
    @include('leads.partials.lead-actions-modal')
    @include('leads.partials.new-note-modal')
    @include('leads.partials.edit-note-modal')
</section>
<!-- /.content -->
@endsection
@push('styles')
@endpush
@push('scripts')
<script async>
jQuery(document).ready(function() {
    // scroll to table if pagination

    simpleTables_pagination();

    jQuery('.format_date').each(function() {
        var getdate = jQuery(this).text();
        //'2010-10-11T00:00:00+05:30'
        var date = new Date(getdate + 'T00:00:00');
        var newDate = ((date.getMonth() > 8) ? (date.getMonth() + 1) : ('0' + (date.getMonth() + 1))) +
            '/' + ((date.getDate() > 9) ? date.getDate() : ('0' + date.getDate())) + '/' + date
            .getFullYear();
        jQuery(this).text(newDate);
    });
    //jQuery('#files_datatable').DataTable().draw(true);
    var leadId = '{{$lead->id}}';


    // ajax setup for table ajax
    jQuery.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
        }

    });

    //load the table
    var t = jQuery('#files_datatable').DataTable({

        // dom: 'lBfrtip',

        processing: true,
        oLanguage: {
            sProcessing: `{!! trim(preg_replace('/\s+/', ' ', view('partials.datatable_loader')->render())) !!}`
        },
        serverSide: true,
        responsive: true,
        autoWidth: false,
        ajax: {
            url: "{{ url('leads/leads-files') }}",
            type: 'POST',
            data: function(d) {

                d.leadId = '{{$lead->id}}'; //send the lead id

            }
        },

        columns: [
            //set table columns
            {
                data: 'DT_RowIndex',
                name: 'DT_RowIndex',
                "targets": [0],
                "searchable": false,
                "orderable": false,
            },
            {
                data: 'id',
                name: 'id',
                'visible': false
            },
            {
                data: 'name',
                name: 'name'
            },
            {
                data: 'description',
                name: 'description'
            },
            {
                data: 'created_at',
                name: 'created_at'
            },

            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false
            },
        ],

        order: [
            [4, 'desc']
        ],
        dom: 'rt<"bottom"ip><"clear">',
    });

    function debounce(func, wait) {
        var timeout;
        return function() {
            var context = this,
                args = arguments;
            clearTimeout(timeout);
            timeout = setTimeout(function() {
                timeout = null;
                func.apply(context, args);
            }, wait);
        };
    }

    $('#customPageLength').on('change', function() {
        var length = $(this).val();
        table.page.len(length).draw();
    });

    $('#customSearchBox').on('keyup', debounce(function(event) {
        $(event.target).siblings('i.fas.fa-search.position-absolute').remove();
        if (!event.target.value) {
            $(event.target).after('<i class="fas fa-search position-absolute"></i>');
        }
        if (event.key === "Enter") {
            table.search(this.value).draw();
        } else {
            table.search(this.value).draw();
        }
    }, 500)); // 500ms debounce interval

    $('#customSearchBox').on('input', debounce(function(event) {
        if (!event.target.value) {
            console.log('cross clicked');
            let localCustomSearchVal = localStorage.getItem('DataTables_leads_datatable_/leads');
            let updatedLocalCustomSearchVal = JSON.parse(localCustomSearchVal);
            updatedLocalCustomSearchVal.search.search = '';
            localStorage.setItem('DataTables_leads_datatable_/leads', JSON.stringify(
                updatedLocalCustomSearchVal));
            $(event.target).blur(); // to remove cursiour from search field.

            $(event.target).siblings('i.fas.fa-search.position-absolute')
                .remove(); // remove search icon and the append
            $(event.target).after('<i class="fas fa-search position-absolute"></i>');
            table.search(event.target.value).draw(); // drow the table
        }
    }, 500));


});

function redirectToLastLeadsManagementUrl(event) {
    // Check if lastLeadsManagementUrl is set in sessionStorage
    var lastLeadsManagementUrl = sessionStorage.getItem('lastLeadsManagementUrl');

    // If set, redirect to that URL; otherwise, use the default href
    if (lastLeadsManagementUrl) {
        event.preventDefault(); // Prevent the default link behavior
        window.location.href = lastLeadsManagementUrl;
    }
}


// keep the open tab on window refresh
$('a[data-toggle="pill"]').on('shown.bs.tab', function(e) {

    sessionStorage.setItem('activeTab', $(e.target).attr('href'));
});

var activeTab = sessionStorage.getItem('activeTab');
if (activeTab && $('.nav-tabs a[href="' + activeTab + '"]').length > 0) {
    $(".upperpaneltab_leads").removeClass('active');
    $('.nav-tabs a[href="' + activeTab + '"]').tab('show');
    $('.nav-tabs a[href="' + activeTab + '"]').addClass('active');
}

function scroll_to_el(el) {
    $("body,html").animate({
            scrollTop: $(el).offset().top
        },
        500 //speed
    );
}
// function to get url location and params , for internet explorer
function getQueryString() {
    var key = false,
        res = {},
        itm = null;
    // get the query string without the ?
    var qs = location.search.substring(1);
    // check for the key as an argument
    if (arguments.length > 0 && arguments[0].length > 1)
        key = arguments[0];
    // make a regex pattern to grab key/value
    var pattern = /([^&=]+)=([^&]*)/g;
    // loop the items in the query string, either
    // find a match to the argument, or build an object
    // with key/value pairs
    while (itm = pattern.exec(qs)) {
        if (key !== false && decodeURIComponent(itm[1]) === key)
            return decodeURIComponent(itm[2]);
        else if (key === false)
            res[decodeURIComponent(itm[1])] = decodeURIComponent(itm[2]);
    }

    return key === false ? res : null;
}

function simpleTables_pagination() {

    //this was an easy fix . will work to improve tables functionality later
    // get the params from link
    var params = new URLSearchParams(location.search);
    var campaignParam = params.get('campaignsShow');
    var actionsParam = params.get('actionsShow');
    var allparams = getQueryString();

    //ad existing params to link
    if (allparams) {
        delete allparams['actionsShow'];
        delete allparams['campaignsShow'];
        for (p in allparams) {
            //add other params to paginate links

            $('.pagination a').each(function() {

                var thisLink = $(this).attr('href');

                $(this).attr('href', thisLink + "&" + p + "=" + allparams[p])

            })
        }

    }

    // scroll to table if pagination
    if (campaignParam) {
        scroll_to_el('.campaignTable')
    }
    if (actionsParam) {
        scroll_to_el('.actionTable')
    }
}

/** ************************************
    Script for Confirm Modal
**************************************/
// I'm not sure if we still ned this . Removed onclick="leadActionModal(this,'{{$lead->id}}')" from lead actions button
function leadActionModal(elem, $id) {
    $('#leadActionModal').attr('data-source', '#ordr_' + $id);
    console.log(elem);
    $tar = $(elem).parents('form');
    $('#confirm').click(function() {


        //submit form
        $($tar).submit();
    });

}
// get the value of the input and set it for "other" option in the dropdown
function get_set_other_val(elem) {
    console.log(elem);
    console.log('here');

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
</script>
@endpush