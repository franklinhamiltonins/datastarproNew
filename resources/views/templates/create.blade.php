@extends('layouts.app')
@section('pagetitle', 'Add New Template')
@push('breadcrumbs')
<li class="breadcrumb-item"><a href="{{route('templates.index')}}">Templates Management</a></li>
<li class="breadcrumb-item active">Add New Template</li>
@endpush
@section('content')
<!-- <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script> -->
<!-- <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" /> -->
<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12 mb-3">

                <div class="pull-right">
                    <a class="btn btn-sm btn-primary" href="{{ route('templates.index') }}"><i
                            class="fas fa-arrow-circle-left"></i> Back</a>
                </div>
            </div>
        </div>


        <div class="row">
            <div class="col-12">
                <div class="card card-secondary">
                    <div class="card-header">
                        <h3 class="card-title">Create New Template</h3>
                    </div>
                    {!! Form::open(array('route' => 'templates.store','method'=>'POST', 'id' => 'createTemplate')) !!}
                    @csrf
                    <div class="card-body p-2 p-lg-3">
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <strong>Template Name<sup class="mandatoryClass">*</sup>:</strong>
                                    {!! Form::text('template_name', null, array('placeholder' => 'Name','class' =>
                                    'form-control')) !!}
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <strong>Template Type<sup class="mandatoryClass">*</sup>:</strong>
                                    {!! Form::select('template_type', ['sms'=>'SMS', 'mail' => 'Email'],null,
                                    array('class' => 'form-control', 'id' => 'template_type')) !!}
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <strong>Template Subject:</strong>
                                    {!! Form::text('template_subject', null, array('placeholder' => 'Subject','class' =>
                                    'form-control')) !!}
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <strong>Template Content<sup class="mandatoryClass">*</sup>:</strong>

                                    <p class="smsrealtedarea" id="charactercountdisplay">Character Count: <strong><span id="charCount">0</span></strong>/160</p>

                                    {!! Form::textarea('template_content', null, array('placeholder' => 'Write your
                                    content...','class' => 'form-control ckeditor','id' => 'template_content')) !!}
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <strong>Insert Placeholder in Textarea:</strong>
                                    <div class="placeholders d-flex flex-wrap mb-2">
                                        <span class="insert-placeholder font-weight-semibold p-2 border small"
                                            data-placeholder="{CANDIDATE_FIRST_NAME}">{CANDIDATE_FIRST_NAME}</span>
                                        <span class="insert-placeholder font-weight-semibold p-2 border small"
                                            data-placeholder="{CANDIDATE_LAST_NAME}">{CANDIDATE_LAST_NAME}</span>
                                        <span class="insert-placeholder font-weight-semibold p-2 border small"
                                            data-placeholder="{BUSINESS_NAME}">{BUSINESS_NAME}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p class="small text-secondary mb-0">While writing you template content, click these buttons to insert placeholders.</p>
                        <br>
                        <p class="small text-secondary mb-0 smsrealtedarea">Note-  The length of dynamic content placeholders like {CANDIDATE_FIRST_NAME}, {CANDIDATE_LAST_NAME}, and {BUSINESS_NAME} is assumed to be 15 characters each. Please ensure that your message fits within the allowed character limit, accounting for these placeholders.</p>

                            @if($is_admin)
                            <div class="col-12 col-md-6 mt-3">
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" 
                                        @if( old('set_for_all') == '1' || !old('template_content')) checked='checked' @endif
                                            name="set_for_all" value="{{ old('set_for_all') == '1' ? 1 : (!old('template_content') ? 1 : 0) }}" id="templateForAll">
                                        <label class="custom-control-label" for="templateForAll">Set for all Agents</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-5" id="show_user_option" style="display:{{ old('set_for_all') == '1' ? 'none' : (!old('template_content') ? 'none' : 'block') }}">
                                <div class="form-group">
                                    <strong>User Name:</strong>
                                    {!! Form::select('user_id[]', $agent_users, isset($template)? $template->user_id : [],
                                    array('class' => 'form-control select2', 'multiple', 'id' => 'selectUser','data-placeholder'=>'Select User')) !!}
                                </div>
                            </div>
                            @else
                                <input type="hidden" name="user_id[]" value="{{ auth()->user()->id }}">
                            @endif

                    </div>
                    <div class="card-footer">

                        <button type="submit" class="btn btn-sm btn-primary">Add Template</button>
                    </div>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>



    </div><!-- /.container-fluid -->
</section>
<!-- /.content -->
@endsection
@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
jQuery(document).ready(function() {

    let theEditor;
    $('.select2').select2({
        width: '100%',
        'placeholder': $(this).data('placeholder')
    });

    ClassicEditor
        .create(document.querySelector('#template_content'))
        .then(editor => {
            theEditor = editor; // Save for later use.

            theEditor.model.document.on('change:data', updateCharCount);
            updateCharCount();
        })
        .catch(error => {
            console.error(error);
        });

    $('.insert-placeholder').click(function() {
        let placeholder = $(this).data('placeholder');
        theEditor.model.change(writer => {
            let selection = theEditor.model.document.selection;
            let position = selection.getFirstPosition();
            writer.insertText(placeholder, position);
        });
        theEditor.editing.view.focus();

    });

    $('#createTemplate').submit(function(event) {
        event.preventDefault();

        let editorContent = theEditor.getData().trim();
        let textContent = getTextContentWithoutTags(editorContent);
        $('#createTemplate').siblings("#template_content").val(textContent)
        if (textContent == '') {
            toastr.error('Template content should not be blank');
            return false; // Prevent form submission
        }
        if($('#template_type').val() == 'sms'){
            if(parseInt($('#charCount').text()) > 160){
                toastr.error('Sms Template content length must not be greater than 160');
                return false; // Prevent form submission
            }
        }
        let set_for_all = $("#templateForAll").val();
        let selectUser = $("#selectUser").val();
        if(selectUser) {
            selectUserlength = selectUser.length;
            if(set_for_all == 0 && selectUserlength == 0) {
                toastr.error('Select User Name');
                return false; // Prevent form submission
            } else {
                $(this)[0].submit();
            }
        } else
            $(this)[0].submit();
    });

    $('#templateForAll').change(function() {
        // console.log("this.checked = "+this.checked);
        if(this.checked) {
            $(this).val(1);
            $("#show_user_option").css("display","none");
        } else {
            $(this).val(0);
            $("#show_user_option").css("display","block"); 
        }
            
    });

    $(document).on('change','#template_type',function() {
        showmessagecharactercountarea($(this).val());
    });
    showmessagecharactercountarea('sms');

    function showmessagecharactercountarea(type) {
        if(type == 'sms'){
            $(".smsrealtedarea").removeClass('displayNoneClass');
        }
        else{
            $(".smsrealtedarea").addClass('displayNoneClass');
        }
    }

    function getContentLength(content) {
        // Calculate the initial length of the content
        let length = content.length;

        // Define placeholder patterns and their assumed lengths
        const placeholders = [
            { placeholder: '{CANDIDATE_FIRST_NAME}', length: 15 },
            { placeholder: '{CANDIDATE_LAST_NAME}', length: 15 },
            { placeholder: '{BUSINESS_NAME}', length: 15 }
        ];

        // Adjust the length by removing the placeholder lengths and adding assumed lengths
        placeholders.forEach(item => {
            const regex = new RegExp(item.placeholder, 'g');
            const matches = content.match(regex) || [];

            // Subtract the actual length of the placeholder
            length -= matches.length * item.placeholder.length;

            // Add the assumed length
            length += matches.length * item.length;
        });

        return length;
    }


    function getTextContentWithoutTags(htmlContent) {
        // Create a new DOMParser to parse the HTML content
        let parser = new DOMParser();
        let doc = parser.parseFromString(htmlContent, 'text/html');

        // Extract the text content from the parsed document
        return doc.body.textContent || "";
    }

    function updateCharCount() {
        let content = theEditor.getData().trim();
        let textContent = getTextContentWithoutTags(content);
        // console.log(textContent);
        let currentLength = getContentLength(textContent);
        $('#charCount').text(currentLength);

        // Optionally, change color if limit exceeded
        if (currentLength > 160) {
            $('#charCount').css('color', 'red');
        } else {
            $('#charCount').css('color', 'black');
        }
    }

})
</script>
@endpush