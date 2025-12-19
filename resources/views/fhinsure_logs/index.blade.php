@extends('layouts.app')
@section('pagetitle', 'Newsletter')
@push('breadcrumbs')
<li class="breadcrumb-item"><a href="{{route('newsletter.index')}}">All Newsletter</a></li>
<li class="breadcrumb-item active">Newsletter</li>
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
                                        <a href="javascript:void(0)" id="bulk_fhinsure_logs_remove"
                                            class="bulk_smtps_remove text-nowrap btn-block rounded-left-0 btn btn-danger btn-sm btn-sm closebtn mr-1">
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
                                                aria-controls="fhinsure_datatable">
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
                                <div id="fhinsure_datatable_filter" class="dataTables_filter search-sec mb-0">
                                    <label
                                        class="d-flex align-items-center justify-content-end mb-0 position-relative"><input
                                            type="search" id="customSearchBox" placeholder="Search for Entries"
                                            aria-controls="fhinsure_datatable" class="form-control">
                                        <i class="fas fa-search position-absolute"></i>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-3 pt-2">
                    <div class="table-container pb-2">
                        <table class="order-column compact hover searchHighlight" id="fhinsure_datatable">
                            <thead class="text-nowrap" style="font-size: 0.93rem;">
                                <tr>
                                    <th id="serial_no"></th>
                                    <th></th>
                                    <th>First Name <span class="arrow"></span></th>
                                    <th>Last Name <span class="arrow"></span></th>
                                    <th>Email <span class="arrow"></span></th>
                                    <th>Phone <span class="arrow"></span></th>
                                    <th>Zip <span class="arrow"></span></th>
                                    <th>Site Name <span class="arrow"></span></th>
                                    <th>Opt for Newsletter <span class="arrow"></span></th>
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
    <!-- Right saved template listing START -->
    <div id="lead-saved-filter-sidebar" class="lead-saved-filter-sidebar">
        <div class="header d-flex align-items-center justify-content-between p-2">
            <span><label>Saved Templates</label></span>
            <a href="javascript:void(0)" class="closebtn" onclick="closeSavedTemplateNav()"><i class="fas fa-times"></i></a>
        </div>
        {{-- <div class="lead-saved-filters d-flex row m-0 mt-2 justify-content-center templateSavedList" id="templateSavedList">
        </div> --}}
    </div>
    <!-- Right saved template listing END -->
    <!-- Send message modal -->
    <div class="message-modal create-modal" id="sendMessageModal">
        <div class="modal-overlay modal-toggle"></div>
        <div class="modal-wrapper modal-transition">
            <div class="modal-header">
                <button class="modal-close modal-toggle message_modal_close_class">
                <small><i class="fas fa-times"></i></small>
                </button>
                <h2 class="modal-heading show-create-form" style="display:none">Add new template</h2>
                <h2 class="modal-heading hide-create-form">Send mesage to <span id="messageToContact"></span></h2>
            </div>
            <div class="modal-body">
                <div class="modal-content">
                    <form id="myFormSendMessage" class="create-template-form">
                        <input type="hidden" id="current_path" value="{{ request()->path() }}">
                        <div class="form-group">
                            <label for="message_content" class="mb-1">Message *:</label>
                            <textarea id="message_content" class="form-control border-dark"
                                name="message_content" row='6' placeholder="Write your message..."></textarea>
                        </div>

                        <div class="hide-create-form d-inline-block">
                            <select id="messageTemplateSelect" class="form-control">
                                <option>-- Templates --</option>
                                <option>Create Template</option>
                                <option>Saved Templates</option>
                            </select>
                        </div>

                        <div class="text-left modal-btns mt-3 pt-3">
                            <input type="button" value="Close" class="btn btn-secondary btn-sm message_modal_close_class">
                            <input type="submit" value="Send" class="btn btn-primary btn-sm hide-create-form-button">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Create sms Template -->

    <div class="email-modal create-modal" id="createSmsTemplate">
        <div class="modal-overlay modal-toggle"></div>
        <div class="modal-wrapper modal-transition">
            <div class="modal-header">
                <button class="modal-close modal-toggle template_modal_close_class">
                    <svg width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M9.29563 8.18259C9.60867 8.49563 9.60867 8.98259 9.29563 9.29563C9.13911 9.45215 8.9478 9.52172 8.73911 9.52172C8.53041 9.52172 8.33911 9.45215 8.18259 9.29563L4.99998 6.11302L1.81737 9.29563C1.66085 9.45215 1.46954 9.52172 1.26085 9.52172C1.05215 9.52172 0.860848 9.45215 0.704326 9.29563C0.391283 8.98259 0.391283 8.49563 0.704326 8.18259L3.88693 4.99998L0.704326 1.81737C0.391283 1.50433 0.391283 1.01737 0.704326 0.704326C1.01737 0.391283 1.50433 0.391283 1.81737 0.704326L4.99998 3.88693L8.18259 0.704326C8.49563 0.391283 8.98259 0.391283 9.29563 0.704326C9.60867 1.01737 9.60867 1.50433 9.29563 1.81737L6.11302 4.99998L9.29563 8.18259Z"
                            fill="black" />
                    </svg>
                </button>
                <h2 class="modal-heading">Add new template</h2>
            </div>
            <div class="modal-body">
                <div class="modal-content">
                    <form id="myFormCreateTemplate" class="create-template-form">
                        <input type="hidden" id="current_path" value="{{ request()->path() }}">
                        <div class="form-group">
                            <label for="template_name" class="mb-1">Template Name *:</label>
                            <input type="text" class="form-control border-dark" id="sms_template_name" name="template_name" placeholder="Enter template name">
                        </div>
                        <div class="form-group">
                            <label for="template_content" class="mb-1">Content *:</label>
                            <textarea id="sms_template_content" class="form-control border-dark ckeditor"
                                name="template_content" row='10' placeholder="Write your content..."></textarea>
                        </div>
                        <p class="font-weight-bold insert-placeholder-label mb-1">Insert Placeholder in Textarea:</p>
                        <div class="placeholders d-flex flex-wrap mb-2">
                            <span class="addPlaceholderTemplate insert-placeholder font-weight-semibold p-2 border small"
                                data-placeholder="{CANDIDATE_FIRST_NAME}">{CANDIDATE_FIRST_NAME}</span>
                            <span class="addPlaceholderTemplate insert-placeholder font-weight-semibold p-2 border small"
                                data-placeholder="{CANDIDATE_LAST_NAME}">{CANDIDATE_LAST_NAME}</span>
                        </div>
                        <p class="small text-secondary mb-1">While writing you email content, click these buttons to insert
                            placeholders.</p>
                        
                        <div class="text-left modal-btns mt-3 pt-3">
                            <input type="button" value="Close" class="btn btn-secondary btn-sm template_modal_close_class">
                            <input type="button" id="saveSmsTemplate" value="Save Template" class="btn btn-primary btn-sm show-create-form-button">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @include('partials.delete-modal')
    @include('partials.email-modal')
    
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
    $('#fhinsure_datatable tbody').on('click', 'tr', function() {
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
    
    var table = jQuery('#fhinsure_datatable').DataTable({
        // dom: 'lBfrtip',
        processing: true,
        oLanguage: {
            sProcessing: `{!! trim(preg_replace('/\s+/', ' ', view('partials.datatable_loader')->render())) !!}`
        },
        serverSide: true,
        responsive: true,
        autoWidth: false,
        searchHighlight: true,
        ajax: {
            url: "{{ url('newsletter/get_fhinsure_log') }}",
            type: 'POST',
            data: function(d) {

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
                data: 'first_name',
                name: 'first_name'
            },
            {
                data: 'last_name',
                name: 'last_name'
            },
            {
                data: 'email',
                name: 'email'
            },
            {
                data: 'phone',
                name: 'phone'
            },
            {
                data: 'zip',
                name: 'zip'
            },
            {
                data: 'site_name',
                name: 'site_name'
            },
            {
                data: 'is_checked',
                name: 'is_checked'
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
    var $thead = jQuery('#fhinsure_datatable thead #serial_no');
    $thead.prepend('<input type="checkbox" class="select-all">');

    // Select all checkboxes 
    jQuery('#fhinsure_datatable').on('change', '.select-all', function() {
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
    jQuery('#fhinsure_datatable').on('change', '.select-row', function() {
        var $checkboxes = jQuery('.select-row');
        jQuery('.select-all').prop('checked', $checkboxes.length === $checkboxes.filter(':checked').length);
        // Log selected checkboxes
        var selectedValues = jQuery('.select-row:checked').map(function() {
            return this.value;
        }).get();

    });

    $('#bulk_fhinsure_logs_remove').on('click', function() {
        jQuery('#bulk_fhinsure_logs_remove').prop('disabled', true);
        jQuery('#fhinsure_datatable_processing').show();
        var selectedValues = $('.select-row:checked').map(function() {
            return this.value;
        }).get();

        if (selectedValues.length > 0) {
            // Open the modal
            $('#deleteModal').modal('show');
        } else {
            toastr.error('Please check at least one checkbox to continue');
            jQuery('#bulk_fhinsure_logs_remove').prop('disabled', false);
            jQuery('#fhinsure_datatable_processing').hide();
            return false;
        }
    });
    $('#deleteModal').on('hide.bs.modal', function() {
        // Clear the selected values when modal is closed
        $('#deleteModal').removeData('selectedValues');
        jQuery('.select-all, .select-row').prop('checked', false);
        jQuery('#bulk_fhinsure_logs_remove').prop('disabled', false);
        jQuery('#fhinsure_datatable_processing').hide();
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
            url: '/newsletter/delete',
            type: 'POST',
            data: {
                selectedValues: selectedValues
            },
            success: function(response) {

                toastr.success(response.message);
                
                jQuery('#fhinsure_datatable').DataTable().draw(true);
                jQuery('.select-all, .select-row').prop('checked', false);
            },
            error: function(xhr, status, error) {
                toastr.error("Something went wrong.Please contact administrator.");
            },
            complete: function() {
                // Re-enable the button and hide loader after AJAX request completes
                jQuery('#bulk_fhinsure_logs_remove').prop('disabled', false);
                jQuery('#fhinsure_datatable_processing').hide();
            }
        });
    }
}

$('.form-check-input').change(function() {
    let columnValue = $(this).val();
    let isChecked = this.checked;

    // Save the state of the checkbox in sessionStorage
    sessionStorage.setItem(columnValue, isChecked);
    let columnIndex = jQuery('#fhinsure_datatable').DataTable().column(columnValue + ':name').index();
    if (isChecked) {
        jQuery('#fhinsure_datatable').DataTable().column(columnIndex).visible(true);
    } else {
        jQuery('#fhinsure_datatable').DataTable().column(columnIndex).visible(false);
    }
});
ClassicEditor
    .create(document.querySelector('#email_content'))
    .then(editor => {
        theEmailEditor = editor; // Save for later use.
    })
    .catch(error => {
        console.error(error);
});
ClassicEditor
    .create(document.querySelector('#sms_template_content'))
    .then(editor => {
        theTemplateEditor = editor; // Save for later use.
    })
    .catch(error => {
        console.error(error);
});
function applySavedTemplate(templateId, singleContactId) {
    // getting data from contact table
    $.ajax({
        url: `/newsletter/singleDetail/logDetail/${singleContactId}`,
        method: 'GET',
        success: async function(data) {
            let responseData;
            try {
                responseData = JSON.parse(data);

                // getting template-content from template id
                let singleTemplateWithJson = await singleTemplateDetail(templateId);
                let jsonTemplate = JSON.parse(singleTemplateWithJson);

                let template_content = jsonTemplate.response[0].template_content;
                let email_content = jsonTemplate.response[0].template_content;
                let template_subject = jsonTemplate.response[0].template_subject;
                
                let c_first_name  = responseData.response.first_name || '';
                let c_last_name   = responseData.response.last_name || '';

                // console.log(c_first_name);

                // txtToElem(template_content);
                template_content = template_content.replace(/{CANDIDATE_FIRST_NAME}/g, c_first_name);
                template_content = template_content.replace(/{CANDIDATE_LAST_NAME}/g, c_last_name);
                template_content = template_content.replace(/{BUSINESS_NAME}/g, "");
                // console.log(template_content);
                
                // Create a temporary div element
                let tempDiv = document.createElement('div');
                tempDiv.innerHTML = template_content;

                const contact_mode = localStorage.getItem('contact_mode');
                if (contact_mode && contact_mode == "email") {
                    let renderedContent = tempDiv.innerHTML || '';
                    theEmailEditor.setData(renderedContent);
                    $(`#email_subject`).val(template_subject);
                } else if(contact_mode && contact_mode == "message") {
                    // Get the plain text content of the temporary div
                    let renderedContent = tempDiv.textContent || tempDiv.innerText || '';
                    $(`#message_content`).val(renderedContent);
                }
            } catch (error) {
                console.log(error);
                toastr.error('Invalid server response');
                return;
            }
            if (responseData.status == '200') {
                // toastr.success('Contact detail showed successfully.');
                closeSavedTemplateNav();
            } else {
                toastr.error('Unexpected server response');
                return;
            }
        },
        error: function(xhr, status, error) {
            let jsonResponse = JSON.parse(xhr.responseText);
            toastr.error(jsonResponse.response);
            return;
        }
    });
}

function singleTemplateDetail(singleTemplateId) {
    return new Promise((resolve, reject) => {
        $.ajax({
            url: `/template/singleDetail/templateDetail/${singleTemplateId}`,
            method: 'GET',
            success: function(data) {
                resolve(data);
            },
            error: function(xhr, status, error) {
                reject(xhr.responseText);
                return;
            }
        });
    })
}
$('.template_modal_close_class').click(function() {
    $('.template-modal').removeClass('is-visible');
    $('body').removeClass('overflow-hidden');
});

function closeSavedTemplateNav() {
    // alert('close');
    $("#lead-saved-filter-sidebar").removeClass('show');
    $("#emailModal").removeClass('modal-disable');
    $("#sendMessageModal").removeClass('modal-disable');
    $("#messageTemplateSelect").val('-- Templates --');
    $("#emailTemplateSelect").val('-- Templates --');
    $("#lead-saved-filter-sidebar").find('.templateSavedList').remove();
    templateContentAppended = false;
}

    $(document).on("click", '.openEmailPopup',function(){
        const emailContactId = $(this).data("contact-id");
        const emailToContact = $(this).data("email-to-contact");
        $("#emailToContact").text(emailToContact);
        localStorage.setItem("current_selected_contact_id", emailContactId);
        localStorage.setItem("contact_mode", 'email');
        $('#emailModal').addClass('is-visible');
        $('body').addClass('overflow-hidden');
    });
    
</script>

@include('partials.email-modal-script')
@include('partials.message-modal-script')

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