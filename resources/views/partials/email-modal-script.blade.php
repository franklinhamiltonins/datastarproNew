<script>

    $('.email_modal_close_class').on('click',function() {
        $("#myFormSendEmail")[0].reset();
        $(".show-create-form").css('display', 'none');
        $(".show-create-form-button").css('display', 'none');
        $(".hide-create-form").css('display', 'block');
        $(".hide-create-form-button").css('display', 'inline-block');
        // theEmailEditor.setData('');
        $('#emailModal').removeClass('is-visible');
        $('body').removeClass('overflow-hidden');
        localStorage.removeItem("contact_mode");
    });

    $('.addPlaceholder').on('click',function() {
        let placeholder = $(this).data('placeholder');
        theEmailEditor.model.change(writer => {
            let selection = theEmailEditor.model.document.selection;
            let position = selection.getFirstPosition();
            writer.insertText(placeholder, position);
        });
        theEmailEditor.editing.view.focus();
    });

    // template coding
    let templateContentAppended = false;
    $(document).on('change', '#emailTemplateSelect', function() {
        $(".noDataInSavedTemplate").remove();
        let self = this;
        if (self.value === 'Saved Templates') {
            $(".show-create-form").css('display', 'none');
            $(".show-create-form-button").css('display', 'none');
            $(".hide-create-form").css('display', 'block');
            $(".hide-create-form-button").css('display', 'inline-block');

            $.ajax({
                url: `/template/listByUserid/alldata`,
                method: 'POST',
                data: {
                    type: "mail"
                },
                success: function(data) {
                    let templateData = JSON.parse(data);
                    if (templateData.response.length === 0 && !templateContentAppended) {
                        $("#lead-saved-filter-sidebar").append(`
                            <div class="lead-saved-filters d-flex row m-0 justify-content-center mt-3 noDataInSavedTemplate">
                                There is no data in list
                            </div>`);
                    $("#lead-saved-filter-sidebar").addClass('show');
                    $("#emailModal").addClass('modal-disable');

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
                                                <i class="fas fa-check"></i>
                                            </button>
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
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </div>
                                    </div>`
                                );
                            }
                            
                        }

                        $("#lead-saved-filter-sidebar").addClass('show');
                        $("#emailModal").addClass('modal-disable');
                        // $("#lead-saved-filter-sidebar").css('z-index', '99999');
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
        }
        // else if (self.value !== 'Saved Templates') {
        else if (self.value == 'Create Template') {
            closeSavedTemplateNav();
            $("#myFormSendEmail")[0].reset();
            theEmailEditor.setData('');
            $(".show-create-form").css('display', 'block');
            $(".show-create-form-button").css('display', 'inline-block');
            $(".hide-create-form").css('display', 'none');
            $(".hide-create-form-button").css('display', 'none');
            $('body').addClass('overflow-hidden');

        } else if (self.value == '-- Templates --') {
            closeSavedTemplateNav();
        }
    });

    $('#saveEmailTemplate').on('click',function(event) {
        event.preventDefault();

        let editorContent = theEmailEditor.getData().trim();
        var email_subject = $('#email_subject').val();
        $('#myFormSendEmail').siblings("#email_content").val(editorContent);
        if (editorContent === '') {
            toastr.error('Template content should not be blank');
            return false; // Prevent form submission
        }

        let formData = $('#myFormSendEmail').serializeArray();
        formData.push({
            name: 'template_content',
            value: editorContent
        });
        formData.push({
            name: 'template_type',
            value: 'mail'
        });
        formData.push({
            name: 'template_subject',
            value: email_subject
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
                        $("#myFormSendEmail")[0].reset();
                        $(".show-create-form").css('display', 'none');
                        $(".show-create-form-button").css('display', 'none');
                        $(".hide-create-form").css('display', 'block');
                        $(".hide-create-form-button").css('display', 'inline-block');
                        theEmailEditor.setData('');

                    } else if (responseData.status == '422') {
                        responseData.errors.forEach((e) => {
                            toastr.error(e);
                        });
                        return false;
                    } else if (responseData.status == '500') {
                        toastr.error(responseData.response);
                        return false;
                    } else {
                        toastr.error('Unexpected server response');
                        return false;
                    }

                } catch (error) {
                    toastr.error('Internal server error');
                    return false;
                }
            },
            error: function(xhr, status, error) {
                let jsonResponse = JSON.parse(xhr.responseText);
                toastr.error(jsonResponse.response);
                return false;
            }
        });
    });

    $('#myFormSendEmail').on('submit',function(event) {
            event.preventDefault();

            let editorContent = theEmailEditor.getData().trim();
            let email_subject = $('#email_subject').val();
            let current_path = $("#current_path").val();
            let contact_id = localStorage.getItem("current_selected_contact_id");
            $('#myFormSendEmail').siblings("#email_content").val(editorContent);
            if (email_subject === '') {
                toastr.error('Subject should not be blank');
                return false; // Prevent form submission
            }
            if (editorContent === '') {
                toastr.error('Content should not be blank');
                return false; // Prevent form submission
            }
            $(".hide-create-form-button").prop("disabled",true);
            $(".hide-create-form-button").val("Please wait...");

            let formData = $('#myFormSendEmail').serializeArray();
            console.log(formData);
            formData.push({
                name: 'template_content',
                value: editorContent
            });
            formData.push({
                name: 'template_subject',
                value: email_subject
            });
            formData.push({
                name: 'contact_id',
                value: contact_id
            });
            formData.push({
                name: 'current_path',
                value: current_path
            });
            $.ajax({
                type: 'POST',
                url: '/contact/mail',
                data: formData,
                success: function(response) {
                    try {
                        let responseData = response;
                        if (responseData.status == '200') {
                            theEmailEditor.setData('');
                            toastr.success(responseData.message);
                            $('.email_modal_close_class').click();
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

    function deleteSavedTemplate(id) {
        // alert('deleteSavedTemplate', id, name);
        $.confirm({
            title: 'Delete Record?',
            content: 'Are you sure You want to delete the record?',
            type: 'white',
            buttons: {
                ok: {
                    text: "DELETE",
                    btnClass: 'btn btn-danger',
                    keys: ['enter'],
                    action: function() {
                        deleteTemplate(id);
                    }
                },
                cancel: function() {
                    console.log('the user clicked cancel');
                }
            }
        });
    }

    function deleteTemplate(templateId) {
        // alert('delete clicked nichhe');
        $.ajax({
            url: `/template/deleteTemplate/delete/${templateId}`,
            method: 'POST',
            data: {
                templateId: templateId

            },
            success: function(data) {
                let responseData;
                try {
                    responseData = JSON.parse(data);
                } catch (error) {
                    toastr.error('Invalid server response');
                    return;
                }
                if (responseData.status == '200') {
                    toastr.success('Template deleted successfully');
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

</script>