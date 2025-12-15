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
            <form method="POST" action="{{ route('password.email') }}">
                @csrf

                <div class="input-group mb-3 ">
                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                        name="email" placeholder="E-Mail Address" value="{{ old('email') }}" required
                        autocomplete="email" autofocus>
                    <div class="input-group-append input-group-text">
                        <span class="fas fa-envelope"></span>
                    </div>
                    @error('email')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>

                <div class="row">

                    <div class="col-9">
                        <button type="submit" class="btn btn-flat btn-blockn btn-primary">
                            {{ __('Send Password Reset Link') }}
                        </button>
                    </div>
                    <div class="col-3">
                        <a href="{{ route('login') }}" class="btn btn-flat btn-block btn-primary">Login</a>

                    </div>
                    <!-- /.col -->
                </div>
            </form>



        </div>
        <!-- /.login-card-body -->
    </div>
</div>
<!-- /.login-box -->
@endsection