@extends('layouts.app')
@section('pagetitle', $dialing->name)
@push('breadcrumbs')
<li class="breadcrumb-item"><a href="{{route('dialings.index')}}">Lists</a></li>
<li class="breadcrumb-item active">View Dialing List</li>
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
                        <div class="col-lg-12 margin-tb table-top-sec">
                            <div class="filteredTable mt-4" style="display:none">
                                <i class="fas fa-filter f-icon"></i>
                                <span class="filtered"></span>
                                <sup class="btn" onclick="closeInfoSearch()">
                                    <i class="fas fa-times-circle text-danger"></i>
                                </sup>
                            </div>
                            <div class="left-content d-flex align-items-center">
                                <a class="btn btn-info btn-sm px-2 mb-3 mb-md-0"
                                    href="{{ route('dialings.index') }}"
                                    ><i class="fas fa-arrow-circle-left"></i> Back</a>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mt-3">
                                <div class="d-flex flex-wrap action-dropdown ">
                                    @if($is_admin)
                                        <div class="dropdown">
                                            <button class="btn btn-info btn-sm dropdown-toggle" type="button" id="actionbtn"
                                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                Actions
                                            </button>
                                            <div class="dropdown-menu p-0 m-0 text-nowrap" aria-labelledby="actionbtn">
                                                <a href="javascript:void(0)"
                                                        class="rounded-0 btn-block btn btn-info text-left btn-sm mt-0"
                                                        id="bulk_aggent_reassign">
                                                        <i class="fas fa-registered mr-1"></i>
                                                        <span class="d-none d-md-inline">Agent Reassign</span>
                                                </a>
                                            </div>
                                        </div>
                                    @endif
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
                                <div class="d-flex flex-wrap ">
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
                </div>
                <div class="px-3 pt-3 border-top">
                    <div class="table-container pb-2">
                        <table class="order-column compact hover searchHighlight mt-3" id="agents_leads_datatable">
                            <thead style="font-size: 0.93rem;">
                                <tr>
                                    <th style="min-width: 30px;">No</th>
                                    <th></th>
                                    @if($is_admin)
                                        <th id="serial_no"></th>
                                    @endif
                                    <th style="min-width: 192px;">Business Name <span class="arrow"></span></th>
                                    <th>City <span class="arrow"></span></th>
                                    <th>County <span class="arrow"></span></th>
                                    <th style="min-width: 78px;">Unit Count <span class="arrow"></span></th>
                                    <th style="width: 250px">Contacts</th>
                                    <th style="min-width: 80px">Call Count <span class="arrow"></span></th>
                                    <th style="min-width: 80px">Queued On <span class="arrow"></span></th>

                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('dialings.partials.reassignagent-list-modal')
</section>
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
@if(!isset($agentlist_id))
{{$agentlist_id = 0}}
@endif

<script>
    var rows_selected = [];
    var selectedCheckboxes = [];



    /****  Document Ready ****/
    var agentlist_id = 0;
    
    jQuery(document).ready(function() {
        agentlist_id = '{{$agentlist_id}}';
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


        $('#bulk_aggent_reassign').on('click', function() {
            // console.log("hello");
            $('#leads_datatable_processing').show();
            var selectedValues = $('.select-row:checked').map(function() {
                return this.value;
            }).get();

            if (selectedValues.length > 0) {
                $('#reassignagentlist').modal('show');
            } else {
                toastr.error('Please check at least one checkbox to continue');
                $('#leads_datatable_processing').hide();
                return false;
            }
        });

        // console.log($('#bulk_aggent_reassign').length);
    });

    /**** Draw dataTable ****/
    function draw_table() {
        let pagination_number = parseInt(getFromSessionStorage('pagination_number')) || 1;
        let previous_clicked_lead_id = parseInt(getFromSessionStorage('lead_id')) || 1;

        const savedOrder = JSON.parse(getFromSessionStorage('datatable_sort_order')) || [[8, 'asc']];

        // console.log(pagination_number + '=>' + previous_clicked_lead_id);
        console.log(savedOrder);

        var isAdmin = @json($is_admin); 

        var columns = [
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
        ];

        if (isAdmin) {
            columns.push({
                data: 'DT_RowIndex',
                name: 'DT_RowIndex',
                searchable: false,
                orderable: false,
                render: function(data, type, row, meta) {
                    return '<input type="checkbox" class="select-row" value="' + row.id + '">';
                }
            });
        }

        columns.push(
            {
                data: 'name',
                name: 'name',
                render: function(data, type, row, meta) {
                    // console.log(row);
                    let encryptedId = btoa(row.id);  // base64 encode
                    return `<a class="anchortag"  href="/leads/edit/${encryptedId}" >${data}</a>`;
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
                searchable: false,
                orderable: false,
            },
            {
                data: 'no_of_times_contacts_called',
                name: 'no_of_times_contacts_called'
            },
            {
                data: 'queued_at',
                name: 'queued_at'
            }
        );


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
            displayStart: (pagination_number - 1) * 25,
            ajax: {
                url: "{{ url('dialings/dialings-leads-custom') }}",
                type: 'GET',
                data: function(d) {
                    d.agentlist_id = agentlist_id;
                }
            },



            columns:columns,
            order: savedOrder,
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

        // Reset the search field on page show (back/forward navigation)
        window.addEventListener('pageshow', function(event) {
            $('#customSearchBox').val('');
            $('#customPageLength').val('25');
        });


        // Handle table draw event
        table.on('draw', function() {
            // Update state of "Select all" control



        });
    }

    var thead = $('#agents_leads_datatable thead #serial_no');
    thead.prepend('<input type="checkbox" class="select-all">');

    // Select all checkboxes
    $('#agents_leads_datatable').on('change', '.select-all', function() {
        var checked = this.checked;
        $('.select-row').prop('checked', checked);
        // Log selected checkboxes
        if (checked) {
            var selectedValues = $('.select-row:checked').map(function() {
                return this.value;
            }).get();

        } else {

        }
    });

    // Handle individual row selections
    $('#agents_leads_datatable').on('change', '.select-row', function() {
        var $checkboxes = $('.select-row');
        $('.select-all').prop('checked', $checkboxes.length === $checkboxes.filter(':checked').length);
        // Log selected checkboxes
        var selectedValues = $('.select-row:checked').map(function() {
            return this.value;
        }).get();

    });

    $(document).on('click', '#reassign_agent_list_button', function() {
        var agent_list = $('select[name="agent_list"]').val();
        if(agent_list.length == 0){
            toastr.error("Please select any agent");
            return false;
        }
        var selectedValues = $('.select-row:checked').map(function() {
            return this.value;
        }).get();
        if(agent_list.length > selectedValues.length){
            toastr.error("Agent Selection cant be greater than selected list");
            return false;
        }
        $('#reassign_agent_list_button').attr('disabled', 'disabled');

        $.ajax({
            type: 'POST',
            url: "{{ url('/dialings/reassignagent') }}",
            data: {
                agent_list: agent_list, 
                selectedValues: selectedValues,
                dialing_id: agentlist_id,
            },
            success: function(data, status, xhr) {
                console.log(data);
                // jQuery('#bulk_aggent_reassign').prop('disabled', false);
                if (data.status) {
                    toastr.success(data.message);
                } else {
                    toastr.error(data.message);
                }
                $('#reassign_agent_list_button').removeAttr('disabled');
                $('#reassignagentlist').modal('hide');
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#reassign_agent_list_button').removeAttr('disabled');
                $('#reassignagentlist').modal('hide');
                toastr.error(errorThrown);
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