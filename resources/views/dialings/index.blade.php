@extends('layouts.app')
@section('pagetitle', 'Dialing Lists')
@push('breadcrumbs')
<li class="breadcrumb-item"><a href="{{route('dialings.index')}}">Lists</a></li>
<li class="breadcrumb-item active">Dialing Lists</li>
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
                            <div class="d-flex flex-wrap pb-2">
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
                            </div>
                        </div>
                    </div>
                </div>
                <div class="px-3 pt-3 border-top">
                    <div class="table-container pb-2">
                        <table class="order-column compact hover searchHighlight mt-3" id="agents_datatable">
                            <thead class="text-nowrap" style="font-size: 0.93rem;">
                                <tr>

                                    <th>No</th>
                                    <th></th>
                                    <th>List Name <span class="arrow"></span></th>
                                    <!-- <th style="min-width: 80px;">No. of leads</th> -->

                                    <th>Agent <span class="arrow"></span></th>
                                    <th>Status</th>

                                    <th>Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
</section>
<!-- @include('dialings.partials.ownagentlead-modal') -->
@include('partials.delete-modal')
@include('dialings.partials.agent-assign-modal')
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
var rows_selected = [];
var selectedCheckboxes = [];

/****  Document Ready ****/
jQuery(document).ready(function() {
    jQuery.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
        }
    });
    resetAgentLeadDatatable(); // for dialing inside data filtering clean
    draw_table();
    sessionStorage.setItem("selectedAgentIds", selectedCheckboxes);

    // Reset the search field on page show (back/forward navigation)
    window.addEventListener('pageshow', function(event) {
        $('body').find('#customSearchBox').val('');
        $('body').find('#customPageLength').val('25');
    });
});


$(document).on('click', '#save_agent_list_button', function() {
    var selected_agent_id = $('#agent_list').val();
    var selected_agent_ids = "";
    if (sessionStorage.getItem("selectedAgentIds")) {
        selected_agent_ids = JSON.parse(sessionStorage.getItem("selectedAgentIds"));
    }
    if (!selected_agent_id || selected_agent_id.length === 0) {
        toastr.error('Please select an agent to assign');
        return false;
    }


    $.ajax({
        type: 'POST',
        url: "{{ url('/dialings/assign') }}",
        dataType: 'json',
        data: {
            selected_agent_id: selected_agent_id, // send the search options
            selected_agent_list_ids: selected_agent_ids, // send agent list name
        },
        success: function(data, status, xhr) {
            $('#agentassignmodal').modal('hide');
            if (data.custom_status) {
                toastr.success(data.message);
                setTimeout(function() {
                    location.reload();
                }, 3000); 
            } else {
                toastr.error(data.message);
            }


        },
        error: function(jqXHR, textStatus, errorThrown) {
            $('#agentassignmodal').modal('hide');
            toastr.error(errorThrown);

        }
    });
});

function updateStatus(agentlist_id, current_status) {
    $.ajax({
        type: 'POST',
        url: "{{ url('/dialings/statuschange') }}",
        dataType: 'json',
        data: {
            agentlist_id: agentlist_id, // send the search options
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
    var selectedCheckboxes = [];
    // stateSave- when there are no filters
    var table = jQuery('#agents_datatable').DataTable({
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
            url: "{{ url('dialings/dialings-custom') }}",
            type: 'GET',
        },

        columns: [
            //set table columns

            {
                data: 'DT_RowIndex',
                name: 'DT_RowIndex',
                targets: [1],
                searchable: false,
                orderable: false,
            },
            {
                data: 'id',
                name: 'id',
                visible: false
            },
            {
                data: 'name',
                name: 'name'
            },
            // {
            // 	data: 'lead_number',
            // 	name: 'lead_number'
            // },
            {
                data: 'agent_name',
                name: 'agent_name'
            },
            {
                data: 'status',
                name: 'status',
                orderable: false,
                searchable: false
            },
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false
            },
        ],
        order: [
            [1, 'desc']
        ],
        dom: 'rt<"bottom"ip><"clear">',
        rowCallback: function(row, data, dataIndex) {
            // Get row ID
            var rowId = data[0];
            // If row ID is in the list of selected row IDs
            if ($.inArray(rowId, rows_selected) !== -1) {
                $(row).find('input[type="checkbox"]').prop('checked', true);
                $(row).addClass('selected');
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
        if(!event.target.value){
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
            
            $(event.target).siblings('i.fas.fa-search.position-absolute').remove(); // remove search icon and the append
            $(event.target).after('<i class="fas fa-search position-absolute"></i>');
            table.search(event.target.value).draw(); // drow the table
        }
    }, 500));


    // Handle table draw event
    table.on('draw', function() {
        // Update state of "Select all" control
        updateDataTableSelectAllCtrl(table);
    });

}

function updateDataTableSelectAllCtrl(table) {
    var $table = table.table().node();
    var $chkbox_all = $('#agents_datatable tbody input[type="checkbox"]', $table);
    var $chkbox_checked = $('#agents_datatable tbody input[type="checkbox"]:checked', $table);
    var chkbox_select_all = $('#agents_datatable thead input[name="select_all"]', $table).get(0);

    if (chkbox_select_all) {

        // If none of the checkboxes are checked
        if ($chkbox_checked.length === 0) {
            chkbox_select_all.checked = false;
            if ('indeterminate' in chkbox_select_all) {
                chkbox_select_all.indeterminate = false;
            }

            // If all of the checkboxes are checked
        } else if ($chkbox_checked.length === $chkbox_all.length) {
            chkbox_select_all.checked = true;
            if ('indeterminate' in chkbox_select_all) {
                chkbox_select_all.indeterminate = false;
            }

            // If some of the checkboxes are checked
        } else {
            chkbox_select_all.checked = true;
            if ('indeterminate' in chkbox_select_all) {
                chkbox_select_all.indeterminate = true;
            }
        }
    }
}
</script>
<!-- <script src="https://cdn.datatables.net/plug-ins/1.11.3/features/searchHighlight/dataTables.searchHighlight.min.js">
</script>
<script src="//bartaz.github.io/sandbox.js/jquery.highlight.js"></script>
<script src="https://cdn.datatables.net/buttons/2.0.1/js/dataTables.buttons.min.js" defer></script>
<script src="https://cdn.datatables.net/buttons/2.0.1/js/buttons.html5.min.js" defer></script>
<script src="https://cdn.datatables.net/buttons/2.0.1/js/buttons.print.min.js" defer></script>
<script src="https://code.jquery.com/jquery-migrate-3.0.0.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.0/jquery-ui.min.css"
    integrity="sha512-LDB28UFxGU7qq5q67S1iJbTIU33WtOJ61AVuiOnM6aTNlOLvP+sZORIHqbS9G+H40R3Pn2wERaAeJrXg+/nu6g=="
    crossorigin="anonymous" referrerpolicy="no-referrer" /> -->

@endpush