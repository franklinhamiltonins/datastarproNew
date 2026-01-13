@extends('layouts.app')
@section('pagetitle', 'Add New User')
@push('breadcrumbs')
<li class="breadcrumb-item"><a href="{{route('users.index')}}">All Users</a></li>
<li class="breadcrumb-item active">Add New User</li>
@endpush
@section('content')
<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12 mb-3">

                <div class="pull-right">
                    <a class="btn btn-sm btn-primary" href="{{ route('users.index') }}"><i
                            class="fas fa-arrow-circle-left"></i> Back</a>
                </div>
            </div>
        </div>


        <div class="row mt-2 mt-md-4">
            <div class="col-xl-12">
                <div class="card card-secondary">
                    <div class="card-header">
                        <h3 class="card-title">Create New User</h3>
                    </div>
                    {!! Form::open(array('route' => 'users.store','method'=>'POST')) !!}
                    <div class="card-body">
                        <div class="row gx-4 mb-3">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <div class="form-group mb-0">
                                    <strong>Name:</strong>
                                    {!! Form::text('name', null, array('placeholder' => 'Name','class' => 'form-control')) !!}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-0">
                                    <strong>Email:</strong>
                                    {!! Form::text('email', null, array('placeholder' => 'Email','class' => 'form-control')) !!}
                                </div>
                            </div>
                        </div>
                        <div class="row gx-4 mb-3">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <div class="form-group mb-0">
                                    <strong>Password:</strong>
                                    {!! Form::password('password', array('placeholder' => 'Password','class' => 'form-control'))
                                    !!}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <strong>Confirm Password:</strong>
                                    {!! Form::password('confirm-password', array('placeholder' => 'Confirm Password','class' =>
                                    'form-control')) !!}
                                </div>
                            </div>
                        </div>
                        <div class="row gx-4">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <div class="form-group mb-0">
                                    <strong>Role:</strong>
                                    {!! Form::select('roles[]', $roles,[], array('class' => 'form-control multiple')) !!}
                                </div>
                            </div>
                            <div class="col-md-6">
                                @can('user-map-bigocean-id')
                                    <div class="form-group mb-0">
                                        <strong>Big Ocean user ID:</strong>
                                        {!! Form::text('bigoceanuser_id', null, array('placeholder' => 'Big Ocean user ID','class' => 'form-control')) !!}
                                    </div>
                                @endcan
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary text-uppercase">Add User</button>
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
@endpush