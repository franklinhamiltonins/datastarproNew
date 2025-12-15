<script>

    $(document).on("click", '.sendMessagePopup',function(){
        const messageContactId = $(this).data("contact-id");
        const messageToContact = $(this).data("message-to-contact");
        $("#messageToContact").text(messageToContact);
        localStorage.setItem("contact_mode", 'message');
        localStorage.setItem("current_selected_contact_id", messageContactId);
        $('#sendMessageModal').addClass('is-visible');
        $('body').addClass('overflow-hidden');
    });

    $('.message_modal_close_class').click(function() {
        $('.message-modal').removeClass('is-visible');
        $('body').removeClass('overflow-hidden');
    });

    $('.template_modal_close_class').click(function() {
        $('#createSmsTemplate').removeClass('is-visible');
        $('body').removeClass('overflow-hidden');
    });

    $('.addPlaceholderTemplate').click(function() {
        let placeholder = $(this).data('placeholder');
        theTemplateEditor.model.change(writer => {
            let selection = theTemplateEditor.model.document.selection;
            let position = selection.getFirstPosition();
            writer.insertText(placeholder, position);
        });
        theTemplateEditor.editing.view.focus();
    });

    $(document).on('change', '#messageTemplateSelect', function() {
        $(".noDataInSavedTemplate").remove();
        let self = this;        
        if (self.value === 'Saved Templates') {
            $.ajax({
                url: `/template/listByUserid/alldata`,
                method: 'GET',
                success: function(data) {
                    let templateData = JSON.parse(data);
                    if (templateData.response.length === 0 && !templateContentAppended) {
                        $("#lead-saved-filter-sidebar").append(`
                            <div class="lead-saved-filters d-flex row m-0 justify-content-center mt-3 noDataInSavedTemplate">
                                There is no data in list
                            </div>`);
                        $("#lead-saved-filter-sidebar").addClass('show');
                        //$("#lead-saved-filter-sidebar").css('z-index', '99999');
                        templateContentAppended = true;
                    } else {
                        templateData.response.forEach(template => {
                            let chatContent = template.template_content;
                            if (!templateContentAppended) {
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
                        });
                    }
                    templateContentAppended = true;
                },

                error: function(xhr, status, error) {
                    let jsonResponse = JSON.parse(xhr.responseText);
                    toastr.error(jsonResponse.error);
                    return false;
                }
            });
            // alert('Selected Template: ' + self.value);
        }
        else if (self.value == 'Create Template') {
            closeSavedTemplateNav();
            $('.message_modal_close_class').click();
            $('#createSmsTemplate').addClass('is-visible');
            $('body').addClass('overflow-hidden');

        } else if (self.value == '-- Templates --') {
            closeSavedTemplateNav();
        }
    });

    
    $('#myFormSendMessage').submit(function(event) {
        event.preventDefault();

        let message_content = $('#message_content').val();
        let current_path = $("#current_path").val();
        let contact_id = localStorage.getItem("current_selected_contact_id");
        
        if (message_content === '') {
            toastr.error('Content should not be blank');
            return false; // Prevent form submission
        }
        $(".hide-create-form-button").prop("disabled",true);
        $(".hide-create-form-button").val("Please wait...");

        let formData = $('#myFormSendMessage').serializeArray();
        console.log(formData);
        formData.push({
            name: 'message_content',
            value: message_content
        });
        formData.push({
            name: 'current_path',
            value: current_path
        });
        formData.push({
            name: 'contact_id',
            value: contact_id
        });

        $.ajax({
            type: 'POST',
            url: '/newsletter/message',
            data: formData,
            success: function(response) {
                try {
                    let responseData = response;
                    if (responseData.status == '200') {
                        $('#message_content').val("");
                        toastr.success(responseData.message);
                        $('.message_modal_close_class').click();
                    } else if (responseData.status == '500') {
                        toastr.error(responseData.response);
                        $(".hide-create-form-button").prop("disabled",false);
                        $(".hide-create-form-button").val("Send");
                        return false;
                    } else {
                        toastr.error('Unexpected server response');
                        $(".hide-create-form-button").prop("disabled",false);
                        $(".hide-create-form-button").val("Send");
                        return false;
                    }

                } catch (error) {
                    console.log(error);
                    toastr.error('Internal server error');
                    $(".hide-create-form-button").prop("disabled",false);
                    $(".hide-create-form-button").val("Send");
                    return false;
                }
                $(".hide-create-form-button").prop("disabled",false);
                $(".hide-create-form-button").val("Send");
            },
            error: function(xhr, status, error) {
                let jsonResponse = JSON.parse(xhr.responseText);
                toastr.error(jsonResponse.response);
                $(".hide-create-form-button").prop("disabled",false);
                $(".hide-create-form-button").val("Send");
                return false;
            }
        })  
    });

    $('#saveSmsTemplate').click(function(event) {
        event.preventDefault();

        let editorContent = theTemplateEditor.getData().trim();
        var template_name = $('#sms_template_name').val();
        $('#myFormCreateTemplate').siblings("#sms_template_content").val(editorContent);
        if (template_name === '') {
            toastr.error('Template name should not be blank');
            return false; // Prevent form submission
        }
        if (editorContent === '') {
            toastr.error('Template content should not be blank');
            return false; // Prevent form submission
        }

        let formData = $('#myFormCreateTemplate').serializeArray();
        formData.push({
            name: 'template_content',
            value: editorContent
        });
        formData.push({
            name: 'template_name',
            value: template_name
        });
        formData.push({
            name: 'template_type',
            value: 'sms'
        });
        $.ajax({
            type: 'POST',
            url: '/template/addNewTemplate/addNew',
            data: formData,
            success: function(response) {
                try {
                    let responseData = response;
                    if (responseData.status == '200') {

                        toastr.success(responseData.message);
                        $("#myFormCreateTemplate")[0].reset();
                        theTemplateEditor.setData('');
                        $('.template_modal_close_class').click();

                    } else if (responseData.status == '422') {
                        responseData.errors.forEach((e) => {
                            toastr.error(e);
                        });
                        $(".hide-create-form-button").prop("disabled",false);
                        $(".hide-create-form-button").val("Send");
                        return false;
                    } else if (responseData.status == '500') {
                        toastr.error(responseData.response);
                        $(".hide-create-form-button").prop("disabled",false);
                        $(".hide-create-form-button").val("Send");
                        return false;
                    } else {
                        toastr.error('Unexpected server response');
                        $(".hide-create-form-button").prop("disabled",false);
                        $(".hide-create-form-button").val("Send");
                        return false;
                    }

                } catch (error) {
                    toastr.error('Internal server error');
                    $(".hide-create-form-button").prop("disabled",false);
                    $(".hide-create-form-button").val("Send");
                    return false;
                }
            },
            error: function(xhr, status, error) {
                let jsonResponse = JSON.parse(xhr.responseText);
                toastr.error(jsonResponse.response);
                $(".hide-create-form-button").prop("disabled",false);
                $(".hide-create-form-button").val("Send");
                return false;
            }
        });
    });

</script>