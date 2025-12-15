@extends('layouts.app')
@section('pagetitle', 'Agent Activity Reports')
@push('breadcrumbs')
<li class="breadcrumb-item active">Agent Activity</li>
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
                            <div class="d-flex align-items-center flex-wrap action-dropdown pb-2">
                                <div class="dropdown">
                                    <button class="btn btn-info btn-sm dropdown-toggle" type="button" id="actionbtn"
                                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        Actions
                                    </button>
                                    <div class="dropdown-menu p-0 m-0 text-nowrap" aria-labelledby="actionbtn">
                                        <button class="btn btn-sm rounded-0 btn-block btn-info" data-toggle="collapse"
                                            data-target="#filters">
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
                                    @can('report-activity-form')
                                        <a class="btn btn-success btn-sm create-btn d-inline-flex align-items-center justify-content-center" href="{{route('agentreport.activityIndex')}}" title="Create New" style="width: 42px;height: 42px;">
                                            <i class="fas fa-plus-circle"></i>
                                        </a>
                                    @endcan
                                    @can('report-activity-download')
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
                                    @include('activityreport.partials.report_section_comman_filter')
                                </div>
                                <div class="dropdown-divider mb-2 mt-2"></div>
                                <div class="form-row mt-3 justify-content-end">
                                    <div class="col-auto">
                                        <button id="resetFilters" class="btn btn-secondary btn-sm">
                                            Reset
                                        </button>
                                        <button id="applyFilters" class="btn btn-primary btn-sm">
                                            Apply
                                        </button>
                                    </div>
                                </div>
                                
                            </div>
                        </div>
                    </div>
                </div>
                @include('activityreport.partials.currentViewType')
                <div class="px-3 pt-2">
                    <div class="loader-area table-container pb-2">
                        <div class="table-responsive shadow-sm rounded bg-white" id="logWiseView">
                            <table class="table compact table-hover table-sm mb-0" id="activity_report_table">
                                <thead class="text-nowrap text-sm">
                                    <tr class="align-middle text-nowrap">
                                        <th>Date <span class="arrow"></span></th>
                                        <th>Agent <span class="arrow"></span></th>
                                        <th>Appointments <span class="arrow"></span></th>
                                        <th>Policies <span class="arrow"></span></th>
                                        <th>Expiring Policy Premium <span class="arrow"></span></th>
                                        <th>Community Name <span class="arrow"></span></th>
                                        <th>AOR Break Down <span class="arrow"></span></th>
                                        <th>Details</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>

                        <div class="table-responsive shadow-sm bg-white displayNoneClass" id="consolidatedView">
                            <table class="table compact table-hover table-sm mb-0" id="activity_report_consolidated_table">
                                <thead class="text-sm text-nowrap">
                                    <tr class="align-middle">
                                        <th>Agent <span class="arrow"></span></th>
                                        <th>Total Agent Activity Submissions <span class="arrow"></span></th>
                                        <th>Total Appointments <span class="arrow"></span></th>
                                        <th>Total Policies <span class="arrow"></span></th>
                                        <th>Total Expiring Policy Premium <span class="arrow"></span></th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
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
<!-- <script src="{{ asset('js/custom-pagination.js') }}"></script> -->
<script src="{{ asset('js/custom-helper.js') }}" defer></script>
<script>
    // DataTable instances
    let logWiseTable = null;
    let consolidatedTable = null;

    let table;
    setInSessionStorage("backpage_url", window.location.href);

    // Ensure CSRF works for all AJAX requests
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    // Common function to get filter data
    async function getLeadFormData() {
        return {
            agent: $('#filter_agent').val(),
            date_range: $('#filter_date_range').val(),
            from_date: $('#custom_from').val(),
            to_date: $('#custom_to').val(),
            custom_days: $('#custom_days').val(),
            view_type: currentViewType
        };
    }

    // Initialize DataTable dynamically based on current view
    async function initDataTable() {
        // Destroy any previous instance (safe reset)
        if ($.fn.DataTable.isDataTable('#activity_report_table')) {
            $('#activity_report_table').DataTable().destroy();
        }
        if ($.fn.DataTable.isDataTable('#activity_report_consolidated_table')) {
            $('#activity_report_consolidated_table').DataTable().destroy();
        }

        const ajaxOptions = {
            url: "{{ route('agentreport.activityList') }}",
            type: 'POST',
            data: function (d) {
                const formData = {
                    agent: $('#filter_agent').val(),
                    date_range: $('#filter_date_range').val(),
                    from_date: $('#custom_from').val(),
                    to_date: $('#custom_to').val(),
                    custom_days: $('#custom_days').val(),
                    view_type: currentViewType
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
            // searching: true,
            // ordering: true,
            destroy: true,
            pageLength: 10,
            lengthMenu: [10, 20, 50, 100],
            dom: 'rt<"bottom"ip><"clear">',
        };

        const tableName = (currentViewType == 1)
        ? "activity_report_table"
        : "activity_report_consolidated_table";


        await loadSearchArea(currentViewType,tableName);

        if (currentViewType == 1) {
            logWiseTable = $('#'+tableName).DataTable({
                ...baseOptions,
                ajax: ajaxOptions,
                columns: [
                    {
                        data: 'date',
                        name: 'date',
                        render: function (data, type, row) {
                            return formatDateMDY(data);
                        }
                    },
                    { data: 'agent_name', name: 'agent.name' },
                    { data: 'appointments', name: 'appointments' },
                    { data: 'policies', name: 'policies' },
                    {
                        data: 'expiry_policies_premium',
                        name: 'expiry_policies_premium',
                        render: function (data, type, row) {
                            return assign_value_numberformat(data);
                        }
                    },
                    { data: 'community_name', name: 'community_name' },
                    { data: 'aor_breakdown', name: 'aor_breakdown' },
                    { data: 'details', orderable: false, searchable: false }
                ],
                order: [
                    [0, 'desc']
                ]
            });

            table = logWiseTable;
        } else {
            consolidatedTable = $('#'+tableName).DataTable({
                ...baseOptions,
                ajax: ajaxOptions,
                columns: [
                    { data: 'agent_name', name: 'agent_name' },
                    { data: 'total_lead', name: 'total_lead' },
                    { data: 'total_appointments', name: 'total_appointments' },
                    { data: 'total_policies', name: 'total_policies' },
                    {
                        data: 'total_expiry_policies_premium',
                        name: 'total_expiry_policies_premium',
                        render: function (data, type, row) {
                            return assign_value_numberformat(data);
                        }
                    },
                ]
            });

            table = consolidatedTable;
        }
    }

    // Reset filters cleanly
    function resetForm() {
        $('#filter_agent').val('');
        $('#filter_date_range').val('last_7_days');
        $('#custom_days').val('');
        $('#custom_from').val('');
        $('#custom_to').val('');
        $('body').find('#customSearchBox').val('');

        $('.custom-date-range, .custom-days-input').addClass('d-none');

        reloadActiveTable();
    }

    // Handle showing/hiding extra date inputs
    $('#filter_date_range').on('change', function () {
        const value = $(this).val();
        $('.custom-date-range, .custom-days-input').addClass('d-none');
        if (value === 'custom') $('.custom-date-range').removeClass('d-none');
        if (value === 'custom_days') $('.custom-days-input').removeClass('d-none');
    });

    // Reload whichever table is active
    async function reloadActiveTable() {
        if (currentViewType == 1 && logWiseTable) {
            logWiseTable.ajax.reload(null, false);
        } else if (consolidatedTable) {
            consolidatedTable.ajax.reload(null, false);
        } else {
            initDataTable();
        }
    }

    $(document).ready(function () {
        // Initialize once
        initDataTable();

        window.addEventListener('pageshow', function(event) {
            $('body').find('#customSearchBox').val('');
            $('body').find('#customPageLength').val('10');
        });

        // Apply Filters
        $('#applyFilters').on('click', async function (e) {
            e.preventDefault();
            const valid = await checkFilterValue();
            if (!valid) return;
            await reloadActiveTable();
            releaseDownloadButton();
            $('body').find('#customSearchBox').val('');
        });

        // Reset Filters
        $('#resetFilters').on('click', function () {
            resetForm();
        });

        // Download
        $('.download-btn').on('click', async function () {
            const dataRequest = await getLeadFormData();
            const downLoadUrl = "{{ route('agentreport.activityListDownload') }}";
            dataDownLoad(downLoadUrl, dataRequest);
        });
    });
</script>

@endpush