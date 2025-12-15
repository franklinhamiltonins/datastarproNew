@extends('layouts.app')
@section('pagetitle')
    @if($pending == 1) Rating Management 
    @elseif($pending == 2) Rating Management (Pending Request)
    @endif
@endsection
@push('breadcrumbs')
<li class="breadcrumb-item"><a href="{{route('rating.index')}}">Rating</a></li>
<li class="breadcrumb-item active">Rating Management</li>
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
                            <div class="d-flex flexwrap-wrap action-dropdown">

                                <div class="dropdown">
                                    <!-- <button class="btn btn-info btn-sm dropdown-toggle" type="button" id="actionbtn"
                                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        Actions
                                    </button> -->
                                    <div class="dropdown-menu p-0 m-0 text-nowrap" aria-labelledby="actionbtn">
                                        {{-- @can('template-delete') --}}
                                        <a href="javascript:void(0)" id="bulk_rating_remove"
                                            class="bulk_rating_remove text-nowrap btn-block rounded-left-0 btn btn-danger btn-sm btn-sm closebtn mr-1">
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
                                                aria-controls="rating_status_datatable">
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
                                <div id="rating_status_datatable_filter" class="dataTables_filter search-sec mb-0">
                                    <label
                                        class="d-flex align-items-center justify-content-end mb-0 position-relative"><input
                                            type="search" id="customSearchBox" placeholder="Search for Entries"
                                            aria-controls="rating_status_datatable" class="form-control">
                                        <i class="fas fa-search position-absolute"></i>
                                    </label>
                                </div>
                                <div class="d-flex ml-1">
                                    <a title="Create rating" class="btn btn-success btn-sm d-flex align-items-center justify-content-center create-btn" href="{{route('rating.create')}}" style="width: 42px;height: 42px;">
                                        <i class="fas fa-plus-circle"></i>
                                        <!-- <span class="d-none d-md-inline">Create</span> -->
                                    </a>
                                    @if($pending == 1)
                                        <a title="rating List" class="btn btn-success btn-sm d-flex align-items-center justify-content-center create-btn ml-1" href="{{route('rating.index', 2)}}" style="width: 42px;height: 42px;">
                                            <i class="fas fa-list"></i>
                                            <!-- <span class="d-none d-md-inline">Create</span> -->
                                        </a>
                                    @else
                                        <a title="rating List (Pending Request)" class="btn btn-success btn-sm d-flex align-items-center justify-content-center create-btn ml-1" href="{{route('rating.index', 1)}}" style="width: 42px;height: 42px;">
                                            <i class="fas fa-list"></i>
                                            <!-- <span class="d-none d-md-inline">Create</span> -->
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-3 pt-2">
                    <div class="table-container pb-2">
                        <table class="order-column compact hover searchHighlight rating_status_datatable" id="rating_status_datatable">
                            <thead class="text-nowrap" style="font-size: 0.93rem;">
                                <tr>
                                    <!-- <th id="serial_no" style="width: 10px;"></th> -->
                                    <th></th>
                                    <th>Name <span class="arrow"></span></th>
                                    <th>Insurance Type</th>
                                    <th>
                                        <div class="d-flex align-items-center justify-content-center">
                                            @if($pending == 1)Action
                                            @elseif($pending == 2) Approve/Reject
                                            @endif
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
    @include('partials.ratingdelete-modal')
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
    $('#rating_status_datatable tbody').on('click', 'tr', function() {
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

    $(document).on('click','.deleteRatingBtn',function () {
        const data_id = $(this).data("id");
        $("#previous_rating_id").val(data_id);
        $.ajax({
            url: '/rating/countLeadAssociationRating',
            method: "POST",
            data: {
                data_id: data_id,

            },
            success: function(response) {
                // console.log(response);
                if(response.status){
                    $("#newdeleteModal").modal("show");
                    let deleteBodyContent = $(".deleteBodyContent");
                    deleteBodyContent.empty(); // Clear existing content

                    if (response.totalcount > 0) {
                        // When there are associated ratings
                        deleteBodyContent.append(`
                            <div class="alert reassign-alert mt-2 p-3 text-right">
                                <div class="d-flex reassign-alert-cntnt text-left">
                                    <i class="fa fa-exclamation-triangle pt-1 pr-2 text-warning"></i>
                                    <p class="mb-2 small">You have `+response.totalcount+` associations with this rating, Please reassign those associations before deleting, or, you can proceed without reassigning:</p>
                                </div>
                               
                                <button type="button" id="forcefullydelete" data-id="`+data_id+`" class="btn btn-sm btn-danger mt-2">Delete Anyway</button>
                            </div>
                        `);

                        $.each(response.list, function (key, value) {
                            if (value.count > 0) {
                                let selectBox = $('<select>')
                                    .attr("name", key)
                                    .addClass("rating-select form-control mb-2");
                                
                                selectBox.append($('<option>').text("Select " + value.name).attr("value", ""));

                                if (value.rating.length > 0) {
                                    $.each(value.rating, function (index, rating) {
                                        selectBox.append($('<option>').text(rating.name).attr("value", rating.id));
                                    });
                                }

                                let label = $("<label>").text(value.name + ": ").addClass("fw-bold d-block mb-1");
                                deleteBodyContent.append(label).append(selectBox);

                                // Show warning message if no alternate rating is available
                                if (value.rating.length === 0) {
                                    let warningMessage = $("<p>")
                                        .addClass("text-danger my-1 text-xs py-1 px-2 border border-danger rounded warniing-cntnt")
                                        .text("No alternate Rating available for " + value.name + ". Please create a new rating before reassigning and deleting.");
                                    
                                    deleteBodyContent.append(warningMessage);
                                }
                            }
                        });


                        // Make selection required
                        $(document).on("change", ".rating-select", function () {
                            let allSelected = true;

                            $(".rating-select").each(function () {
                                if ($(this).val() === "") {
                                    allSelected = false;
                                    return false; // Break loop
                                }
                            });

                            $("#confirmdelete").prop("disabled", !allSelected);
                        });
                        document.getElementById("confirmdelete").textContent = "Reassign and Delete";

                        $("#confirmdelete").prop("disabled", true); // Initially disable confirm button
                    } else {
                        // No associated ratings, allow direct delete
                        document.getElementById("confirmdelete").textContent = "Delete";
                        deleteBodyContent.append('<p class="mb-0">The record will be deleted and You cannot undo this!</p>');
                        $("#confirmdelete").prop("disabled", false);
                    }
                }
            },
            error: function(xhr, status, error) {
            }
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
    var table = jQuery('#rating_status_datatable').DataTable({
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
            url: "{{ url('rating/data') }}",
            type: 'POST',
            data: function(d) {
                d.pending = '{{$pending}}';
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
            // {
            //     data: 'DT_RowIndex',
            //     name: 'DT_RowIndex',
            //     "targets": [0],
            //     "searchable": false,
            //     "orderable": false,
            //     className: "text-center",
            //     render: function(data, type, row, meta) {
            //         return '<input type="checkbox" class="select-row" value="' + row.id + '">';
            //     }
            // },
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
                data: 'insurance_types',
                name: 'insurance_types',
                "searchable": false,
                "orderable": false
            },
            {
                data: 'id',
                name: 'action',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    let encryptedId = btoa(row.id);  // base64 encode
                    if(parseInt('{{$pending}}') == 1){
                        return `<div class="d-flex justify-content-center action-btns">
                            <a  title="View Rating" class="btn btn-sm  btn-info action-btn m-0 d-flex justify-content-center align-items-center" href="{{url('rating/show')}}/`+encryptedId+`" >
                                <i class="fa fa-eye"></i>
                            </a>
                            <a title="Edit Rating" class="btn btn-sm  btn-success action-btn m-0 d-flex justify-content-center align-items-center" href="{{url('rating/edit')}}/`+encryptedId+`">
                                <i class="fa fa-edit"></i>
                            </a>
                            <a href="javascript:void(0)" title="Delete carrier" data-id="`+data+`" class="btn btn-sm btn-danger deleteRatingBtn action-btn m-0 d-flex justify-content-center align-items-center">
                                <i class="fa fa-trash"></i>
                            </a>
                        </div>`;
                    }
                    else{
                        return `<div class="d-flex justify-content-center action-btns">
                            <a class="btn btn-sm  btn-success action-btn m-0 d-flex justify-content-center align-items-center" href="{{url('rating/edit')}}/`+encryptedId+`/2" >
                                <i class="fa fa-balance-scale" title="Approve/Reject"></i>
                            </a>
                        </div>`;
                    }
                }
            },
        ],
        order: [
            [0, 'desc']
        ],
        dom: 'rt<"bottom"ip><"clear">',
        initComplete: function() {
            // After the table is initialized, set the visibility of columns based on sessionStorage
            // $('.form-check-input').each(function() {
            //     let columnValue = $(this).val();
            //     let isChecked = sessionStorage.getItem(columnValue);

            //     if (isChecked === 'true') {
            //         $(this).prop('checked', true);
            //         let columnIndex = table.column(columnValue + ':name').index();
            //         table.column(columnIndex).visible(true);
            //     } else if (isChecked === 'false') {
            //         $(this).prop('checked', false);
            //         let columnIndex = table.column(columnValue + ':name').index();
            //         table.column(columnIndex).visible(false);
            //     }
            // });
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
    var $thead = jQuery('#rating_status_datatable thead #serial_no');
    $thead.prepend('<input type="checkbox" class="select-all">');

    // Select all checkboxes 
    jQuery('#rating_status_datatable').on('change', '.select-all', function() {
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
    jQuery('#rating_status_datatable').on('change', '.select-row', function() {
        var $checkboxes = jQuery('.select-row');
        jQuery('.select-all').prop('checked', $checkboxes.length === $checkboxes.filter(':checked').length);
        // Log selected checkboxes
        var selectedValues = jQuery('.select-row:checked').map(function() {
            return this.value;
        }).get();

    });

    $('#bulk_rating_remove').on('click', function() {
        jQuery('#bulk_rating_remove').prop('disabled', true);
        jQuery('#rating_status_datatable_processing').show();
        var selectedValues = $('.select-row:checked').map(function() {
            return this.value;
        }).get();

        if (selectedValues.length > 0) {
            // Open the modal
            $('#deleteModal').modal('show');
        } else {
            toastr.error('Please check at least one checkbox to continue');
            jQuery('#bulk_rating_remove').prop('disabled', false);
            jQuery('#rating_status_datatable_processing').hide();
            return false;
        }
    });
    $('#deleteModal').on('hide.bs.modal', function() {
        // Clear the selected values when modal is closed
        $('#deleteModal').removeData('selectedValues');
        jQuery('.select-all, .select-row').prop('checked', false);
        jQuery('#bulk_rating_remove').prop('disabled', false);
        jQuery('#rating_status_datatable_processing').hide();
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
            url: '/rating/deletebulk',
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
                jQuery('#rating_status_datatable').DataTable().draw(true);
                jQuery('.select-all, .select-row').prop('checked', false);
            },
            error: function(xhr, status, error) {
                toastr.error("Something went wrong.Please contact administrator.");
            },
            complete: function() {
                // Re-enable the button and hide loader after AJAX request completes
                jQuery('#bulk_rating_remove').prop('disabled', false);
                jQuery('#rating_status_datatable_processing').hide();
            }
        });
    }
}

$('.form-check-input').change(function() {
    let columnValue = $(this).val();
    let isChecked = this.checked;

    // Save the state of the checkbox in sessionStorage
    sessionStorage.setItem(columnValue, isChecked);
    let columnIndex = jQuery('#rating_status_datatable').DataTable().column(columnValue + ':name').index();
    if (isChecked) {
        jQuery('#rating_status_datatable').DataTable().column(columnIndex).visible(true);
    } else {
        jQuery('#rating_status_datatable').DataTable().column(columnIndex).visible(false);
    }
});
</script>
<script src="https://code.jquery.com/jquery-migrate-3.0.0.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.0/jquery-ui.min.css"
    integrity="sha512-LDB28UFxGU7qq5q67S1iJbTIU33WtOJ61AVuiOnM6aTNlOLvP+sZORIHqbS9G+H40R3Pn2wERaAeJrXg+/nu6g=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
<script src="https://cdn.datatables.net/plug-ins/1.11.3/features/searchHighlight/dataTables.searchHighlight.min.js">
</script>
<script src="//bartaz.github.io/sandbox.js/jquery.highlight.js"></script>
<!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css"> -->
<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js"></script> -->
@endpush