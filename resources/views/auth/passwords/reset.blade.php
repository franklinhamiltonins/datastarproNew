@extends('layouts.auth')
@section('pagetitle', 'Reset Password')
@section('content')
<div class="login-box">
    <div class="login-logo">
        <a href="/">{{env('APP_NAME')}}</a>
    </div>
    <!-- /.login-logo -->
    <div class="card">
        <div class="card-body login-card-body">
            <p class="login-box-msg">Reset your password</p>
            @if (session('status'))
            <div class="alert alert-success" role="alert">
                {{ session('status') }}
            </div>
            @endif
            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <div class="input-group ">
                    <label for="email" class="col-md-12 col-form-label">{{ __('E-Mail Address') }}</label>
                    <input placeholder="E-Mail Address" id="email" type="email"
                        class="form-control @error('email') is-invalid @enderror" name="email"
                        value="{{ $email ?? old('email') }}" required autocomplete="email" autofocus>
                    <div class="input-group-append input-group-text">
                        <span class="fas fa-envelope"></span>
                    </div>
                    @error('email')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>
                <div class="input-group ">
                    <label for="password" class="col-md-12 col-form-label">{{ __('Password') }}</label>
                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror"
                        name="password" required autocomplete="new-password">
                    <div class="input-group-append input-group-text">
                        <span class="input-group-text" style="cursor:pointer" id="togglePassword">
                            <i class="fas fa-eye" id="eyeIcon"></i>
                        </span>
                    </div>
                    @error('password')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>
                <div class="input-group mb-3 ">
                    <label for="password-confirm" class="col-md-12 col-form-label">{{ __('Confirm Password') }}</label>
                    <input id="password-confirm" type="password" class="form-control" name="password_confirmation"
                        required autocomplete="new-password">
                    <div class="input-group-append input-group-text">
                        <span class="input-group-text" style="cursor:pointer" id="togglePasswordConfirm">
                            <i class="fas fa-eye" id="eyeIconConfirm"></i>
                        </span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-flat btn-block btn-primary">
                            {{ __('Reset Password') }}
                        </button>
                    </div>
                    <!-- /.col -->
                </div>
            </form>
        </div>
        <!-- /.login-card-body -->
    </div>
</div>
<!-- /.login-box -->
@stop

@push('scripts')
<!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> -->
<script src="{{ asset('js/native/jquery-3.7.1.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const passwordInput = document.getElementById('password');
        const togglePassword = document.getElementById('togglePassword');
        const eyeIcon = document.getElementById('eyeIcon');

        togglePassword.addEventListener('click', function () {
            const isPassword = passwordInput.getAttribute('type') === 'password';

            // Toggle input type
            passwordInput.setAttribute(
                'type',
                isPassword ? 'text' : 'password'
            );

            // Toggle icon
            eyeIcon.classList.toggle('fa-eye');
            eyeIcon.classList.toggle('fa-eye-slash');
        });

        const passwordConfirmInput = document.getElementById('password-confirm');
        const togglePasswordConfirm = document.getElementById('togglePasswordConfirm');
        const eyeIconConfirm = document.getElementById('eyeIconConfirm');

        togglePasswordConfirm.addEventListener('click', function () {
            const isPasswordConfirm = passwordConfirmInput.getAttribute('type') === 'password';

            // Toggle input type
            passwordConfirmInput.setAttribute(
                'type',
                isPasswordConfirm ? 'text' : 'password'
            );

            // Toggle icon
            eyeIconConfirm.classList.toggle('fa-eye');
            eyeIconConfirm.classList.toggle('fa-eye-slash');
        });
    });
</script>
@endpush