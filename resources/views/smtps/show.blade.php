@extends('layouts.app')
@section('pagetitle', 'Show SMTP' )
@push('breadcrumbs')
<li class="breadcrumb-item"><a href="{{route('smtps.index')}}">SMTPs Management</a></li>
<li class="breadcrumb-item active">Show SMTP Configuration </li>
@endpush
@section('content')
<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12 margin-tb mb-3">
                <div class="pull-right">
                    <a class="btn btn-info btn-sm px-2" href="{{ route('smtps.index') }}"><i class="fas fa-arrow-circle-left"></i> Back</a>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-xl-12">
                <div class="card card-secondary">
                    <div class="card-header">
                        <h3 class="card-title">SMTP Configuration Information</h3>
                        <div class="pull-right float-right">
                            <a class="btn btn-info btn-sm px-2" title="Edit SMTP Information" href="{{ route('smtps.edit',base64_encode($smtpConfiguration->id)) }}"><i class="fas fa-edit"></i></a>
                        </div>
                    </div>
                    <div class="card-body p-2 p-lg-3">
                        <div class="form-row">
                            <div class="form-group col-lg-6">
                                <strong>Provider:</strong>
                                {{ isset($smtpConfiguration->provider->provider_name) ? $smtpConfiguration->provider->provider_name : "" }}
                            </div>
                            <div class="form-group col-lg-6">
                                <strong>Host:</strong>
                                {{ $smtpConfiguration->host }}
                            </div>
                            <div class="form-group col-lg-6">
                                <strong>Port:</strong>
                                {{ $smtpConfiguration->port }}
                            </div>

                            <div class="form-group col-lg-6">
                                <strong>Email:</strong>
                                {{ $smtpConfiguration->username}}
                            </div>

                            <div class="form-group col-lg-6">
                                <strong>Password:</strong>
                                @if($smtpConfiguration->password)
                                    <div class="position-relative password-input">
                                        <span id="hideData">*******</span>
                                        <!-- <span id="showData" style="display:none">{{ $smtpConfiguration->password }}</span> -->
                                        <!-- <i id="showPassword" title="Show Password" class="fa fa-eye-slash position-absolute mr-2 text-gray"></i>
                                        <i id="hidePassword" title="Hide Password" class="fa fa-eye position-absolute mr-2 text-gray" style="display:none"></i> -->
                                    </div>
                                @else
                                    <div class="position-relative password-input">
                                        <span></span>
                                    </div>
                                @endif
                                
                            </div>

                            <div class="form-group col-lg-6">
                                <strong>Encryption:</strong>
                                {{ $smtpConfiguration->encryption}}
                            </div>

                            <div class="form-group col-lg-6">
                                <strong>Auth:</strong>
                                {{ ucfirst($smtpConfiguration->auth) }}
                            </div>

                            <div class="form-group col-lg-6">
                                <strong>From Name:</strong>
                                {!! $smtpConfiguration->from_name !!}
                            </div>
                            <div class="form-group col-lg-6">
                                <strong>User:</strong>
                                {{ $smtpConfiguration->user->name }}
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-lg-6">
                                <strong>Email Signature:</strong>
                                <table cellspacing="0" cellpadding="0" width="100%" bgcolor="#fff" style="padding: 10px; font-family: Google Sans,Roboto,sans-serif; font-size: 13px; color: #646464;">   
                                @if($smtpConfiguration['signature_image'] || $smtpConfiguration['signature_text'])
                                    <tr>
                                        <td>
                                            <table cellspacing="0" cellpadding="0" width="100%">
                                                <tr>
                                                    @if($smtpConfiguration['signature_image'] && file_exists(public_path('images/signature/'.$smtpConfiguration['signature_image'])))
                                                    <td align="left" valign="top" width="" style="padding-right: 8px; border-right: 1px solid #000;">
                                                        <figure style="margin: 0;">
                                                            <img style="width: 100%;" src="{{asset('images/signature/'.$smtpConfiguration['signature_image'])}}" alt="">
                                                        </figure>
                                                    </td>
                                                    @endif
                                                    @if($smtpConfiguration['signature_text'])
                                                    <td align="left" valign="top" width="80%" style="padding-left: 8px;">
                                                        {!! $smtpConfiguration['signature_text'] !!}
                                                    </td>
                                                    @endif
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                @else
                                    <p>None</p>
                                @endif
                                </table>
                            </div>
                        </div>
                    </div>
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

    $('#showPassword').on('click', function() {
        $("#showPassword").css('display','none');
        $("#hidePassword").css('display','inline-block');
        $("#showData").css('display','block');
        $("#hideData").css('display','none');
    });

    $('#hidePassword').on('click', function() {
        $("#showPassword").css('display','inline-block');
        $("#hidePassword").css('display','none');
        $("#hideData").css('display','block');
        $("#showData").css('display','none');
    });
})
</script>
@endpush
