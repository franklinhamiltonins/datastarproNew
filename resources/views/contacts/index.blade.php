@extends('layouts.app')
@section('pagetitle', 'All Contacts')
@push('breadcrumbs')
<li class="breadcrumb-item"><a href="{{route('leads.index')}}">Business</a></a></li>
<li class="breadcrumb-item active">All Contacts</li>
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
                        <div class="col-lg-12 margin-tb d-flex align-items-center flex-wrap justify-content-between table-top-sec">
                            <div class="d-flex flex-wrap action-dropdown">
                                <div class="dropdown">
                                    <button class="btn btn-info btn-sm dropdown-toggle"
                                            type="button"
                                            id="actionbtn"
                                            data-bs-toggle="dropdown"
                                            aria-expanded="false">
                                        Actions
                                    </button>

                                    <div class="dropdown-menu p-0 m-0 text-nowrap" aria-labelledby="actionbtn">

                                        @can('contact-filters')
                                        <button type="button"
                                                class="dropdown-item btn-sm btn-primary"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#filters">
                                            <i class="fas fa-filter me-1"></i>
                                            Filters
                                        </button>
                                        @endcan

                                        @can('contact-delete')
                                        <a href="javascript:void(0)"
                                           class="dropdown-item btn-sm btn-danger"
                                           id="bulk_contact_remove">
                                            <i class="fas fa-trash-alt me-1"></i>
                                            <span class="d-none d-md-inline">Remove</span>
                                        </a>
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
                                        <label for="c_city"><strong>City</strong></label>
                                        <select data-column="c_city" id="c_city" class="form-control select">
                                            <option value="" selected>All Cities</option>
                                            @foreach($cityCounts as $key => $city)
                                                <option value="{{ $city->c_city }}">{{ $city->c_city }} - ({{ $city->total }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="business_type"><strong>Business Type</strong></label>
                                        <select data-column="leads.type" id="business_type" class="form-control select">
                                            <option value="" selected>All Types</option>
                                            <option value="Condo">Condo</option>
                                            <option value="HOA">HOA</option>
                                            <option value="Commercial">Commercial</option>
                                            <option value="Co-Op">Co-Op</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="c_merge_status"><strong>Mergeable</strong></label>
                                        <select data-column="c_merge_status" id="c_merge_status" class="form-control select">
                                            <option value="" selected>All Types</option>
                                            <option value="0">No</option>
                                            <option value="1">Yes</option>
                                        </select>
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
                    <div class="table-wrapper">
                        <div class="top-scrollbar-container">
                            <div class="top-scrollbar-spacer"></div>
                        </div>
                        <div class="table-container pb-2">
                            <table class="order-column compact hover searchHighlight" id="leads_datatable">
                                <thead class="text-nowrap" style="font-size: 0.93rem;">
                                    <tr>
                                        <th id="serial_no"></th>
                                        <th style="min-width: 150px;">Name <span class="arrow"></span></th>
                                        <!-- <th style="width:99px">Business Type</th>
                                        <th style="min-width: 192px;">Business Name</th> -->
                                        <!-- <th style="width: 99px;">Creation Date</th> -->
                                        <th style="min-width: 100px;">City <span class="arrow"></span></th>
                                        <th>State <span class="arrow"></span></th>
                                        <th>Zip</th>
                                        <th>Phone <span class="arrow"></span></th>
                                        <th>Email <span class="arrow"></span></th>
                                        <th>Is Client <span class="arrow"></span></th>
                                        <th style="min-width: 200px;">Slug <span class="arrow"></span></th>
                                        <th>Mergeable <span class="arrow"></span></th>
                                        <th style="min-width: 65px; text-align: center;">Action <span class="arrow"></span></th>
                                    </tr>
                                </thead>

                            </table>
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
                url: "{{ route('contacts.data') }}",
                data: function(d) {
                    // if (sessionStorage.getItem("search_field")) {
                    //     d.search_field = sessionStorage.getItem("search_field");
                    // }
                    // if (sessionStorage.getItem("search_field_value")) {
                    //     d.search_field_value = sessionStorage.getItem("search_field_value");
                    // }

                    const city = getValueIfExists("c_city");
                    const business_type = getValueIfExists("business_type");
                    const c_merge_status = getValueIfExists("c_merge_status");

                    d.city = city;
                    d.business_type = business_type;
                    d.c_merge_status = c_merge_status;

                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', searchable: false, orderable: false, render: function(data, type, row, meta) {
                    return '<input type="checkbox" class="select-row" value="' + row.id + '">';
                }},
                {
                    data: 'c_full_name',
                    name: 'c_full_name',
                    render: function (data, type, row) {
                        let encryptedId = btoa(row.lead_id);  // base64 encode
                        return `<span id="businessName_${row.id}" onclick="sendToProspects({lead_id: ${row.lead_id},contact_id: ${row.id},lead_url: '/leads/edit/${encryptedId}',backpage_url: window.location.href,page_type: 'contact_list',dialing_id:0})">
                                        <a href="javascript:void(0)" >${data}</a>
                                    </span>`;
                    }
                },
                // { data: 'c_full_name', name: 'c_full_name' },
                { data: 'c_city', name: 'c_city' },
                { data: 'c_state', name: 'c_state' },
                { data: 'c_zip', name: 'c_zip' },
                { data: 'c_phone', name: 'c_phone' },
                { data: 'c_email', name: 'c_email' },
                { data: 'c_is_client', name: 'c_is_client' },
                { data: 'contact_slug', name: 'contact_slug' },
                { data: 'c_merge_status', name: 'c_merge_status' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ],
            order: [[1, 'desc']],
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


        // Add select all checkbox to table header
        var $thead = $('#leads_datatable thead #serial_no');
        $thead.prepend('<input type="checkbox" class="select-all">');

        // Select all checkboxes
        $('#leads_datatable').on('change', '.select-all', function() {
            var checked = this.checked;
            $('.select-row').prop('checked', checked);
            // Log selected checkboxes
            if (checked) {
                var selectedValues = $('.select-row:checked').map(function() {
                    return this.value;
                }).get();

            } else {

            }
        });

        // Handle individual row selections
        $('#leads_datatable').on('change', '.select-row', function() {
            var $checkboxes = $('.select-row');
            $('.select-all').prop('checked', $checkboxes.length === $checkboxes.filter(':checked').length);
            // Log selected checkboxes
            var selectedValues = $('.select-row:checked').map(function() {
                return this.value;
            }).get();

        });


        $('#bulk_contact_remove').on('click', function() {
            jQuery('#bulk_contact_remove').prop('disabled', true);
            jQuery('#leads_datatable_processing').show();
            var selectedValues = $('.select-row:checked').map(function() {
                return this.value;
            }).get();

            if (selectedValues.length > 0) {
                // Open the modal
                $('#deleteModal').modal('show');
            } else {
                toastr.error('Please check at least one checkbox to continue');
                jQuery('#bulk_contact_remove').prop('disabled', false);
                jQuery('#leads_datatable_processing').hide();
                return false;
            }
        });
        $('#deleteModal').on('hide.bs.modal', function() {
            // Clear the selected values when modal is closed
            $('#deleteModal').removeData('selectedValues');
            jQuery('.select-all, .select-row').prop('checked', false);
            jQuery('#bulk_contact_remove').prop('disabled', false);
            jQuery('#leads_datatable_processing').hide();
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
            $.ajax({
                url: '/contacts/delete',
                type: 'POST',
                data: {
                    selectedValues: selectedValues
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.contactsCount) {
                        toastr.success(response.message);
                    } else {
                        toastr.error(response.message);
                    }
                    $('#leads_datatable').DataTable().draw(true);
                    $('.select-all').prop('checked', false);
                },
                error: function(xhr, status, error) {
                    toastr.error("Something went wrong.Please contact administrator.");
                },
                complete: function() {
                    // Re-enable the button and hide loader after AJAX request completes
                    $('#bulk_contact_remove').prop('disabled', false);
                    $('#leads_datatable_processing').hide();
                }
            });
        }
    });

    jQuery('#leads_datatable').on('draw.dt', function() {
        const topScrollbarContainer = document.querySelector('.top-scrollbar-container');
        const topScrollbarSpacer = document.querySelector('.top-scrollbar-spacer');
        const tableContentContainer = document.querySelector('.table-container');
        const dataTable = document.querySelector('.dataTable');
        const tableWidth = dataTable.scrollWidth;
        topScrollbarSpacer.style.width = tableWidth + 'px';
        topScrollbarContainer.addEventListener( 'scroll', function() {
            tableContentContainer.scrollLeft = this.scrollLeft;
        });
        tableContentContainer.addEventListener( 'scroll', function() {
            topScrollbarContainer.scrollLeft = this.scrollLeft;
        });
    });


    document.getElementById('applyFilters').addEventListener('click', function () {

        $table.draw();
    });
    document.getElementById('resetFilters').addEventListener('click', function () {
        document.getElementById('c_city').value = '';
        document.getElementById('business_type').value = '';
        document.getElementById('c_merge_status').value = '';

        $table.draw();
    });
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

    function setDeleteModal(element, id) {
        // Unbind previous click handlers to avoid duplicate bindings
        $('#confirm').off('click').on('click', function () {
            deleteContactRecord(id);
        });
    }

    function deleteContactRecord(id) {
        // Disable the confirm button to prevent multiple clicks
        $('#confirm').prop('disabled', true);

        jQuery.ajax({
            url: `/leads/edit/contact-delete/${id}`,
            type: 'DELETE',
            data: { id: id },
            success: function (response) {
                console.log('Delete successful', response);

                if (response.contactsCount) {
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message);
                }

                // Refresh the DataTable
                jQuery('#leads_datatable').DataTable().draw(true);

                $("#close_delete_modal").click();

                // Hide the modal properly
                // $('#deleteModal').modal('hide');
                // $('.modal-backdrop').remove(); // Remove the backdrop
                // $('body').removeClass('modal-open'); // Remove body class if it's not cleaned up
            },
            error: function (xhr, status, error) {
                console.error('Error during deletion:', error);
                toastr.error("Something went wrong. Please contact the administrator.");
            },
            complete: function () {
                // Re-enable the confirm button
                $('#confirm').prop('disabled', false);
            }
        });
    }

    function handleDelete($url) {
        event.preventDefault();
        console.log($url);
        Swal.fire({
            icon: 'question',
            title: 'Are you sure?',
            preConfirm: () => [
                $.ajax({
                    url: $url,
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
                    },
                    success: (res) => {
                        toastr.success(res.message, 'Success');
                        $table.ajax.reload(null, false);
                    },
                    error: (res) => {
                        console.log(res);
                        toastr.error('There was an error with deleting this record!', 'Error...');
                    }
                })
            ]
        });
    }
</script>
<script src="https://cdn.datatables.net/plug-ins/1.11.3/features/searchHighlight/dataTables.searchHighlight.min.js">
</script>
<script src="//bartaz.github.io/sandbox.js/jquery.highlight.js"></script>
@endpush