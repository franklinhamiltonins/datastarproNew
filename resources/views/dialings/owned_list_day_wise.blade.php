@extends('layouts.app')
@section('pagetitle', 'Own Leads Time Wise')
@push('breadcrumbs')
<li class="breadcrumb-item"><a href="{{route('dialings.ownedleads')}}">Own Leads</a></li>
<li class="breadcrumb-item active">Own Leads Time Wise</li>
@endpush
@section('content')
<link href="/css/jquery.dataTables.min.css" rel="stylesheet">
<!-- here try -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<!-- try -->
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
                <div class="p-3">
                    <div class="row">
                        <div class="col-lg-12 margin-tb table-top-sec">
                            <div class="left-content d-flex align-items-center">
                                <a class="btn btn-info btn-sm px-2 mb-3 mb-md-0"
                                    href="{{ route('dialings.ownedleads') }}"
                                    ><i class="fas fa-arrow-circle-left"></i> Back</a>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mt-3">
                                <div class="d-flex flexwrap-wrap action-dropdown">
                                    <div class="custom_search_page d-flex align-items-center justify-content-between ml-2">
                                        <div id="custom_length_menu">
                                            <label class="d-flex align-items-center justify-content-between mb-0">Show
                                                <select id="customPageLength"
                                                    class="form-control form-control-sm mx-1 px-0 bg-transparent"
                                                    aria-controls="smsprovider_datatable">
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

                                <div class="d-flex flexwrap-wrap" >
                                    <div id="smsprovider_datatable_filter" class="dataTables_filter search-sec mb-0">
                                        <label
                                            class="d-flex align-items-center justify-content-end mb-0 position-relative"><input
                                                type="search" id="customSearchBox" placeholder="Search for Entries"
                                                aria-controls="smsprovider_datatable" class="form-control">
                                            <i class="fas fa-search position-absolute"></i>
                                        </label>
                                    </div>
                                    <div class="ml-1">
                                        <div class="tooltip-wrapper">
                                            <a class="btn btn-success btn-sm " id="download-button" href="#">
                                                <i class="fas fa-file-export"></i>
                                                <!-- <span class="d-none d-md-inline">Create</span> -->
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="collapse show" id="filterByDate">
                        <div class="card card-body mb-0 p-2 rounded-top-0 box-shadow-btm">
                            <div class="search-filter">
                                @include('dialings.partials.search-filter')
                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-3 pt-2">
                    <div class="table-container pb-2">
                        <input type="hidden" id="agent_own_data_response_count">
                        <table class="order-column compact hover searchHighlight agents_leads_datatable" id="agents_leads_datatable">
                            <thead style="font-size: 0.93rem;">
                                <tr>
                                    <th style="min-width: 30px;">No</th>
                                    <th></th>
                                    <th>Agent Name</th>
                                    <th style="min-width: 192px;">Business Name</th>
                                    <th style="min-width: 192px;">Address</th>
                                    <th>City</th>
                                    <th style="min-width: 80px">County</th>
                                    <th style="min-width: 55px">Marked own On</th>
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
    @include('smsprovider.partials.chat-complete-modal')
    @include('smsprovider.partials.chat-stop-modal')
</section>


@endsection
@push('styles')
@endpush
@push('scripts')

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const now = new Date().toLocaleString("en-US", { timeZone: "America/New_York" });
    
        // Convert to Date object
        const estDate = new Date(now);
        flatpickr("#min_time", {
            enableTime: true,
            dateFormat: "Y-m-d H:i", // Set the format to YYYY-MM-DD HH:MM
            maxDate: estDate
        });
        flatpickr("#max_time", {
            enableTime: true,
            dateFormat: "Y-m-d H:i", // Set the format to YYYY-MM-DD HH:MM
            maxDate: estDate
        });
    });
</script>
<script>
/****  Document Ready ****/
jQuery(document).ready(function() {
    draw_table();
});

document.getElementById('download-button').addEventListener('click', function() {

    if(parseInt($("#agent_own_data_response_count").val()) > 10000){
        toastr.error("You cannot download more than 10,000 items.");
        return false;
    }

    let start = document.getElementById('min_time').value;
    let end = document.getElementById('max_time').value;

    // console.log(start, end);

    if (start && end) {
        // Prepare FormData for POST request
        let formData = new FormData();
        formData.append('min_time', start);
        formData.append('max_time', end);
        formData.append('agent_list', jQuery('#agent_list').val());
        formData.append('_token', '{{ csrf_token() }}'); // Include CSRF token

        fetch("{{ route('dialings.dialingsOwnedLeads_daywise_export') }}", {
            method: 'POST',
            body: formData,
        })
        .then(response => {
            // Check if the response is OK
            if (!response.ok) {
                if (response.status === 404) {
                    throw new Error('No data found');
                } else {
                    throw new Error('An error occurred while downloading the file');
                }
            }
            return response.blob(); // Convert the response into a blob (CSV file)
        })
        .then(blob => {
            // Create a link element for downloading
            let downloadLink = document.createElement('a');
            let url = window.URL.createObjectURL(blob);
            downloadLink.href = url;
            downloadLink.download = 'ownlead.csv'; // The file name to be downloaded
            document.body.appendChild(downloadLink);
            downloadLink.click();
            window.URL.revokeObjectURL(url); // Clean up the object URL
            downloadLink.remove(); // Remove the link element after download
        })
        .catch(error => {
            if (error.message === 'No data found') {
                alert('No data found for the selected time range.');
            } else {
                console.error('Error:', error);
                alert('An error occurred while processing the request.');
            }
        });
    } else {
        alert('Please select both start and end timestamps.');
    }
});




/**** Draw dataTable Ajax ****/
var function_already_called = 0;
function draw_table() {

    jQuery.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
        }
    });
    // stateSave- when there are no filters
    var table = jQuery('#agents_leads_datatable').DataTable({
        // dom: 'lBfrtip',
        processing: true,
        oLanguage: {
            sProcessing: `{!! trim(preg_replace('/\s+/', ' ', view('partials.datatable_loader')->render())) !!}`
        },
        serverSide: true,
        responsive: true,
        autoWidth: false,
        searchHighlight: true,
        pageLength: 25,
        ajax: {
            url: "{{ url('dialings/dialingsOwnedLeads_daywise') }}",
            type: 'GET',
            data: function(d) {
                d.min_time = jQuery('#min_time').val();
                d.max_time = jQuery('#max_time').val();
                d.agent_list = jQuery('#agent_list').val();
            },
            dataSrc: function(json) {
                $("#agent_own_data_response_count").val(json.recordsFiltered);
                // Check if the total record count is 0
                if (json.data.length === 0) {
                    jQuery('#download-button').addClass('notClickAble');
                }
                else{
                    jQuery('#download-button').removeClass('notClickAble');
                }

                // Return the data to display in the table
                return json.data;
            }
        },

        columns: [{
                data: 'DT_RowIndex',
                name: 'DT_RowIndex',
                "targets": [1],
                "searchable": false,
                "orderable": false,
            },
            {
                data: 'id',
                name: 'id',
                'visible': false
            },
            {
                data: 'agent_info',
                name: 'agent_info',
                orderable: false,
                searchable: false,
            },
            {
                data: 'name',
                name: 'name',
                render: function(data, type, row) {
                    // Construct the URL with the id
                    let encryptedId = btoa(row.id);  // base64 encode
                    return `<a href="/leads/edit/${encryptedId}">${data}</a>`;
                }
            },
            {
                data: 'address1',
                name: 'address1'
            },
            {
                data: 'city',
                name: 'city'
            },
            {
                data: 'county',
                name: 'county'
            },
            {
                data: 'ownmarked_at',
                name: 'ownmarked_at',
                orderable: false,
                searchable: false,
                render: function(data) {
                    // Format the date
                    const date = new Date(data);
                    const options = { day: 'numeric', month: 'short', year: '2-digit', hour: '2-digit', minute: '2-digit', hour12: false };
                    const formattedDate = date.toLocaleString('en-GB', options).replace(',', ''); // Remove the comma
                    return formattedDate;
                }
            }
        ],
        order: [
            [5, 'desc']
        ],
        dom: 'rt<"bottom"ip><"clear">',
        rowCallback: function(row, data) {
            


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
    var $thead = jQuery('#agents_leads_datatable thead #serial_no');
    $thead.prepend('<input type="checkbox" class="select-all">');

    // Select all checkboxes 
    jQuery('#agents_leads_datatable').on('change', '.select-all', function() {
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
    jQuery('#agents_leads_datatable').on('change', '.select-row', function() {
        var $checkboxes = jQuery('.select-row');
        jQuery('.select-all').prop('checked', $checkboxes.length === $checkboxes.filter(':checked').length);
        // Log selected checkboxes
        var selectedValues = jQuery('.select-row:checked').map(function() {
            return this.value;
        }).get();

    });
}

function filter_table() {
    jQuery('#agents_leads_datatable').DataTable().draw(true);
}

function reset_table() {
    jQuery('#min_time').val("{{$est_timenow_minus1day}}");
    jQuery('#max_time').val("{{$est_timenow}}");
    jQuery('#agent_list').val("0");
    jQuery('#agents_leads_datatable').DataTable().draw(true);
}

</script>

<!-- jQuery Core -->
<!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> -->
<!-- jQuery Migrate (if needed) -->
<!-- <script src="https://code.jquery.com/jquery-migrate-3.0.0.min.js"></script> -->

<!-- Bootstrap JavaScript (Make sure it's after jQuery) -->
<!-- <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script> -->

<!-- jQuery UI (if necessary) -->
<!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.0/jquery-ui.min.css" integrity="sha512-LDB28UFxGU7qq5q67S1iJbTIU33WtOJ61AVuiOnM6aTNlOLvP+sZORIHqbS9G+H40R3Pn2wERaAeJrXg+/nu6g==" crossorigin="anonymous" referrerpolicy="no-referrer" /> -->

<!-- DataTables Search Highlight -->
<script src="https://cdn.datatables.net/plug-ins/1.11.3/features/searchHighlight/dataTables.searchHighlight.min.js"></script>
<script src="//bartaz.github.io/sandbox.js/jquery.highlight.js"></script>

<!-- jQuery Confirm -->
<!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css"> -->
<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js"></script> -->

@endpush