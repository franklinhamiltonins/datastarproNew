@extends('layouts.app')
@section('pagetitle')
    Lead Source Management
@endsection
@push('breadcrumbs')
<li class="breadcrumb-item"><a href="{{route('leadsource.index')}}">Lead Source</a></li>
<li class="breadcrumb-item active">Lead Source Management</li>
@endpush
@section('content')
<link href="/css/jquery.dataTables.min.css" rel="stylesheet">
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
<script src="/js/keydragzoom.js"></script>
<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-body p-0 pb-3">
                <div class="p-3">
                    <div class="row">
                        <div class="col-lg-12 margin-tb d-flex flexwrap-wrap justify-content-between table-top-sec">
                            <div class="d-flex align-items-center flexwrap-wrap action-dropdown">
                                <div class="dropdown">
                                    <button class="btn btn-info btn-sm dropdown-toggle" type="button" id="actionbtn"
                                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        Actions
                                    </button>
                                    <div class="dropdown-menu p-0 m-0 text-nowrap" aria-labelledby="actionbtn">
                                        {{-- @can('template-delete') --}}
                                        <a href="javascript:void(0)" id="bulk_leadsource_remove"
                                            class="bulk_leadsource_remove text-nowrap btn-block rounded-left-0 btn btn-danger btn-sm btn-sm closebtn mr-1">
                                            <i class="fas fa-trash-alt mr-1"></i>
                                            <span class="d-none d-md-inline">Remove</span>
                                        </a>
                                        {{-- @endcan --}}
                                    </div>
                                </div>
                                <div class="custom_search_page d-flex align-items-center justify-content-between ml-2">
                                    <div id="custom_length_menu">
                                        <label class="d-flex align-items-center justify-content-between mb-0">Show
                                            <select id="customPageLength"
                                                class="form-control form-control-sm mx-1 px-0 bg-transparent"
                                                aria-controls="leadsource_status_datatable">
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
                            <div class="d-flex flexwrap-wrap">
                                <div id="leadsource_status_datatable_filter" class="dataTables_filter search-sec mb-0">
                                    <label
                                        class="d-flex align-items-center justify-content-end mb-0 position-relative"><input
                                            type="search" id="customSearchBox" placeholder="Search for Entries"
                                            aria-controls="leadsource_status_datatable" class="form-control">
                                        <i class="fas fa-search position-absolute"></i>
                                    </label>
                                </div>
                                <div class="ml-1">
                                    <a title="Create Lead Source" class="btn btn-success btn-sm d-flex align-items-center justify-content-center create-btn" href="{{route('leadsource.create')}}" style="width: 42px;height: 42px;">
                                        <i class="fas fa-plus-circle"></i>
                                        <!-- <span class="d-none d-md-inline">Create</span> -->
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-3 pt-2">
                    <div class="table-container pb-2">
                        <table class="order-column compact hover searchHighlight leadsource_status_datatable" id="leadsource_status_datatable">
                            <thead class="text-nowrap" style="font-size: 0.93rem;">
                                <tr>
                                    <th id="serial_no"></th>
                                    <th></th>
                                    <th>Name <span class="arrow"></span></th>
                                    <th>
                                        <div class="d-flex align-items-center justify-content-center">
                                            Action
                                        </div>
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
    @include('partials.delete-modal')
</section>
<!-- /.content -->
@endsection
@push('styles')
@endpush
@push('scripts')

<script>
/****  Document Ready ****/
jQuery(document).ready(function() {

    draw_table();
    $('#leadsource_status_datatable tbody').on('click', 'tr', function() {
        // remove selected row on click and refresh page
        if ($(this).hasClass('selected')) {
            $(this).removeClass('selected');
            remove_params('id');
        }
    });

    // Reset the search field on page show (back/forward navigation)
    window.addEventListener('pageshow', function(event) {
        $('body').find('#customSearchBox').val('');
        $('body').find('#customPageLength').val('25');
    });

});

/**** Draw dataTable Ajax ****/
function draw_table() {

    // ajax setup for table ajax
    jQuery.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
        }
    });
    // stateSave- when there are no filters
    var table = jQuery('#leadsource_status_datatable').DataTable({
        // dom: 'lBfrtip',
        processing: true,
        oLanguage: {
            sProcessing: `{!! trim(preg_replace('/\s+/', ' ', view('partials.datatable_loader')->render())) !!}`
        },

        serverSide: true,
        responsive: true,
        autoWidth: false,
        searchHighlight: true,
        // stateSave: !isEmpty(sessionStorage.getItem("filters")) || !isEmpty(sessionStorage.getItem("campaign")) ? false : true,
        pageLength: 25,
        ajax: {
            url: "{{ url('leadsource/data') }}",
            type: 'POST',
            data: function(d) {
                d.start = d.start || 0;  // Ensure start is always set
                d.length = d.length || 25; // Ensure length is always set
                // if (!isEmpty(sessionStorage.getItem("filters"))) {
                // 	d.searchFields = JSON.parse(sessionStorage.getItem("filters"));
                // }
            }
        },
        rowCallback: function(row, data) {

        },
        columns: [
            //set table columns
            {
                data: 'DT_RowIndex',
                name: 'DT_RowIndex',
                "targets": [0],
                "searchable": false,
                "orderable": false,
                className: "",
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
                data: 'name',
                name: 'name'
            },
            {
                data: 'id',
                name: 'action',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    let encryptedId = btoa(row.id);  // base64 encode
                    return `<div class="d-flex justify-content-center action-btns">
                        <a  title="View Lead Source" class="btn btn-sm  btn-info action-btn m-0 d-flex justify-content-center align-items-center" href="{{url('leadsource/show')}}/`+encryptedId+`"  target="_blank">
                            <i class="fa fa-eye"></i>
                        </a>
                        <a title="Edit Lead Source" class="btn btn-sm  btn-success action-btn m-0 d-flex justify-content-center align-items-center" href="{{url('leadsource/edit')}}/`+encryptedId+`" target="_blank">
                            <i class="fa fa-edit"></i>
                        </a>
                        <form method="get" action="{{url('leadsource/destroy')}}/`+data+`" accept-charset="UTF-8" style="display:inline" class="leadForm-3"><input name="_method" type="hidden" value="DELETE"><input name="_token" type="hidden" value="{{ csrf_token() }}">
                    
                            <a href="#" title="Delete Lead Source" data-toggle="modal" data-target="#deleteModal" onclick=" setModal(this,'3')" class="btn btn-sm btn-danger deletebtn action-btn m-0 d-flex justify-content-center align-items-center">
                                <i class="fa fa-trash"></i>
                            </a>
                        </form>
                    </div>`;
                }
            },
        ],
        order: [
            [1, 'asc']
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
            console.log('contact search cross clicked');
            $(event.target).blur(); // to remove cursiour from search field.

            $(event.target).siblings('i.fas.fa-search.position-absolute')
                .remove(); // remove search icon and the append
            $(event.target).after('<i class="fas fa-search position-absolute"></i>');
            table.search(event.target.value).draw(); // drow the table
        }
    }, 500));

    // Add select all checkbox to table header
    var $thead = jQuery('#leadsource_status_datatable thead #serial_no');
    $thead.prepend('<input type="checkbox" class="select-all">');

    // Select all checkboxes 
    jQuery('#leadsource_status_datatable').on('change', '.select-all', function() {
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
    jQuery('#leadsource_status_datatable').on('change', '.select-row', function() {
        var $checkboxes = jQuery('.select-row');
        jQuery('.select-all').prop('checked', $checkboxes.length === $checkboxes.filter(':checked').length);
        // Log selected checkboxes
        var selectedValues = jQuery('.select-row:checked').map(function() {
            return this.value;
        }).get();

    });

    $('#bulk_leadsource_remove').on('click', function() {
        jQuery('#bulk_leadsource_remove').prop('disabled', true);
        jQuery('#leadsource_status_datatable_processing').show();
        var selectedValues = $('.select-row:checked').map(function() {
            return this.value;
        }).get();

        if (selectedValues.length > 0) {
            // Open the modal
            $('#deleteModal').modal('show');
        } else {
            toastr.error('Please check at least one checkbox to continue');
            jQuery('#bulk_leadsource_remove').prop('disabled', false);
            jQuery('#leadsource_status_datatable_processing').hide();
            return false;
        }
    });
    $('#deleteModal').on('hide.bs.modal', function() {
        // Clear the selected values when modal is closed
        $('#deleteModal').removeData('selectedValues');
        jQuery('.select-all, .select-row').prop('checked', false);
        jQuery('#bulk_leadsource_remove').prop('disabled', false);
        jQuery('#leadsource_status_datatable_processing').hide();
    });
    $('#confirm').on('click', function() {
        var selectedValues = $('.select-row:checked').map(function() {
            return this.value;
        }).get();

        if (selectedValues.length > 0) {
            // console.log("Selected values:", selectedValues);
            // function to delete bulk ajax
            deleteSelectedRecords(selectedValues);
        }
        // Close the modal
        $('#deleteModal').modal('hide');
    });

    function deleteSelectedRecords(selectedValues) {
        // Perform AJAX post request
        jQuery.ajax({
            url: '/leadsource/deletebulk',
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
                jQuery('#leadsource_status_datatable').DataTable().draw(true);
                jQuery('.select-all, .select-row').prop('checked', false);
            },
            error: function(xhr, status, error) {
                toastr.error("Something went wrong.Please contact administrator.");
            },
            complete: function() {
                // Re-enable the button and hide loader after AJAX request completes
                jQuery('#bulk_leadsource_remove').prop('disabled', false);
                jQuery('#leadsource_status_datatable_processing').hide();
            }
        });
    }
}

$('.form-check-input').change(function() {
    let columnValue = $(this).val();
    let isChecked = this.checked;

    // Save the state of the checkbox in sessionStorage
    sessionStorage.setItem(columnValue, isChecked);
    let columnIndex = jQuery('#leadsource_status_datatable').DataTable().column(columnValue + ':name').index();
    if (isChecked) {
        jQuery('#leadsource_status_datatable').DataTable().column(columnIndex).visible(true);
    } else {
        jQuery('#leadsource_status_datatable').DataTable().column(columnIndex).visible(false);
    }
});
</script>
@endpush