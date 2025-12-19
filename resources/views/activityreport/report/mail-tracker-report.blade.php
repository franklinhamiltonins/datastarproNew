@extends('layouts.app')
@section('pagetitle', 'Mailer Lead Tracker Report')
@push('breadcrumbs')
<li class="breadcrumb-item active">Mailer Lead Tracker</li>
<li class="breadcrumb-item">Reports</a></li>
@endpush
@section('content')
<link href="/css/jquery.dataTables.min.css" rel="stylesheet">
<section class="content">
    <div class="container-fluid dashboard-sec">
        <div class="card">
            <div class="card-body p-0 pb-3">
                <div class="px-3 pt-3 pb-1">
                    <div class="row">
                        <div class="col-lg-12 margin-tb d-flex flex-wrap justify-content-between table-top-sec">
                            <div class="d-flex flex-wrap action-dropdown pb-2">
                                <div class="dropdown">
                                    <button class="btn btn-info btn-sm dropdown-toggle" type="button" id="actionbtn"
                                        data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        Actions
                                    </button>
                                    <div class="dropdown-menu p-0 m-0 text-nowrap" aria-labelledby="actionbtn">
                                        <button class="btn btn-sm rounded-0 btn-block btn-info" data-bs-toggle="collapse"
                                            data-bs-target="#filters">
                                            <i class="fas fa-filter"></i>
                                            <span>Filters</span>
                                        </button>
                                    </div>
                                </div>
                                <div class="custom_search_page d-flex align-items-center justify-content-between ml-2" id="pageLengthAreaMenu">
                                    <div id="custom_length_menu" >
                                    </div>
                                </div>
                            </div>
                            <div class="ml-1">
                                <div class="d-flex flex-wrap">
                                    <div id="searchAreaMenu"> 
                                    </div>
                                    @can('report-mailer-lead-form')
                                        <a class="btn btn-success btn-sm create-btn d-inline-flex align-items-center justify-content-center" href="{{route('agentreport.mailerLeadIndex')}}" title="Create New" style="width: 42px;height: 42px;">
                                            <i class="fas fa-plus-circle"></i>
                                        </a>
                                    @endcan
                                    @can('report-mailer-lead-download')
                                        <a class="btn btn-primary btn-sm download-btn d-inline-flex align-items-center justify-content-center ml-2" id="download-btn" href="javascript:void(0)" title="Download" style="width: 42px;height: 42px;">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    @endcan
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="filters" class="collapse">
                        <div class="card card-body mb-0 p-3 rounded-top-0 shadow-sm">
                            <div class="search-filter">
                                <div class="form-row">
                                    <div class="form-group col-md-4">
                                        <strong>Lead Source</strong>
                                        <select class="form-control inputSelectionBox" id="filter_lead_source">
                                            <option value="">All</option>
                                            @foreach($leadSource as $source)
                                                <option value="{{ $source->id }}">{{ $source->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    @include('activityreport.partials.report_section_comman_filter')

                                </div>
                                <div class="dropdown-divider mb-2 mt-2"></div>
                                <div class="form-row mt-3 justify-content-end">
                                    <div class="col-auto">
                                        <button id="resetFilters" class="btn btn-secondary btn-sm">
                                            Reset
                                        </button>
                                        <button id="applyFilters" class="btn btn-primary btn-sm">
                                            <i class="fas fa-filter"></i> Apply Filters
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @include('activityreport.partials.currentViewType')

                <div class="px-3 pt-2">
                    <div class="loader-area">
                        <div id="logWiseView">
                            <div class="table-wrapper">
                                <div class="top-scrollbar-container">
                                    <div class="top-scrollbar-spacer"></div>
                                </div>
                                <div class="table-container pb-2">
                                    <table class="table compact table-hover table-sm mb-0" id="mailer_lead_tracker_report_table">
                                        <thead class="text-sm text-nowrap">
                                            <tr class="align-middle">
                                                <th>Business Name <span class="arrow"></span></th>
                                                <th>Lead Source <span class="arrow"></span></th>
                                                <th>Agent <span class="arrow"></span></th>
                                                <th>Contact FirstName <span class="arrow"></span></th>
                                                <th>Contact LastName <span class="arrow"></span></th>
                                                <th>Phone <span class="arrow"></span></th>
                                                <th>Email <span class="arrow"></span></th>
                                                <th>Status Notes <span class="arrow"></span></th>
                                                <th>Date <span class="arrow"></span></th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Data will be populated by JS -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive bg-white displayNoneClass" id="consolidatedView">
                            <table class="table compact table-hover table-sm mb-0" id="mailer_lead_consolidated_table">
                                <thead class="text-sm text-nowrap">
                                    <tr class="align-middle">
                                        <th>Agent <span class="arrow"></span></th>
                                        <th>Total Mailer Lead Submissions <span class="arrow"></span></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data will be populated by JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div id="pagination-wrapper" class="mt-3 text-center"></div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
@push('styles')
@endpush
@push('scripts')
<script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js" defer></script>
<script src="{{ asset('js/custom-pagination.js') }}"></script>
<script src="{{ asset('js/custom-helper.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// DataTable instances
let mailerLeadTable = null;
let consolidatedTable = null;
let table;
setInSessionStorage("backpage_url", window.location.href);

$.ajaxSetup({
    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
});

async function getLeadFormData() {
    return {
        lead_source: $('#filter_lead_source').val(),
        agent: $('#filter_agent').val(),
        date_range: $('#filter_date_range').val(),
        from_date: $('#custom_from').val(),
        to_date: $('#custom_to').val(),
        custom_days: $('#custom_days').val(),
        view_type: currentViewType,
    };
}

$('#filter_date_range').on('change', function () {
    const value = $(this).val();
    $('.custom-date-range, .custom-days-input').addClass('d-none');
    if (value === 'custom') $('.custom-date-range').removeClass('d-none');
    if (value === 'custom_days') $('.custom-days-input').removeClass('d-none');
});

async function initDataTable() {
    // Destroy previous instances
    if ($.fn.DataTable.isDataTable('#mailer_lead_tracker_report_table')) {
        $('#mailer_lead_tracker_report_table').DataTable().destroy();
    }
    if ($.fn.DataTable.isDataTable('#mailer_lead_consolidated_table')) {
        $('#mailer_lead_consolidated_table').DataTable().destroy();
    }

    const ajaxOptions = {
        url: "{{ route('agentreport.mailLeadTrackerList') }}",
        type: "POST",
        data: function (d) {
            const formData = {
                lead_source: $('#filter_lead_source').val(),
                agent: $('#filter_agent').val(),
                date_range: $('#filter_date_range').val(),
                from_date: $('#custom_from').val(),
                to_date: $('#custom_to').val(),
                custom_days: $('#custom_days').val(),
                view_type: currentViewType,
            };
            // merge DataTables internal params (search, start, length, etc.)
            return $.extend({}, d, formData);
        }
    };

    const baseOptions = {
        processing: true,
        oLanguage: {
            sProcessing: `{!! trim(preg_replace('/\s+/', ' ', view('partials.datatable_loader')->render())) !!}`
        },
        serverSide: true,
        responsive: true,
        autoWidth: false,
        searchHighlight: true,
        searching: true,
        ordering: true,
        destroy: true,
        pageLength: 10,
        lengthMenu: [10, 20, 50, 100],
        dom: 'rt<"bottom"ip><"clear">',
    };

    const tableName = (currentViewType == 1)
        ? "mailer_lead_tracker_report_table"
        : "mailer_lead_consolidated_table";


    await loadSearchArea(currentViewType,tableName);

    if (currentViewType == 1) {
        mailerLeadTable = $('#'+tableName).DataTable({
            ...baseOptions,
            ajax: ajaxOptions,
            columns: [
                { data: 'business', name: 'mailer_leads_tracker.business' },
                { data: 'lead_source_name', name: 'leadSource.name' },
                { data: 'agent_name', name: 'agent.name' },
                { data: 'contact_firstname', name: 'mailer_leads_tracker.contact_firstname' },
                { data: 'contact_lastname', name: 'mailer_leads_tracker.contact_lastname' },
                { data: 'phone', name: 'mailer_leads_tracker.phone' },
                { data: 'email_address', name: 'mailer_leads_tracker.email_address' },
                {
                    data: 'status_note',
                    name: 'mailer_leads_tracker.status_note',
                    render: function (data) {
                          return htmlEntries(data);
                    }
                },
                {
                    data: 'date',
                    name: 'date',
                    render: function (data) {
                        return formatDateMDY(data);
                    }
                },
                { data: 'action', orderable: false, searchable: false }
            ],
            order: [
                [8, 'desc']
            ],
        });

        table = mailerLeadTable;
    } else {
        consolidatedTable = $('#'+tableName).DataTable({
            ...baseOptions,
            ajax: ajaxOptions,
            columns: [
                { data: 'agent_name', name: 'agent_name' },
                { data: 'total_lead', name: 'total_lead' },
            ]
        });

        table = consolidatedTable;
    }
}

function resetForm() {
    $('#filter_lead_source').val('');
    $('#filter_agent').val('');
    $('#filter_date_range').val('last_7_days');
    $('#custom_days').val('');
    $('#custom_from').val('');
    $('#custom_to').val('');
    $('.custom-date-range, .custom-days-input').addClass('d-none');
    $('body').find('#customSearchBox').val('');

    if (currentViewType == 1 && mailerLeadTable) {
        mailerLeadTable.ajax.reload();
    } else if (consolidatedTable) {
        consolidatedTable.ajax.reload();
    }
}

function formatDateValue(dateStr) {
  if (!dateStr || dateStr.trim() === '') return 'N/A';
  const date = new Date(dateStr);
  if (isNaN(date)) return 'N/A';
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const day = String(date.getDate()).padStart(2, '0');
  const year = date.getFullYear();
  return `${month}/${day}/${year}`;
}

$(document).ready(function () {
    initDataTable();

    $('#applyFilters').on('click', async function (e) {
        e.preventDefault();
        const valid = await checkFilterValue();
        if (!valid) return;

        if (currentViewType == 1 && mailerLeadTable) {
            mailerLeadTable.ajax.reload();
        } else if (consolidatedTable) {
            consolidatedTable.ajax.reload();
        }

        releaseDownloadButton();

        $('body').find('#customSearchBox').val('');
    });

    $('#resetFilters').on('click', resetForm);

    $('.download-btn').on('click', async function () {
        const dataRequest = await getLeadFormData();
        const downLoadUrl = "{{ route('agentreport.mailLeadTrackerListDownload') }}";
        dataDownLoad(downLoadUrl, dataRequest);
    });

    $(document).on('click', '.deleteMailer', function (e) {
        e.preventDefault();
        const encodedId = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then(result => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/agentreport/mailerleadtracker/delete/${encodedId}`,
                    type: 'POST',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function (response) {
                        toastr.success(response.message || 'Deleted successfully!');
                        if (mailerLeadTable) mailerLeadTable.ajax.reload();
                    },
                    error: function () {
                        toastr.error('Failed to delete the item.');
                    }
                });
            }
        });
    });
});

    jQuery('#mailer_lead_tracker_report_table').on('draw.dt', function() {
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
</script>

@endpush