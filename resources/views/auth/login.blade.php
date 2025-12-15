@extends('layouts.auth')
@section('pagetitle', 'Login')
@section('content')
<div class="login-box">
    <div class="login-logo"><a href="/">{{ env('APP_NAME') }}</a></div>
    <div class="card">
        <div class="card-body login-card-body">
            <p class="login-box-msg">Sign in to start your session</p>
            <div id="server-message"></div>
            <div class="ajax-loader-wrapper " id="ajaxLoader" style="display:none">
                @include('partials.datatable_loader')
            </div>



      <form id="loginForm" class="pb-4">
        @csrf
        <div id="step1">
          <div class="input-group mb-3">
            <input id="email" type="email" class="form-control" name="email" placeholder="E-Mail Address" required autocomplete="email" autofocus>
            <div class="input-group-append input-group-text"><span class="fas fa-envelope"></span></div>
          </div>

          <div class="input-group mb-3">
            <input id="password" type="password" class="form-control" name="password" placeholder="Password" required>
            <div class="input-group-append input-group-text"><span class="fas fa-lock"></span></div>
          </div>

          <div class="row">
            <div class="col-8">
              <div class="icheck-primary">
                <input type="checkbox" name="remember" id="remember">
                <label for="remember">Remember Me</label>
              </div>
            </div>
            <div class="col-4">
              <button id="step1Submit" type="submit" class="btn btn-primary btn-block btn-flat">Sign In</button>
            </div>
          </div>
          @if (Route::has('password.request'))
            <p class="text-sm text-center text-decoration-underline mt-2">
                <a href="{{ route('password.request') }}">I forgot my password</a>
            </p>
            @endif
            @if (Route::has('register'))

            <p class="mb-0">
                <a href="{{ route('register') }}" class="text-center">Register a new membership</a>
            </p>
            @endif
        </div>

        <!-- Step 2 (OTP) - initially hidden -->
        <div id="step2" style="display:none;">
            <div class="input-group mb-3">
                <input id="otp" type="text" class="form-control" name="otp" placeholder="Enter 2FA Code" maxlength="6">
                <div class="input-group-append input-group-text"><span class="fas fa-key"></span></div>
            </div>
            <div class="d-flex gap-3 align-items-center">
                <button id="verifyOtpBtn" type="button" class="btn btn-primary d-flex align-items-center justify-content-center flex-fill">
                    <!-- <span class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span> -->
                    <span class="d-inline-block">Verify Code</span>
                </button>
                <button id="resendOtpBtn" type="button" class="p-0 border-0 text-primary bg-transparent flex-fill text-decoration-underline outline-none">Resend Code</button>
            </div>
          <div class="showLeftAttemt displayNoneClass">
            <p class="text-sm text-center mb-0 mt-3">
                You have <span id="leftAttempt"></span> remaining attempts out of <span id="totalAttempt"></span>.
            </p>
          </div>
        </div>

        <!-- store user id (returned from server) -->
        <input type="hidden" id="login_user_id" name="user_id" value="">
      </form>
    </div>
  </div>
</div>
@stop

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(function() {
    // Step 1 submit (email + password) — AJAX
    $('#loginForm').on('submit', function(e) {
        e.preventDefault();
        $('#server-message').html('');
        $('#step1Submit').prop('disabled', true);
        showLoader();

        $.ajax({
            url: "{{ route('login') }}",
            method: 'POST',
            data: {
                _token: $('input[name="_token"]').val(),
                email: $('#email').val(),
                password: $('#password').val(),
                remember: $('#remember').is(':checked') ? 1 : 0
            },
            success: function(res) {
                $('#step1Submit').prop('disabled', false);
                hideLoader();
                if (res.status && res.showOtpBox) {
                    // show OTP input
                    $('#login_user_id').val(res.userId);
                    $('#step1').hide();
                    $('#step2').show();
                    $('#server-message').html('<div class="">' + res.message + '</div>');
                } else {
                    $('#server-message').html('<div class="">' + res.message + '</div>');
                }
            },
            error: function(xhr) {
                $('#step1Submit').prop('disabled', false);
                hideLoader();
                let err = xhr.responseJSON?.message ?? 'Something went wrong';
                $('#server-message').html('<div class="alert alert-danger">' + err + '</div>');
            }
        });
    });

    // Verify OTP
    $('#verifyOtpBtn').on('click', function(e) {
        e.preventDefault();
        $('#server-message').html('');
        if($('#otp').val() == ""){
            $('#server-message').html('<div class="alert alert-danger">Please Enter 2FA Code</div>');
            return false;
        }
        $('#verifyOtpBtn').prop('disabled', true);
        showLoader();

        $.ajax({
            url: "{{ route('login.verifyOtp') }}",
            method: 'POST',
            data: {
                _token: $('input[name="_token"]').val(),
                user_id: $('#login_user_id').val(),
                otp: $('#otp').val()
            },
            success: function(res) {
                // console.log(res);return false;
                $('#verifyOtpBtn').prop('disabled', false);
                hideLoader();
                if (res.status) {
                    // redirect to dashboard
                    window.location.href = res.redirectTo;
                } else {
                    $('#server-message').html('<div class="alert alert-danger">' + res.message + '</div>');
                    if(res.triedAttempt > 0){
                        $('#totalAttempt').html(res.totalAttempt);
                        $('#leftAttempt').html(res.totalAttempt - res.triedAttempt);
                        $(".showLeftAttemt").removeClass("displayNoneClass");
                        if(res.totalAttempt == res.triedAttempt){
                            $('#verifyOtpBtn').prop('disabled', true);
                            $('#resendOtpBtn').prop('disabled', true);
                        }
                    }
                    else{
                        $(".showLeftAttemt").addClass("displayNoneClass");
                    }
                }
            },
            error: function(xhr) {
                $('#verifyOtpBtn').prop('disabled', false);
                hideLoader();
                let err = xhr.responseJSON?.message ?? 'Something went wrong';
                $('#server-message').html('<div class="alert alert-danger">' + err + '</div>');
            }
        });
    });

    // Resend OTP
    $('#resendOtpBtn').on('click', function(e) {
        e.preventDefault();
        $('#resendOtpBtn').prop('disabled', true);
        showLoader();
        $('#server-message').html('');
        // Re-use login-step1 to resend OTP (call with same email & password is not good)
        // Better: create a dedicated resend route — for simplicity, call step1 again with known email
        $.ajax({
            url: "{{ route('login.resendOtp') }}",
            method: 'POST',
            data: {
                _token: $('input[name="_token"]').val(),
                user_id: $('#login_user_id').val()
            },
            success: function(res) {
                if (res.status) {
                    $('#server-message').html('<div class="alert alert-success">Code Resent Successfully.</div>');
                    // $('#login_user_id').val(res.user_id);
                } else {
                    $('#server-message').html('<div class="alert alert-danger">' + res.message + '</div>');
                }
                $('#resendOtpBtn').prop('disabled', false);
                hideLoader();
            }
        });
    });

    function showLoader() {
        $('#ajaxLoader').fadeIn(150);
    }

    function hideLoader() {
        $('#ajaxLoader').fadeOut(150);
    }

});
</script>
@endpush