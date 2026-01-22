@extends('layouts.app')
@section('pagetitle', 'Edit Business')
@push('breadcrumbs')
<li class="breadcrumb-item"><a href="{{route('leads.index')}}">Business</a></li>
<li class="breadcrumb-item active">Edit Business</li>
@endpush
@section('content')
<!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery.sumoselect/3.1.0/sumoselect.min.css" integrity="sha512-DwvcXBSYqgsNMre0DRTf/WWSBiKhG+Z+cGYwgOSpkvlf9jZoLVL6OvWGTDa0a/5qm3T1F+obp11aJJNksWURNA==" crossorigin="anonymous" referrerpolicy="no-referrer" /> -->
<!-- <link href="/css/jquery.dataTables.min.css" rel="stylesheet"> -->
<!-- <script src="https://code.jquery.com/jquery-3.1.0.min.js"></script> -->

<!-- <script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js" defer></script> -->
<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.sumoselect/3.1.0/jquery.sumoselect.min.js" ></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.sumoselect/3.1.0/jquery.sumoselect.js" ></script> -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.16/css/dataTables.bootstrap4.min.css">
<!-- Main content -->
<section class="lead-edit-form content">
    <div class="container-fluid pb-3">
        <div class="row m-0">
            <div class="col-lg-12 margin-tb mb-3">
                <div class="d-flex justify-content-between">
                    <div class="left-content row d-flex align-items-center">
                        <a class="btn btn-info btn-sm px-2 mb-3 mb-md-0"
                            href="{{ route('leads.index', ['id' => $lead->id]) }}"
                            onclick="changeBackButtonLink(event)"><i class="fas fa-arrow-circle-left"></i> Back</a>
                    </div>
                    <div class="actions">
                        <button class="btn btn-sm btn-secondary mb-0" type="button" data-bs-toggle="modal"
                            data-bs-target="#logModal">
                            <i class="fa fa-comment-dots"></i>
                            <span class="d-none d-lg-inline">Lead Log</span>
                        </button>
                        @can('lead-action')
                        <button class="btn btn-sm btn-warning action-btn m-0" data-bs-toggle="modal"
                            data-bs-target="#userLeadActions">
                            <i class="fas fa-mouse-pointer"></i>
                            <span class="d-none d-lg-inline">Add Lead Actions</span>
                        </button>
                        @endcan
                        @can('lead-list')
                        <a class="btn btn-sm btn-info action-btn m-0" href="{{ route('leads.show',base64_encode($lead->id)) }}"><i
                                class="fa fa-eye"></i>
                            <span class="d-none d-lg-inline">View Lead</span>
                        </a>
                        @endcan
                        @can('lead-delete')
                        {!! Form::open(['method' => 'DELETE','route' => ['leads.destroy',
                        $lead->id],'style'=>'display:inline','class' => ['leadForm-'.$lead->id]]) !!}
                        {{-- trigger confirmation modal --}}
                        <a href="#" data-bs-toggle="modal" data-bs-target="#deleteModal"
                            onclick="setModal(this,'{{$lead->id}}')"
                            class="btn btn-sm btn-danger deletebtn action-btn m-0">
                            <i class="fa fa-trash"></i>
                            <span class="d-none d-lg-inline">Delete Lead</span>
                        </a>
                        {!! Form::close() !!}
                        @endcan
                    </div>
                </div>
            </div>
        </div>
        <h5 class="text-dark mb-3 pb-1 border-0">
            {{ $lead->name }}

            <span class="badge badge-secondary ml-2 badgeLabel" >
                {{ $lead->leadStatus->name }}
            </span>

            @if(!empty($lead->ownedAgent->name))
                <span class="badge badge-secondary ml-2 badgeLabel">
                    {{ $lead->ownedAgent->name }}
                </span>
            @endif
        </h5>


        <!-- <h6 class=" pb-3 card-header text-secondary" style="background: rgba(40,167,69 , 0.05);">{{ $lead->name }}</h6> -->
        <ul class="nav nav-tabs nav-justified " id="pills-tab" role="tablist">
            <li class="nav-item">
                <a class="nav-link active upperpaneltab_leads" id="pills-Lead-tab" data-bs-toggle="pill" href="#pills-Lead" role="tab"
                    aria-controls="pills-Lead" aria-selected="true">Lead</a>
            </li>
            <li class="nav-item">
                <a class="nav-link upperpaneltab_leads" id="pills-client-insurance-info-tab" data-bs-toggle="pill"
                    href="#pills-client-insurance-info" role="tab" aria-controls="pills-client-insurance-info"
                    aria-selected="true">Client Insurance Info</a>
            </li>
            <li class="nav-item">
                <a class="nav-link upperpaneltab_leads" id="pills-lead-actions-tab" data-bs-toggle="pill" href="#pills-lead-actions" role="tab"
                    aria-controls="pills-lead-actions" aria-selected="true"> Lead Actions</a>
            </li>
            <li class="nav-item">
                <a class="nav-link upperpaneltab_leads" id="pills-lead-campaigns-tab" data-bs-toggle="pill" href="#pills-lead-campaigns"
                    role="tab" aria-controls="pills-lead-campaigns" aria-selected="true"> Lead Campaigns</a>
            </li>
            @can('lead-file-list')
            <li class="nav-item">
                <a class="nav-link t upperpaneltab_leads" id="pills-File-tab" data-bs-toggle="pill" href="#pills-File" role="tab"
                    aria-controls="pills-File" aria-selected="false">Uploaded Files</a>
            </li>
            @endcan
        </ul>
        <div class="tab-content pt-3 bg-white p-3 border-top-0 rounded rounded-top-0 edit-section"
            id="pills-tabContent">
            <div class="tab-pane show active" id="pills-Lead" role="tabpanel" aria-labelledby="pills-Lead-tab">
                <div class="container-fluid">
                    <div class="row">
                        {{-- lead form --}}
                        <div class="col-xl-7">
                            {!! Form::model($lead, [
                                'method' => 'PATCH',
                                'route' => ['leads.update', $lead->id],
                                'id' => 'lead_update_form',
                                'onsubmit' => 'return leadSubmissionValidation()' 
                            ]) !!}
                                <ul class="nav nav-tabs nav-justified" id="pills-tab-2" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active small px-1 lowerpaneltab_leads" id="pills-Lead-tab-2" data-bs-toggle="pill" href="#pills-Lead-2" role="tab"
                                            aria-controls="pills-Lead-2" aria-selected="true">Appraisal</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link small px-1 lowerpaneltab_leads" id="pills-lead-actions-tab-2" data-bs-toggle="pill" href="#pills-lead-actions-2" role="tab"
                                            aria-controls="pills-lead-actions-2" aria-selected="false">Wind Mitigation</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link small px-1 lowerpaneltab_leads" id="pills-lead-campaigns-tab-2" data-bs-toggle="pill" href="#pills-lead-campaigns-2"
                                            role="tab" aria-controls="pills-lead-campaigns-2" aria-selected="false">Prospect’s Insurance</a>
                                    </li>
                                </ul>
                                <div class="tab-content bg-white border-top-0 rounded rounded-top-0 edit-section" id="pills-tabContent-2" bis_skin_checked="1">
                                    <div class="tab-pane show active" id="pills-Lead-2" role="tabpanel" aria-labelledby="pills-Lead-tab-2" bis_skin_checked="1">
                                        <div class="card card-secondary pt-4 rounded-top-0 mb-0 shadow-none">
                                            <!-- <h3 class="card-title fs-2 mb-3 pb-2 border-bottom">Business</h3> -->
                                            
                                            <div class="lead-edit-form">
                                                <!-- lead-edit-form-scroll-->
                                                @include('leads.partials.lead-form-business-info')
                                                <div class="card-footer px-0 pb-0">
                                                    <button onclick="nextbuttonClicked(2)" type="button" class="btn btn-primary btn-sm">Next</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane" id="pills-lead-actions-2" role="tabpanel" aria-labelledby="pills-lead-actions-tab-2" bis_skin_checked="1">
                                        @include('leads.partials.lead-form-community-info')
                                        <div class="card-footer">
                                            <button onclick="nextbuttonClicked(1)" type="button" class="btn btn-primary btn-sm">Previous</button>
                                            <button onclick="nextbuttonClicked(3)" type="button" class="btn btn-primary btn-sm">Next</button>
                                        </div>
                                    </div>
                                    <div class="tab-pane" id="pills-lead-campaigns-2" role="tabpanel" aria-labelledby="pills-lead-campaigns-tab-2" bis_skin_checked="1">
                                        @include('leads.partials.lead-form-property-info')
                                        <div class="card-footer" >
                                            <button onclick="nextbuttonClicked(2)" type="button" class="btn btn-primary btn-sm">Previous</button>
                                            <button id="preview_btn" type="button" class="btn btn-primary btn-sm">Preview</button>
                                            <button type="submit" class="btn btn-primary btn-sm">Update Lead</button>
                                        </div>
                                    </div>
                                </div>
                               
                            {!! Form::close() !!}
                            
                        </div>
                        {{-- lead form ends --}}
                        <div class="col-xl-5">
                            {{-- contacts form --}}
                            <div class="card card-secondary">
                                <div class="card-header">
                                    <h3 class="card-title"> Contact Information</h3>
                                </div>
                                @if (!count($contacts))
                                    <div class="card-body p-2 p-lg-3 text-center">
                                        The Lead does not have any contacts yet .
                                    </div>
                                @else
                                <div class="card-body p-2 p-lg-3">
                                    <div id="contactsAccordion">
                                        @php
                                        $i = 0
                                        @endphp
                                        @foreach ($contacts as $contact )
                                        @php
                                        $i++
                                        @endphp
                                        <h3 class="position-relative mt-2 accordian-header-light @if($contact->fake_address == 1 || $contact->fake_address == 2) {{'fakeClass'}} @endif">
                                            <div class="custom_contact_class p-0 rounded">
                                                <div class="card-header pt-1 pb-1 px-0 border-0">
                                                    <div class="d-flex justify-content-between align-items-center">

                                                        <button id="custom_contact{{$contact->id}}"
                                                            class="btn w-100 p-0 pl-2 text-left" >

                                                            <div class="d-flex justify-content-between align-items-center">
                                                                <h6 class="text-left mb-1">
                                                                    <strong>{{$contact->c_first_name}}
                                                                        {{$contact->c_last_name}}</strong>
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

                                                            <div
                                                                class="d-flex justify-content-between align-items-center cntct-info-action">
                                                                <span class="phne-no">
                                                                    @if($contact->c_phone)
                                                                    {{$contact->c_phone}}
                                                                    @endif
                                                                    @if($contact->contactStatus->name)
                                                                    <span
                                                                        style="font-size: 12px;color: black;background: #dfd6d6;padding: 2px 7px;border-radius: 10px;">{{$contact->contactStatus->name}}</span>
                                                                    @endif
                                                                </span>



                                                                <div class="d-flex justify-content-center action-btns">
                                                                    @if($contact->c_email)
                                                                    <a href="javascript:void(0)"
                                                                        onclick="event.stopPropagation();"
                                                                        class="btn btn-teal contact-actions btn-sm m-0 text-light action-btn openEmailPopup"
                                                                        title="Email"
                                                                        data-contact-id="{{$contact->id}}"
                                                                        data-email-to-contact="{{$contact->c_first_name.' '.$contact->c_last_name}}({{$contact->c_email}})">
                                                                        <i class="fas fa-envelope"></i>
                                                                    </a>
                                                                    @endif
                                                                    @if($contact->c_phone)
                                                                    <a  href="javascript:void(0)"
                                                                            
                                                                     class="btn btn-sm btn-info text-white chat_intitialise contact-actions action-btn m-0"
                                                                        title="Text"
                                                                        id="chat_contact_{{ $contact->id }}"
                                                                        data-name="{{$contact->c_first_name}}  {{$contact->c_last_name}}"
                                                                        data-chat_contact_status="{{ $contact->has_initiated_stop_chat }}">
                                                                        <i class="fa fa-comments"></i></a>
                                                                    @endif

                                                                    <!-- <script>
                                                                    alert('can not send the sms');
                                                                    </script> -->

                                                                    @can('lead-action')
                                                                    <a href="javascript:void(0)"
                                                                        title="Action"
                                                                        class="btn btn-warning text-white contact-actions btn-sm m-0 text-light action-btn"
                                                                        data-bs-toggle="modal" data-bs-target="#userLeadActions"
                                                                        onClick="event.stopPropagation(); setActionModal(this, {{ $contact->id }})">
                                                                        <i class="fas fa-mouse-pointer"></i>
                                                                    </a>
                                                                    @endcan
                                                                    @can('contact-delete')
                                                                    <form
                                                                        action="{{route('leads.contact_destroy',$contact->id)  }}"
                                                                        method="POST"
                                                                        class=" d-flex leadContactForm-{{ $contact->id }}">
                                                                        @csrf
                                                                        @method('delete')
                                                                        {{-- {!! Form::open(['method' => 'DELETE','route' => ['leads.contact_destroy', $contact->id],'style'=>'display:inline','class' => ['leadContactForm-'.$contact->id]]) !!} --}}
                                                                        {{-- trigger confirmation modal --}}
                                                                        <a href="javascript:void(0)" data-bs-toggle="modal"
                                                                            data-bs-target="#deleteModal"
                                                                            title="Delete"
                                                                            onclick="event.stopPropagation(); setModal(this,'{{$contact->id}}')"
                                                                            class="btn btn-sm text-white btn-danger deletebtn action-btn contact-actions m-0">
                                                                            <i class="fa fa-trash"></i>
                                                                        </a>
                                                                    </form>
                                                                    @endcan
                                                                </div>

                                                            </div>

                                                        </button>

                                                    </div>
                                                </div>
                                            </div>
                                        </h3>
                                        <div id="collapseElement{{$contact->id}}" class="custom_contact_class rounded rounded-top-0 wrapper_content p-0">
                                            <div class="wrapper_content">
                                                <div class="card-body p-0">
                                                    {!! Form::model($contact, ['method' => 'post','route' =>
                                                    ['leads.contact_update', $contact->id]]) !!}
                                                        @include('leads.partials.contact-form')
                                                        @can('contact-edit')
                                                            <button type="submit" class="btn btn-sm btn-primary" onclick="setInSessionStorage('contact_id','{{$contact->id}}')">
                                                                Update Contact
                                                            </button>
                                                        @endcan
                                                    {!! Form::close() !!}
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                                <div class="card-footer">
                                    @can('contact-create')
                                        <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#addContactModal">
                                            Add Contact
                                        </button>
                                    @endcan
                                    @if (count($contacts))
                                        <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#contactStatusModal">
                                            Change Contact Status
                                        </button>
                                    @endif
                                </div>
                            </div>
                            {{-- contacts form ends --}}

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
                                <div class="card-header">
                                    <h3 class="card-title">Lead Notes</h3>
                                </div>
                                @if (!count($notes))
                                <div class="card-body p-2 p-lg-3 text-center">
                                    The Lead does not have any notes yet .
                                </div>
                                @else
                                <div class="card-body p-2 p-lg-3" style="overflow: auto;max-height: 64vh;">
                                    <div id="notesAccordion">
                                        @php
                                        $i = 0
                                        @endphp
                                        @foreach ($notes as $note )
                                        {{-- {{ dd($note); }} --}}
                                        @php
                                        $i++
                                        @endphp
                                        <div class="">{{--card --}}
                                            <div class="card-header pt-1 pb-1 px-0" id="heading{{$i}}">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <h5 class="mb-0">
                                                        <!-- <button class="btn btn-sm text-left" data-toggle="collapse" data-target="#notecollapse{{$i}}" aria-expanded="true" aria-controls="collapse{{$i}}"> -->
                                                        <button class="btn btn-sm text-justify px-0 pr-3">
                                                            @if($note->contact_name)
                                                            <strong>{{$note->contact_name}} - </strong>
                                                            @endif
                                                            @if($note->created_at)
                                                                {{date("m/d/Y-H:m", strtotime($note->created_at))}}
                                                            @endif
                                                            <strong class="notDesc"> {!! $note->description !!}</strong>
                                                        </button>
                                                    </h5>
                                                    @can('lead-edit')
                                                    <div
                                                        class="notes-div-section justify-content-start align-items-start d-flex action-btns">
                                                        <button class="btn rounded-right-0 btn-sm btn-success action-btn contactDeleteBtn m-0"
                                                                onclick="setNoteModal(this, {{ $note->id }})">
                                                            <i class="fa fa-edit"></i>
                                                        </button>

                                                        {!! Form::open(['method' => 'DELETE','route' =>
                                                        ['leads.note_destroy',
                                                        $note->id],'style'=>'display:flex','class' =>
                                                        ['leadNotesForm-'.$note->id]]) !!}
                                                        {{-- trigger confirmation modal --}}
                                                        <a href="#" data-bs-toggle="modal" data-bs-target="#deleteModal"
                                                            onclick="setModal(this,'{{$note->id}}')"
                                                            class="btn btn-sm rounded-left-0 m-0 btn-danger deletebtn action-btn contactDeleteBtn">
                                                            <i class="fa fa-trash"></i>
                                                        </a>
                                                        {!! Form::close() !!}
                                                    </div>
                                                    @endcan
                                                </div>
                                            </div>
                                            <div id="notecollapse{{$i}}" class="collapse"
                                                aria-labelledby="heading{{$i}}" data-parent="#notesAccordion">
                                                <div class="card-body  bg-light">
                                                    <p>{{$note->description}}</p>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                                @can('lead-edit')
                                <div class="card-footer">
                                    <button class="btn btn-info btn-sm" data-bs-toggle="modal" id="add_note_lead_page"
                                        data-bs-target="#addNoteModal">Add Note</button>
                                </div>
                                @endcan
                            </div>
                            {{-- end of lead notes --}}
                        </div>
                    </div>

                    {{-- Actions Table --}}
                    {{-- <div class="row">
						<div class="col-12">
							<div class="card card-secondary actionTable">
								<div class="card-header ">
									<h3 class="card-title">Lead Actions </h3>
								</div>
								<div class="card-body">
									<div class="table-container ">
										<table class="order-column compact hover searchHighlight dataTable no-footer">
											@if(count($actions) == 0)
											<div>No actions</div>
											@else
											@php
											$a = 0;
											@endphp
											<tr>
												<th style="width:56px;">No</th>
												<!-- <th>User</th> -->
												<th>Action</th>
												<th>Contact Name</th>
												<th>Campaign</th>
												<th>Date of Contact</th>
												<th style="width: 138px">Created At</th>
											</tr>
											@foreach ($actions as $key => $action)

											<tr>
												<td>{{ ++ $a }}</td>

                    <td>{{ $action->action}}</td>
                    <td> {{ $action->contact_name}}</td>
                    <td>
                        @can ('campaign-list')
                        <a href="{{ $action->campaigns? route('campaigns.show',$action->campaigns->id) : ''}}">
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
                    @endif
                    </table>
                    {{ $actions->links() }}
                </div>
            </div>
        </div>
    </div>
    </div> --}}
    {{-- Actions Table --}}

    {{-- @can('campaign-list')
					<div class="row">
						<div class="col-12">
							<div class="card card-secondary campaignTable">
								<div class="card-header ">
									<h3 class="card-title">Lead Campaigns </h3>
								</div>
								<div class="card-body">
									<div class="table-container ">
										<table class="order-column compact hover searchHighlight dataTable no-footer">
											@php
											$c = 0;
											@endphp
											@if(count($data) == 0)
											<div>No campaigns</div>
											@else
											<tr>
												<th style="width:56px;">No</th>
												<th>Name</th>
												<th>Status</th>
												<th style="width: 138px">Campaign Date</th>
												<th style="width: 50px">View</th>
											</tr>
											@foreach ($data as $key => $campaign)
											<tr>
												<td>{{ ++$c}}</td>
    <td>{{ $campaign->name }}</td>
    <td class="@if ($campaign->status == 'PENDING') text-info @else text-success @endif">
        {{ $campaign->status }}
    </td>
    <td> @if($campaign->campaign_date)
        {{date("m/d/Y", strtotime($campaign->campaign_date))}}
        @endif
    </td>
    <td>
        <a class="btn btn-sm btn-info action-btn" target="_blank" title="View Campaign"
            href="{{ route('campaigns.show',$campaign->id) }}"><i class="fa fa-eye"></i></a>
    </td>
    </tr>
    @endforeach
    @endif
    </table>
    {!! $data->links() !!}
    </div>
    </div>
    </div>
    </div>
    </div>
    @endcan --}}
    </div>
    </div>

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
                        @if($renewed_lead == 1)
                            @include('leads.partials.leads-previous-filled-data-modal')
                        @else
                            <div class="card-body p-2 p-lg-3 text-center">
                                No information
                            </div>
                        @endif
                    </div>
                </div>
                {{-- lead form ends --}}
            </div>

        </div>
    </div>


    <div class="tab-pane" id="pills-lead-actions" role="tabpanel" aria-labelledby="pills-lead-actions-tab">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card card-secondary actionTable">
                        <div class="card-header ">
                            <h3 class="card-title">Lead Actions </h3>
                        </div>
                        <div class="card-body p-2 p-lg-3">


                            @if(count($actions) == 0)
                            <div class="text-center">No actions</div>
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
                                        <th>Date of Contact</th>
                                        <th style="width: 138px">Created At</th>
                                    </tr>
                                    @foreach ($actions as $key => $action)

                                    <tr>
                                        <td>{{ ++ $a }}</td>

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
        </div>
    </div>


    <div class="tab-pane" id="pills-lead-campaigns" role="tabpanel" aria-labelledby="pills-lead-campaigns-tab">
        <div class="container-fluid">

            @can('campaign-list')
            <div class="row">
                <div class="col-12">
                    <div class="card card-secondary campaignTable">
                        <div class="card-header ">
                            <h3 class="card-title">Lead Campaigns </h3>
                        </div>
                        <div class="card-body p-2 p-lg-3">

                            @php
                            $c = 0;
                            @endphp
                            @if(count($data) == 0)
                            <div class="text-center">No campaigns</div>
                            @else
                            <div class="table-container ">
                                <table class="order-column compact hover searchHighlight dataTable no-footer">
                                    <tr>
                                        <th style="width:56px;">No</th>
                                        <th>Name</th>
                                        <th>Status</th>
                                        <th style="width: 138px">Campaign Date</th>
                                        <th style="width: 50px; text-align: center;">View</th>
                                    </tr>
                                    @foreach ($data as $key => $campaign)
                                    <tr>
                                        <td>{{ ++$c}}</td>
                                        <td>{{ $campaign->name }}</td>
                                        <td
                                            class="@if ($campaign->status == 'PENDING') text-info @else text-success @endif">
                                            {{ $campaign->status }}
                                        </td>
                                        <td> @if($campaign->campaign_date)
                                            {{date("m/d/Y", strtotime($campaign->campaign_date))}}
                                            @endif
                                        </td>
                                        <td align="center" valign="middle">
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

        </div>
    </div>


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
            @include('leads.partials.upload-files-table')
        </div>
    </div>
    {{-- File Tab --}}
    @endcan
    </div>
    </div>

    <!-- /.container-fluid -->
    <!-- Add New Contact Modal -->
    @include('leads.partials.new-contact')
    <!-- Chnage status of all Contact Modal -->
    @include('leads.partials.change-all-contact-status')
    {{-- delete modal --}}
    @include('partials.delete-modal')
    @include('leads.partials.log-modal')
    @include('leads.partials.lead-actions-modal')
    @include('leads.partials.new-note-modal')
    @include('leads.partials.edit-note-modal')
    @include('leads.partials.leads-preview-modal')
</section>
<div id="chat-wrapper" class="position-fixed d-flex justify-content-end align-items-end pr-4"></div>
<!-- /.content -->

<!-- Right saved template listing START -->
<div id="lead-saved-filter-sidebar" class="lead-saved-filter-sidebar">
    <div class="header d-flex align-items-center justify-content-between p-2">
        <span><label>Saved Templates</label></span>
        <a href="javascript:void(0)" class="closebtn" onclick="closeSavedTemplateNav()"><i class="fas fa-times"></i></a>
    </div>
    {{-- <div class="lead-saved-filters d-flex row m-0 mt-2 justify-content-center templateSavedList" id="templateSavedList">
    </div> --}}
</div>
<!-- Right saved template listing END -->

<!-- Add new template START -->
<div class="template-modal create-modal">
    <div class="modal-overlay modal-toggle"></div>
    <div class="modal-wrapper modal-transition">
        <div class="modal-header">
            <button class="modal-close modal-toggle template_modal_close_class text-xs" id="template_modal_close">
                <!-- <svg width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M9.29563 8.18259C9.60867 8.49563 9.60867 8.98259 9.29563 9.29563C9.13911 9.45215 8.9478 9.52172 8.73911 9.52172C8.53041 9.52172 8.33911 9.45215 8.18259 9.29563L4.99998 6.11302L1.81737 9.29563C1.66085 9.45215 1.46954 9.52172 1.26085 9.52172C1.05215 9.52172 0.860848 9.45215 0.704326 9.29563C0.391283 8.98259 0.391283 8.49563 0.704326 8.18259L3.88693 4.99998L0.704326 1.81737C0.391283 1.50433 0.391283 1.01737 0.704326 0.704326C1.01737 0.391283 1.50433 0.391283 1.81737 0.704326L4.99998 3.88693L8.18259 0.704326C8.49563 0.391283 8.98259 0.391283 9.29563 0.704326C9.60867 1.01737 9.60867 1.50433 9.29563 1.81737L6.11302 4.99998L9.29563 8.18259Z"
                        fill="black" />
                </svg> -->
                <i class="fas fa-times"></i>
            </button>
            <h2 class="modal-heading">Add new template</h2>
        </div>
        <div class="modal-body">
            <div class="modal-content">

                <form id="myFormAddNewTemplate" class="create-template-form">
                    <div class="form-group">
                        <label for="template_name">Template Name:</label>
                        <input type="text" class="form-control border-dark" id="template_name" name="template_name">
                    </div>
                    <div class="form-group">
                        <label for="template_content">Template Content:</label>
                        <textarea  id="template_content" class="form-control border-dark ckeditor"
                            name="template_content" row='10' placeholder="Write your content..."></textarea>
                    </div>
                    <p class="font-weight-bold mb-2">Insert Placeholder in Textarea:</p>
                    <div class="placeholders d-flex flex-wrap mb-2">
                        <span class="insert-placeholder font-weight-semibold p-2 border small"
                            data-placeholder="{CANDIDATE_FIRST_NAME}">{CANDIDATE_FIRST_NAME}</span>
                        <span class="insert-placeholder font-weight-semibold p-2 border small"
                            data-placeholder="{CANDIDATE_LAST_NAME}">{CANDIDATE_LAST_NAME}</span>
                        <span class="insert-placeholder font-weight-semibold p-2 border small"
                            data-placeholder="{BUSINESS_NAME}">{BUSINESS_NAME}</span>
                    </div>
                    <p class="small text-secondary">While writing you message template, click these buttons to insert
                        placeholders.</p>
                    <div class="text-left modal-btns mt-3 pt-3">
                        <input type="button" value="Close" class="btn btn-secondary btn-sm template_modal_close_class">
                        <input type="submit" value="Submit" class="btn btn-primary btn-sm">
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
<!-- Add new template END -->

@include('partials.email-modal')
</div>

@endsection
@push('styles')
@endpush
@push('scripts')

<script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js" defer></script>

<!-- <script src="https://cdn.datatables.net/plug-ins/1.11.3/features/searchHighlight/dataTables.searchHighlight.min.js"></script> -->
<script>
/******************************
        Ricochet webhook for contact
    ******************************/
// let params = new URLSearchParams(location.search);
// var contactId = params.get('contact_id'); //get contact id
// if (contactId) {
//     sessionStorage.removeItem('activeTab') //reset tab to home
//     var contact = $('#contactsAccordion [data-id=' + contactId + ']').collapse('show'); //open contact collapse
//     var id = $(contact).attr('id');
//     $('#contactsAccordion [data-target="#' + id + '"]').addClass('bg-success'); //highlight contact
// }
/******************************/
</script>
<script src="{{ asset('js/custom-helper.js') }}" async></script>
<script src="{{ asset('js/ckeditor-reuired-function.js') }}" defer></script>
<script async>

    function leadSubmissionValidation() {
        let isValid = true; 

        // Get form values
        const type = document.getElementById('type').value.trim();
        const name = document.getElementById('name').value.trim();
        const address1 = document.getElementById('address1').value.trim();
        const city = document.getElementById('city').value.trim();
        const zip = document.getElementById('zip').value.trim();

        // Validation rules
        if (type === "") {
            toastr.error("Business Type is required");
            isValid = false;
        }
        // return false;

        if (name === "") {
            toastr.error("Business Name is required");
            isValid = false;
        }

        if (address1 === "" || !/^\d/.test(address1)) {
            toastr.error("Address is required and must start with a number.");
            isValid = false;
        }

        if (city === "") {
            toastr.error("City is required");
            isValid = false;
        }

        if (zip === "") {
            toastr.error("Zip code is required");
            isValid = false;
        }

        const fieldPairs = [
            // Property
            { field1: "premium", field2: "premium_year", message1: "Business Premium Year is required when Business Premium is provided.", message2: "Business Premium is required when Business Premium Year is provided." },
            { field1: "insured_amount", field2: "insured_year", message1: "Business Insured Year is required when Business Insured Amount is provided.", message2: "Business Insured Amount is required when Business Insured Year is provided." },
            { field1: "ins_prop_carrier", field2: "renewal_carrier_month", message1: "Renewal Carrier Month is required when Insurance Property Carrier is provided.", message2: "Insurance Property Carrier is required when Renewal Carrier Month is provided." },

            // General Liability
            { field1: "general_liability", field2: "GL_ren_month", message1: "General Liability Renewal Month is required when General Liability Carrier is provided.", message2: "General Liability Carrier is required when General Liability Renewal Month is provided." },
            { field1: "gl_expiry_premium", field2: "gl_policy_renewal_date", message1: "General Liability Policy Renewal Date is required when General Liability Expiring Premium is provided.", message2: "General Liability Expiring Premium is required when General Liability Policy Renewal Date is provided." },

            // Crime Insurance
            { field1: "crime_insurance", field2: "CI_ren_month", message1: "Crime Insurance Renewal Month is required when Crime Insurance Carrier is provided.", message2: "Crime Insurance Carrier is required when Crime Insurance Renewal Month is provided." },
            { field1: "ci_expiry_premium", field2: "ci_policy_renewal_date", message1: "Crime Insurance Policy Renewal Date is required when Crime Insurance Expiring Premium is provided.", message2: "Crime Insurance Expiring Premium is required when Crime Insurance Policy Renewal Date is provided." },

            // Directors & Officers
            { field1: "directors_officers", field2: "DO_ren_month", message1: "Directors & Officers Renewal Month is required when Directors & Officers Carrier is provided.", message2: "Directors & Officers Carrier is required when Directors & Officers Renewal Month is provided." },
            { field1: "do_expiry_premium", field2: "do_policy_renewal_date", message1: "Directors & Officers Policy Renewal Date is required when Directors & Officers Expiring Premium is provided.", message2: "Directors & Officers Expiring Premium is required when Directors & Officers Policy Renewal Date is provided." },

            // Workers Compensation
            { field1: "workers_compensation", field2: "WC_ren_month", message1: "Workers Compensation Renewal Month is required when Workers Compensation Carrier is provided.", message2: "Workers Compensation Carrier is required when Workers Compensation Renewal Month is provided." },
            { field1: "wc_expiry_premium", field2: "wc_policy_renewal_date", message1: "Workers Compensation Policy Renewal Date is required when Workers Compensation Expiring Premium is provided.", message2: "Workers Compensation Expiring Premium is required when Workers Compensation Policy Renewal Date is provided." },

            // Umbrella
            { field1: "umbrella", field2: "U_ren_month", message1: "Umbrella Renewal Month is required when Umbrella Carrier is provided.", message2: "Umbrella Carrier is required when Umbrella Renewal Month is provided." },
            { field1: "umbrella_expiry_premium", field2: "umbrella_policy_renewal_date", message1: "Umbrella Policy Renewal Date is required when Umbrella Expiring Premium is provided.", message2: "Umbrella Expiring Premium is required when Umbrella Policy Renewal Date is provided." },

            // Flood
            { field1: "flood", field2: "F_ren_month", message1: "Flood Renewal Month is required when Flood Carrier is provided.", message2: "Flood Carrier is required when Flood Renewal Month is provided." },
            { field1: "flood_expiry_premium", field2: "flood_policy_renewal_date", message1: "Flood Policy Renewal Date is required when Flood Expiring Premium is provided.", message2: "Flood Expiring Premium is required when Flood Policy Renewal Date is provided." },

            // Difference In Conditions
            { field1: "difference_in_condition", field2: "dic_ren_month", message1: "Difference In Conditions Renewal Month is required when Difference In Conditions Carrier is provided.", message2: "Difference In Conditions Carrier is required when Difference In Conditions Renewal Month is provided." },
            { field1: "dic_expiry_premium", field2: "dic_policy_renewal_date", message1: "Difference In Conditions Policy Renewal Date is required when Difference In Conditions Expiring Premium is provided.", message2: "Difference In Conditions Expiring Premium is required when Difference In Conditions Policy Renewal Date is provided." },

            // X-Wind
            { field1: "x_wind", field2: "xw_ren_month", message1: "X-Wind Renewal Month is required when X-Wind Carrier is provided.", message2: "X-Wind Carrier is required when X-Wind Renewal Month is provided." },
            { field1: "xw_expiry_premium", field2: "xw_policy_renewal_date", message1: "X-Wind Policy Renewal Date is required when X-Wind Expiring Premium is provided.", message2: "X-Wind Expiring Premium is required when X-Wind Policy Renewal Date is provided." },

            // Equipment Breakdown
            { field1: "equipment_breakdown", field2: "eb_ren_month", message1: "Equipment Breakdown Renewal Month is required when Equipment Breakdown Carrier is provided.", message2: "Equipment Breakdown Carrier is required when Equipment Breakdown Renewal Month is provided." },
            { field1: "eb_expiry_premium", field2: "eb_policy_renewal_date", message1: "Equipment Breakdown Policy Renewal Date is required when Equipment Breakdown Expiring Premium is provided.", message2: "Equipment Breakdown Expiring Premium is required when Equipment Breakdown Policy Renewal Date is provided." },

            // Commercial Automobiles
            { field1: "commercial_automobiles", field2: "ca_ren_month", message1: "Commercial Automobiles Renewal Month is required when Commercial Automobiles Carrier is provided.", message2: "Commercial Automobiles Carrier is required when Commercial Automobiles Renewal Month is provided." },
            { field1: "ca_expiry_premium", field2: "ca_policy_renewal_date", message1: "Commercial Automobiles Policy Renewal Date is required when Commercial Automobiles Expiring Premium is provided.", message2: "Commercial Automobiles Expiring Premium is required when Commercial Automobiles Policy Renewal Date is provided." },

            // Marina
            { field1: "marina", field2: "m_ren_month", message1: "Marina Renewal Month is required when Marina Carrier is provided.", message2: "Marina Carrier is required when Marina Renewal Month is provided." },
            { field1: "m_expiry_premium", field2: "m_policy_renewal_date", message1: "Marina Policy Renewal Date is required when Marina Expiring Premium is provided.", message2: "Marina Expiring Premium is required when Marina Policy Renewal Date is provided." }
        ];

        fieldPairs.forEach(({ field1, field2, message1, message2 }) => {
            const value1 = document.getElementById(field1).value.trim();
            const value2 = document.getElementById(field2).value.trim();

            if (value1 !== "" && value2 === "") {
                toastr.error(message1);
                isValid = false;
            }

            if (value2 !== "" && value1 === "") {
                toastr.error(message2);
                isValid = false;
            }
        });

        const fields = [
            { key: "ins_prop_carrier", name: "Property carrier" },
            { key: "general_liability", name: "General Liability carrier" },
            { key: "crime_insurance", name: "Crime Insurance carrier" },
            { key: "directors_officers", name: "Directors & Officers carrier" },
            { key: "umbrella_exclusions", name: "Umbrella carrier" },
            { key: "workers_compensation", name: "Workers Compensation carrier" },
            { key: "flood", name: "Flood carrier" },
            { key: "difference_in_condition", name: "Difference In Conditions carrier" },
            { key: "x_wind", name: "X-Wind carrier" },
            { key: "equipment_breakdown", name: "Equipment Breakdown carrier" },
            { key: "commercial_automobiles", name: "Commercial AutoMobiles carrier" },
            { key: "marina", name: "Marina carrier" },

            { key: "rating", name: "Property Rating" },
            { key: "gl_rating", name: "General Liability Rating" },
            { key: "ci_rating", name: "Crime Insurance  Rating" },
            { key: "do_rating", name: "Directors & Officers Rating" },
            { key: "umbrella_rating", name: "Umbrella Rating" },
            { key: "wc_rating", name: "Workers Compensation Rating" },
            { key: "flood_rating", name: "Flood Rating" },

            { key: "ordinance_of_law", name: "Ordinance of Law" },
        ];

        fields.forEach(field => {
            const carrier = document.getElementById(field.key)?.value.trim();
            const otherValue = document.getElementById(`${field.key}-other`)?.value.trim();

            if (carrier === "other" && (!otherValue || otherValue === "")) {
                toastr.error(`You have selected 'Other' for the ${field.name} but have not provided a value. Please provide the required information.`);
                isValid = false;
            }

            // console.log(carrier,otherValue,isValid);
        });


        $(".additional_policy").each(function() {
            const index = $(this).data('id');
            let policy_type = $("#policy_type" + index).val().trim();
            let carrier = $("#carrier" + index).val().trim();

            let additional_name;

            if(policy_type == ""){
                additional_name = `Additional Policy ${index + 1}`;
            }
            else{
                additional_name = `Additional Policy (${policy_type})`;
            }

            if (!carrier || carrier === "") {
                toastr.error(`Carrier Selection is required for ${additional_name}`);
                isValid = false;
            }

            if(carrier == 'other'){
                carrier = $("#carrier"+index+"-other").val().trim();
                if (!carrier || carrier === "") {
                    toastr.error(`'other' is selected for  Carrier Selection of ${additional_name}, But havenot provided any value`);
                    isValid = false;
                }
            }

            if (!policy_type || policy_type === "") {
                toastr.error(`Policy Type Selection is required for Additional Policy ${additional_name}`);
                isValid = false;
            }
        });

        // Return false to prevent form submission if validation fails
        return isValid;
    }



    $( "#contactsAccordion" ).accordion({
      heightStyle: "content",
      collapsible: true,
      active: false
    });
let prevChatContactIds = [];
let clickCount = 0;

$(document).on("click",'#preview_btn',function () {
    const modalElement = document.getElementById('previewLeadModal');
    const modal = new bootstrap.Modal(modalElement);
    modal.show();

    // $("#previewLeadModal").modal("show");
    loadPreviewData();
});

$(document).on("click",'.pre_filled_data',function () {
    const modalElement = document.getElementById('previousFilledLeadModal');
    const modal = new bootstrap.Modal(modalElement);
    modal.show();

    // $("#previousFilledLeadModal").modal("show");
});

function nextbuttonClicked(type) {
    var tabId;

    // Determine the tab ID based on the type
    if (type === 1) {
        tabId = "#pills-Lead-tab-2";
    } else if (type === 2) {
        tabId = "#pills-lead-actions-tab-2";
    } else if (type === 3) {
        tabId = "#pills-lead-campaigns-tab-2";
    }

    // If a valid tab ID is determined, activate the tab
    if (tabId) {
        var tabElement = document.querySelector(tabId);
        
        if (tabElement) {
            // Trigger the click event using pure JavaScript
            tabElement.click();
        } else {
            console.error('Tab element not found:', tabId);
        }
    } else {
        console.error('Invalid type:', type);
    }
}



// Function to remove excess chat persons
function removeExcessChatPersons() {
    const maxDivs = 3;
    if ($("#chat-wrapper .chat-person").length === maxDivs) {
        // let removableContactId = $("#chat-wrapper .chat-person:last-child").attr('id').replace("chat_person_", "");
        prevChatContactIds.shift();

        $("#chat-wrapper .chat-person:last-child").remove();

        // const indexToRemove = prevChatContactIds.indexOf(removableContactId);
        // console.log(removableContactId,prevChatContactIds,indexToRemove);

        // if (indexToRemove !== -1) {
        //     prevChatContactIds.splice(indexToRemove, 1);
        // }
        // console.log(removableContactId,prevChatContactIds,indexToRemove);
    }
}


// Function to append a new chat person
function appendNewChatPerson(chatContactId, chatContactName, chatContactStatus) {
    const borderClass = getBorderClass();

    // Fetch chat content from Laravel using Ajax
    fetchChatContent(chatContactId, function(data) {

        // check the checkMaxExecTime - START
        checkMaxExecTime(chatContactId);
        // check the checkMaxExecTime - STOP

        // Append the new chat person with dynamic content
        // data = JSON.parse(data);
        let html = '';
        let prevDate = null;

        data.response.forEach(message => {
            const msgDate = new Date(message.created_at);
            const currentDate = new Date();

            const differenceInDays = Math.floor((currentDate - msgDate) / (1000 * 60 * 60 * 24));
            const formattedDateTime = differenceInDays <= 6 ?
                msgDate.toLocaleDateString('en-US', {
                    weekday: 'long'
                }) :
                msgDate.toLocaleDateString();
            let timeString = new Date(message.created_at).toLocaleTimeString();
            let timeWithoutSecond = new Date(message.created_at).toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit',
                hour12: true
            });

            // let formattedDateTime = msgDate + ' ' + timeString;

            if (formattedDateTime !== prevDate) {
                html += `<div class="message-date"><span>${formattedDateTime}</span></div>`;
                prevDate = formattedDateTime;
            }

            if (message.chat_type === 'outbound') {
                html +=
                    `<p class="my-txt mb-2 p-2">`;
                if(data.is_admin){
                    let agent_name = message.name ? message.name : 'System';

                    html += `<span class="agent-name d-block mb-1 pb-1 text-right border-bottom font-weight-bold"> ${agent_name}</span>`;
                }

                html += `<span class="d-block">${message.content}</span></p> <p class="snd-msg">${timeWithoutSecond}</p>`;
            } else if (message.chat_type === 'inbound' && message.chat_sms_sent_status === 5) {
                html += `<p class="other-txt mb-2 startstopmessage">${message.content}</p>`;
            } else {
                html +=
                    `<p class="other-txt mb-2">${message.content}</p> <p class="rcv-msg">${timeWithoutSecond}</p>`;
            }
        });

        // button for attamenet, removed this <button class="position-absolute chat-attachment">
                            //     <svg xmlns="http://www.w3.org/2000/svg" height="20" width="28" viewBox="0 -960 960 960" width="48"><path d="M728-326q0 103-72.18 174.5-72.17 71.5-175 71.5Q378-80 305.5-151.5T233-326v-380q0-72.5 51.5-123.25T408-880q72 0 123.5 50.75T583-706v360q0 42-30 72t-72.5 30q-42.5 0-72.5-29.67-30-29.68-30-72.33v-370h60v370q0 17 12.5 29.5t30.64 12.5q18.14 0 30-12.5T523-346v-360q0-48-33.5-81t-81.71-33q-48.21 0-81.5 33.06T293-706v380q0 78 54.97 132T481-140q77.92 0 132.46-54Q668-248 668-326v-390h60v390Z"/></svg>
                            // </button>
        $("#chat-wrapper").prepend(`
				<div class="position-relative chat-person ml-3 ${borderClass} border rounded" id="chat_person_${chatContactId}">
                    
					<h4 class="bg-${borderClass.replace('border-', '')} mb-0 px-2 py-3 d-flex align-items-center justify-content-between">${chatContactName}
                    <div class="d-flex align-items-center">
                    <i class="fas fa-chevron-down minimise_chatbox mr-2" id="${chatContactId}"></i>
                    <div data-id="${chatContactId}" class="close_chatbox bg-${borderClass.replace('border-', '')} d-flex align-items-center justify-content-center rounded-circle cross-chat">
                        <i class="fas fa-times"></i>
                    </div>
                        
                        </div>
                    </h4>
					<div class="off-div" id="off-div-${chatContactId}">
						<div class="chat-box p-2" id="chat_message_${chatContactId}">
							
					${html}

						</div>
						<div class="position-relative chat-footer" id="chat_footer_${chatContactId}">
							<textarea type="text" class="text-input" placeholder="Write your text here"></textarea>

                            
                            <select id="templateSelect" class="chat-template">
                                <option>-- Templates --</option>
                                <option>Create Template</option>
                                <option>Saved Templates</option>
                            </select>
							<button class="chat-send" id="chat_send_${chatContactId}"> 
								<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Uploaded to svgrepo.com" width="20px" height="20px" viewBox="0 0 32 32" xml:space="preserve">
									<path class="stone_een" d="M10.774,23.619l-1.625,5.691C9.06,29.164,9,28.992,9,28.794v-5.57l13.09-12.793L10.774,23.619z   M10.017,29.786c0.243-0.002,0.489-0.084,0.69-0.285l3.638-3.639l-2.772-1.386L10.017,29.786z M28.835,2.009L3.802,14.326  c-2.226,1.095-2.236,4.266-0.017,5.375l4.89,2.445L27.464,3.79c0.204-0.199,0.516-0.234,0.759-0.086  c0.326,0.2,0.396,0.644,0.147,0.935l-16.3,18.976l8.84,4.4c1.746,0.873,3.848-0.128,4.27-2.034l5.071-22.858  C30.435,2.304,29.588,1.639,28.835,2.009z"/>
								</svg>
							</button>
						</div>
					</div>
				</div>
			`);

        if (chatContactStatus) {
            $('#chat_footer_' + chatContactId).hide();
        }

        clickCount++;
        prevChatContactIds.push(chatContactId); // Add the current chatContactId to the array
        console.log(prevChatContactIds);
    });

}

$(document).on('click', '#chat-wrapper .close_chatbox', function(e) {
    e.preventDefault();
    let closeDiv = $(this).closest('.chat-person');
    let removableContactId = parseInt($(this).attr('data-id'));

    console.log(prevChatContactIds,removableContactId);

    prevChatContactIds = prevChatContactIds.filter(function(id) {
        return id !== removableContactId;
    });
    closeDiv.remove();
    console.log(prevChatContactIds,removableContactId);

    // let indexToRemove = prevChatContactIds.indexOf(removableContactId);
    // closeDiv.remove();
    
    // if (indexToRemove !== -1) {
    //     prevChatContactIds.splice(indexToRemove, 1);
    //     console.log(prevChatContactIds);
    // }
});

// Function to handle chat initialization
function handleChatInit(e) {
    // console.log("hello");
    e.preventDefault();
    e.stopPropagation();

    const maxDivs = 3;
    const chatContactId = parseInt($(this).attr("id").replace("chat_contact_", ""));
    const chatContactName = $(this).data("name");
    const chatContactStatus = $(this).data("chat_contact_status");

    // Check if the clicked chatContactId is not in the array of previous ones

    if (!prevChatContactIds.includes(chatContactId)) {
        removeExcessChatPersons();
        appendNewChatPerson(chatContactId, chatContactName, chatContactStatus);
    }
}


function getBorderClass() {
    const borderClasses = ['border-danger', 'border-info', 'border-dark'];
    return borderClasses[clickCount % 3];
}


function fetchChatContent(chatContactId, successCallback) {
    $.ajax({
        url: `/chat/${chatContactId}`, // Replace with your Laravel route
        type: 'GET',
        success: successCallback,
        error: function(error) {
            console.error('Error fetching chat content:', error);
        }
    });
}


$("#chat-wrapper").on("click", ".chat-send", function(e) {
    e.preventDefault();
    e.stopPropagation();

    let timeString = new Date().toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: true
    });

    let chatContent = $(this).siblings(".text-input").val();
    const chatContactId = $(this).attr("id").replace("chat_send_", "");

    let dataMsgs = document.getElementById(`chat_message_${chatContactId}`);
    let viewContent = chatContent;
    if (dataMsgs.children.length === 0) {
        viewContent = chatContent + `</br> Please text "STOP" to stop the conversation.`;
    }
    if (chatContent.trim() !== "") {

        // function to save data in mesage and append data in msg
        saveMessageInChat(chatContent, chatContactId, viewContent, timeString);
        // $.ajax({
        //     url: '/chat',
        //     method: "POST",
        //     data: {
        //         content: chatContent,
        //         chatContactId: chatContactId

        //     },
        //     success: function(response) {
        //         $(`#chat_message_${chatContactId}`).append(
        //             `<p class="my-txt mb-2">${viewContent}</p>
        // 			<p class="snd-msg">${timeString}</p>`);
        //         $(`#chat_contact_${chatContactId} .text-input`).val("");
        //     },
        //     error: function(xhr, status, error) {
        //         let jsonResponse = JSON.parse(xhr.responseText);
        //         toastr.error(jsonResponse.response);
        //     }
        //     // error: function(error) {
        //     //     console.error(error);
        //     // }
        // });

        // Clear the textarea after posting the chat
        $(this).siblings(".text-input").val("");
    }
});

function saveMessageInChat(chatContent, chatContactId, viewContent, timeString) {
    $.ajax({
        url: '/chat',
        method: "POST",
        data: {
            content: chatContent,
            chatContactId: chatContactId

        },
        success: function(response) {
            // console.log(response.is_admin);
            let appendhtml = 
                `<p class="my-txt mb-2 p-2">`;
            if(response.is_admin){
                let agent_name = response.logged_in_user_name ? response.logged_in_user_name : 'System';

                appendhtml += `<span class="agent-name d-block mb-1 pb-1 text-right border-bottom font-weight-bold"> ${agent_name}</span>`;
            }

            appendhtml += `<span class="d-block">${viewContent}</span></p> <p class="snd-msg">${timeString}</p>`;

            $(`#chat_message_${chatContactId}`).append(appendhtml);

            // $(`#chat_message_${chatContactId}`).append(
            //     `<p class="my-txt mb-2">${viewContent}</p>
			// 		<p class="snd-msg">${timeString}</p>`);
            $(`#chat_contact_${chatContactId} .text-input`).val("");
        },
        error: function(xhr, status, error) {
            let jsonResponse = JSON.parse(xhr.responseText);
            toastr.error(jsonResponse.response);
        }
    });
}

function applySavedTemplate(templateId, singleContactId) {
    // getting data from contact table
    $.ajax({
        url: `/template/singleDetail/contactDetail/${singleContactId}`,
        method: 'GET',
        success: async function(data) {
            let responseData;
            try {
                responseData = JSON.parse(data);

                // getting template-content from template id
                let singleTemplateWithJson = await singleTemplateDetail(templateId);
                let jsonTemplate = JSON.parse(singleTemplateWithJson);

                let template_content = jsonTemplate.response[0].template_content;
                let email_content = jsonTemplate.response[0].template_content;
                let template_subject = jsonTemplate.response[0].template_subject;

                let c_first_name  = responseData.response[0].c_first_name;
                let c_last_name   = responseData.response[0].c_last_name;
                let business_name = responseData.response[0].leads.name;

                // txtToElem(template_content);
                template_content = template_content.replace(/{CANDIDATE_FIRST_NAME}/g, c_first_name);
                template_content = template_content.replace(/{CANDIDATE_LAST_NAME}/g, c_last_name);
                template_content = template_content.replace(/{BUSINESS_NAME}/g, business_name);
                // template_content = template_content.replace("{CANDIDATE_FIRST_NAME}", c_first_name);
                // template_content = template_content.replace("{CANDIDATE_LAST_NAME}", c_last_name);

                // $(`#chat_footer_${singleContactId} textarea.text-input`).val(template_content);
                // $(`#chat_footer_${singleContactId} textarea.text-input`).html(template_content);


                // Create a temporary div element
                // console.log(template_content);
                let tempDiv = document.createElement('div');
                tempDiv.innerHTML = template_content;

                // return false;

                // Get the plain text content of the temporary div

                const contact_mode = localStorage.getItem('contact_mode');
                if (contact_mode && contact_mode == "email") {
                    let renderedContent = tempDiv.innerHTML || '';
                    theEmailEditor.setData(renderedContent);
                    $(`#email_subject`).val(template_subject);
                } else {
                    let renderedContent = tempDiv.textContent || tempDiv.innerText || '';
                    // Set the plain text content as the value of the textarea
                    $(`#chat_footer_${singleContactId} textarea.text-input`).val(renderedContent);
                }

            } catch (error) {
                console.log(error);
                toastr.error('Invalid server response');
                return;
            }
            if (responseData.status == '200') {
                // toastr.success('Contact detail showed successfully.');
                closeSavedTemplateNav();
            } else {
                toastr.error('Unexpected server response');
                return;
            }
        },
        error: function(xhr, status, error) {
            let jsonResponse = JSON.parse(xhr.responseText);
            toastr.error(jsonResponse.response);
            return;
        }
    });
}

function singleTemplateDetail(singleTemplateId) {
    return new Promise((resolve, reject) => {
        $.ajax({
            url: `/template/singleDetail/templateDetail/${singleTemplateId}`,
            method: 'GET',
            success: function(data) {
                resolve(data);
            },
            error: function(xhr, status, error) {
                reject(xhr.responseText);
                return;
            }
        });
    })
}

function checkMaxExecTime(contactId) {
    $.ajax({
        url: `/check_max_execution_time/${contactId}`,
        method: 'GET',
        success: function(response) {
            if (response.status == '200' && response.success == true && response.response > 0) {
                // console.log($(`#chat_send_${contactId}`));
                $(`#chat_send_${contactId}`).attr('disabled', true);
            } else {
                console.log($(`#chat_send_ELSEEEEEEEEEEEE`));
                $(`#chat_send_${contactId}`).attr('disabled', false);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error:', error);
            console.log('Response:', xhr.responseText);
        }
    });
}




jQuery(document).ready(function() {

    const leadNoteSuccessMessage = localStorage.getItem('leadNoteSuccessMessage');
    if (leadNoteSuccessMessage) {
        toastr.success(leadNoteSuccessMessage);
        localStorage.removeItem('leadNoteSuccessMessage'); // Remove the message after displaying
    }
    // check page has query parameter chat_contact_open = START
    let queryString = window.location.search;
    let queryParams = new URLSearchParams(queryString);
    let contactQueryId = queryParams.get('chat_contact_open');
    if (queryParams && contactQueryId) {
        // console.log($(`#chat_contact_${contactQueryId}`));
        $(`#chat_contact_${contactQueryId}`).trigger("click");
    }
    // check page has query parameter chat_contact_open = STOP

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

    ClassicEditor
        .create(document.querySelector('#template_content'))
        .then(editor => {
            theEditor = editor; // Save for later use.
        })
        .catch(error => {
            console.error(error);
        });
    ClassicEditor
        .create(document.querySelector('#email_content'))
        .then(editor => {
            theEmailEditor = editor; // Save for later use.
        })
        .catch(error => {
            console.error(error);
        });



});
$('.insert-placeholder').on('click', function () {
    let placeholder = $(this).data('placeholder');
    console.log(placeholder);
    theEditor.model.change(writer => {
        let selection = theEditor.model.document.selection;
        let position = selection.getFirstPosition();
        writer.insertText(placeholder, position);
    });
    theEditor.editing.view.focus();

});

// keep the open tab on window refresh
document.addEventListener('shown.bs.tab', function (e) {
    sessionStorage.setItem('activeTab', e.target.getAttribute('href'));
});

document.addEventListener('DOMContentLoaded', function () {
    const activeTab = sessionStorage.getItem('activeTab');
    if (!activeTab) return;

    const tabTrigger = document.querySelector(
        `a[data-bs-toggle="pill"][href="${activeTab}"]`
    );

    if (tabTrigger) {
        new bootstrap.Tab(tabTrigger).show();
    }
});

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

function setActionModal(button, $id) {
    $('#userLeadActions').find('select[name=contact_id] option[value=' + $id + ']').prop('selected', true);
}

$('#userLeadActions').on('shown.bs.modal', function() {
    var select = $(this).find('select[name=contact_id]');
    get_set_other_val_actions(select)
});

$(document).on('click', '#chat-wrapper .fas.fa-chevron-down', function(e) {
    e.preventDefault();
    let offDivs = $(this).closest('.chat-person').find('.off-div');
    $(this).css("transform-origin", "center");
    if ($(this).hasClass('rotate-180')) {
        $(this).css("transform", "rotate(0deg)");
        $(this).removeClass('rotate-180');
    } else {
        $(this).css("transform", "rotate(180deg)");
        $(this).addClass('rotate-180');
    }
    offDivs.toggle('slow');
});

// template coding
// let templateContentAppended = false;
$(document).on('change', '#templateSelect', function() {
    $(".noDataInSavedTemplate").remove();
    let self = this;
    // let chatContactId = '';
    let chatContactId = $(self).siblings('.chat-send').attr('id').replace(
        "chat_send_", "");
    localStorage.setItem("current_selected_contact_id", chatContactId);
    if (self.value === 'Saved Templates') {
        $.ajax({
            url: `/template/listByUserid/alldata`,
            method: 'GET',
            success: function(data) {
                let templateData = JSON.parse(data);
                if (templateData.response.length === 0 && !templateContentAppended) {
                    $("#lead-saved-filter-sidebar").append(`
						<div class="lead-saved-filters d-flex row m-0 justify-content-center mt-3 noDataInSavedTemplate">
							There is no data in list
						</div>`);
                    $("#lead-saved-filter-sidebar").addClass('show');
                    //$("#lead-saved-filter-sidebar").css('z-index', '99999');
                    templateContentAppended = true;
                } else {
                    templateData.response.forEach(template => {
                        let chatContent = template.template_content;
                        if (!templateContentAppended) {
                            // $("#noDataInSavedTemplate").empty();
                            // $(".lead-saved-filters").append(`
                            if(template.delete_permission) {
                                $("#lead-saved-filter-sidebar").append(`
                                    <div class="lead-saved-filters d-flex row m-0 justify-content-center templateSavedList" id="templateSavedList">
                                        <div class="filter d-flex align-items-center py-1 filter_id_${template.id} w-100">
                                            <div class="title d-flex">
                                                <label>${template.template_name}</label>
                                            </div>
                                            <button class="btn btn-success btn-sm mr-2 apply" type="button" onclick="applySavedTemplate('${template.id}', '${localStorage.getItem('current_selected_contact_id')}')">
                                                <i class="fas fa-check"></i></button>
                                            <button class="btn btn-danger btn-sm closebtn mr-1" type="button" onclick="deleteSavedTemplate('${template.id}')">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>`
                                );
                            } else {
                                $("#lead-saved-filter-sidebar").append(`
                                    <div class="lead-saved-filters d-flex row m-0 justify-content-center templateSavedList" id="templateSavedList">
                                        <div class="filter d-flex align-items-center py-1 filter_id_${template.id} w-100">
                                            <div class="title d-flex">
                                                <label>${template.template_name}</label>
                                            </div>
                                            <button class="btn btn-success btn-sm mr-2 apply" type="button" onclick="applySavedTemplate('${template.id}', '${localStorage.getItem('current_selected_contact_id')}')">
                                                <i class="fas fa-check"></i></button>
                                        </div>
                                    </div>`
                                );
                            }
                        }

                        $("#lead-saved-filter-sidebar").addClass('show');
                        // $("#lead-saved-filter-sidebar").css('z-index', '99999');
                    });
                }
                templateContentAppended = true;
            },

            error: function(xhr, status, error) {
                let jsonResponse = JSON.parse(xhr.responseText);
                toastr.error(jsonResponse.error);
                return false;
            }
        });
        // alert('Selected Template: ' + self.value);
    }
    // else if (self.value !== 'Saved Templates') {
    else if (self.value == 'Create Template') {
        closeSavedTemplateNav();
        $('.template-modal').addClass('is-visible');
        $('body').addClass('overflow-hidden');

    } else if (self.value == '-- Templates --') {
        closeSavedTemplateNav();
    }
});


$('.template_modal_close_class').on('click', () => {
    $('.template-modal').removeClass('is-visible');
    $('body').removeClass('overflow-hidden');
});

function closeSavedTemplateNav() {
    // alert('close');
    $("#lead-saved-filter-sidebar").removeClass('show');
    $("#emailModal").removeClass('modal-disable');
    $(".chat-template").val('-- Templates --');
    $("#emailTemplateSelect").val('-- Templates --');
    $("#lead-saved-filter-sidebar").find('.templateSavedList').remove();
    templateContentAppended = false;
}

// add new template
$('#myFormAddNewTemplate').on('submit', function(event) {
    event.preventDefault();

    let editorContent = theEditor.getData().trim();
    $('#myFormAddNewTemplate').siblings("#template_content").val(editorContent)
    // theEditor.setData(editorContent);
    if (editorContent === '') {
        toastr.error('Template content should not be blank');
        return false; // Prevent form submission
    }
   
    let formData = $(this).serializeArray();
    formData.push({
        name: 'template_content',
        value: editorContent
    });
    $.ajax({
        type: 'POST',
        url: '/template/addNewTemplate/addNew',
        data: formData,
        success: function(response) {
            try {
                let responseData = response;
                if (responseData.status == '200') {
                    $('#myFormAddNewTemplate').siblings("#template_name").val("");
                    $('#myFormAddNewTemplate').siblings("#template_content").val("");
                    $('.template-modal').removeClass('is-visible');
                    $('body').removeClass('overflow-hidden');
                    theEditor.setData('');
                    toastr.success(responseData.message);
                } else if (responseData.status == '422') {
                    responseData.errors.forEach((e) => {
                        toastr.error(e);
                    });
                    return false;
                } else if (responseData.status == '500') {
                    toastr.error(responseData.response);
                    return false;
                } else {
                    toastr.error('Unexpected server response');
                    return false;
                }

            } catch (error) {
                toastr.error('Internal server error');
                return false;
            }
        },
        error: function(xhr, status, error) {
            let jsonResponse = JSON.parse(xhr.responseText);
            toastr.error(jsonResponse.response);
            return false;
        }
    });
    
});


let theEditorEdit;
function setNoteModal(elem, $id) {
    // console.log(elem);
    //ajax to get note data
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        },
        url: window.location.origin + '/leads/show/note-show-modal/' + $id,
        dataType: "json",
        data: {
            id: $id
        },
        type: 'GET',
        success: function(response) {
            //set values
            // $('.noteTitleEdit').val(response['title']);
            $('.noteContactEdit').val(response['contact_id']);
            populateNoteEditor(response['description']);
        },
        error: function(response) {}

    });

    function populateNoteEditor(description) {
        if (theEditorEdit) {
            theEditorEdit.setData(description);
        } else {
            // Create the editor
            ClassicEditor
                .create(document.getElementById('note_desc_edit'), {
                    extraPlugins: [ MyCustomUploadAdapterPlugin ],
                })
                .then(editor => {
                    theEditorEdit = editor;
                    theEditorEdit.setData(description);
                })
                .catch(error => {
                    console.error(error);
                });
        }
    }

    //show modal
    // $('#editNoteModal').modal('show');
    const modalElement = document.getElementById('editNoteModal');
    const modal = new bootstrap.Modal(modalElement);
    modal.show();
    $('#editNoteModal').attr('data-source', '#ordr_' + $id);
    //set url
    var url = window.location.origin + '/leads/show/note-update/' + $id;


    $('#editNoteModal #saveNote').on('click',function() {
        const editorData = theEditorEdit.getData();
        if (!editorData) {
            toastr.error('Description is required');
            return false;
        }
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('#editNoteModal input[name="_token"]').val()
            },
            url: url,
            dataType: "json",
            data: {
                contact_id: $('.noteContactEdit').val(),
                description: editorData,
                id: $id
            },
            type: 'POST',
        }).done(function(response) {
            if (response.error) {
                toastr.error(response.error);
                return false;
            }
            if (response.success) {
                localStorage.setItem('leadNoteSuccessMessage', response.success);
                window.location.reload(); // Reload the page to trigger Toastr display
            }
        }).fail(function(response) {
            if (response.responseJSON && response.responseJSON.message) {
                toastr.error(response.responseJSON.message);
            } else {
                toastr.error('An unexpected error occurred. Please try again later.');
            }
            return false;
        });

        toastr.options.onHidden = function() {
            localStorage.removeItem('leadNoteSuccessMessage');
        };
    });

}


function set_reload() {
    // reload page when toastr finishes
    toastr.options.onHidden = function() {
        window.location.reload();
    }
    //reload page when modal is closed    
    $('#editNoteModal').on('hide.bs.modal', function(e) {
        location.reload();
    });


}
function set_reload() {
    // reload page when toastr finishes
    toastr.options.onHidden = function() {
        window.location.reload();
    }
    //reload page when modal is closed    
    $('#editNoteModal').on('hide.bs.modal', function(e) {
        location.reload();
    });


}

$('.openEmailPopup').on('click',function(event) {
    event.preventDefault();
    const emailContactId = $(this).data("contact-id");
    const emailToContact = $(this).data("email-to-contact");
    $("#emailToContact").text(emailToContact);
    localStorage.setItem("current_selected_contact_id", emailContactId);
    localStorage.setItem("contact_mode", 'email');
    $('#emailModal').addClass('is-visible');
    $('body').addClass('overflow-hidden');
});

$(".chat_intitialise").off("click").on("click", handleChatInit);

function restrictInput(input, maxLength) {
    let value = input.value;

    // Remove any non-numeric characters
    value = value.replace(/[^0-9]/g, '');

    // Restrict to maxLength digits
    if (value.length > maxLength) {
        value = value.slice(0, maxLength);
    }

    // Set the sanitized value back to the input field
    input.value = value;
}


// Adding sticky header
</script>

@include('partials.email-modal-script')
@endpush