@extends('layouts.app')
@section('pagetitle', 'SMS Provider Management')
@push('breadcrumbs')
<li class="breadcrumb-item active">SMS Provider Management</li>
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
            <div class="card-body p-0 pb-3">
                <div class="p-3">
                    <div class="row">
                        <div class="col-lg-12 margin-tb table-top-sec">
                            <div class="left-content d-flex align-items-center">
                                @can('agent-create')
                                    <a class="btn btn-info btn-sm px-2 mb-3 mb-md-0"
                                    href="{{ route('smsprovider.index') }}"
                                    ><i class="fas fa-arrow-circle-left"></i> Back</a>
                                @else
                                    <a class="btn btn-info btn-sm px-2 mb-3 mb-md-0"
                                    href="{{ route('leads.index') }}"
                                    ><i class="fas fa-arrow-circle-left"></i> Back</a>
                                @endcan
                            </div>
                            <div class="d-flex align-items-center justify-content-between mt-3">
                                <div class="d-flex flexwrap-wrap action-dropdown">
                                    <div class="dropdown">
                                        <button class="btn btn-info btn-sm dropdown-toggle" type="button" id="actionbtn"
                                            data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            Actions
                                        </button>
                                        <div class="dropdown-menu p-0 m-0 text-nowrap" aria-labelledby="actionbtn">
                                            <button class="btn btn-teal rounded-0 btn-sm btn-block m-0"
                                                type="button" data-bs-toggle="collapse" data-bs-target="#filterByDate"
                                                aria-expanded="true" aria-controls="filterByDate">
                                                <i class="fas fa-filter"></i>
                                                <span class="d-none d-md-inline">Filter Your Search</span>
                                            </button>
                                        </div>
                                    </div>
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
                                    <div id="smsprovider_datatable_filter" class="dataTables_filter search-sec mb-0" style="display:none;">
                                        <label
                                            class="d-flex align-items-center justify-content-end mb-0 position-relative"><input
                                                type="search" id="customSearchBox" placeholder="Search for Entries"
                                                aria-controls="smsprovider_datatable" class="form-control">
                                            <i class="fas fa-search position-absolute"></i>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="collapse @if(!empty($type)) {{'show'}} @endif" id="filterByDate">
                        <div class="card card-body mb-0 p-2 rounded-top-0 box-shadow-btm">
                            <div class="search-filter">
                                @include('smsprovider.partials.search-filter')
                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-3 pt-2">
                    <div class="table-container pb-2">
                        <table class="order-column compact hover searchHighlight smsproviderlist_datatable" id="smsproviderlist_datatable">
                            <thead style="font-size: 0.93rem;">
                                <tr>
                                    <th style="width: 14%;">Name<span class="arrow"></span></th>
                                    <th style="width: 14%;">Mobile Number<span class="arrow"></span></th>
                                    <th style="width: 12%;">Outbound Count<span class="arrow"></span></th>
                                    <th style="width: 12%;">Inbound Count<span class="arrow"></span></th>
                                    <th style="width: 14%;">Last Out Time<span class="arrow"></span></th>
                                    <th style="width: 14%;">Last In Time<span class="arrow"></span></th>
                                    <th style="width: 8%;">Action</th>
                                    <th style="width: 7%;">Complete</th>
                                    <th style="width: 5%;">Stop</th>
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
<!-- chat content here -->
<div id="chat-wrapper" class="position-fixed d-flex justify-content-end align-items-end pr-4"></div>


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

<!-- Add new template START -->
<div class="template-modal create-modal">
    <div class="modal-overlay modal-toggle"></div>
    <div class="modal-wrapper modal-transition">
        <div class="modal-header">
            <button class="modal-close modal-toggle template_modal_close_class text-xs" id="template_modal_close">
                <!-- <svg width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M9.29563 8.18259C9.60867 8.49563 9.60867 8.98259 9.29563 9.29563C9.13911 9.45215 8.9478 9.52172 8.73911 9.52172C8.53041 9.52172 8.33911 9.45215 8.18259 9.29563L4.99998 6.11302L1.81737 9.29563C1.66085 9.45215 1.46954 9.52172 1.26085 9.52172C1.05215 9.52172 0.860848 9.45215 0.704326 9.29563C0.391283 8.98259 0.391283 8.49563 0.704326 8.18259L3.88693 4.99998L0.704326 1.81737C0.391283 1.50433 0.391283 1.01737 0.704326 0.704326C1.01737 0.391283 1.50433 0.391283 1.81737 0.704326L4.99998 3.88693L8.18259 0.704326C8.49563 0.391283 8.98259 0.391283 9.29563 0.704326C9.60867 1.01737 9.60867 1.50433 9.29563 1.81737L6.11302 4.99998L9.29563 8.18259Z"
                        fill="black" />
                </svg> -->
                <i class="fas fa-times"></i>
            </button>
            <h2 class="modal-heading">Add new template</h2>
        </div>
        <div class="modal-body">
            <div class="modal-content">
                <form id="myFormAddNewTemplate" class="create-template-form">
                    <div class="form-group">
                        <label for="template_name">Template Name:</label>
                        <input type="text" class="form-control border-dark" id="template_name" name="template_name">
                    </div>
                    <div class="form-group">
                        <label for="template_content">Template Content:</label>
                        <textarea  id="template_content" class="form-control border-dark ckeditor"
                            name="template_content" row='10' placeholder="Write your content..."></textarea>
                    </div>
                    <p class="font-weight-bold mb-2">Insert Placeholder in Textarea:</p>
                    <div class="placeholders d-flex flex-wrap mb-2">
                        <span class="insert-placeholder font-weight-semibold p-2 border small"
                            data-placeholder="{CANDIDATE_FIRST_NAME}">{CANDIDATE_FIRST_NAME}</span>
                        <span class="insert-placeholder font-weight-semibold p-2 border small"
                            data-placeholder="{CANDIDATE_LAST_NAME}">{CANDIDATE_LAST_NAME}</span>
                        <span class="insert-placeholder font-weight-semibold p-2 border small"
                            data-placeholder="{BUSINESS_NAME}">{BUSINESS_NAME}</span>
                    </div>
                    <p class="small text-secondary">While writing you message template, click these buttons to insert
                        placeholders.</p>
                    <div class="text-left modal-btns mt-3 pt-3">
                        <input type="button" value="Close" class="btn btn-secondary btn-sm template_modal_close_class">
                        <input type="submit" value="Submit" class="btn btn-primary btn-sm">
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
<!-- Add new template END -->

<!-- /.content -->
@endsection
@push('styles')
@endpush
@push('scripts')

<script>
/****  Document Ready ****/
jQuery(document).ready(function() {
    $('.numeric-input').on('input paste', function() {
        // Delay the processing to allow the paste event to complete
        setTimeout(() => {
            this.value = this.value.replace(/[^0-9]/g, '');
        }, 0);
    });

    draw_table();
    $('#smsproviderlist_datatable tbody').on('click', 'tr', function() {
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

$(document).ready(function() {
    $(document).on("click", ".name_area", function() {
        let contact_id = $(this).data('contact_id');
        let lead_id = $(this).data('lead_id');
        // sessionStorage.setItem('lastLeadsManagementUrl', '');

        // console.log(contact_id, lead_id);
        let encryptedId = btoa(lead_id);  // base64 encode
        let url = '{{ url("leads/edit") }}/' + encryptedId;

        // Open the new page in a new tab
        window.open(url, '_blank'); 
    });

    $(document).on('click', '.mark_comolete_button', function() {
        var contact_id = $(this).data("contact_id");
        $("#chat_confirm_value").val(contact_id);
        $('#chat-complete-model').modal('show');
    });

    $(document).on('click', '.mark_stop_button', function() {
        var contact_id = $(this).data("contact_id");
        $("#chat_stop_value").val(contact_id);
        $('#chat-stop-model').modal('show');
    });

    $(document).on('click', '#confirm_chat', function() {
        $('#confirm_chat').prop('disabled', true);
        var contact_id = $("#chat_confirm_value").val();
        $.ajax({
            url: '/contacts/mark_comolete_chat',
            type: 'POST',
            data: {
                contact_id: contact_id
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message);
                }
                $('#chat-complete-model').modal('hide');
                setTimeout(function(){
                    location.reload(); // Reload the page
                }, 6000); 
            },
            error: function(xhr, status, error) {
                toastr.error("Something went wrong.Please contact administrator.");
            },
            complete: function() {
                // Re-enable the button and hide loader after AJAX request completes
                $('#confirm_chat').prop('disabled', false);
            }
        });
        // console.log(contact_id);
    });

    $(document).on('click', '#stop_chat', function() {
        $('#stop_chat').prop('disabled', true);
        var contact_id = $("#chat_stop_value").val();
        $.ajax({
            url: '/contacts/mark_stop_chat',
            type: 'POST',
            data: {
                contact_id: contact_id
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message);
                }
                $('#chat-complete-model').modal('hide');
                setTimeout(function(){
                    location.reload(); // Reload the page
                }, 6000); 
            },
            error: function(xhr, status, error) {
                toastr.error("Something went wrong.Please contact administrator.");
            },
            complete: function() {
                // Re-enable the button and hide loader after AJAX request completes
                $('#stop_chat').prop('disabled', false);
            }
        });
        // console.log(contact_id);
    });
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
    var table = jQuery('#smsproviderlist_datatable').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        autoWidth: false,
        searchHighlight: true,
        pageLength: 20, // Set the default number of rows to display
        ajax: {
            url: "{{ url('smsproviderlist/data') }}",
            type: 'POST',
            data: function(d) {
                d.outbound_type_selection = jQuery('#outbound_type_selection').val();
                d.outbound_type_value = jQuery('#outbound_type_value').val();
                d.inbound_type_selection = jQuery('#inbound_type_selection').val();
                d.inbound_type_value = jQuery('#inbound_type_value').val();
                d.through_sms_provider_flag = jQuery('#message_type_selection').val();
                d.user_type_selection = jQuery('#user_type_selection').val();
            }
        },
        columns: [
            { data: 'name_area', name: 'name_area',searchable: false},
            { data: 'c_phone', name: 'c_phone' ,searchable: false},
            { data: 'outbound_count', name: 'outbound_count' ,searchable: false},
            { data: 'inbound_count', name: 'inbound_count' ,searchable: false},
            {
                data: 'last_out_time',
                name: 'last_out_time',
                searchable: false,
                render: function(data) {
                    return formatDate(data);
                }
            },
            {
                data: 'last_in_time',
                name: 'last_in_time',
                searchable: false,
                render: function(data) {
                    return formatDate(data);
                }
            },
            { data: 'actions', name: 'actions' ,searchable: false, orderable: false},
            { data: 'mark_complete', name: 'mark_complete' ,searchable: false, orderable: false},
            { data: 'mark_stop', name: 'mark_stop' ,searchable: false, orderable: false}
        ],
        order: [[5, 'desc']], // Sort by last in time desc
        dom: 'rt<"bottom"ip><"clear">',
        initComplete: function(settings, json) {
            if(function_already_called == 0){
                callChatOpenFunction();
                function_already_called++;
            }
        }
    });

    function callChatOpenFunction() {
        var chat_type = parseInt("{{ $type }}"); 
        var chat_id = parseInt("{{ $id }}"); 

        if(chat_id != 0){
            if(chat_type == 1){
                var contactChatElement = document.querySelector('.contact_chat_' + chat_id);
                if (contactChatElement) {
                    contactChatElement.click();
                }
            }
            else if(chat_type == 2){
                var newsletterChatElement = document.querySelector('.newsletters_chat_' + chat_id);
                if (newsletterChatElement) {
                    newsletterChatElement.click();
                }
            }
        }
    }

    function formatDate(dateString) {
        if(dateString && dateString != ''){
            const date = new Date(dateString);
    
            // Get day, month, year, and time components
            const day = date.getDate();
            const month = date.toLocaleString('default', { month: 'short' });
            const year = date.getFullYear();
            const hours = date.getHours();
            const minutes = date.getMinutes();
            const ampm = hours >= 12 ? 'PM' : 'AM';
            
            // Convert hours from 24-hour to 12-hour format
            const formattedHours = hours % 12 || 12; // if hours is 0, set to 12
            const formattedMinutes = minutes < 10 ? '0' + minutes : minutes; // pad minutes with leading zero if needed

            // Get day suffix
            const suffix = (day % 10 === 1 && day !== 11) ? 'st' : 
                           (day % 10 === 2 && day !== 12) ? 'nd' :
                           (day % 10 === 3 && day !== 13) ? 'rd' : 'th';

            // Construct the final formatted string
            return `${day}${suffix} ${month} ${year}, ${formattedHours}:${formattedMinutes} ${ampm}`;
        }
        return '';
        
    }



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
    var $thead = jQuery('#smsproviderlist_datatable thead #serial_no');
    $thead.prepend('<input type="checkbox" class="select-all">');

    // Select all checkboxes 
    jQuery('#smsproviderlist_datatable').on('change', '.select-all', function() {
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
    jQuery('#smsproviderlist_datatable').on('change', '.select-row', function() {
        var $checkboxes = jQuery('.select-row');
        jQuery('.select-all').prop('checked', $checkboxes.length === $checkboxes.filter(':checked').length);
        // Log selected checkboxes
        var selectedValues = jQuery('.select-row:checked').map(function() {
            return this.value;
        }).get();

    });
}

function filter_table() {
    jQuery('#smsproviderlist_datatable').DataTable().draw(true);
}

function reset_table() {
    jQuery('#outbound_type_selection').val('');
    jQuery('#outbound_type_value').val('');
    jQuery('#inbound_type_selection').val('');
    jQuery('#inbound_type_value').val('');
    jQuery('#message_type_selection').val('');
    jQuery('#user_type_selection').val('1');
    jQuery('#smsproviderlist_datatable').DataTable().draw(true);
}

let templateContentAppendedsms = false;
$(document).on('change', '#templateSelect', function() {
    $(".noDataInSavedTemplate").remove();
    let self = this;
    // let chatContactId = '';
    let chatContactId = $(self).siblings('.chat-send').attr('id').replace(
        "chat_send_", "");
    let isNewsletterContact = $(self).siblings('.chat-send').attr('data-is_newsletter_contact');
    localStorage.setItem("current_selected_contact_id", chatContactId);
    localStorage.setItem("isNewsletterContact", isNewsletterContact);
    if (self.value === 'Saved Templates') {
        $.ajax({
            url: `/template/listByUserid/alldata`,
            method: 'GET',
            success: function(data) {
                let templateData = JSON.parse(data);
                if (templateData.response.length === 0 && !templateContentAppendedsms) {
                    $("#lead-saved-filter-sidebar").append(`
						<div class="lead-saved-filters d-flex row m-0 justify-content-center mt-3 noDataInSavedTemplate">
							There is no data in list
						</div>`);
                    $("#lead-saved-filter-sidebar").addClass('show');
                    //$("#lead-saved-filter-sidebar").css('z-index', '99999');
                    templateContentAppendedsms = true;
                } else {
                    templateData.response.forEach(template => {
                        let chatContent = template.template_content;
                        if (!templateContentAppendedsms) {
                            // $("#noDataInSavedTemplate").empty();
                            // $(".lead-saved-filters").append(`
                            if(template.delete_permission) {
                                $("#lead-saved-filter-sidebar").append(`
                                    <div class="lead-saved-filters d-flex row m-0 justify-content-center templateSavedList" id="templateSavedList">
                                        <div class="filter d-flex align-items-center py-1 filter_id_${template.id} w-100">
                                            <div class="title d-flex">
                                                <label>${template.template_name}</label>
                                            </div>
                                            <button class="btn btn-success btn-sm mr-2 apply" type="button" onclick="applySavedTemplate('${template.id}', '${localStorage.getItem('current_selected_contact_id')}')">
                                                <i class="fas fa-check"></i></button>
                                            <button class="btn btn-danger btn-sm closebtn mr-1" type="button" onclick="deleteSavedTemplate('${template.id}')">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>`
                                );
                            } else {
                                $("#lead-saved-filter-sidebar").append(`
                                    <div class="lead-saved-filters d-flex row m-0 justify-content-center templateSavedList" id="templateSavedList">
                                        <div class="filter d-flex align-items-center py-1 filter_id_${template.id} w-100">
                                            <div class="title d-flex">
                                                <label>${template.template_name}</label>
                                            </div>
                                            <button class="btn btn-success btn-sm mr-2 apply" type="button" onclick="applySavedTemplate('${template.id}', '${localStorage.getItem('current_selected_contact_id')}')">
                                                <i class="fas fa-check"></i></button>
                                        </div>
                                    </div>`
                                );
                            }
                        }

                        $("#lead-saved-filter-sidebar").addClass('show');
                        // $("#lead-saved-filter-sidebar").css('z-index', '99999');
                    });
                }
                templateContentAppendedsms = true;
            },

            error: function(xhr, status, error) {
                let jsonResponse = JSON.parse(xhr.responseText);
                toastr.error(jsonResponse.error);
                return false;
            }
        });
        // alert('Selected Template: ' + self.value);
    } else if (self.value == '-- Templates --') {
        closeSavedTemplateNav();
    }
});
function applySavedTemplate(templateId, singleContactId) {
    // getting data from contact table
    let isNewsletterContact = localStorage.getItem("isNewsletterContact");
    if(isNewsletterContact == "yes") 
        var data_url = `/newsletter/singleDetail/logDetail/${singleContactId}`;
    else 
        var data_url = `/template/singleDetail/contactDetail/${singleContactId}`;

    $.ajax({
        url: data_url,
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
             
                if(isNewsletterContact == "yes") {
                    let c_first_name  = responseData.response.first_name || '';
                    let c_last_name   = responseData.response.last_name || '';
                    template_content = template_content.replace(/{CANDIDATE_FIRST_NAME}/g, c_first_name);
                    template_content = template_content.replace(/{CANDIDATE_LAST_NAME}/g, c_last_name); 
                    template_content = template_content.replace(/{BUSINESS_NAME}/g, "");
                } else {
                    let c_first_name  = responseData.response[0].c_first_name;
                    let c_last_name   = responseData.response[0].c_last_name;
                    let business_name = responseData.response[0].leads.name;
                    template_content = template_content.replace(/{CANDIDATE_FIRST_NAME}/g, c_first_name);
                    template_content = template_content.replace(/{CANDIDATE_LAST_NAME}/g, c_last_name); 
                    template_content = template_content.replace(/{BUSINESS_NAME}/g, business_name);
                }     
                // txtToElem(template_content);
                          

                // Create a temporary div element
                let tempDiv = document.createElement('div');
                tempDiv.innerHTML = template_content;

                // Get the plain text content of the temporary div
                let renderedContent = tempDiv.textContent || tempDiv.innerText || '';

                // Set the plain text content as the value of the textarea
                $(`#chat_footer_${singleContactId} textarea.text-input`).val(renderedContent);
                

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

function closeSavedTemplateNav() {
    // alert('close');
    $("#lead-saved-filter-sidebar").removeClass('show');
    $("#emailModal").removeClass('modal-disable');
    $(".chat-template").val('-- Templates --');
    $("#emailTemplateSelect").val('-- Templates --');
    $("#lead-saved-filter-sidebar").find('.templateSavedList').remove();
    templateContentAppendedsms = false;
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
</script>
<!-- Chat script will be added here -->
@include('partials.email-modal-script')
@include('partials.chat-scripts')

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