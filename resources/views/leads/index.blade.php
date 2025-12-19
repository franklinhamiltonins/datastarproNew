@extends('layouts.app')
@section('pagetitle', 'All Business')
@push('breadcrumbs')
<li class="breadcrumb-item">Business</a></li>
<li class="breadcrumb-item active">All Business</li>
@endpush
@section('content')
<link href="/css/jquery.dataTables.min.css" rel="stylesheet">
<script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js" defer></script>
<script src="https://maps.google.com/maps/api/js?sensor=false&key={{$google_map_api_key}}"></script>
<script src="/js/keydragzoom.js"></script>
<!-- Main content -->
<section class="content">

    <div class="container-fluid">
        <div class="card">
            <div class="card-body p-0 pb-3">
                <div class="px-3 pt-3 pb-1">
                    <div class="row">
                        <div class="col-lg-12 margin-tb d-flex flex-wrap align-items-center justify-content-between table-top-sec">
                            <div class="d-flex align-items-center flex-wrap action-dropdown">
                                @can('lead-action')
                                <div class="dropdown">
                                    <button class="btn btn-info btn-sm dropdown-toggle" type="button" id="actionbtn"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                        Actions
                                    </button>

                                    <div class="dropdown-menu rounded-top-0 p-0 m-0 text-nowrap"
                                        aria-labelledby="actionbtn" data-id="1">

                                        <!-- Filters -->
                                        <div class="dropdown d-flex dropend">
                                            <button class="btn btn-block rounded-0 btn-primary btn-sm dropdown-toggle dropdown-item"
                                                type="button" id="filtersec" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fas fa-filter"></i> Filters
                                            </button>

                                            <div class="dropdown-menu p-0 m-0" aria-labelledby="filtersec" data-id="2">
                                                @can('lead-filters')
                                                <input type="text" id="selected_filter_name" class="d-none" value="" />
                                                <input type="text" id="selected_filter_id" class="d-none" value="0" />

                                                <button class="btn btn-teal rounded-0 btn-sm btn-block m-0"
                                                    type="button" data-bs-toggle="collapse" data-bs-target="#filterByDate"
                                                    aria-expanded="true" aria-controls="filterByDate" id="filter_your_search">
                                                    <i class="fas fa-filter"></i>
                                                    <span class="d-none d-md-inline">Filter Your Search</span>
                                                </button>

                                                <button class="btn btn-secondary rounded-0 btn-sm btn-block m-0"
                                                    type="button" onclick="openSavedFiltersNav()">
                                                    <i class="fas fa-save"></i>
                                                    <span class="d-none d-md-inline">Saved Filters</span>
                                                </button>
                                                @endcan
                                            </div>
                                        </div>

                                        <!-- Maps -->
                                        <div class="dropdown d-flex dropend">
                                            <button class="btn btn-block rounded-0 btn-success btn-sm dropdown-toggle dropdown-item"
                                                type="button" id="searchsec" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fas fa-map-marker-alt"></i> Maps
                                            </button>

                                            <div class="dropdown-menu p-0 m-0 text-nowrap" aria-labelledby="searchsec" data-id="2">
                                                @can('lead-mapsearch')
                                                <button class="rounded-0 btn btn-teal btn-sm btn-block m-0"
                                                    data-bs-toggle="modal" id="mapSearchId" data-bs-target="#mapsearch">
                                                    <i class="fas fa-map"></i>
                                                    <span class="d-none d-md-inline">All</span>
                                                </button>

                                                <button class="rounded-0 btn btn-primary btn-sm btn-block m-0"
                                                    data-bs-toggle="modal" data-bs-target="#mapsearch" id="clientSearch">
                                                    <i class="fa fa-map-marker"></i>
                                                    <span class="d-none d-md-inline">Client Search</span>
                                                </button>
                                                @endcan
                                            </div>
                                        </div>

                                        <!-- Lists -->
                                        <div class="dropdown d-flex dropend">
                                            <button class="rounded-0 btn-block btn btn-secondary btn-sm dropdown-toggle dropdown-item"
                                                type="button" id="createlist" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fa fa-file-alt"></i> Lists
                                            </button>

                                            <div class="dropdown-menu p-0 m-0 text-nowrap" aria-labelledby="createlist" data-id="2">
                                                @can('lead-export')
                                                <button data-href="/export"
                                                    class="rounded-0 btn btn-sm btn-primary btn-block m-0"
                                                    data-bs-toggle="modal" data-bs-target="#saveCampaign">
                                                    <i class="fas fa-mail-bulk"></i>
                                                    <span class="d-none d-md-inline">Mailing</span>
                                                </button>

                                                <button data-href="/export"
                                                    class="rounded-0 btn btn-sm btn-teal btn-block m-0"
                                                    data-bs-toggle="modal" data-bs-target="#saveagentlist">
                                                    <i class="fas fa-phone-alt"></i>
                                                    <span class="d-none d-md-inline">Dialing</span>
                                                </button>
                                                @endcan
                                            </div>
                                        </div>

                                        <!-- Remove -->
                                        @can('lead-delete')
                                        <a href="javascript:void(0)" id="bulk_lead_remove_2"
                                            class="bulk_lead_remove text-left rounded-0 text-nowrap btn-block btn btn-danger btn-sm closebtn m-1">
                                            <i class="fas fa-trash-alt me-1"></i>
                                            <span class="d-none d-md-inline">Remove</span>
                                        </a>
                                        @endcan

                                    </div>
                                </div>

                                @endcan
                                <div class="custom_search_page d-flex align-items-center justify-content-between ml-2">
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
                            </div>
                            <div class="d-flex flex-wrap">
                                <div id="leads_datatable_filter" class="dataTables_filter search-sec mb-0">
                                    <label
                                        class="d-flex align-items-center justify-content-end mb-0 position-relative"><input
                                            type="search" id="customSearchBox" placeholder="Search for Entries"
                                            aria-controls="leads_datatable" class="form-control" val="">
                                        <i class="fas fa-search position-absolute"></i>
                                    </label>
                                </div>
                                <div class="ml-1">
                                    <a class="btn btn-success btn-sm d-flex align-items-center justify-content-center create-btn" style="width: 42px;height: 42px;" href="{{route('leads.create')}}"
                                        title="Create New">
                                        <i class="fas fa-plus-circle"></i>
                                        <!-- <span class="d-none d-md-inline">Create</span> -->
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="collapse " id="filterByDate">
                        <div class="card card-body mb-0 p-2 rounded-top-0 box-shadow-btm">
                            <div class="search-filter">
                                @include('leads.partials.search-lead-form')
                            </div>
                        </div>
                    </div>
                    <div class="filteredTable mt-4" style="display:none">
                        <i class="fas fa-filter f-icon"></i>
                        <span class="filtered"></span>
                        <sup class="btn" onclick="closeInfoSearch()">
                            <i class="fas fa-times-circle text-danger"></i>
                        </sup>
                    </div>
                    @if(!$all_account_list_permission)
                        <div  id="backButton" class="backButton mt-4 text-primary" onclick="backBtnClickSubAccount()" style="display:none">
                            <i class="fas fa-arrow-left f-icon"></i>
                            <span class="backtext">Back</span>
                        </div>
                    @endif
                </div>

                <div class="px-3 pt-1">
                    <div class="table-wrapper">
                        <div class="top-scrollbar-container">
                            <div class="top-scrollbar-spacer"></div>
                        </div>
                        <div class="table-container pb-2">
                            <table class="order-column compact hover searchHighlight" id="leads_datatable">
                                <thead class="text-nowrap" style="font-size: 0.93rem;">
                                    <tr>

                                        <th id="serial_no"></th>
                                        <th></th>
                                        <th style="min-width: 100px;">Type <span class="arrow"></span></th>
                                        <th style="min-width: 250px;">Name <span class="arrow"></span></th>
                                        <th>Year Built <span class="arrow"></span></th>
                                        <th style="min-width: 200px;">Address <span class="arrow"></span></th>
                                        <th style="min-width: 150px;">City <span class="arrow"></span></th>
                                        <th>State <span class="arrow"></span></th>
                                        <th>Zip <span class="arrow"></span></th>
                                        <th style="min-width: 100px;">County <span class="arrow"></span></th>
                                        <th>Unit Count <span class="arrow"></span></th>
                                        <!-- <th style="width: 96px;">Property Insurance Renewal Date</th> -->
                                        <th>PI Renewal Month <span class="arrow"></span></th>
                                        <th>Latitudes <span class="arrow"></span></th>
                                        <th>Longitudes <span class="arrow"></span></th>
                                        <th>Contacts Phone </th>
                                        <th style="min-width: 300px;">Slug <span class="arrow"></span></th>
                                        <th>Assigned Agent <span class="arrow"></span></th>
                                        <th>Registered Agent Name <span class="arrow"></span></th>
                                        <th>Registered Agent Address <span class="arrow"></span></th>
                                        <!-- <th>Imported</th>
                                        <th>Mergeable</th> -->
                                        <th>
                                            <div class="d-flex align-items-center justify-content-center">Action
                                                @include('leads.partials.action-settings')</div>
                                                <span class="arrow"></span>
                                        </th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    </div>
    </div>
    @include('partials.delete-modal')
    @include('leads.partials.save-campaign-modal')
    @include('leads.partials.save-filter-modal')
    @include('leads.partials.lead-saved-filter-sidebar')
    @include('leads.partials.lead-map-search-modal')
    @include('leads.partials.agent-list-modal')
</section>
<!-- /.content -->
@endsection
@push('styles')
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
@if(!isset($leadId))
{{$leadId = ''}}
@endif
<script src="{{ asset('js/custom-helper.js') }}"></script>
<script>
var base_url = "{{url('/')}}";
var google_map_api_key = '{{$google_map_api_key}}';
var map;
var markers = [];
var infowindow = new google.maps.InfoWindow();
var location_leads_id = [];
var selected_markers = [];
var marker = null;
var imagered = base_url + '/images/red.png';
var imagegreen = base_url + '/images/green.png';

/****  Document Ready ****/
const all_account_permission = @json($all_account_list_permission);
jQuery(document).ready(function() {
    // console.log("hi");
    var localCustomSearchVal = localStorage.getItem('DataTables_leads_datatable_/leads');
    if (localCustomSearchVal) {
        let parsedLocalLeadData = JSON.parse(localCustomSearchVal);
        var customSearchKey = JSON.parse(localCustomSearchVal).search.search;
        if (customSearchKey) {
            $('#customSearchBox').siblings('i.fas.fa-search.position-absolute').remove();
            $('#customSearchBox').val(customSearchKey);
        }
        parsedLocalLeadData.length = 10;

        // Reset the search field on page show (back/forward navigation)
        window.addEventListener('pageshow', function(event) {
            // $('body').find('#customSearchBox').val('');
            $('body').find('#customPageLength').val('10');
        });

        localStorage.setItem('DataTables_leads_datatable_/leads', JSON.stringify(parsedLocalLeadData));
    }

    sessionStorage.setItem('map_search', '0');
    var selected_filter_id = sessionStorage.getItem('selected_filter_id');
    var selected_filter_name = sessionStorage.getItem('selected_filter_name');
    $('input#selected_filter_id').val(selected_filter_id);
    $('input#selected_filter_name').val(selected_filter_name);
    if (selected_filter_id) {
        $('#btn_save_as_filter').removeClass('d-none');
        $('.filter_id_' + selected_filter_id + ' button.apply').addClass('d-none');
    } else {
        $('#btn_save_as_filter').addClass('d-none');
    }
    if ($('#lead-saved-filter-sidebar .lead-saved-filters .filter.filter_id_' + selected_filter_id).length) {
        $('#lead-saved-filter-sidebar .lead-saved-filters .filter.filter_id_' + selected_filter_id).addClass(
            'applied');
    }

    $('#lead_business_names_search').autocomplete({
        source: function(request, response) {
            $.ajax({
                url: "/leads/autocomplete-search?type=condo",
                contentType: 'application/json',
                dataType: 'json',
                data: {
                    term: request.term
                },
                success: function(data) {
                    var rows = autocompleteJSONParseCode(data.result);
                    response(rows);
                },
            });
        },
        select: function(event, ui) {
            $('#lead_business_names_search').val(ui.item.label);
            $('#lead_business_name_search_id').val(ui.item.value);
            return false;
        },
        minLength: 3,
        autoFocus: true,
    });

    function autocompleteJSONParseCode(data) {
        var rows = new Array();
        var rowData = null;
        for (var i = 0, dataLength = data.length; i < dataLength; i++) {
            rowData = data[i];
            rows[i] = {
                value: rowData.id,
                label: rowData.name
            };
        }
        return rows;
    }
    //if campaign
    // get the params from link
    var params = new URLSearchParams(location.search);
    //get the campaign param

    //get campaign param and set session
    var campaignParam = params.get('campaign');
    if (!isEmpty(campaignParam)) {
        //set campaign
        sessionStorage.setItem("campaign", campaignParam);
        set_filtered_section('filteredTable', 'text-info', 'Filtered by Campaign');
        //delete searched filters
        sessionStorage.removeItem("filters");
        $('#filterByDate').collapse('hide');
    } else {
        sessionStorage.removeItem("campaign", campaignParam);
    }
    var campaignSession = sessionStorage.getItem("campaign"); //set campaign session
    //if  filters but no campaign
    var searchFilters = sessionStorage.getItem('filters');
    // console.log(searchFilters);

    if (isEmpty(campaignParam) && !isEmpty(searchFilters) && all_account_permission) {
        repop_filters();
        $('#filterByDate').collapse('show');
        set_filtered_section('filteredTable', 'text-success', 'Filtered by Search Filters');
    }
    draw_table();
    $('#leads_datatable tbody').on('click', 'tr', function() {
        // remove selected row on click and refresh page
        if ($(this).hasClass('selected')) {
            $(this).removeClass('selected');
            remove_params('id');
        }
    });

    checkSunBizSessionDataApplyFilter();

});

function backBtnClickSubAccount() {
    window.location.href = '{{ route("registeragent.index") }}';
}

function checkSunBizSessionDataApplyFilter() {
    const sunbizName = sessionStorage.getItem("sunbiz_registered_name");
    const sunbizAddress = sessionStorage.getItem("sunbiz_registered_address");

    console.log(sunbizName,sunbizAddress);

    if (!sunbizName && !sunbizAddress) return;

    // Simulate filter UI interaction
    document.getElementById("actionbtn")?.click();
    document.getElementById("filtersec")?.click();
    document.getElementById("filter_your_search")?.click();

    // Determine which value to apply
    let valueToUse;
    let selectionToUse;
    if(sunbizName){
        valueToUse = sunbizName;
        selectionToUse = "sunbiz_registered_name";
    }
    else{
        valueToUse = sunbizAddress;
        selectionToUse = "sunbiz_registered_address";
    }

    const nameInput = document.getElementById("s_name_1");
    const likeInput = document.getElementById("and_or_1");
    const valueInput = document.getElementById("name_1");

    if (nameInput) nameInput.value = selectionToUse;
    if (valueInput) valueInput.value = valueToUse;
    if (likeInput) likeInput.value = "like";

    // Trigger the filter submit
    document.getElementById("btnFiterSubmitSearch")?.click();
}

/**** Draw dataTable Ajax ****/
function draw_table() {
    let localCustomSearchVal = localStorage.getItem('DataTables_leads_datatable_/leads');
    var leadId = '{{$leadId}}';
    // ajax setup for table ajax
    jQuery.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
        }
    });
    // stateSave- when there are no filters
    var table = jQuery('#leads_datatable').DataTable({
        // dom: 'lBfrtip',
        processing: true,
        oLanguage: {
            sProcessing: `{!! trim(preg_replace('/\s+/', ' ', view('partials.datatable_loader')->render())) !!}`
        },
        serverSide: true,
        responsive: true,
        autoWidth: false,
        searchHighlight: true,
        stateSave: !isEmpty(sessionStorage.getItem("filters")) || !isEmpty(sessionStorage.getItem("campaign")) ?
            false : true,
        pageLength: 25,
        ajax: {
            url: "{{ url('leads/leads-custom') }}",
            type: 'POST',
            data: function(d) {
                if (!isEmpty(sessionStorage.getItem("campaign"))) {
                    d.campaign = sessionStorage.getItem("campaign");
                }
                d.dialing_filters_clicked = sessionStorage.getItem("dialing_filters_clicked");
                if (!isEmpty(sessionStorage.getItem("filters")) && all_account_permission) {
                    d.searchFields = JSON.parse(sessionStorage.getItem("filters"));
                }
                if (location_leads_id.length) {
                    d.location_leads_id = JSON.stringify(location_leads_id);
                    d.location_leads_id_search = true;
                }
            },
            dataSrc: function (json) {
                // console.log("Response:", json);

                //  CASE 1: When backend sends custom error
                if (json.status === false) {
                    toastr.error(json.message || "Something went wrong while fetching data.");

                    // hide loader manually
                    jQuery('#leads_datatable').DataTable().processing(false);

                    // clear the table gracefully
                    jQuery('#leads_datatable').DataTable().clear().draw();

                    // return empty dataset so DataTable doesn’t break
                    return [];
                }

                // CASE 2: Standard DataTables response format
                if (json.data && Array.isArray(json.data)) {
                    return json.data;
                }

                // CASE 3: Unexpected response
                toastr.error("Unexpected response from server.");
                jQuery('#leads_datatable').DataTable().processing(false);
                jQuery('#leads_datatable').DataTable().clear().draw();
                return [];
            },
            error: function (xhr, error, thrown) {
                toastr.error("Server error: Unable to load leads data.");
                jQuery('#leads_datatable').DataTable().processing(false);
                jQuery('#leads_datatable').DataTable().clear().draw();
            },
        },

        rowCallback: function(row, data) {
            // if the lead id from param is the same with the row id, select it
            if (data.id == leadId) {
                $(row).addClass('selected');
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
                render: function(data, type, row, meta) {
                    return '<input type="checkbox" class="select-row" value="' + row.id + '">';
                }
            },
            {
                data: 'id',
                name: 'id',
                'visible': false
            },

            {
                data: 'type',
                name: 'type'
            },
            {
                data: 'name',
                name: 'leads.name',
                render: function (data, type, row) {

                    let encryptedId = btoa(row.id);  // base64 encode

                    return `
                        <span id="businessName_${row.id}">
                            <a href="/leads/edit/${encryptedId}">${data}</a>
                        </span>`;
                }
            },
            {
                data: 'creation_date',
                name: 'creation_date'
            },
            {
                data: 'address1',
                name: 'address1'
            },
            {
                data: 'city',
                name: 'city'
            },
            {
                data: 'state',
                name: 'state'
            },
            {
                data: 'zip',
                name: 'zip'
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
                data: 'renewal_month',
                name: 'renewal_month',
                // orderable: false,
                // searchable: false
            },
            {
                data: 'latitude',
                name: 'latitude'
            },
            {
                data: 'longitude',
                name: 'longitude'
            },
            {
                data: 'contacts',
                name: 'contacts.c_phone',
                orderable: false,
                searchable: false
            },
            {
                data: 'lead_slug',
                name: 'lead_slug'
            },
            {
                data: 'owned_agent_name',
                name: 'owned_agent_name',
                orderable: false,
                searchable: false
            },
            {
                data: 'sunbiz_registered_name',
                name: 'sunbiz_registered_name'
            },
            {
                data: 'sunbiz_registered_address',
                name: 'sunbiz_registered_address'
            },

            // {
            //     data: function(row, data, dataIndex) {
            //         return row.is_added_by_bot == 1 ? 'Yes' : 'No';
            //     },
            //     name: 'is_added_by_bot',
            //     orderable: false,
            //     searchable: false
            // },
            // {
            //     data: function(row) {
            //         return row.merge_status == 1 ? 'Yes' : 'No';
            //     },
            //     name: 'merge_status',
            //     orderable: false,
            //     searchable: false
            // },
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
        initComplete: function() {
            // After the table is initialized, set the visibility of columns based on sessionStorage
            $('.form-check-input').each(function() {
                let columnValue = $(this).val();
                let isChecked = sessionStorage.getItem(columnValue);

                if (isChecked === 'true') {
                    $(this).prop('checked', true);
                    let columnIndex = table.column(columnValue + ':name').index();
                    table.column(columnIndex).visible(true);
                } else if (isChecked === 'false') {
                    $(this).prop('checked', false);
                    let columnIndex = table.column(columnValue + ':name').index();
                    table.column(columnIndex).visible(false);
                }
            });
        }

    });




    jQuery('#leads_datatable').on('draw.dt', function() {
        const topScrollbarContainer = document.querySelector('.top-scrollbar-container');
        const topScrollbarSpacer = document.querySelector('.top-scrollbar-spacer');
        const tableContentContainer = document.querySelector('.table-container');
        const dataTable = document.querySelector('.dataTable');
        const tableWidth = dataTable.scrollWidth;
        topScrollbarSpacer.style.width = tableWidth + 'px';
        topScrollbarContainer.addEventListener( 'scroll', function() {
            tableContentContainer.scrollLeft = this.scrollLeft;
        });
        tableContentContainer.addEventListener( 'scroll', function() {
            topScrollbarContainer.scrollLeft = this.scrollLeft;
        });
    });





    // Add select all checkbox to table header
    var $thead = jQuery('#leads_datatable thead #serial_no');
    $thead.prepend('<input type="checkbox" class="select-all">');

    // Select all checkboxes 
    jQuery('#leads_datatable').on('change', '.select-all', function() {
        var checked = this.checked;
        jQuery('.select-row').prop('checked', checked);
        // Log selected checkboxes
        if (checked) {
            var selectedValues = jQuery('.select-row:checked').map(function() {
                return this.value;
            }).get();

        } else {

        }
    });

    // Handle individual row selections
    jQuery('#leads_datatable').on('change', '.select-row', function() {
        var $checkboxes = jQuery('.select-row');
        jQuery('.select-all').prop('checked', $checkboxes.length === $checkboxes.filter(':checked').length);
        // Log selected checkboxes
        var selectedValues = jQuery('.select-row:checked').map(function() {
            return this.value;
        }).get();

    });

    $('#bulk_lead_remove_2').on('click', function() {
        jQuery('#bulk_lead_remove_2').prop('disabled', true);
        jQuery('#leads_datatable_processing').show();
        var selectedValues = $('.select-row:checked').map(function() {
            return this.value;
        }).get();

        if (selectedValues.length > 0) {
            // Open the modal
            $('#deleteModal').modal('show');
        } else {
            toastr.error('Please check at least one checkbox to continue');
            jQuery('#bulk_lead_remove_2').prop('disabled', false);
            jQuery('#leads_datatable_processing').hide();
            return false;
        }
    });
    $('#deleteModal').on('hide.bs.modal', function() {
        // Clear the selected values when modal is closed
        $('#deleteModal').removeData('selectedValues');
        jQuery('.select-all, .select-row').prop('checked', false);
        jQuery('#bulk_lead_remove_2').prop('disabled', false);
        jQuery('#leads_datatable_processing').hide();
    });
    $('#confirm').on('click', function() {
        var selectedValues = $('.select-row:checked').map(function() {
            return this.value;
        }).get();

        if (selectedValues.length > 0) {
            console.log("Selected values:", selectedValues);
            // function to delete bulk ajax
            deleteSelectedRecords(selectedValues);
        }
        // Close the modal
        $('#deleteModal').modal('hide');
    });

    // // Button click event to submit selected checkbox values via AJAX
    // jQuery('#bulk_lead_remove').on('click', function() {
    //     jQuery('#bulk_lead_remove').prop('disabled', true);
    //     jQuery('#leads_datatable_processing').show();
    //     var selectedValues = jQuery('.select-row:checked').map(function() {
    //         return this.value;
    //     }).get();
    //     console.log("Selected values:", selectedValues);
    //     if (selectedValues.length <= 0) {
    //         toastr.error('Please check at least one checkbox to continue');
    //         jQuery('#bulk_lead_remove').prop('disabled', false);
    //         jQuery('#leads_datatable_processing').hide();
    //         return false;
    //     }

    // // Perform AJAX post request
    // jQuery.ajax({
    //     url: '/leads/delete-leads',
    //     type: 'POST',
    //     data: {
    //         selectedValues: selectedValues
    //     },
    //     success: function(response) {
    //         if (response.leadsCount) {
    //             toastr.success(response.message);
    //         } else {
    //             toastr.error(response.message);
    //         }
    //         jQuery('#leads_datatable').DataTable().draw(true);
    //         jQuery('.select-all').prop('checked', false);
    //     },
    //     error: function(xhr, status, error) {
    //         toastr.error("Something went wrong.Please contact administrator.");
    //     },
    //     complete: function() {
    //         // Re-enable the button and hide loader after AJAX request completes
    //         jQuery('#bulk_lead_remove').prop('disabled', false);
    //         jQuery('#leads_datatable_processing').hide();
    //     }
    // });
    // });

    // bulk-delete selected record function
    function deleteSelectedRecords(selectedValues) {
        // Perform AJAX post request
        jQuery.ajax({
            url: '/leads/delete-leads',
            type: 'POST',
            data: {
                selectedValues: selectedValues
            },
            success: function(response) {
                if (response.leadsCount) {
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message);
                }
                jQuery('#leads_datatable').DataTable().draw(true);
                jQuery('.select-all, .select-row').prop('checked', false);
            },
            error: function(xhr, status, error) {
                toastr.error("Something went wrong.Please contact administrator.");
            },
            complete: function() {
                // Re-enable the button and hide loader after AJAX request completes
                jQuery('#bulk_lead_remove_2').prop('disabled', false);
                jQuery('#leads_datatable_processing').hide();
            }
        });
    }

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
            // $(event.target).blur(); // to remove cursiour from search field.

            $(event.target).siblings('i.fas.fa-search.position-absolute')
                .remove(); // remove search icon and the append
            $(event.target).after('<i class="fas fa-search position-absolute"></i>');
            table.search(event.target.value).draw(); // drow the table
        }
    }, 500));
}

$('.form-check-input').change(function() {
    let columnValue = $(this).val();
    let isChecked = this.checked;

    // Save the state of the checkbox in sessionStorage
    sessionStorage.setItem(columnValue, isChecked);
    let columnIndex = jQuery('#leads_datatable').DataTable().column(columnValue + ':name').index();
    if (isChecked) {
        jQuery('#leads_datatable').DataTable().column(columnIndex).visible(true);
    } else {
        jQuery('#leads_datatable').DataTable().column(columnIndex).visible(false);
    }
});



/**** Get search Filters ****/
function get_filters() {
    var i = 0; //counter
    searchFieldSelect = {};
    /*** Getting and ading the values of address and distance fields ***/
    let address_text = $('.distance_input input[name="address_text"]').val();
    let op = $('.distance_input select[name="distance_op"]').val();
    let distance = $('.distance_input input[name="distance"]').val();
    let distance_query_selection_checkbox = $('#distance_query_selection_checkbox').is(":checked");
    let lead_business_names_search = $("#lead_business_names_search").val();
    let lead_business_name_search_id = $("#lead_business_name_search_id").val();
    if ((address_text || lead_business_names_search) && op && distance) {
        searchFieldSelect[i] = [];
        searchFieldSelect[i].push({
            address_text: address_text,
            distance_op: op,
            distance: distance == '' ? 0 : parseInt(distance),
            distance_query_selection_checkbox: distance_query_selection_checkbox,
            lead_business_names_search: lead_business_names_search,
            lead_business_name_search_id: lead_business_name_search_id
        });
        // i++;
    }
    $('.or_section').each(function() {
        //empty array
        // if ($(this).find('.operator').val() && $(this).find('.select').val() && $(this).find('.input').val()) {
        // i++;
        // searchFieldSelect[i] = [];
        var input_form_feild = {};
        var input_form_feild_counter = 0;
        // search trough it's set of dropdown and input field
        $(this).find('.inputForms').each(function() {
            if ($(this).find('.operator').val() && $(this).find('.select').val()) {
                // searchFieldSelect[i] = [];
                //get input val and operator val
                input_form_feild[input_form_feild_counter] = []
                var thisInput = $(this).find('.input');
                var operator = $(this).find('.operator');
                // if the input is empty and the operator is <,<=,>,>= , set error & reset searchFieldSelect var
                if ($(thisInput).val() == "" && $(operator).val() != "like" && $(operator).val() !=
                    "not like") {
                    toastr.error('Only "=" and "!=" operators are allowed for empty search fields');
                    // searchFieldSelect = ['error'];
                    return false;
                }
                // add select value and input value to array
                input_form_feild[input_form_feild_counter] = {
                    's_op': $(this).find('.operator').val(),
                    's_name': $(this).find('.select').val(),
                    's_val': $(this).find('.input').val()
                };
                input_form_feild_counter++;
                // i++;
            }
        });
        if (Object.keys(input_form_feild).length > 0) {
            i++;
            searchFieldSelect[i] = [];
            for (const key in input_form_feild) {
                if (Object.hasOwnProperty.call(input_form_feild, key)) {
                    const element = input_form_feild[key];
                    searchFieldSelect[i].push(element);
                }
            }
        }
    });
    return searchFieldSelect;
}

/**** Filter table by filters ****/
function filter_table(dialing_status) {
    //get new selected filters
    var searchFilters = get_filters();
    // console.log(searchFilters);
    // console.log(dialing_status);
    sessionStorage.setItem("dialing_filters_clicked", 0);
    if (dialing_status) {
        sessionStorage.setItem("dialing_filters_clicked", 1);
    }
    //if search filters don't have error , continue . Else it will do nothing
    if (searchFilters != "error" && Object.keys(searchFilters).length > 0) {
        //remove all sessions and campaign param
        sessionStorage.removeItem("filters");
        sessionStorage.removeItem("campaign");
        remove_params('campaign');
        remove_filtered_section('filteredTable', '');
        //set new filters
        // var searchFilters = get_filters();
        // console.log(searchFilters);
        sessionStorage.setItem("filters", JSON.stringify(searchFilters));
        // console.log("here");
        if(all_account_permission){
            set_filtered_section('filteredTable', 'text-success', 'Filtered by Search Filters');
        }
        else{
            const sunbizName = sessionStorage.getItem("sunbiz_registered_name");
            const sunbizAddress = sessionStorage.getItem("sunbiz_registered_address");

            if(!sunbizName && !sunbizAddress){
                $("#backButton").hide();
            }
            else{
                $("#backButton").show();
            }
        }
        // jQuery('#leads_datatable').DataTable().draw(true);
        jQuery('#leads_datatable').DataTable().ajax.reload(null, false);
    } else {
        toastr.error('Please select the conditions in the filter.');
    }
}

/**** Reset the filter info div function ****/
function remove_filtered_section(elem, elem2) {
    elem2 = elem2 ? '.' + elem2 : '';
    searchFieldSelect = {};
    $('.' + elem + elem2).hide();
    $('.' + elem + elem2).attr('class', elem + ' mt-4');
    $('.' + elem + elem2).find('.filtered').text('');
}

/**** On close & reset filter ****/
function resetCloseFiltersTab(elem) {
    // hide accordion
    $('#filterByDate').collapse('hide');
    $('#btn_save_as_filter').addClass('d-none');
    if (isEmpty(sessionStorage.getItem("campaign"))) {
        let selected_filter_id = $('input#selected_filter_id').val();
        //remove session filters
        sessionStorage.removeItem("filters");
        sessionStorage.removeItem("selected_filter_id");
        sessionStorage.removeItem("selected_filter_name");
        $('.lead-saved-filters .filter.filter_id_' + selected_filter_id).removeClass('applied');
        $('.lead-saved-filters .filter.filter_id_' + selected_filter_id + ' button').removeClass('d-none');
        $('input#selected_filter_id').val(0);
        $('input#selected_filter_name').val('');

        remove_filtered_section('filteredTable', 'text-success');

        //remove extra sections
        $('#filterByDate .or-val-fields').remove();
        $('#filterByDate .andPlus').remove();

        $('#filterByDate  .default.or_section').find('select').val('name');
        changeInput($('.default .search-fields .input'));
        $('#filterByDate .default.or_section').find('.operator').val('=');
        $('#filterByDate .default.or_section').find('.input').val('');
        $(".distance_input #lead_business_names_search").val('');
        $(".distance_input #lead_business_name_search_id").val(0);
        $('.distance_input #distance_query_selection_checkbox').prop('checked', false);
        sessionStorage.setItem("dialing_filters_clicked", 0);
        jQuery('#leads_datatable').DataTable().draw(true);
    }
}

/**** Set the filter info div function ****/
function set_filtered_section(elem, color, text) {
    elem = '.' + elem;
    $(elem).addClass(color);
    $(elem).find('.filtered').text(text);
    $(elem).fadeIn();
}

// jQuery('.action-dropdown').find('.dropdown.dropright').children('.dropdown-toggle').click(function() {
//     jQuery(this).parent('.dropdown').siblings().children('.dropdown-menu').removeClass('show');
// })
// jQuery('.action-dropdown').children('.dropdown').children('.dropdown-toggle').click(function() {
//     jQuery(this).siblings('.dropdown-menu').find('.dropdown.dropright').children('.dropdown-menu')
//         .removeClass('show');
// })
/**** Click on Close Info Search ****/
function closeInfoSearch() {
    //remove all sessions and campaign param
    const sessionList = ["sunbiz_registered_name", "sunbiz_registered_address","filters","campaign","selected_filter_name","selected_filter_id"];

    sessionList.forEach(key => {
        sessionStorage.removeItem(key);
    });

    $('#lead-saved-filter-sidebar .lead-saved-filters .filter button.apply').removeClass('d-none');
    $('#lead-saved-filter-sidebar .lead-saved-filters .filter').removeClass('applied');
    remove_params('campaign');
    remove_filtered_section('filteredTable', '');
    $('#filterByDate').collapse('hide');

    //remove extra sections
    $('#filterByDate .or-val-fields').remove();
    $('#filterByDate  .andPlus').remove();

    $('#filterByDate  .default.or_section').find('select').val(null);
    changeInput($('.default .search-fields .input'));
    $('#filterByDate  .default.or_section').find('.operator').val('like');
    $('#filterByDate  .default.or_section').find('.input').val('');

    jQuery('#leads_datatable').DataTable().draw(true);
}

/**** Remove params from url function ****/
function remove_params(elem) {
    let params = new URLSearchParams(location.search);
    if ((params + location.hash)) {
        params.delete(elem);
        if (!(params + location.hash)) {
            history.replaceState(null, null, window.location.pathname)

        } else {
            history.replaceState(null, '', '?' + params + location.hash);
        }
    }
}

/**** Populate filter based on sessionStorage Filters , o window refresh ****/
function repop_filters() {
    searchFieldSelect = JSON.parse(sessionStorage.getItem("filters")); //get sections
    var new_searchFieldSelect = {};
    if (!isEmpty(searchFieldSelect)) { //if the object is not empty
        $('#filterByDate').collapse('show'); // show the filter panel
        var i = 0; //sections counter
        for (section in searchFieldSelect) {
            if (section == 0) {
                $('.distance_input input[name="address_text"]').val(searchFieldSelect[0][0].address_text);
                $('.default select[name="distance_op"]').val(searchFieldSelect[0][0].distance_op);
                $('.default input[name="distance"]').val(searchFieldSelect[0][0].distance);
                $('.distance_input #distance_query_selection_checkbox').prop('checked', searchFieldSelect[0][0]
                    .distance_query_selection_checkbox);
                $('.distance_input #lead_business_names_search').val(searchFieldSelect[0][0]
                    .lead_business_names_search);
                $('.distance_input #lead_business_name_search_id').val(searchFieldSelect[0][0]
                    .lead_business_name_search_id);
                if (searchFieldSelect[0][0].distance_query_selection_checkbox == true && searchFieldSelect[0][0]
                    .lead_business_names_search && searchFieldSelect[0][0].lead_business_name_search_id) {
                    $('input[name="address_text"]').addClass('d-none');
                    $('input[name="lead_business_names_search"]').removeClass('d-none');
                    $('#disctance_query_selection_span').html('<strong>Business name :</strong>');
                } else {
                    $('input[name="address_text"]').removeClass('d-none');
                    $('input[name="lead_business_names_search"]').addClass('d-none');
                    $('#disctance_query_selection_span').html('<strong>Address :</strong>');
                }
                for (const key in searchFieldSelect) {
                    if (searchFieldSelect.hasOwnProperty(key) && key > 0) {
                        new_searchFieldSelect[key] = [];
                        new_searchFieldSelect[key] = searchFieldSelect[key];
                    }
                }
                searchFieldSelect = new_searchFieldSelect;
            }
            break;
        }
        for (section in searchFieldSelect) {
            i++
            var section = searchFieldSelect[i];
            if (i == 1) { // if it is the first section
                for (var j = 0; j < Object.keys(section).length; j++) {
                    if (j == 0) {
                        //populate the default one with vals
                        $('.default .search-fields .select').val(section[j]['s_name']);
                        changeInput($('.search-fields .select'));
                        $('.default .search-fields .operator').val(section[j]['s_op']);
                        $('.default .search-fields .input').val(section[j]['s_val']);
                    } else if (j > 0) {
                        // populate the "or" ones with vals
                        var addedOr = add_or_condition(
                            '.default .btn_add_or'); // add sections according to object elements
                        $(addedOr).find('.select').val(section[j]['s_name']);
                        changeInput($(addedOr).find('.select')); // change the input type based on the select val
                        $(addedOr).find('.operator').val(section[j]['s_op']);
                        $(addedOr).find('.input').val(section[j]['s_val']);
                    }
                }
            } else if (i > 1) { // if it is the AND section
                var andCond = add_and_condition(); // add the AND section
                for (var j = 0; j < Object.keys(section).length; j++) {
                    if (j == 0) {
                        //populate the first one with vals
                        $(andCond).find('.select').val(section[j]['s_name']);
                        changeInput($(andCond).find('.select')); // change the input type based on the select val
                        $(andCond).find('.operator').val(section[j]['s_op']);
                        $(andCond).find('.input').val(section[j]['s_val']);
                    } else if (j > 0) {
                        var addedOr = add_or_condition($(andCond).parents('.andPlus').find(
                            '.btn_add_or')); // add the OR section
                        // populate the "or" ones with vals
                        $(addedOr).find('.select').val(section[j]['s_name']);
                        changeInput($(addedOr).find('.select')); // change the input type based on the select val
                        $(addedOr).find('.operator').val(section[j]['s_op']);
                        $(addedOr).find('.input').val(section[j]['s_val']);
                    }
                }
            }
        }
    }
}

/**** Script for Filters ****/
var timesAnd = 1;
var addedOr = '';
addedOr += '<div class=" col-12 col-md-6 inputForms or-val-fields position-relative">';
addedOr += '    <div class="form-row align-items-center inputField">';
addedOr += '        <div class="form-group col-12 col-sm mb-2">';
addedOr += '            <strong>In Column<\/strong>';
addedOr +=
    '            {!! Form::select("s_name_1", $tableHeadingName, null, array("class" => "form-control multiple select add_or_condition","onchange"=>"changeInput(this)"))!!}';
addedOr += '        <\/div>';
addedOr += '        <div class="form-group col mb-2" style="max-width:53px;">';
addedOr += '            <strong>op.<\/strong>';
addedOr +=
    '            {!! Form::select("and_or",array("like"=>" = "," not like "=>"!="),[], array("class" => "form-control multiple operator p-0 ")) !!}';
addedOr += '        <\/div> ';
addedOr += '        <div class="form-group col-12 col-sm mb-2">';
addedOr += '            <strong>For<\/strong>';
addedOr += '            <div class="inputEnter">';
addedOr +=
    '                 {!! Form::text("name", null, array("placeholder" => "Enter value","class"=> "form-control input")) !!}';
addedOr += '            <\/div>';
addedOr += '        <\/div>';
addedOr += '    ';
addedOr += '    <\/div>';
addedOr += '<\/div>'; // the new section

/**** Add the OR condition ****/
function add_or_condition(elem) {
    var elemParent = jQuery(elem).parent().find('.fieldsRow');
    // insert it at the end of button parent, and remove search-fields class
    var newAdded = jQuery(addedOr).appendTo(elemParent);
    //remove the value of the new input
    changeInput(jQuery(newAdded).find('.select'));
    // add OR word, and a close button to close the OR section if needed

    jQuery(
            '<div class="close btn text-danger p-0 position-absolute" onclick="closeFields(this)"><span class="small">Remove Column</span></div>'
        )
        .appendTo(jQuery(newAdded));
    // $(".add_or_condition option[value='distance']").remove();
    return newAdded; // return the new section added, to use it later
}

/**** Add the AND condition ****/
function add_and_condition() {
    timesAnd++
    // var clonedAddedOr = jQuery(".default.or_section .search-fields ").clone(); //clone the default fields
    var button = jQuery(".default.or_section .btn_add_or").clone(); // clone the add OR button
    var containerAdd = jQuery('<div class="or_section andPlus or_' + timesAnd + '"></div>').appendTo(
        '.fields-container'); // add the or_section container
    var fieldsDiv = jQuery('<div class="row flex-wrap align-items-end fieldsRow"></div>').appendTo(
        containerAdd); // insert the row div in or_section container
    var newAndcond = jQuery(addedOr).appendTo(fieldsDiv); // insert the cloned fields in row div
    jQuery(button).appendTo(containerAdd); // insert the cloned button to or_section
    //remove the value of the new input
    //   .val('');
    changeInput(jQuery(addedOr).find('.select'));
    // add the AND title and close button
    jQuery(
            `<div class="dropdown-divider mt-4 mb-0"></div><div class="d-flex align-items-center justify-content-end mb-2"> <small class="close p-0 btn text-danger andClose p-2 border" onclick="closeFields(this)">Remove Section</small></div>`
        )
        .prependTo(containerAdd);
    return newAndcond;
}

/**** Close the sections ****/
function closeFields(element) {
    if (jQuery(element).hasClass('andClose')) {
        jQuery(element).parents('.or_section').remove();
    } else {
        jQuery(element).parents('.or-val-fields').remove();
    }
    // $(".add_or_condition option[value='distance']").remove();
}

/**** Change input on column selection ****/
function changeInput(elem) {
    var switchType;
    // inputs to select or write value in order to search
    // console.log(elem);
    //text
    var inputText =
        `{!! Form::text("text", null, array("placeholder" => "Is Empty","class" => "form-control input","id"=>"name_1")) !!}`;
    //date
    var selectDate =
        `{!! Form::date("date", null, array("placeholder" => "Is Empty","class" => "form-control input")) !!}`;
    //number
    var inputNumber =
        `{!! Form::number("number", null, array("placeholder" => "Is Empty","class" => "form-control input")) !!}`;
    //specials Leads
    var selectBussType =
        ` {!! Form::select("type",array("null"=>"Is Empty","Condo"=>"Condo","HOA"=>"HOA","Commercial"=>"Commercial","Co-Op"=>"Co-Op"),[""], array("class" => "form-control multiple input")) !!}`;
    var selectBussState =
        `{!! Form::select("state",$leadsStates,[], array("class" => "form-control multiple USstates input")) !!}`;
    var selectLeadSource =
        `{!! Form::select("lead_source",$leadSource,[], array("class" => "form-control multiple input")) !!}`;
    var selectBussCounty =
        `{!! Form::select("county",$leadsCounties,[""], array("class" => "form-control  multiple input")) !!}`;
    var selectBussMonths =
        `{!! Form::select("renewal_month",$leadsRenMonths,[""], array("class" => "form-control  multiple input")) !!}`;
    var selectInsPropCareer =
        `{!! Form::select("ins_prop_carrier",$leadsinsurrance,[""], array("class" => "form-control  multiple input")) !!}`;
    //specials Contacts
    var selectCtTitle =
        `{!! Form::select("c_title",$contactsTitle,[], array("class" => "form-control multiple USstates input")) !!}`;
    var selectCtState =
        `{!! Form::select("c_state",$leadsStates,[], array("class" => "form-control multiple USstates input")) !!}`;
    var selectCtSCounty =
        `{!! Form::select("c_county",$leadsCounties,[], array("class" => "form-control multiple USstates input")) !!}`;
    var textCtPhone =
        `{!! Form::text("c_phone", null, array("placeholder" => "Is Empty","class" => "form-control input")) !!}`;
    var addedByScrap =
        `{!! Form::select("added_by_scrap_apis",[0,1,2],[], array("class" => "form-control input")) !!}`;

    const selectAgentSelection = `{!! Form::select("pipeline_agent_id",$agent_users,[], array("class" => "form-control multiple input")) !!}`;

    // get php columns var
    var numberColumns = @json($columnsType['number']); // get integer columns
    var dateColumns = @json($columnsType['date']); // get integer columns
    var optionNumbers =
        '<option value="like"> = </option> <option value="not like"> != </option><option value="<">&lt;</option><option value="<=">&lt;=</option><option value=">"> &gt; </option><option value=">="> &gt;= </option'; // operators for number/date fields
    var elemVal = jQuery(elem).val(); //selected value
    // the input container
    var inputContainer = jQuery(elem).parents('.inputField').find('.inputEnter');
    //change operators depending on what was selected (number / dates or else)
    if (dateColumns.includes(elemVal) || elemVal == 'campaign_date') { // if it is date, input type="date"
        switchType = selectDate;
        jQuery(elem).parents('.inputField').find('.operator').html(optionNumbers);
    } else if (numberColumns.includes(elemVal)) { //if it is number, input type="number"
        switchType = inputNumber;
        optionNumbers =
            '<option value="like"> = </option><option value="<">&lt;</option><option value="<=">&lt;=</option><option value=">"> &gt; </option><option value=">="> &gt;= </option'; // operators for number/date fields
        jQuery(elem).parents('.inputField').find('.operator').html(optionNumbers);
    } else {
        jQuery(elem).parents('.inputField').find('.operator').html(
            '<option value="like"> = </option> <option value="not like"> != </option>'
        ); // else, the operator is just "like"

        switch (elemVal) {
            case 'type':
                switchType = selectBussType;
                break;

            case 'state':
                switchType = selectBussState;
                break;
            case 'lead_source':
                switchType = selectLeadSource;
                break;

            case 'renewal_month':
                switchType = selectBussMonths;
                break;
            case 'GL_ren_month':
                switchType = selectBussMonths;
                break;
            case 'CI_ren_month':
                switchType = selectBussMonths;
                break;
            case 'DO_ren_month':
                switchType = selectBussMonths;
                break;
            case 'U_ren_month':
                switchType = selectBussMonths;
                break;
            case 'WC_ren_month':
                switchType = selectBussMonths;
                break;
            case 'F_ren_month':
                switchType = selectBussMonths;
                break;
            case 'dic_ren_month':
                switchType = selectBussMonths;
                break;
            case 'xw_ren_month':
                switchType = selectBussMonths;
                break;
            case 'eb_ren_month':
                switchType = selectBussMonths;
                break;
            case 'ca_ren_month':
                switchType = selectBussMonths;
                break;
            case 'm_ren_month':
                switchType = selectBussMonths;
                break;

            case 'c_state':
                switchType = selectCtState;
                break;

            case 'c_phone':
                switchType = textCtPhone;
                break;
            case 'added_by_scrap_apis':
                switchType = addedByScrap;
                break;
            case 'pipeline_agent_id':
                switchType = selectAgentSelection;
                break;
            default:
                switchType = inputText;
        }
    }
    //     change the input type
    var addedInput = jQuery(elem).parents('.inputField').find('.inputEnter').html(switchType);
    //    format phonenumber
    $('.input[name="c_phone"]').each(function() {
        $(this).on('keyup', function(evt) {
            var phoneNumber = $(this);
            var charCode = (evt.which) ? evt.which : evt.keyCode;
            var fomratted = $(this).val(phoneFormat($(phoneNumber).val()));
        });
    });
    remove_blanks();
}

/**** Format Filters Contact Phone number ****/
function phoneFormat(input) {
    // Strip all characters from the input except digits
    input = input.replace(/\D/g, '');
    // // Trim the remaining input to ten characters, to preserve phone number format
    // input = input.substring(0,10);

    // // Based upon the length of the string, we add formatting as necessary
    // var size = input.length;
    // if(size == 0){
    //         input = input;
    // }else if(size < 4){
    //         input = input;
    // }else if(size < 7){
    //         input = input.substring(0,3)+'-'+input.substring(3,6);
    // }else{
    //         input = input.substring(0,3)+'-'+input.substring(3,6)+'-'+input.substring(6,10);
    // }
    return input;
}

/**** Remove blank spaces at the begginging and the end of string, in filters input text ****/
function remove_blanks() {
    $('#filterByDate input[name="text"]').each(function() {
        $(this).on('focusout', function() {
            var val = $(this).val();
            var dd = $(this).val(val.replace(/(^\s+)|(\s+$)/, ""));
        });
    })
}

/**** Check if  empty obj ****/
function isEmpty(obj) {
    for (var prop in obj) {
        if (obj.hasOwnProperty(prop)) {
            return false;
        }
    }

    return true;

}

/**** Create  Campaign ****/
function save_campaign(elem) {
    if ($('#saveCampaign .campName').val() != "") {
        // if the campaign name is not empty, create it
        // $('#saveCampaign-button .spinner-border').removeClass('d-none');
        // $('#saveCampaign .waitInfo').removeClass('d-none');
        var campaign = "";
        var searchFieldSelect = "";
        var location_leads_id_search = null;
        if (!isEmpty(sessionStorage.getItem("campaign"))) {
            campaign = sessionStorage.getItem("campaign");
        }
        if (!isEmpty(sessionStorage.getItem("filters"))) {
            searchFieldSelect = JSON.parse(sessionStorage.getItem("filters"));
        }
        if (location_leads_id.length) {
            location_leads_id = JSON.stringify(location_leads_id);
            location_leads_id_search = true;
        }
        $.ajax({
            type: 'POST', //THIS NEEDS TO BE GET
            url: "{{ url('/leads/save-campaign') }}",
            //dataType: 'json',
            data: {
                searchFields1: searchFieldSelect, // send the search options
                campaign: campaign,
                name: $('.campName').val(), // send campaign name
                location_leads_id: location_leads_id,
                location_leads_id_search: location_leads_id_search
            },
            success: function(data, status, xhr) {
                // console.log(data);
                if(data.status == true){
                    $('#close_saveCampaign').click();
                    /**** Export Table leads ****/
                    toastr.success(data.message);
                }
                else{
                    toastr.error(data.message);
                }
                
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#close_saveCampaign').click(); //show modal
                $('#saveCampaign-button .spinner-border').addClass('d-none'); // close spinner
                $('#saveCampaign .waitInfo').addClass('d-none');
                toastr.error(errorThrown);
            }
        });
    } else {
        //if it is, toastr
        $('#close_saveCampaign').click(); // show modal
        $('#saveCampaign-button .spinner-border').addClass('d-none'); // close spinner
        $('#saveCampaign .waitInfo').addClass('d-none');
        toastr.error('Campaign Name is required');
    }
}

/**** Saved filter modal open close ****/
var toggle_saved_button = true;

function openSavedFiltersNav() {
    if (toggle_saved_button) {
        toggle_saved_button = false;
        document.getElementById("lead-saved-filter-sidebar").classList.add('show');
    } else {
        toggle_saved_button = true;
        document.getElementById("lead-saved-filter-sidebar").classList.remove('show');
    }
}

function closeSavedFiltersNav() {
    toggle_saved_button = true;
    document.getElementById("lead-saved-filter-sidebar").classList.remove('show');
}

/**** Open address text box ****/
$('.distance_input select[name="address_type"]').on('change', function(event) {
    if (event.target.value == 'others') {
        $('.distance_input .address_text').show();
        $('.distance_input #distance_select').removeClass('col-md-6');
        $('.distance_input #distance_select').addClass('col-md-8');
        $('.distance_input #distance_select div.address_select').removeClass('col-12');
        $('.distance_input #distance_select div.address_select').addClass('col-4');
        $('.distance_input #distance_text').removeClass('col-md-6');
        $('.distance_input #distance_text').addClass('col-md-4');
    } else {
        $('.distance_input .address_text').hide();
        $('.distance_input #distance_select').removeClass('col-md-8');
        $('.distance_input #distance_select').addClass('col-md-6');
        $('.distance_input #distance_select div.address_select').removeClass('col-4');
        $('.distance_input #distance_select div.address_select').addClass('col-12');
        $('.distance_input #distance_text').removeClass('col-md-4');
        $('.distance_input #distance_text').addClass('col-md-6');
    }
});

/**** Open save filter modal ****/
function openSaveFilterModal(event, type, id) {
    var searchFilters = get_filters();
    if (Object.keys(searchFilters).length > 0) {
        $('#save-filter').modal('show');
        $('#filter_save_type').val(type);
        $('input[name="save_filter_name"').val($('input#selected_filter_name').val());
    } else {
        toastr.error('Please select the conditions in the filter.');
    }
}

/**** Save filter ****/
function saveFilter() {
    if (!$('input[name="save_filter_name"').val()) {
        toastr.error('Please add proper filter name');
        return false;
    }
    let save_filter_name = $('input[name="save_filter_name"').val();
    let save_filter_id = $('input#selected_filter_id').val();
    let filter_save_type = $('input#filter_save_type').val();
    let distance_query_selection_checkbox = $('#distance_query_selection_checkbox').is(":checked");
    let lead_business_names_search = $("#lead_business_names_search").val();
    let lead_business_name_search_id = $("#lead_business_name_search_id").val();
    save_filter_id = filter_save_type == 'save_new' ? save_filter_id : 0;
    $('#save-filter').modal('hide');
    var filters = get_filters();
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
        }
    });
    $.ajax({
        type: 'POST',
        url: "{{ url('/leads/filters')}}",
        data: {
            save_filter_name: save_filter_name,
            filters: JSON.stringify(filters),
            save_filter_id: save_filter_id,
            distance_query_selection_checkbox: distance_query_selection_checkbox,
            lead_business_names_search: lead_business_names_search,
            lead_business_name_search_id: lead_business_name_search_id
        },
        success: function(response) {
            if (response.status) {
                sessionStorage.setItem("selected_filter_name", save_filter_name);
                sessionStorage.setItem("selected_filter_id", response.id);
                if (!save_filter_id) {
                    var filter_html = '<div class="filter d-flex align-items-center m-1 filter_id_' +
                        response.id + ' w-100">';
                    filter_html += '<div class="title d-flex"><label>' + save_filter_name +
                        '</label></div>';
                    filter_html +=
                        '<button class="btn btn-success btn-sm mr-2 apply" type="button">Apply</button>';
                    filter_html +=
                        '<button class="btn btn-danger btn-sm closebtn mr-1" type="button" onclick="deletSavedFilter(' +
                        response.id + ')">';
                    filter_html += '<i class="fas fa-trash"></i>';
                    filter_html += '</button>';
                    filter_html += '</div>';
                    $('#lead-saved-filter-sidebar .lead-saved-filters').append(filter_html);
                    $('#lead-saved-filter-sidebar .lead-saved-filters .no-filter-found').addClass('d-none');
                    $('#lead-saved-filter-sidebar .lead-saved-filters .filter.filter_id_' + response.id)
                        .addClass('applied');
                    if (save_filter_id)
                        $('#lead-saved-filter-sidebar .lead-saved-filters .filter.filter_id_' +
                            save_filter_id).removeClass('applied');

                    $('input#selected_filter_id').val(response.id);
                    $('input#selected_filter_name').val(save_filter_name);
                } else {
                    $('input#selected_filter_id').val(response.id);
                    $('input#selected_filter_name').val(save_filter_name);
                    $('#lead-saved-filter-sidebar .lead-saved-filters .filter.filter_id_' + save_filter_id +
                        ' .title label').html(save_filter_name);
                }
                $('#lead-saved-filter-sidebar .lead-saved-filters .filter.filter_id_' + response.id +
                    ' button.apply').addClass('d-none');
                $('#btnFiterSubmitSearch').click();
                toastr.success(response.message);
            } else {
                toastr.error(response.message);
            }
        },
        error: function(response) {
            toastr.error(response.message);
        }
    });
}

/**** ****/
function applySavedFilterConfirm(id, name, conditions) {
    $.confirm({
        title: 'Apply Filter?',
        content: 'Are you sure You want to apply the filter?',
        type: 'white',
        buttons: {
            ok: {
                text: "APPLY",
                btnClass: 'btn btn-success',
                keys: ['enter'],
                action: function() {
                    applySavedFilter(id, name, conditions);
                }
            },
            cancel: function() {
                console.log('the user clicked cancel');
            }
        }
    });
}

function applySavedFilter(id, name, conditions) {
    toggle_saved_button = true;
    document.getElementById("lead-saved-filter-sidebar").classList.remove('show');
    resetCloseFiltersTab();
    $('input#selected_filter_name').val(name);
    $('input#selected_filter_id').val(id);
    sessionStorage.setItem("selected_filter_name", name);
    sessionStorage.setItem("selected_filter_id", id);
    sessionStorage.removeItem("filters");
    remove_filtered_section('filteredTable', 'text-success');
    sessionStorage.setItem("filters", conditions);
    set_filtered_section('filteredTable', 'text-success', 'Filtered by Search Filters');
    jQuery('#leads_datatable').DataTable().draw(true);
    repop_filters();
    $('#btn_save_as_filter').removeClass('d-none');
    $('#lead-saved-filter-sidebar .lead-saved-filters .filter.filter_id_' + id).addClass('applied');
    $('#lead-saved-filter-sidebar .lead-saved-filters .filter.filter_id_' + id + ' button.apply').addClass('d-none');
}

/**** Delete filter ****/
function deleteSavedFilterConfirm(id, name) {
    $.confirm({
        title: 'Delete Record?',
        content: 'Are you sure You want to delete the record?',
        type: 'white',
        buttons: {
            ok: {
                text: "DELETE",
                btnClass: 'btn btn-danger',
                keys: ['enter'],
                action: function() {
                    deleteSavedFilter(id, name);
                }
            },
            cancel: function() {
                console.log('the user clicked cancel');
            }
        }
    });
}

function deleteSavedFilter(id, name) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
        }
    });
    $.ajax({
        type: 'POST',
        url: "{{ url('/leads/filters-delete')}}",
        data: {
            id: id
        },
        success: function(response) {
            if (response.status) {
                toastr.success(response.message);
                let selected_filter_id = $('input[name="selected_filter_id"').val();
                let selected_filter_name = $('input[name="selected_filter_name"').val();
                if (id == selected_filter_id && name == selected_filter_name) {
                    $('input[name="selected_filter_id"').val('');
                    $('input[name="selected_filter_name"').val('');
                }
                resetCloseFiltersTab();
                $('#lead-saved-filter-sidebar .lead-saved-filters .filter.filter_id_' + id).remove();
                if ($('#lead-saved-filter-sidebar .lead-saved-filters .filter').length === 0)
                    $('#lead-saved-filter-sidebar .lead-saved-filters .no-filter-found').removeClass(
                        'd-none');
            } else {
                toastr.error(response.message);
            }
        },
        error: function(response) {
            toastr.error(response.message);
        }
    });
}

/**** Distance query selection ****/
$('#distance_query_selection_checkbox').on('change', function(event) {
    if (event.target.checked) {
        $('input[name="address_text"]').addClass('d-none');
        $('input[name="lead_business_names_search"]').removeClass('d-none');
        $('#disctance_query_selection_span').html('<strong>Business name :</strong>');
    } else {
        $('input[name="address_text"]').removeClass('d-none');
        $('input[name="lead_business_names_search"]').addClass('d-none');
        $('#disctance_query_selection_span').html('<strong>Address :</strong>');
    }
});

/**** Google Map Search ****/
initialize();

function initialize() {
    map = new google.maps.Map(document.getElementById('map-canvas'), {
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        zoom: 10
    });
    map.enableKeyDragZoom();
    var defaultBounds = new google.maps.LatLngBounds(new google.maps.LatLng(40.0467292, -95.7866702), new google.maps
        .LatLng(38.0214085, -75.0836282));
    map.fitBounds(defaultBounds);
}

function SelectMarkers(Bounds) {
    for (var i = 0; i < markers.length; i++) {
        marker = markers[i];
        if (Bounds.contains(marker.getPosition()) == true) {
            marker.setIcon(imagegreen);
            location_leads_id.push(marker.get('id'));
            selected_markers.push(marker.get('id'));
        }
        marker = null;
    }
    if (location_leads_id.length) {
        $('#map_marker_confirm_button').removeAttr('disabled');
    } else {
        $('#map_marker_confirm_button').attr('disabled', 'disabled');
    }
}

$('#mapSearchId').click(function(e) {
    sessionStorage.setItem('map_search', '0');
});

$('#clientSearch').click(function(e) {
    sessionStorage.setItem('map_search', '1');
});

$('#mapsearch').on('shown.bs.modal', function(e) {
    var sessionMapSearch = sessionStorage.getItem('map_search');
    location_leads_id = [];
    console.log('sessionMapSearch -> ', sessionMapSearch);
    $.ajax({
        type: 'POST',
        url: "/leads/all-leads-location",
        data: {
            is_client: sessionMapSearch
        }
    }).done(function(data) {
        var infowindow = new google.maps.InfoWindow();
        var map_marker, i;
        var locations = data.data;
        for (i = 0; i < locations.length; i++) {
            map_marker = new google.maps.Marker({
                position: new google.maps.LatLng(locations[i].latitude, locations[i].longitude),
                map: map,
                icon: imagered,
                title: locations.name,
                id: locations[i].id
            });
            markers.push(map_marker);

            google.maps.event.addListener(map_marker, 'rightclick', (function(map_marker, i) {
                return function() {
                    infowindow.setContent(
                        `<div class="badge bg-primary text-wrap">${locations[i].name}</div>`
                    );
                    infowindow.open(map, map_marker);
                }
            })(map_marker, i));

            google.maps.event.addListener(map_marker, 'click', function() {
                if (selected_markers.indexOf(this.get('id')) >= 0) {
                    location_leads_id.pop(this.get('id'));
                    selected_markers.pop(this.get('id'));
                    this.setIcon(imagered);
                } else {
                    location_leads_id.push(this.get('id'));
                    selected_markers.push(this.get('id'));
                    this.setIcon(imagegreen);
                }
                if (location_leads_id.length) {
                    $('#map_marker_confirm_button').removeAttr('disabled');
                } else {
                    $('#map_marker_confirm_button').attr('disabled', 'disabled');
                }
            });
        }
    });
})

$("#mapsearch").on("hidden.bs.modal", function() {
    selected_markers = [];
    if (markers) {
        for (i in markers) {
            markers[i].setMap(null);
        }
        markers.length = 0;
    }
    $('select[name="map_marker_distance_op"]').removeAttr('disabled');
    $('input[name="map_marker_distance"]').removeAttr('disabled');
    $('input[name="map_marker_distance"]').val(null);
    $('select[name="map_marker_distance_op"]').val('=');
});

$(document).on("click", '#map_marker_confirm_button', function(event) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
        }
    });
    $('#leads_datatable').DataTable().draw(true);
    $('#mapsearch').modal('hide');
});

$(document).on("click", '#delete_map_marker', function(event) {
    markersArray[event.target.value].setMap(null);
});

$(document).on("keyup", 'input[name="map_marker_distance"]', function(event) {
    if (event.target.value && $('select[name="map_marker_distance_op"]').val()) {
        $('#map-canvas-overlay').hide();
    } else {
        $('#map-canvas-overlay').show();
    }
});

$(document).on("change", 'input[name="map_marker_distance_op"]', function(event) {
    if (event.target.value && $('select[name="map_marker_distance"]').val()) {
        $('#map-canvas-overlay').hide();
    } else {
        $('#map-canvas-overlay').show();
    }
});

/**** Agent List ****/
$(document).on("keyup", 'input[name="agent_list_name"]', function(event) {
    if (event.target.value.trim() && $('#agent_list_dialing').val() &&  $('#agent_list_dialing').val().length > 0) {
        $('#save_agent_list_button').removeAttr('disabled');
    } else {
        $('#save_agent_list_button').attr('disabled', 'disabled');
    }
});

$(document).on("change", '#agent_list_dialing', function(event) {
    if (event.target.value && event.target.value.length > 0 && $('input[name="agent_list_name"]').val().trim()) {
        $('#save_agent_list_button').removeAttr('disabled');
    } else {
        $('#save_agent_list_button').attr('disabled', 'disabled');
    }
});

$(document).on('click', '#save_agent_list_button', function() {
    var agent_list_name = $('input[name="agent_list_name"]').val();
    var agent_list = $('#agent_list_dialing').val();
    if (!agent_list_name || !agent_list) {
        toastr.error('Please add proper agent details');
        return false;
    }
    var searchFieldSelect = "";
    var location_leads_id_search = null;
    if (!isEmpty(sessionStorage.getItem("filters"))) {
        searchFieldSelect = JSON.parse(sessionStorage.getItem("filters"));
    } else if (location_leads_id.length) {
        location_leads_id = JSON.stringify(location_leads_id);
        location_leads_id_search = true;
    }
    $('#save_agent_list_button').attr('disabled', 'disabled');

    $.ajax({
        type: 'POST',
        url: "{{ url('/dialings/create') }}",
        data: {
            search_fields: searchFieldSelect, // send the search options
            agent_list_name: agent_list_name, // send agent list name
            agent_id: agent_list, // send agent selected id
            location_leads_id: location_leads_id,
            location_leads_id_search: location_leads_id_search
        },
        success: function(data, status, xhr) {
            // console.log(data);
            if (data.status) {
                toastr.success(data.message);
            } else {
                toastr.error(data.message);
            }
            $('#save_agent_list_button').removeAttr('disabled');
            $('#close_saveagentlist').click();
        },
        error: function(jqXHR, textStatus, errorThrown) {
            $('#save_agent_list_button').removeAttr('disabled');
            $('#close_saveagentlist').click();
            toastr.error(errorThrown);
        }
    });
});
</script>
<!-- <script src="https://code.jquery.com/jquery-migrate-3.0.0.min.js"></script> -->
<!-- <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script> -->
<!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.0/jquery-ui.min.css"
    integrity="sha512-LDB28UFxGU7qq5q67S1iJbTIU33WtOJ61AVuiOnM6aTNlOLvP+sZORIHqbS9G+H40R3Pn2wERaAeJrXg+/nu6g=="
    crossorigin="anonymous" referrerpolicy="no-referrer" /> -->
<script src="https://cdn.datatables.net/plug-ins/1.11.3/features/searchHighlight/dataTables.searchHighlight.min.js">
</script>
<script src="//bartaz.github.io/sandbox.js/jquery.highlight.js"></script>
<!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css"> -->
<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js"></script> -->
@endpush