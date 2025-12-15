@extends('layouts.app')
@section('pagetitle', 'Templates Management')
@push('breadcrumbs')
<li class="breadcrumb-item"><a href="{{route('templates.index')}}">All Templates</a></li>
<li class="breadcrumb-item active">Templates Management</li>
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
    <input type="hidden" value="{{ $is_admin }}" id="isAdminUser">
    <input type="hidden" value="{{ auth()->user()->id }}" id="auth_id">
    <input type="hidden" value="{{ auth()->user()->can('template-delete') ? 'true' : 'false' }}" id="delete_permission">
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
                                        aria-labelledby="actionbtn" data-id="1">
                                        <div class="dropdown d-flex dropright">
                                            <button class="btn btn-block rounded-0 btn-primary btn-sm dropdown-toggle"
                                                type="button" id="filtersec" data-toggle="dropdown" aria-haspopup="true"
                                                aria-expanded="false">
                                                <i class="fas fa-filter"></i>
                                                Filters
                                            </button>
                                            <div class="dropdown-menu p-0 m-0" aria-labelledby="filtersec" data-id="2">
                                                <button class="btn btn-teal rounded-0 btn-sm btn-block m-0"
                                                    type="button" data-toggle="collapse" onclick="filterDatatable('mail')">
                                                    <i class="fas fa-envelope"></i>
                                                    <span class="d-none d-md-inline">Filter By Email</span>
                                                </button>
                                                <button class="btn btn-secondary rounded-0 btn-sm btn-block m-0"
                                                    type="button"  onclick="filterDatatable('sms')">
                                                    <i class="fas fa-comment"></i>
                                                    <span class="d-none d-md-inline">Filter By SMS</span>
                                                </button>
                                                <button class="btn btn-default rounded-0 btn-sm btn-block m-0"
                                                    type="button"  style="color: red;text-align: center;" onclick="filterDatatable('')">
                                                    <span class="d-none d-md-inline">Reset</span>
                                                </button>
                                            </div>
                                        </div>
                                        <a href="javascript:void(0)" id="bulk_templates_remove"
                                            class="bulk_templates_remove text-nowrap btn-block rounded-left-0 btn btn-danger btn-sm btn-sm closebtn mr-1">
                                            <i class="fas fa-trash-alt mr-1"></i>
                                            <span class="d-none d-md-inline">Remove</span>
                                        </a>
                                    </div>
                                </div>
                                <div class="custom_search_page d-flex align-items-center justify-content-between ml-2">
                                    <div id="custom_length_menu">
                                        <label class="d-flex align-items-center justify-content-between mb-0">Show
                                            <select id="customPageLength"
                                                class="form-control form-control-sm mx-1 px-0 bg-transparent"
                                                aria-controls="templates_datatable">
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
                                <div id="templates_datatable_filter" class="dataTables_filter search-sec mb-0">
                                    <label
                                        class="d-flex align-items-center justify-content-end mb-0 position-relative"><input
                                            type="search" id="customSearchBox" placeholder="Search for Entries"
                                            aria-controls="templates_datatable" class="form-control">
                                        <i class="fas fa-search position-absolute"></i>
                                    </label>
                                </div>
                                <div class="ml-1">
                                    <a class="btn btn-success btn-sm d-flex align-items-center justify-content-center create-btn" href="{{route('templates.create')}}" title="Add new template" style="width: 42px;height: 42px;">
                                        <i class="fas fa-plus-circle"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-3 pt-2">
                    <div class="table-container pb-2">
                        <table class="order-column compact hover searchHighlight" id="templates_datatable">
                            <thead class="text-nowrap" style="font-size: 0.93rem;">
                                <tr>
                                    <th id="serial_no"></th>
                                    <th></th>
                                    <th>Name <span class="arrow"></span></th>
                                    <!-- <th style="min-width: 100px;">Content</th> -->
                                    <th>Subject <span class="arrow"></span></th>
                                    <th>Type <span class="arrow"></span></th>
                                    <th>Set For All <span class="arrow"></span></th>
                                    <th>User</th>
                                    <th>
                                        <div class="d-flex align-items-center justify-content-center">Action</div>
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
    $('#templates_datatable tbody').on('click', 'tr', function() {
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
    var table = jQuery('#templates_datatable').DataTable({
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
        // pageLength: 25,
        ajax: {
            url: "{{ url('templates/templates-data') }}",
            type: 'POST',
            data: function(d) {

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
                className: "text-left",
                render: function(data, type, row, meta) {
                    var is_admin = $("#isAdminUser").val();
                    var auth_id = $("#auth_id").val();
                    
                    if(row.created_by == auth_id)
                        return '<input type="checkbox" class="select-row" value="' + row.id + '">';
                    else 
                        return '<input type="checkbox" class="select-row" value="" disabled>';;
                    
                }
            },
            {
                data: 'id',
                name: 'id',
                'visible': false
            },

            {
                data: 'template_name',
                name: 'template_name'
            },
            // {
            //     data: 'template_content',
            //     render: function(data, type, row, meta) {
            //         let node = $.parseHTML('<span>' + data + '</span>')[0];
            //         return node.innerText;
            //     }
            // },
            {
                data: 'template_subject',
                name: 'template_subject'
            },
            {
                data: 'template_type',
                name: 'template_type'
            },
            {
                data: 'set_for_all',
                name: 'set_for_all',
                visible: $("#isAdminUser").val()
            },
            {
                data: 'user_id',
                name: 'user.name',
                visible: $("#isAdminUser").val(),
                render: function(data, type, row, meta) {
                    let node = $.parseHTML('<span>' + data + '</span>')[0];
                    return node.innerText;
                },
                orderable: false,
            },
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

    // Add select all checkbox to table header
    var $thead = jQuery('#templates_datatable thead #serial_no');
    $thead.prepend('<input type="checkbox" class="select-all" title="Select / De-select All">');
    
    // Select all checkboxes 
    jQuery('#templates_datatable').on('change', '.select-all', function() {
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
    jQuery('#templates_datatable').on('change', '.select-row', function() {
        var $checkboxes = jQuery('.select-row');
        jQuery('.select-all').prop('checked', $checkboxes.length === $checkboxes.filter(':checked').length);
        // Log selected checkboxes
        var selectedValues = jQuery('.select-row:checked').map(function() {
            return this.value;
        }).get();

    });

    $('#bulk_templates_remove').on('click', function() {
        jQuery('#bulk_templates_remove').prop('disabled', true);
        jQuery('#templates_datatable_processing').show();
        var selectedValues = $('.select-row:checked').map(function() {
            return this.value;
        }).get();

        if (selectedValues.length > 0) {
            // Open the modal
            $('#deleteModal').modal('show');
        } else {
            toastr.error('Please check at least one checkbox to continue');
            jQuery('#bulk_templates_remove').prop('disabled', false);
            jQuery('#templates_datatable_processing').hide();
            return false;
        }
    });
    $('#deleteModal').on('hide.bs.modal', function() {
        // Clear the selected values when modal is closed
        $('#deleteModal').removeData('selectedValues');
        jQuery('.select-all, .select-row').prop('checked', false);
        jQuery('#bulk_templates_remove').prop('disabled', false);
        jQuery('#templates_datatable_processing').hide();
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

    function deleteSelectedRecords(selectedValues) {
        // Perform AJAX post request
        jQuery.ajax({
            url: '/templates/delete',
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
                jQuery('#templates_datatable').DataTable().draw(true);
                jQuery('.select-all, .select-row').prop('checked', false);
            },
            error: function(xhr, status, error) {
                toastr.error("Something went wrong.Please contact administrator.");
            },
            complete: function() {
                // Re-enable the button and hide loader after AJAX request completes
                jQuery('#bulk_templates_remove').prop('disabled', false);
                jQuery('#templates_datatable_processing').hide();
            }
        });
    }

    // Button click event to submit selected checkbox values via AJAX
    // jQuery('#bulk_templates_remove').on('click', function() {
    //     jQuery('#bulk_templates_remove').prop('disabled', true);
    //     jQuery('#templates_datatable_processing').show();
    //     var selectedValues = jQuery('.select-row:checked').map(function() {
    //         return this.value;
    //     }).get();
    //     console.log("Selected values:", selectedValues);
    //     if (selectedValues.length <= 0) {
    //         toastr.error('Please check at least one checkbox to continue');
    //         return false;
    //     }
    //     // Perform AJAX post request
    //     jQuery.ajax({
    //         url: '/templates/delete',
    //         type: 'POST',
    //         data: {
    //             selectedValues: selectedValues
    //         },
    //         success: function(response) {
    //             if (response.templateCount) {
    //                 toastr.success(response.message);
    //             } else {
    //                 toastr.error(response.message);
    //             }
    //             jQuery('#templates_datatable').DataTable().draw(true);
    //             jQuery('.select-all').prop('checked', false);
    //         },
    //         error: function(xhr, status, error) {
    //             toastr.error("Something went wrong.Please contact administrator.");
    //         },
    //         complete: function() {
    //             // Re-enable the button and hide loader after AJAX request completes
    //             jQuery('#bulk_templates_remove').prop('disabled', false);
    //             jQuery('#templates_datatable_processing').hide();
    //         }
    //     });
    // });
}

$('.form-check-input').change(function() {
    let columnValue = $(this).val();
    let isChecked = this.checked;

    // Save the state of the checkbox in sessionStorage
    sessionStorage.setItem(columnValue, isChecked);
    let columnIndex = jQuery('#templates_datatable').DataTable().column(columnValue + ':name').index();
    if (isChecked) {
        jQuery('#templates_datatable').DataTable().column(columnIndex).visible(true);
    } else {
        jQuery('#templates_datatable').DataTable().column(columnIndex).visible(false);
    }
});

function filterDatatable(value) {
    jQuery('#templates_datatable').DataTable().search(value).draw();
}
</script>
<!-- <script src="https://code.jquery.com/jquery-migrate-3.0.0.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.0/jquery-ui.min.css"
    integrity="sha512-LDB28UFxGU7qq5q67S1iJbTIU33WtOJ61AVuiOnM6aTNlOLvP+sZORIHqbS9G+H40R3Pn2wERaAeJrXg+/nu6g=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
 --><script src="https://cdn.datatables.net/plug-ins/1.11.3/features/searchHighlight/dataTables.searchHighlight.min.js">
</script>
<script src="//bartaz.github.io/sandbox.js/jquery.highlight.js"></script>
<!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css"> -->
<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js"></script> -->
@endpush