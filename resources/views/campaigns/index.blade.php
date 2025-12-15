@extends('layouts.app')
@section('pagetitle', 'Mailing Lists')
@push('breadcrumbs')
<li class="breadcrumb-item">Mailing Lists</li>

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
                            <div class="d-flex flex-wrap pb-2">
                                <div class="custom_search_page d-flex align-items-center justify-content-between ml-1">
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
                <div class="px-3 pt-1">
                    <div class="table-container pb-2">
                        <table class="order-column compact hover searchHighlight mt-3" id="campaignsTable">
                            <thead class="text-nowrap">
                                <tr>

                                    <th style="width:30px;" scope="col">No</th>
                                    <th></th>
                                    <th style=" min-width: 192px;">Name <span class="arrow"></span></th>
                                    <th style="width: 180px">Status <span class="arrow"></span></th>
                                    <th style="width: 117px">Export Date <span class="arrow"></span></th>
                                    <th style="width: 117px">Campaign Date <span class="arrow"></span></th>
                                    <th style="width: 50px">Lead number <span class="arrow"></span></th>
                                    <th style="width: 100px">User actions <span class="arrow"></span></th>
                                    {{-- <th style="width: 100px">Actions Number <span class="arrow"></span></th> --}}
                                    <th style="min-width: 81px;width: 81px; text-align: center;">Action</th>
                                </tr>
                            </thead>

                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
    {{-- container fluid --}}
    @include('partials.delete-modal')
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
<script async>
jQuery(document).ready(function() {

    var localCustomSearchVal = localStorage.getItem('DataTables_campaignsTable_/marketing-campaigns');
        if(localCustomSearchVal){
            let parshedLocalLeadData = JSON.parse(localCustomSearchVal);
            var customSearchKey = JSON.parse(localCustomSearchVal).search.search;
            if(customSearchKey){
                $('#customSearchBox').siblings('i.fas.fa-search.position-absolute').remove();
                $('#customSearchBox').val(customSearchKey);
            }
            parshedLocalLeadData.length = 10;
            localStorage.setItem('DataTables_campaignsTable_/marketing-campaigns', JSON.stringify(parshedLocalLeadData));
        }


    // Query('#campaignsTable').DataTable().draw(true);
    // ajax setup for table ajax
    jQuery.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
        }

    });

    //load the table
    var t = jQuery('#campaignsTable').DataTable({
        // dom: 'lBfrtip',
        processing: true,
        oLanguage: {
            sProcessing: `{!! trim(preg_replace('/\s+/', ' ', view('partials.datatable_loader')->render())) !!}`
        },
        serverSide: true,
        responsive: true,
        autoWidth: false,
        searchHighlight: true,
        stateSave: true,
        ajax: {
            url: "{{ url('marketing-campaigns-table') }}",
            type: 'POST',
            data: function(d) {



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
            },
            {
                data: 'id',
                name: 'id',
                'visible': false
            },
            {
                data: 'name',
                name: 'name'
            },
            {
                data: 'status',
                name: 'status'
            },
            {
                data: 'created_at',
                name: 'created_at'
            },
            {
                data: 'campaign_date',
                name: 'campaign_date'
            },
            {
                data: 'lead_number',
                name: 'lead_number'
            },
            {
                data: 'user_actions',
                name: 'user_actions'
            },

            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false
            },
        ],
        // createdCell: function (td, cellData, rowData, row, col)  {
        //     console.log(cellData);
        //         if ( cellData == "PENDING" ) {
        //             //console.log(row);
        //         $(td).addClass( 'text-warning' );
        //         }else if(cellData['status'] == "COMPLETED" ) {
        //             // $(row).addClass( 'text-success' );
        //         }
        //     },
        columnDefs: [{
            targets: 3,
            createdCell: function(td, cellData, rowData, row, col) {
                if (cellData == "PENDING") {

                    $(td).addClass('text-info statusTd');
                    $('<i class="fas fa-circle nav-icon">&nbsp;&nbsp;</i>').prependTo(td)
                } else if (cellData == "COMPLETED") {
                    $(td).addClass('text-success statusTd');
                    $('<i class="fas fa-circle nav-icon">&nbsp;&nbsp;</i>').prependTo(td)
                }
            }
        }],
        order: [
            [4, 'desc']
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
        t.page.len(length).draw();
    });

    $('#customSearchBox').on('keyup', debounce(function(event) {
        $(event.target).siblings('i.fas.fa-search.position-absolute').remove();
        if(!event.target.value){
            $(event.target).after('<i class="fas fa-search position-absolute"></i>');
        }
        if (event.key === "Enter") {
            t.search(this.value).draw();
        } else {
            t.search(this.value).draw();
        }
    }, 500)); // 500ms debounce interval

    $('#customSearchBox').on('input', debounce(function(event) {
        if (!event.target.value) {
            console.log('cross clicked');
            let localCustomSearchVal = localStorage.getItem('DataTables_campaignsTable_/marketing-campaigns');
            let updatedLocalCustomSearchVal = JSON.parse(localCustomSearchVal);
            updatedLocalCustomSearchVal.search.search = '';
            localStorage.setItem('DataTables_campaignsTable_/marketing-campaigns', JSON.stringify(updatedLocalCustomSearchVal));
            $(event.target).blur(); // to remove cursiour from search field.
            
            $(event.target).siblings('i.fas.fa-search.position-absolute').remove(); // remove search icon and the append
            $(event.target).after('<i class="fas fa-search position-absolute"></i>');
            t.search(event.target.value).draw(); // drow the table
        }
    }, 500));



});
</script>

<script src="https://cdn.datatables.net/plug-ins/1.11.3/features/searchHighlight/dataTables.searchHighlight.min.js">
</script>
<script src="//bartaz.github.io/sandbox.js/jquery.highlight.js"></script>
@endpush