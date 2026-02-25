@extends('layouts.app')
@section('pagetitle', 'Leads Contact Report')

@push('breadcrumbs')
<li class="breadcrumb-item">
    <a href="{{ route('actions.index') }}">Leads Contact Report</a>
</li>
@endpush

@section('content')
<section class="content">
    <div class="container-fluid">
        <!-- Filter Toggle -->
        <div class="d-flex flex-wrap mb-3">
            <button class="btn btn-primary mb-3" type="button" data-bs-toggle="collapse"
                data-bs-target="#filterActions">
                <i class="fas fa-filter"></i>
                <span class="d-none d-md-inline"> Filter Contact Report</span>
            </button>
        </div>
        <!-- Filter Form -->
        <div class="collapse" id="filterActions">
            <div class="card card-body">
                <div class="search-filter">
                    @include('actions.partials.search-action-form')
                </div>
            </div>
        </div>
        <!-- Active Filters -->
        <div class="filteredTable mt-4" style="display:none">
            <i class="fas fa-filter f-icon"></i>
            <span class="filtered"></span>
            <sup class="btn" onclick="closeInfoSearch()">
                <i class="fas fa-times-circle text-danger"></i>
            </sup>
        </div>
        <!-- DataTable -->
        <div class="table-container pt-4 pb-4">
            <table class="row-border order-column compact hover searchHighlight"
                id="action_report_datatable">
                <thead style="font-size: 0.93rem;">
                    <tr>
                        <th>No</th>
                        <th>ID</th>
                        <th style="width:99px">Contact Name</th>
                        <th style="min-width:192px;">Business Name</th>
                        <th style="width:99px;">Contact Phone</th>
                        <th>Contact Email</th>
                        <th style="width:150px;">Date of Contact</th>
                        <th>Campaign</th>
                        <th>Current Client</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
    @include('partials.delete-modal')
    @include('leads.partials.save-campaign-modal')
</section>
@endsection
@push('scripts')
<script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js" defer></script>
<script src="https://cdn.datatables.net/plug-ins/1.11.3/features/searchHighlight/dataTables.searchHighlight.min.js"></script>
<script src="//bartaz.github.io/sandbox.js/jquery.highlight.js"></script>
<script>
	const tableId = "#action_report_datatable";

    $(document).ready(() => {
        sessionStorage.setItem("actionFilters", "");
        initDataTable();
    });
    function initDataTable() {
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

        $(tableId).DataTable({
            dom: "Bfrtip",
            buttons: [{ extend: "csv", className: "exportreport", text: "Export Report" }],
            processing: true,
            serverSide: true,
            responsive: true,
            autoWidth: false,
            searchHighlight: true,
            stateSave: true,
            paging: false,
            oLanguage: { sProcessing: `{!! trim(preg_replace('/\s+/', ' ', view('partials.datatable_loader')->render())) !!}` },
            ajax: {
                url: "{{ url('/actions/datatable') }}",
                type: "POST",
                data: d => {
                    let filters = sessionStorage.getItem("actionFilters");
                    if (filters) d.filters = JSON.parse(filters);
                }
            },
            columns: getTableColumns(),
            order: [[6, "desc"]]
        });
    }
    function getTableColumns() {
        return [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'id', name: 'id', visible: false },
            { data: 'contact_name', name: 'contact_name' },
            { data: 'leads', name: 'leads.name' },
            { data: 'contacts', name: 'contacts.c_phone' },
            { data: 'email', name: 'contacts.c_email' },
            { data: 'contact_date', name: 'contact_date' },
            { data: 'campaigns', name: 'campaigns.name' },
            { data: 'c_is_client', name: 'c_is_client' }
        ];
    }
    function filter_table() {
        const filters = {
            startDate: $("#start_date").val(),
            endDate: $("#end_date").val(),
        };
        sessionStorage.setItem("actionFilters", JSON.stringify(filters));
        $(tableId).DataTable().draw(true);
    }
    function resetCloseFiltersTab() {
        $('#filterActions').collapse('hide');
        $('#filterActions .controls input').val('');
        sessionStorage.setItem("actionFilters", "");
        $(tableId).DataTable().draw(true);
    }
</script>
@endpush
