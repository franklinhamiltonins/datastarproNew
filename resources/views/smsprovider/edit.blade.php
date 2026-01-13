@extends('layouts.app')
@section('pagetitle', 'Edit Sms Provider')
@push('breadcrumbs')
<li class="breadcrumb-item"><a href="{{route('smsprovider.index')}}">SMS Provider Management</a></li>
<li class="breadcrumb-item active">Edit Sms Provider </li>
@endpush
@section('content')
<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12 mb-3">

                <div class="pull-right">
                    <a class="btn btn-sm btn-primary" href="{{ route('smsprovider.index') }}"><i
                            class="fas fa-arrow-circle-left"></i> Back</a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card card-secondary">
                    <div class="card-header">
                        <h3 class="card-title">Sms Provider Information</h3>
                    </div>
                    {!! Form::model($smsprovider, ['method' => 'PATCH','route' => ['smsprovider.update', $smsprovider->id], 'id'
                    => 'updateSmsprovider']) !!}
                    @csrf
                    <div class="card-body p-2 p-lg-3">
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <strong>Cycle Name<sup class="mandatoryClass">*</sup>:</strong>
                                    {!! Form::text('cycle_name', null, array('placeholder' => 'Name','class' =>
                                    'form-control' ,'id' => 'cycle_name')) !!}
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <strong>Minute delay<sup class="mandatoryClass">*</sup>:</strong>
                                    {!! Form::text('minute_delay', null, array('placeholder' => 'Minute Delay','class' =>
                                    'form-control' ,'id' => 'minute_delay')) !!}
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <strong>Day delay<sup class="mandatoryClass">*</sup>:</strong>
                                    {!! Form::text('day_delay', null, array('placeholder' => 'Day delay','class' =>
                                    'form-control' ,'id' => 'day_delay')) !!}
                                </div>
                            </div> 
                            <div class="col-12 col-md-12">
                                <div class="form-group">
                                    <strong>Text<sup class="mandatoryClass">*</sup>:</strong>

                                    <p id="charactercountdisplay">Character Count: <strong><span id="charCount">0</span></strong>/160</p>

                                    {!! Form::textarea('text', null, array('placeholder' => 'Text','class' =>
                                    'form-control' ,'id' => 'smsprovider_content')) !!}
                                    
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
                                <span class="notemessage">Note-  The length of dynamic content placeholders like {CANDIDATE_FIRST_NAME}, {CANDIDATE_LAST_NAME}, and {BUSINESS_NAME} is assumed to be 15 characters each. Please ensure that your message fits within the allowed character limit, accounting for these placeholders.</span>
                            </div>
                        </div>

                    </div>

                    <div class="card-footer">
                        <button type="submit" id="updateSmsproviderbtn"  class="btn btn-primary">Update Sms Provider</button>
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
@endpush
@push('scripts')
<script>
jQuery(document).ready(function() {

    let theEditor;

    ClassicEditor
        .create(document.querySelector('#smsprovider_content'))
        .then(editor => {
            theEditor = editor; // Save for later use.

            theEditor.model.document.on('change:data', updateCharCount);
            updateCharCount();
        })
        .catch(error => {
            console.error(error);
        });

    $(document).on('click','.insert-placeholder',function() {
        let placeholder = $(this).data('placeholder');
        theEditor.model.change(writer => {
            let selection = theEditor.model.document.selection;
            let position = selection.getFirstPosition();
            writer.insertText(placeholder, position);
        });
        theEditor.editing.view.focus();

    });

    $(document).on('click','#updateSmsproviderbtn',function(event) {
        event.preventDefault();

        if($("#cycle_name").val() == ''){
            toastr.error('Cycle Name should not be blank');
            return false; // Prevent form submission
        }
        if($("#minute_delay").val() == ''){
            toastr.error('Minute delay should not be blank');
            return false; // Prevent form submission
        }
        if($("#day_delay").val() == ''){
            toastr.error('Day delay should not be blank');
            return false; // Prevent form submission
        }

        let editorContent = theEditor.getData().trim();
        let textContent = getTextContentWithoutTags(editorContent);
        // console.log(textContent);
        $('#smsprovider_content').val(textContent);
        // $('#updateSmsproviderbtn').siblings("#smsprovider_content").val(textContent);
        // return false;
        if (textContent == '') {
            toastr.error('Smsprovider content should not be blank');
            return false; // Prevent form submission
        }
        else if(parseInt($('#charCount').text()) > 160){
            toastr.error('Smsprovider content length must not be greater than 160');
            return false; // Prevent form submission
        }
        $('#updateSmsprovider').submit();
    });

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