@extends('layouts.app')
@section('pagetitle', 'Scrap Api Platform Management')
@push('breadcrumbs')

<li class="breadcrumb-item active">Scrap Api Platform Management</li>
@endpush
@section('content')
<link href="/css/jquery.dataTables.min.css" rel="stylesheet">
<script type="text/javascript" src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
<script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js" defer></script>
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

<!-- Main content -->
<section class="content">

    <div class="container-fluid">
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
                                    <div class="dropdown-menu rounded-top-0 p-0 m-0 text-nowrap"
                                        aria-labelledby="actionbtn">
                                        <a class="btn btn-sm rounded-0 btn-block btn-info" href="{{route('platform_setting.scrap_contact_view')}}">
                                            <i class="fas fa-database"></i>
                                            <span class="d-none d-md-inline">Scrap Contacts</span>
                                        </a>
                                    </div>
                                </div>
                                <div class="custom_search_page d-flex align-items-center justify-content-between ml-2">
                                    <div id="custom_length_menu">
                                        <label class="d-flex align-items-center justify-content-between mb-0">Show
                                            <select id="customPageLength"
                                                class="form-control form-control-sm mx-1 px-0 bg-transparent"
                                                aria-controls="scrap_api_platforms_datatable">
                                                <option value="10" selected>10</option>
                                                <option value="25">25</option>
                                                <option value="50">50</option>
                                                <option value="100">100</option>
                                            </select>
                                            entries
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex flex-wrap pb-2">
                                <div id="scrap_api_platforms_datatable_filter" class="dataTables_filter search-sec mb-0">
                                    <label
                                        class="d-flex align-items-center justify-content-end mb-0 position-relative"><input
                                            type="search" id="customSearchBox" placeholder="Search for Entries"
                                            aria-controls="scrap_api_platforms_datatable" class="form-control" val="">
                                        <i class="fas fa-search position-absolute"></i>
                                    </label>
                                </div>
                                <div class="ml-1 d-flex">
                                    <!-- <a class="btn btn-success btn-sm create-btn" href="{{route('leads.create')}}"
                                        title="Create New">
                                        <i class="fas fa-plus-circle"></i> -->
                                        <!-- <span class="d-none d-md-inline">Create</span> -->
                                    <!-- </a> -->
                                    <a title="Create New" class="btn btn-success btn-sm d-flex align-items-center justify-content-center" href="{{route('platform_setting.create')}}" style="width: 42px;height: 42px;">
                                        <i class="fas fa-plus-circle"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- <div class="row">
                    <div class="col-lg-12 margin-tb d-flex flex-wrap justify-content-between">
                        <div class="d-flex flex-wrap">
                            <div class="mr-1">
                                <a class="btn btn-secondary btn-sm" href="{{route('platform_setting.create')}}">
                                    <i class="fas fa-plus-circle"></i>
                                    <span class="d-none d-md-inline">Create</span>
                                </a>
                            </div>
                            <div class="mr-1">
                                <a class="btn btn-info btn-sm" href="{{route('platform_setting.scrap_contact_view')}}">
                                    <i class="fas fa-database"></i>
                                    <span class="d-none d-md-inline">Scrap Contacts</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div> -->
                <div class="px-3 pt-1">
                    <div class="table-container pb-2">
                        <table class="row-border order-column compact hover searchHighlight scrap_api_platforms_datatable border-top" id="scrap_api_platforms_datatable">
                            <thead class="text-nowrap" style="font-size: 0.93rem;">
                                <tr>
                                    <th id="serial_no">No <span class="arrow"></span></th>
                                    <!-- <th></th> -->
                                    <th>Platform Name <span class="arrow"></span></th>
                                    <th>Priority Order <span class="arrow"></span></th>
                                    <th>Platform Type <span class="arrow"></span></th>
                                    <th>Status <span class="arrow"></span></th>
                                    <th>Created At <span class="arrow"></span></th>
                                    <th>Auth Expiry Date <span class="arrow"></span></th>
                                    <th style="text-align: center;">Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- /.content -->
@include('partials.delete-modal')
@endsection
@push('styles')
@endpush'
@push('scripts')

<script>
    $(document).ready(function() {
        // // ajax setup for table ajax

        draw_table();

        function draw_table() {

            var table = jQuery('#scrap_api_platforms_datatable').DataTable({
                // dom: 'lBfrtip',
                processing: true,
                oLanguage: {
                    sProcessing: `{!! trim(preg_replace('/\s+/', ' ', view('partials.datatable_loader')->render())) !!}`
                },
                serverSide: true,
                responsive: true,
                autoWidth: true,
                searchHighlight: true,
                // stateSave: !isEmpty(sessionStorage.getItem("filters")) || !isEmpty(sessionStorage.getItem("campaign")) ? false : true,
                // pageLength: 25,
                ajax: {
                    url: "{{ url('/platform_setting/getApiPlatforms') }}",
                    type: 'GET',

                },
                rowCallback: function(row, data) {
                    // if the lead id from param is the same with the row id, select it
                    // console.log('data', data)

                },
                columns: [
                    //set table columns
                    // {
                    //     data: 'DT_RowIndex',
                    //     name: 'DT_RowIndex',
                    //     "targets": [0],
                    //     "searchable": false,
                    //     "orderable": false,
                    //     render: function(data, type, row, meta) {
                    //         return '<input type="checkbox" class="select-row" value="' + row.id + '">';
                    //     }
                    // },
                    {
                        data: 'id',
                        name: 'id',
                        'visible': true
                    },

                    {
                        data: 'platform_name',
                        name: 'platform_name'
                    },
                    {
                        data: 'priority_order',
                        name: 'priority_order'
                    },
                    {
                        data: 'platform_type',
                        name: 'platform_type'
                    },
                    // {
                    //     data: 'status',
                    //     name: 'status'
                    // },
                    {
                        data: function(row, data, dataIndex) {
                            return row.status == 1 ? 'Active' : 'Inactive';
                        },
                        name: 'status'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                    {
                        data: 'auth_expiry_date',
                        name: 'auth_expiry_date'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ],
                order: [
                    [0, 'desc']
                ],
                dom: 'rt<"bottom"ip><"clear">',

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

        }
    });
</script>
<!-- <script src="https://code.jquery.com/jquery-migrate-3.0.0.min.js"></script> -->
<!-- <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script> -->
<!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.0/jquery-ui.min.css" integrity="sha512-LDB28UFxGU7qq5q67S1iJbTIU33WtOJ61AVuiOnM6aTNlOLvP+sZORIHqbS9G+H40R3Pn2wERaAeJrXg+/nu6g==" crossorigin="anonymous" referrerpolicy="no-referrer" /> -->
<script src="https://cdn.datatables.net/plug-ins/1.11.3/features/searchHighlight/dataTables.searchHighlight.min.js"></script>
<script src="//bartaz.github.io/sandbox.js/jquery.highlight.js"></script>
<!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css"> -->
<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js"></script> -->
@endpush