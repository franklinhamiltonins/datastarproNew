@extends('layouts.app')
@section('pagetitle', 'Add New SMTP')
@push('breadcrumbs')
<li class="breadcrumb-item">SMTPs Management</li>

<li class="breadcrumb-item active">Add New SMTP</li>
@endpush
@section('content')
<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12 mb-3">

                <div class="pull-right">
                    <a class="btn btn-sm btn-primary" href="{{ route('smtps.index') }}"><i
                            class="fas fa-arrow-circle-left"></i> Back</a>
                </div>
            </div>
        </div>


        <div class="row">
            <div class="col-12">
                <div class="card card-secondary">
                    <div class="card-header">
                        <h3 class="card-title">Create New SMTP</h3>
                    </div>
                    {!! Form::open(array('route' => 'smtps.store','method'=>'POST', 'id' => 'createSmtp', 'autocomplete' => 'off', 'enctype'=> 'multipart/form-data')) !!}
                    @csrf
                    <input type="hidden" id="logged_user_id" value="{{auth()->user()->id}}">
                    <div class="card-body p-2 p-lg-3 pb-0">
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <strong>Email Providers:</strong>
                                    {!! Form::select('provider_id', $email_providers,[],
                                    array('class' => 'form-control', 'id' => 'changeEmailProvider')) !!}
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <strong>User Name<sup class="mandatoryClass">*</sup>:</strong>
                                    {!! Form::select('user_id', $agent_users,[], array('class' => 'form-control', 'id' => 'selectAgent')) !!}
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <strong>Host<sup class="mandatoryClass">*</sup>:</strong>
                                    {!! Form::text('host', null, array('placeholder' => 'Host','class' =>
                                    'form-control')) !!}
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <strong>Port<sup class="mandatoryClass">*</sup>:</strong>
                                    {!! Form::text('port', null, array('placeholder' => 'Port','class' =>
                                        'form-control')) !!}
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <strong>Encryption<sup class="mandatoryClass">*</sup>:</strong>
                                    {!! Form::select('encryption', ['TLS'=>'TLS', 'SSL' => 'SSL', 'None' => 'None'],'TLS',
                                    array('class' => 'form-control')) !!}
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <strong>Auth<sup class="mandatoryClass">*</sup>:</strong>
                                    {!! Form::select('auth', ['true'=>'True', 'false' => 'False', 'None' => 'None'],'true',
                                    array('class' => 'form-control')) !!}
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <strong>Email<sup class="mandatoryClass requiredForLoggedUser" 
                                        style="display:{{old('user_id') && old('user_id') == auth()->user()->id ? 'inherit' : 'none'}}">*</sup>:</strong>
                                        {!! Form::text('username', null, array('placeholder' => 'Email','class' =>
                                        'form-control', 'autocomplete'=>'false')) !!}
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <strong>Password<sup class="mandatoryClass requiredForLoggedUser" 
                                        style="display:{{old('user_id') && old('user_id') == auth()->user()->id ? 'inherit' : 'none'}}">*</sup>:</strong>
                                    <span style="font-size: 0.80rem;">(For Gmail, use App password)</span>
                                    <div class="position-relative password-input">
                                        {!! Form::password('password', array('placeholder' => 'Password','class' => 'form-control', 'id' => 'password', 'autocomplete'=>'false')) !!}
                                        <!-- <i id="showPassword" title="Show Password" class="fa fa-eye-slash position-absolute mr-2 text-gray"></i>
                                        <i id="hidePassword" title="Hide Password" class="fa fa-eye position-absolute mr-2 text-gray" style="display:none"></i> -->
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <strong>From Name<sup class="mandatoryClass">*</sup>:</strong>
                                    {!! Form::text('from_name', auth()->user()->name, array('placeholder' => 'From Name','class' =>
                                        'form-control')) !!}
                                </div>
                            </div>
                            
                            <div class="col-12 col-md-12">
                                <div class="row">
                                    <div class="col-12 col-md-6">
                                        <div class="form-group">
                                            <strong>Signature Text</strong>
                                            {!! Form::textarea('signature_text', null, array('placeholder' => 'Write your content...','class' => 'form-control ckeditor','id' => 'signature_text')) !!}
                                        </div>
                                        <div class="form-group">
                                            <strong>Signature Image:</strong>
                                            <div class="input-group">
                                                <div class="custom-file">
                                                    <input type="file" name="signature_image" class="custom-file-input" id="">
                                                    <label class="custom-file-label" for="exampleInputFile">Choose file</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                    <div class="form-group">
                                    <strong class="mb-2">Email Signature:</strong>
                                        <table cellspacing="0" cellpadding="0" width="100%" bgcolor="#fff" style="padding: 10px; font-family: Google Sans,Roboto,sans-serif; font-size: 13px; color: #646464;">
                                            <tr>
                                                <td>
                                                    <table cellspacing="0" cellpadding="0" width="100%">
                                                        <tr>
                                                            <td align="left" valign="middle" width="24%" style="padding-right: 8px; border-right: 1px solid #000;">
                                                                <figure style="margin: 0;">
                                                                    <img id="signature-image-preview" style="width: 100%;" src="/images/placeholder-img.png" alt="Signature image">
                                                                </figure>
                                                            </td>
                                                            <td align="left" valign="middle" width="76%" style="padding-left: 8px;">
                                                                <p style="margin: 0 0 2px;">John Doe</p>
                                                                <p style="margin: 0 0 2px;">Agent, Generic Tech Solutions</p>
                                                                <p style="margin: 0 0 2px; text-decoration: underline;">Office: <a href="tel:(555) 123-4567" style="text-decoration: none;">(555) 015-2720</a>  Cell: <a href="tel:(555) 010-2020" style="text-decoration: none;">(555) 123-4567</a></p>
                                                                <p style="margin: 0 0 2px;"><a href="mailto:Nsledge@fhinsure.com">johndoe@example.com</a></p>
                                                                <p style="margin: 0 0 2px;"><a href="www.fhinsure.com" style="text-decoration: underline;">www.example.com</a></p>
                                                                <p style="margin: 0 0 2px;">123 Main Street, Anytown, Anystate, 12345</p>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                    </div>
                                </div>
                            </div>
                            <!-- <div class="col-12 col-md-6">
                                
                                <div class="form-group">
                                    <img id="signature-image-preview" src="" alt="Signature image preview" style="display:none" class="form-control-file h-25 p-3 w-25">
                                </div>
                            </div> -->
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Add SMTP</button>
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

    ClassicEditor
        .create(document.querySelector('#signature_text'))
        .then(editor => {
            theEditor = editor; // Save for later use.
        })
        .catch(error => {
            console.error(error);
        });


    $('#createSmtp').submit(function(event) {
        event.preventDefault();
        let editorContent = theEditor.getData().trim();
        $('#createSmtp').siblings("#signature_text").val(editorContent);
        // if (editorContent === '') {
        //     toastr.error('Template content should not be blank');
        //     return false; // Prevent form submission
        // }
        $(this)[0].submit();
    });

    $('#showPassword').on('click', function() {
        $("#showPassword").css('display','none');
        $("#hidePassword").css('display','inline-block');
        $('#password').attr('type',"text"); 
    });

    $('#hidePassword').on('click', function() {
        $("#hidePassword").css('display','none');
        $("#showPassword").css('display','inline-block');
        $('#password').attr('type',"password"); 
    });

    $('#changeEmailProvider').on('change', function() {
        var provider_id = $(this).val();
        if(provider_id > 0) {
            $.ajax({
                url: `/provider-details/${provider_id}`,
                method: 'GET',
                success: function(responseData) {
                    try {
                        var provider_details = responseData.provider;
                        $("input[name='host']").val(provider_details.host);
                        $("input[name='port']").val(provider_details.port);
                        $("select[name='encryption']").val(provider_details.encryption);
                        $("select[name='auth']").val(provider_details.auth);
                    } catch (error) {
                        toastr.error('Invalid server response');
                        return;
                    }
                },
                error: function(xhr, status, error) {
                    let jsonResponse = JSON.parse(xhr.responseText);
                    toastr.error(jsonResponse.response);
                    return;
                }
            });
        } else {
            $("input[name='host']").val('');
            $("input[name='port']").val('');
            $("select[name='encryption']").val('None');
            $("select[name='auth']").val('None');
        }
        
    });

    $('#selectAgent').on('change', function() {
        var user_id = $(this).val();
        if(user_id > 0) {
            var logged_user_id = $("#logged_user_id").val();
            console.log(logged_user_id+"=="+user_id);
            if(user_id == logged_user_id)
                $(".requiredForLoggedUser").css("display","inherit");
            else
                $(".requiredForLoggedUser").css("display","none");
            $.ajax({
                url: `/user-details/${user_id}`,
                method: 'GET',
                success: function(responseData) {
                    try {
                        var user_details = responseData.agent;
                        console.log(user_details);
                        $("input[name='username']").val(user_details.email);
                        $("input[name='from_name']").val(user_details.name);
                    } catch (error) {
                        toastr.error('Invalid server response');
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
        
    });

    $('.custom-file-input').on('change', function() {
        var $this = $(this),
            $files = $this[0].files,
            $name = '',
            $separation = '',
            $i = 0;
        if ($files.length > 1) {
            $separation = ', ';
        }
        while ($i < $files.length) {
            $name += $files[$i].name + $separation;
            $i++;
        }
        if ($name.length > 80) {
            $name = $files.length + ' files';
        }

        var reader = new FileReader();
        reader.onload = function(e) {
        $('#signature-image-preview').attr('src', e.target.result).show();
        }
        reader.readAsDataURL(this.files[0]);

        $this.next().html($name);
    });

})
</script>
@endpush