@extends('layouts.app')
@section('pagetitle', 'Registered Agent')
@push('breadcrumbs')
<li class="breadcrumb-item">Business</a></li>
<li class="breadcrumb-item active">Registered Agent</li>
@endpush

@push('styles')
{{-- <link href="/css/jquery.dataTables.min.css" rel="stylesheet"> --}}
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.16/css/dataTables.bootstrap4.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    table.dataTable span.highlight {
        background-color: #FFFF88;
        border-radius: 0.28571429rem;
    }
    table.dataTable span.column_highlight {
        background-color: #ffcc99;
        border-radius: 0.28571429rem;
    }
    .filter-grid {
        display: grid;
        /* margin-top: 1em; */
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        grid-gap: 1em;
        background-color: #ffffff;
        border-radius: 15px;
        padding: 15px;
        overflow: hidden;
    }
</style>
@endpush

@section('content')
<link href="/css/jquery.dataTables.min.css" rel="stylesheet">
<!-- <script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js" defer></script> -->

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-body p-0 pb-3">
                <div class="px-3 pt-3 pb-1">
                    <div class="row">
                        <div class="col-lg-12 margin-tb d-flex flex-wrap justify-content-between table-top-sec">
                            <div class="d-flex align-items-center flex-wrap action-dropdown">
                                <div class="dropdown">
                                    <button class="btn btn-info btn-sm dropdown-toggle" type="button" id="actionbtn"
                                        data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        Actions
                                    </button>
                                    <div class="dropdown-menu p-0 m-0 text-nowrap" aria-labelledby="actionbtn">
                                        @can('register-agent-filters')
                                        <button class="btn dropdown-item btn-sm rounded-0 btn-block btn-info" data-bs-toggle="collapse"
                                            data-bs-target="#filters">
                                            <i class="fas fa-filter"></i>
                                            <span>Filters</span>
                                        </button>
                                        @endcan
                                    </div>
                                </div>
                                <div class="custom_search_page d-flex align-items-center justify-content-between ml-2">
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
                            <div class="d-flex flex-wrap">
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
                    <div id="filters" class="collapse">
                        <div class="card card-body mb-0 p-3 rounded-top-0 shadow-sm">
                            <div class="search-filter">
                                <div class="form-row">
                                    <div class="form-group col-md-4">
                                        <label for="sunbiz_registered_name"><strong>Registered Agent Name</strong></label>
                                        <input type="text" name="sunbiz_registered_name" id="sunbiz_registered_name" class="form-control select" maxlength="50">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="sunbiz_registered_address"><strong>Registered Agent Address</strong></label>
                                        <input type="text" name="sunbiz_registered_address" id="sunbiz_registered_address" class="form-control select" maxlength="80">
                                    </div>
                                </div>
                                <div class="dropdown-divider mb-2 mt-2"></div>
                                <div class="form-row mt-3 justify-content-end">
                                    <div class="col-auto">
                                        <button type="button" id="applyFilters" class="btn btn-sm btn-primary">Apply</button>
                                        <button type="button" id="resetFilters" class="btn btn-sm btn-secondary ml-2">Reset</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="px-3 pt-1">
                    <div class="table-container pb-2">
                        <table class="order-column compact hover searchHighlight text-left" id="leads_datatable">
                            <thead class="text-nowrap" style="font-size: 0.93rem;">
                                <tr>
                                    <th>Registered Agent Name <span class="arrow"></span></th>
                                    <th>Registered Agent Address <span class="arrow"></span></th>
                                    <th>Associated Lead Count <span class="arrow"></span></th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('partials.delete-modal')
</section>
<!-- /.content -->
@endsection
@push('scripts')
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.3/js/dataTables.bootstrap4.min.js"></script>
<script>
    $(document).ready(function() {
        // Set up CSRF token for all AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        window.addEventListener('pageshow', function(event) {
            $('body').find('#customSearchBox').val('');
            $('body').find('#customPageLength').val('25');
        });

        clearSessionStorage();
    });

    function getValueIfExists(id) {
        const el = document.getElementById(id);
        return el && el.value ? el.value : undefined;
    }

    // stateSave- when there are no filters
    var $table;
    $(function() {
        $table = $('#leads_datatable').DataTable({
            paging: true,
            processing: true,
            serverSide: true,
            responsive: true,
            autoWidth: false,
            searchHighlight: true,
            pageLength: 25,
            oLanguage: {
                sProcessing: `{!! trim(preg_replace('/\s+/', ' ', view('partials.datatable_loader')->render())) !!}`
            },
            ajax: {
                url: "{{ route('registeragent.data') }}",
                data: function(d) {
                    // if (sessionStorage.getItem("search_field")) {
                    //     d.search_field = sessionStorage.getItem("search_field");
                    // }
                    // if (sessionStorage.getItem("search_field_value")) {
                    //     d.search_field_value = sessionStorage.getItem("search_field_value");
                    // }

                    const sunbiz_registered_name = getValueIfExists("sunbiz_registered_name");
                    const sunbiz_registered_address = getValueIfExists("sunbiz_registered_address");

                    d.sunbiz_registered_name = sunbiz_registered_name;
                    d.sunbiz_registered_address = sunbiz_registered_address;

                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            },
            columns: [
                {
                    data: 'sunbiz_registered_name',
                    name: 'sunbiz_registered_name',
                    render: function(data, type, row) {
                        return `<span class="set-session text-primary text-decoration-underline" data-key="sunbiz_registered_name" data-value="${data}">${data}</span>`;
                    }
                },
                {
                    data: 'sunbiz_registered_address',
                    name: 'sunbiz_registered_address',
                    render: function(data, type, row) {
                        return `<span class="set-session text-primary text-decoration-underline" data-key="sunbiz_registered_address" data-value="${data}">${data}</span>`;
                    }
                },
                { data: 'associated_lead', name: 'associated_lead',searchable: false }
            ],
            order: [[0, 'asc']],
            dom: 'rt<"bottom"ip><"clear">',
            initComplete: function() {
                // After the table is initialized, set the visibility of columns based on sessionStorage
                $('.form-check-input').each(function() {
                    let columnValue = $(this).val();
                    let isChecked = sessionStorage.getItem(columnValue);

                    if (isChecked === 'true') {
                        $(this).prop('checked', true);
                        let columnIndex = $table.column(columnValue + ':name').index();
                        $table.column(columnIndex).visible(true);
                    } else if (isChecked === 'false') {
                        $(this).prop('checked', false);
                        let columnIndex = $table.column(columnValue + ':name').index();
                        $table.column(columnIndex).visible(false);
                    }
                });
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
            $table.page.len(length).draw();
        });

        $('#customSearchBox').on('keyup', debounce(function(event) {
            $(event.target).siblings('i.fas.fa-search.position-absolute').remove();
            if (!event.target.value) {
                $(event.target).after('<i class="fas fa-search position-absolute"></i>');
            }
            if (event.key === "Enter") {
                $table.search(this.value).draw();
            } else {
                $table.search(this.value).draw();
            }
        }, 500)); // 500ms debounce interval

        $('#customSearchBox').on('input', debounce(function(event) {
            if (!event.target.value) {
                console.log('contact search cross clicked');
                $(event.target).blur(); // to remove cursor from search field

                $(event.target).siblings('i.fas.fa-search.position-absolute').remove(); // remove search icon and then append
                $(event.target).after('<i class="fas fa-search position-absolute"></i>');
                $table.search(event.target.value).draw(); // draw the table
            }
        }, 500));
            
    });
    document.getElementById('applyFilters').addEventListener('click', function () {

        $table.draw();
    });
    document.getElementById('resetFilters').addEventListener('click', function () {
        document.getElementById('sunbiz_registered_name').value = '';
        document.getElementById('sunbiz_registered_address').value = '';

        $table.draw();
    });
    // Using event delegation in case DataTables redraws rows
    $(document).on('click', '.set-session',async function () {
        await clearSessionStorage();
        const key = $(this).data('key');
        const value = $(this).data('value');
        sessionStorage.setItem(key, value);
        // console.log(`Stored: ${key} = ${value}`);

        window.location.href = '{{ route("leads.index") }}';
    });

    async function clearSessionStorage() {
        const sessionList = ["sunbiz_registered_name", "sunbiz_registered_address"];
        // console.log("ceared called");

        sessionList.forEach(key => {
            sessionStorage.removeItem(key);
        });
    }


    $('.filterfield.select').select2({
        width: '100%',
        'placeholder': $(this).data('placeholder')
    });

    $('.filterfield').on('change', (e) => {
        var $this = $(e.target),
            $colIndex = $this.attr('data-column'),
            $result,
            $val = $this.val();
        $('.filterfield').each(function() {
            var $select = $(this);
            // Check if the current select element is not the same as the one changed
            if ($select[0] !== $this[0]) {
                // Deselect all options except the first one
                $select.val(null).trigger('change.select2');
            }
        });

        if ($this.val()) {
            console.log($val, $colIndex, $result);
            $result = '^' + $this.val();
            setInSessionStorage('search_field', $colIndex);
            setInSessionStorage('search_field_value', $val);
            // $table.column($colIndex).search($result, true, false).draw();
            $table.draw();
        } else {
            setInSessionStorage('search_field', '');
            setInSessionStorage('search_field_value', '');
            // $table.column($colIndex).search('').draw();
            $table.draw();
        }

        console.log($val, $colIndex, $result);

    });
</script>
<script src="https://cdn.datatables.net/plug-ins/1.11.3/features/searchHighlight/dataTables.searchHighlight.min.js">
</script>
<script src="//bartaz.github.io/sandbox.js/jquery.highlight.js"></script>
@endpush