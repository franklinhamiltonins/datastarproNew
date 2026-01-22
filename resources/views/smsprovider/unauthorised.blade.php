@extends('layouts.app')
@section('pagetitle', 'Unauthorized')
@push('breadcrumbs')
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
<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-body d-flex flex-column justify-content-center align-items-center" style="min-height: 80vh;">
                <img src="{{asset('/images/unauthorized.png')}}" class="img-fluid mb-4" alt="Unauthorized Image" style="max-width: 330px;"> <!-- Increased size -->
                <p class="text-center" style="font-size: 1.5rem;">You are not authorized to access this content.</p>
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
    $('#smsprovider_datatable tbody').on('click', 'tr', function() {
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
    var table = jQuery('#smsprovider_datatable').DataTable({
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
            url: "{{ url('smsprovider/data') }}",
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
                data: 'cycle_name',
                name: 'cycle_name'
            },
            {
                data: 'minute_delay',
                name: 'minute_delay'
            },
            {
                data: 'day_delay',
                name: 'day_delay'
            },
            {
                data: 'text',
                name: 'text'
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
    var $thead = jQuery('#smsprovider_datatable thead #serial_no');
    $thead.prepend('<input type="checkbox" class="select-all">');

    // Select all checkboxes 
    jQuery('#smsprovider_datatable').on('change', '.select-all', function() {
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
    jQuery('#smsprovider_datatable').on('change', '.select-row', function() {
        var $checkboxes = jQuery('.select-row');
        jQuery('.select-all').prop('checked', $checkboxes.length === $checkboxes.filter(':checked').length);
        // Log selected checkboxes
        var selectedValues = jQuery('.select-row:checked').map(function() {
            return this.value;
        }).get();

    });

    $('#bulk_smsprovider_remove').on('click', function() {
        jQuery('#bulk_smsprovider_remove').prop('disabled', true);
        jQuery('#smsprovider_datatable_processing').show();
        var selectedValues = $('.select-row:checked').map(function() {
            return this.value;
        }).get();

        if (selectedValues.length > 0) {
            // Open the modal
            $('#deleteModal').modal('show');
        } else {
            toastr.error('Please check at least one checkbox to continue');
            jQuery('#bulk_smsprovider_remove').prop('disabled', false);
            jQuery('#smsprovider_datatable_processing').hide();
            return false;
        }
    });
    $('#deleteModal').on('hide.bs.modal', function() {
        // Clear the selected values when modal is closed
        $('#deleteModal').removeData('selectedValues');
        jQuery('.select-all, .select-row').prop('checked', false);
        jQuery('#bulk_smsprovider_remove').prop('disabled', false);
        jQuery('#smsprovider_datatable_processing').hide();
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
            url: '/smsprovider/delete',
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
                jQuery('#smsprovider_datatable').DataTable().draw(true);
                jQuery('.select-all, .select-row').prop('checked', false);
            },
            error: function(xhr, status, error) {
                toastr.error("Something went wrong.Please contact administrator.");
            },
            complete: function() {
                // Re-enable the button and hide loader after AJAX request completes
                jQuery('#bulk_smsprovider_remove').prop('disabled', false);
                jQuery('#smsprovider_datatable_processing').hide();
            }
        });
    }
}

$('.form-check-input').change(function() {
    let columnValue = $(this).val();
    let isChecked = this.checked;

    // Save the state of the checkbox in sessionStorage
    sessionStorage.setItem(columnValue, isChecked);
    let columnIndex = jQuery('#smsprovider_datatable').DataTable().column(columnValue + ':name').index();
    if (isChecked) {
        jQuery('#smsprovider_datatable').DataTable().column(columnIndex).visible(true);
    } else {
        jQuery('#smsprovider_datatable').DataTable().column(columnIndex).visible(false);
    }
});
</script>
<!-- <script src="https://code.jquery.com/jquery-migrate-3.0.0.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.0/jquery-ui.min.css"
    integrity="sha512-LDB28UFxGU7qq5q67S1iJbTIU33WtOJ61AVuiOnM6aTNlOLvP+sZORIHqbS9G+H40R3Pn2wERaAeJrXg+/nu6g=="
    crossorigin="anonymous" referrerpolicy="no-referrer" /> -->
<script src="https://cdn.datatables.net/plug-ins/1.11.3/features/searchHighlight/dataTables.searchHighlight.min.js">
</script>
<script src="//bartaz.github.io/sandbox.js/jquery.highlight.js"></script>
<!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css"> -->
<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js"></script> -->
@endpush