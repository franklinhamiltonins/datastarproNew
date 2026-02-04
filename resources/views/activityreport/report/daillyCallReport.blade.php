@extends('layouts.app')
@section('pagetitle', 'Daily Call Report')
@push('breadcrumbs')
<li class="breadcrumb-item active">Daily Call</li>
<li class="breadcrumb-item">Reports</li>
@endpush
@section('content')
<link href="/css/jquery.dataTables.min.css" rel="stylesheet">
<section class="content">
    <div class="container-fluid dashboard-sec">
        <div class="card">
            <div class="card-body p-0 pb-3">
                <div class="px-3 pt-3 pb-1">
                    @include('activityreport.partials.daily_call_report_field_details')
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
                                <div class="custom_search_page d-flex align-items-center justify-content-between ml-2">
                                    <div id="custom_length_menu">
                                        <label class="d-flex align-items-center justify-content-between mb-0">Show
                                            <select id="customPageLength"
                                                class="form-control form-control-sm mx-1 px-0 bg-transparent"
                                                aria-controls="lead_report_table">
                                                <option value="10">10</option>
                                                <option selected value="25">25</option>
                                                <option value="50">50</option>
                                                <option value="100">100</option>
                                            </select>
                                            entries
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="ml-1">
                                <div class="d-flex flex-wrap">
                                    <div id="lead_report_table_filter" class="dataTables_filter search-sec mb-0 mr-2">
                                        <label class="d-flex align-items-center justify-content-end mb-0 position-relative">
                                            <input type="search" id="customSearchBox" placeholder="Search for Entries"
                                            aria-controls="lead_report_table" class="form-control" val="">
                                            <i class="fas fa-search position-absolute"></i>
                                        </label>
                                    </div>
                                    @can('report-daily-call-download')
                                        <a class="btn btn-primary btn-sm download-btn d-inline-flex align-items-center justify-content-center" id="download-btn" href="javascript:void(0)" title="Download" style="width: 42px;height: 42px;">
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
                                            <i class="fas fa-filter"></i> Apply Filters
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="px-3 pt-2">
                    <div class="loader-area table-container pb-2">
                        <div class="table-responsive shadow-sm rounded bg-white">
                            <table class="table compact table-hover table-sm mb-0" id="lead_report_table">
                                <thead class="text-sm text-nowrap">
                                    <tr class="align-middle">
                                        <th> Producer Name <span class="arrow"></span></th>
                                        <th> Outbound <span class="arrow"></span></th>
                                        <th> Facebook <span class="arrow"></span></th>
                                        <th> Mailer <span class="arrow"></span></th>
                                        <th> SMS <span class="arrow"></span></th>
                                        <th> Email <span class="arrow"></span></th>
                                        <th> 611 Transfer <span class="arrow"></span></th>
                                        <th> 611 Referral <span class="arrow"></span></th>
                                        <th> Appointment <span class="arrow"></span></th>
                                        <th> Policies <span class="arrow"></span></th>
                                        <th> Expiry Premium <span class="arrow"></span></th>
                                        <th> AOR <span class="arrow"></span></th>
                                        <th> AOR Month <span class="arrow"></span></th>
                                        <th> AOR Premium <span class="arrow"></span></th>
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
<script src="{{ asset('js/custom-helper.js') }}" defer></script>
<script >
    let table;

    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    async function getLeadFormData() {
        return {
            _token: $('meta[name="csrf-token"]').attr('content'),
            agent: $('#filter_agent').val(),
            date_range: $('#filter_date_range').val(),
            from_date: $('#custom_from').val(),
            to_date: $('#custom_to').val(),
            custom_days: $('#custom_days').val(),
        };
    }

    function initDailyCallTable() {
        if ($.fn.DataTable.isDataTable('#lead_report_table')) {
            $('#lead_report_table').DataTable().destroy();
        }

        table = $('#lead_report_table').DataTable({
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
            pageLength: 25,
            // lengthMenu: [10, 20, 50, 100],

            ajax: {
                url: "{{ route('agentreport.dailycallReportList') }}",
                type: "POST",
                data: function (d) {
                    const formData = {
                        agent: $('#filter_agent').val(),
                        date_range: $('#filter_date_range').val(),
                        from_date: $('#custom_from').val(),
                        to_date: $('#custom_to').val(),
                        custom_days: $('#custom_days').val(),
                    };
                    // merge DataTables internal params (search, start, length, etc.)
                    return $.extend({}, d, formData);
                }
            },

            columns: [
                { data: "producer_name", name: "producer_name" },
                { data: "outbound_calls", name: "outbound_calls" },
                { data: "facebook", name: "facebook" },
                { data: "mailer", name: "mailer" },
                { data: "sms", name: "sms" },
                { data: "email", name: "email" },
                { data: "transfer_611", name: "transfer_611" },
                { data: "referal_611", name: "referal_611" },
                { data: "appointments", name: "appointments" },
                { data: "policies", name: "policies" },
                { 
                    data: "expiry_premium", 
                    name: "expiry_premium",
                    render: function (data) {
                        return assign_value_numberformat(data);
                    }
                },
                { data: "aor", name: "aor" },
                { data: "aor_effective_month", name: "aor_effective_month" },
                { 
                    data: "aor_premium", 
                    name: "aor_premium",
                    render: function (data) {
                        return assign_value_numberformat(data);
                    }
                },
            ],
            dom: 'rt<"bottom"ip><"clear">',
        });
    }

    async function getLeadFormData() {
        const agent = document.getElementById("filter_agent").value;
        const dateRange = document.getElementById("filter_date_range").value;
        const customFrom = document.getElementById("custom_from").value;
        const customTo = document.getElementById("custom_to").value;
        const customDays = document.getElementById("custom_days").value;

        return {
                _token: $('meta[name="csrf-token"]').attr('content'),
                agent: agent,
                date_range: dateRange,
                from_date: customFrom,
                to_date: customTo,
                custom_days: customDays
            };
    }

    function resetForm() {
        // document.getElementById("filter_agent").value = '';
        document.getElementById("filter_agent").selectedIndex = 0;
        document.getElementById("filter_date_range").value = 'last_7_days';
        document.getElementById("custom_days").value = '';
        document.getElementById("custom_from").value = '';
        document.getElementById("custom_to").value = '';

        // Hide conditional fields
        $('.custom-date-range').addClass('d-none');
        $('.custom-days-input').addClass('d-none');

        table.ajax.reload();
    }

    $('#filter_date_range').on('change', function () {
        const value = $(this).val();

        $('.custom-date-range').addClass('d-none');
        $('.custom-days-input').addClass('d-none');

        if (value === 'custom') {
            $('.custom-date-range').removeClass('d-none');
        } else if (value === 'custom_days') {
            $('.custom-days-input').removeClass('d-none');
        }
    });


    $(document).ready(function() {
        initDailyCallTable();

        window.addEventListener('pageshow', function(event) {
            $('body').find('#customSearchBox').val('');
            $('body').find('#customPageLength').val('10');
        });

        $('#applyFilters').on('click',async function () {
            const filter = await checkFilterValue();
            if(!filter){
                return;
            }
            table.ajax.reload();

            releaseDownloadButton();
        });

        $('#resetFilters').on('click', function () {
            resetForm();
        });

        $('.download-btn').on('click', async function () {
            const dataRequest = await getLeadFormData();

            const downLoadUrl =  "{{ route('agentreport.dailycallReportListDownload') }}";

            
            dataDownLoad(downLoadUrl,dataRequest);
        });
    });

</script>
@endpush