@extends('layouts.app')
@section('pagetitle', 'Owned Leads')
@push('breadcrumbs')
<li class="breadcrumb-item"><a href="{{route('dialings.index')}}">Agent</a></li>
<li class="breadcrumb-item active">Owned Leads</li>
@endpush
@section('content')
<link href="/css/jquery.dataTables.min.css" rel="stylesheet">
<script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js" defer></script>
<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-body p-0 pb-3">
                <div class="px-3 pt-3 pb-1">
                    <div class="row">
                        <div class="col-lg-12 margin-tb d-flex flex-wrap justify-content-between table-top-sec">
                            <div class="filteredTable mt-4" style="display:none">
                                <i class="fas fa-filter f-icon"></i>
                                <span class="filtered"></span>
                                <sup class="btn" onclick="closeInfoSearch()">
                                    <i class="fas fa-times-circle text-danger"></i>
                                </sup>
                            </div>
                            <div class="d-flex flex-wrap">
                                <div class="custom_search_page d-flex align-items-center justify-content-between ml-1">
                                    <div id="custom_length_menu">
                                        <label class="d-flex align-items-center justify-content-between mb-0">Show
                                            <select id="customPageLength"
                                                class="form-control form-control-sm mx-1 px-0 bg-transparent"
                                                aria-controls="leads_datatable">
                                                <option value="10">10</option>
                                                <option value="25" selected>25</option>
                                                <option value="50">50</option>
                                                <option value="100">100</option>
                                            </select>
                                            entries
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex flex-wrap pb-2">
                                <div id="leads_datatable_filter" class="dataTables_filter search-sec mb-0">
                                    <label
                                        class="d-flex align-items-center justify-content-end mb-0 position-relative"><input
                                            type="search" id="customSearchBox" placeholder="Search for Entries"
                                            aria-controls="leads_datatable" class="form-control">
                                        <i class="fas fa-search position-absolute"></i>
                                    </label>
                                </div>
                                <div class="ml-1">
                                    <a class="btn btn-success btn-sm d-flex align-items-center justify-content-center create-btn" href="{{route('dialings.ownedleadsdaywise')}}" style="width: 42px;height: 42px;">
                                        <i class="fas fa-filter"></i>
                                        <!-- <span class="d-none d-md-inline">Create</span> -->
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="px-3 pt-3 border-top">
                    <div class="table-container pb-2">
                        <table class="order-column compact hover searchHighlight mt-3" id="agents_leads_datatable">
                            <thead class="text-nowrap" style="font-size: 0.93rem;">
                                <tr>
                                    <th>No</th>
                                    <th></th>
                                    <th style="min-width: 200px;">Business Name <span class="arrow"></span></th>
                                    <th style="min-width: 150px;">City</th>
                                    <th style="min-width: 100px;">County <span class="arrow"></span></th>
                                    <th>Unit Count <span class="arrow"></span></th>
                                    <th style="min-width: 300px;">Contacts</th>
                                    <th>Call Count <span class="arrow"></span></th>
                                    <th style="min-width: 110px;">Queued On</th>
                                    @if($is_admin_user)
                                        <th>Owned By</th>
                                    @endif
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@include('dialings.partials.agentleads-assign-modal')
@include('partials.newreassign-modal')
<!-- /.content -->
@endsection
@push('styles')
{{--
<link href="/css/jquery.dataTables.min.css" rel="stylesheet"> --}}
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.16/css/dataTables.bootstrap4.min.css">
<style>
    table.dataTable span.highlight {
        background-color: #FFFF88;
        border-radius: 0.28571429rem;
    }
    table.dataTable span.column_highlight {
        background-color: #ffcc99;
        border-radius: 0.28571429rem;
    }
</style>
@endpush
@push('scripts')

<script>
const IS_ADMIN_USER = parseInt('{{$is_admin_user}}');
var rows_selected = [];
var selectedCheckboxes = [];
/****  Document Ready ****/
var agentlist_id = 0;
jQuery(document).ready(function() {

    jQuery.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
        }
    });

    draw_table();

    let previous_clicked_lead_id = parseInt(getFromSessionStorage('lead_id')) || 0;
    if (previous_clicked_lead_id) {
        let leadRow = $('#lead_custom_display_' + previous_clicked_lead_id);
        if (leadRow) {
            console.log(leadRow);
            $('html, body').animate({
                scrollTop: $('#lead_custom_display_' + previous_clicked_lead_id).offset().top
            }, 'slow');
        }
    }
    resetBackClickedSessionData();


    // Reset the search field on page show (back/forward navigation)
    window.addEventListener('pageshow', function(event) {
        $('body').find('#customSearchBox').val('');
        $('body').find('#customPageLength').val('25');
    });

});



function updateLeadStatus(contact_id, current_status) {

    $.ajax({
        type: 'POST',
        url: "{{ url('/dialings/updatecontactleads') }}",
        dataType: 'json',
        data: {
            contact_id: contact_id, // send the search options  
            current_status: current_status, // send agent list name
        },
        success: function(data, status, xhr) {
            if (data.custom_status) {
                toastr.success(data.message);
            } else {
                toastr.error(data.message);
            }

        },
        error: function(jqXHR, textStatus, errorThrown) {

            toastr.error(errorThrown);
        }
    });
}




/**** Draw dataTable ****/
function draw_table() {
    let pagination_number = parseInt(getFromSessionStorage('pagination_number')) || 1;
    let previous_clicked_lead_id = parseInt(getFromSessionStorage('lead_id')) || 1;
    // console.log(pagination_number + '=>' + previous_clicked_lead_id);

    let columns = [
        {
            data: 'DT_RowIndex',
            name: 'DT_RowIndex',
            "targets": [1],
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
            name: 'name',
            render: function(data, type, row, meta) {
                // console.log(row);
                let encryptedId = btoa(row.id);  // base64 encode
                return `<a  class="anchortag" href="/leads/edit/${encryptedId}">${data}</a>`;
            }
        },
        {
            data: 'city',
            name: 'city'
        },
        {
            data: 'county',
            name: 'county'
        },
        {
            data: 'unit_count',
            name: 'unit_count'
        },
        {
            data: 'business_contacts',
            name: 'business_contacts',
            orderable: false,
            searchable: false
        },
        {
            data: 'no_of_times_contacts_called',
            name: 'no_of_times_contacts_called'
        },
        {
            data: 'queued_at',
            name: 'queued_at',
            orderable: false,
            searchable: false
        }
    ];

    // Add the "Owned By" column for admin users
    if (IS_ADMIN_USER) {
        columns.splice(9, 0, { // insert after 'name'
            data: 'owned_by',
            name: 'owned_by',
            orderable: false,
            searchable: false,
            render: function(data, type, row) {

                return `
                    <div class="d-flex align-items-center cursor-pointer" style="gap: 0.5rem;">
                        <span class="d-block">${data}</span>
                        <i class="fas fa-edit ms-2 edit-owner-icon" data-lead-id="${row.id}" data-dialing_id="${row.dialing_id}" data-owned_by_id="${row.owned_by_id}"></i>
                    </div>
                `;
            }
        });
    }


    // stateSave- when there are no filters
    var table = jQuery('#agents_leads_datatable').DataTable({
        // dom: 'lBfrtip',
        processing: true,
        oLanguage: {
            sProcessing: `{!! trim(preg_replace('/\s+/', ' ', view('partials.datatable_loader')->render())) !!}`
        },
        serverSide: true,
        responsive: true,
        autoWidth: false,
        searchHighlight: true,
        pageLength: 25,
        ajax: {
            url: "{{ url('dialings/dialingsOwnedLeads') }}",
            type: 'GET',
            data: function(d) {

            }
        },

        columns: columns,
        order: [
            [7, 'asc']
        ],
        dom: 'rt<"bottom"ip><"clear">',
        rowCallback: function(row, data) {
            $(row).attr('id', 'lead_custom_display_' + data.id);
            if (data.id == previous_clicked_lead_id) {
                $(row).addClass('selected-back-lead');
            }


        }
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
            console.log('contact search cross clicked');
            $(event.target).blur(); // to remove cursiour from search field.

            $(event.target).siblings('i.fas.fa-search.position-absolute')
                .remove(); // remove search icon and the append
            $(event.target).after('<i class="fas fa-search position-absolute"></i>');
            table.search(event.target.value).draw(); // drow the table
        }
    }, 500));

    // Handle table draw event
    table.on('draw', function() {
        // Update state of "Select all" control


    });
}

$(document).on('click', '.edit-owner-icon', function () {
    const leadId = $(this).data('lead-id');
    const dialingId = $(this).data('dialing_id');
    const owned_by_id = $(this).data('owned_by_id');

    // console.log(owned_by_id);

    $("#reassign_agent_id").val(owned_by_id);
    $("#old_agent_id").val(owned_by_id);
    $("#reassign_dialing_id").val(dialingId);
    $("#reassign_lead_id").val(leadId);

    const modalElement = document.getElementById('newreassignModal');
    const modal = new bootstrap.Modal(modalElement);
    modal.show();
    // $("#newreassignModal").modal("show");
    let reassignBodyContent = $(".reassignBodyContent");
    // reassignBodyContent.empty(); 
});

$(document).on('change', '.owner-select-box', function () {
    const leadId = $(this).data('lead-id');
    const dialingId = $(this).data('dialing_id');
    const newOwnerId = $(this).val();

    $.ajax({
        url: `/dialings/update-owner/${leadId}`,
        method: 'POST',
        data: {
            owner_id: newOwnerId,
            dialingId: dialingId,
            _token: '{{ csrf_token() }}'
        },
        success: function (res) {
            if (res.status) {
                // alert('Owner updated!');
                toastr.success(res.message);
                $('#agents_leads_datatable').DataTable().ajax.reload(null, false);
            } else {
                toastr.error(res.message);
                // alert('Update failed!');
            }
        }
    });
});



/**** On close & reset filter ****/
</script>
<script src="https://code.jquery.com/jquery-migrate-3.0.0.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.0/jquery-ui.min.css"
    integrity="sha512-LDB28UFxGU7qq5q67S1iJbTIU33WtOJ61AVuiOnM6aTNlOLvP+sZORIHqbS9G+H40R3Pn2wERaAeJrXg+/nu6g=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
<script src="https://cdn.datatables.net/plug-ins/1.11.3/features/searchHighlight/dataTables.searchHighlight.min.js">
</script>
<script src="//bartaz.github.io/sandbox.js/jquery.highlight.js"></script>
@endpush