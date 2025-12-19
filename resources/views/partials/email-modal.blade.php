<div class="email-modal create-modal othermodalsection" id="emailModal">
    <div class="modal-overlay modal-toggle"></div>
    <div class="modal-wrapper modal-transition">
        <div class="modal-header">
            <!-- <button class="modal-close modal-toggle email_modal_close_class" id="email_modal_close" data-bs-dismiss="modal" aria-label="Close"> -->
                <!-- <svg width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M9.29563 8.18259C9.60867 8.49563 9.60867 8.98259 9.29563 9.29563C9.13911 9.45215 8.9478 9.52172 8.73911 9.52172C8.53041 9.52172 8.33911 9.45215 8.18259 9.29563L4.99998 6.11302L1.81737 9.29563C1.66085 9.45215 1.46954 9.52172 1.26085 9.52172C1.05215 9.52172 0.860848 9.45215 0.704326 9.29563C0.391283 8.98259 0.391283 8.49563 0.704326 8.18259L3.88693 4.99998L0.704326 1.81737C0.391283 1.50433 0.391283 1.01737 0.704326 0.704326C1.01737 0.391283 1.50433 0.391283 1.81737 0.704326L4.99998 3.88693L8.18259 0.704326C8.49563 0.391283 8.98259 0.391283 9.29563 0.704326C9.60867 1.01737 9.60867 1.50433 9.29563 1.81737L6.11302 4.99998L9.29563 8.18259Z"
                        fill="black" />
                </svg> -->
                <!-- <span aria-hidden="true">×</span>
            </button> -->
            <h2 class="modal-heading show-create-form" style="display:none">Add new template</h2>
            <h2 class="modal-heading hide-create-form">Send email to <span id="emailToContact"></span></h2>
            <button type="button" class="close email_modal_close_class" data-bs-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">×</span>
            </button>
        </div>
        <div class="modal-body">
            <div class="modal-content">
                <form id="myFormSendEmail" class="create-template-form">
                    <input type="hidden" id="current_path" value="{{ request()->path() }}">
                    <div class="form-group show-create-form" style="display:none">
                        <label for="template_name" class="mb-1">Template Name *:</label>
                        <input type="text" class="form-control border-dark" id="template_name" name="template_name" placeholder="Enter template name">
                    </div>
                    <div class="form-group">
                        <label for="email_subject" class="mb-1">Subject *:</label>
                        <input type="text" class="form-control border-dark" id="email_subject" name="email_subject" placeholder="Enter subject">
                    </div>
                    <div class="form-group">
                        <label for="email_content" class="mb-1">Content *:</label>
                        <textarea id="email_content" class="form-control border-dark ckeditor"
                            name="email_content" row='10' placeholder="Write your content..."></textarea>
                    </div>
                    <p class="font-weight-bold insert-placeholder-label mb-1">Insert Placeholder in Textarea:</p>
                    <div class="placeholders d-flex flex-wrap mb-2">
                        <span class="addPlaceholder insert-placeholder font-weight-semibold p-2 border small"
                            data-placeholder="{CANDIDATE_FIRST_NAME}">{CANDIDATE_FIRST_NAME}</span>
                        <span class="addPlaceholder insert-placeholder font-weight-semibold p-2 border small"
                            data-placeholder="{CANDIDATE_LAST_NAME}">{CANDIDATE_LAST_NAME}</span>
                        @if(!str_contains(request()->path(), 'newsletter'))
                        <span class="addPlaceholder insert-placeholder font-weight-semibold p-2 border small"
                            data-placeholder="{BUSINESS_NAME}">{BUSINESS_NAME}</span>
                        @endif
                    </div>
                    <p class="small text-secondary mb-1">While writing you email content, click these buttons to insert
                        placeholders.</p>

                    <div class="hide-create-form d-inline-block">
                        <select id="emailTemplateSelect" class="form-control">
                            <option>-- Templates --</option>
                            <option>Create Template</option>
                            <option>Saved Templates</option>
                        </select>
                    </div>

                    <div class="text-left modal-btns mt-3 pt-3">
                        <!-- <input type="button" id="openSavedEmailTemplate" value="Saved Templates" class="btn btn-default btn-sm" style="float:left;"> -->
                        <input type="button" value="Close" class="btn btn-secondary btn-sm email_modal_close_class">
                        <input type="submit" value="Send" class="btn btn-primary btn-sm hide-create-form-button" {{ ($smtp_data < 1) ? "disabled" : "" }} title="{{$smtp_data > 0 ? '': 'Please add your SMTP configuration to send email.'}}">
                        <input type="button" id="saveEmailTemplate" value="Save Template" class="btn btn-primary btn-sm show-create-form-button" style="display:none">
                    </div>
                    @if($smtp_data < 1)
                    <div style="margin: 12px 0;">
                        <p class="small mandatoryClass">Please add your <a href="{{route('smtp.settings')}}">SMTP configuration</a> to send email.</p>
                    </div>
                    @endif
                </form>
        </div>
    </div>
</div>