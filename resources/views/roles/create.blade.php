@extends('layouts.app')
@section('pagetitle', 'Add New Role')
@push('breadcrumbs')
<li class="breadcrumb-item"><a href="{{route('roles.index')}}">All Roles</a></li>
<li class="breadcrumb-item active">Add New Role</li>
@endpush
@section('content')
<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12 mb-3">

                <div class="pull-right">
                    <a class="btn btn-sm btn-primary" href="{{ route('roles.index') }}"><i
                            class="fas fa-arrow-circle-left"></i> Back</a>
                </div>
            </div>
        </div>


        <div class="row mt-2 mt-md-4">
            <div class="col-12">
                <div class="card card-secondary">
                    <div class="card-header">
                        <h3 class="card-title">Create New Role</h3>
                    </div>
                    {!! Form::open(array('route' => 'roles.store','method'=>'POST')) !!}
                    <div class="card-body">

                        <div class="form-group mb-3">
                            <strong>Name:</strong>
                            {!! Form::text('name', null, array('placeholder' => 'Name','class' => 'form-control')) !!}
                        </div>

                        <div class="form-group mb-0">
                            <strong>Permission:</strong>
                            <div class="row gap-y-4">
                                @foreach($permissionPage as $value)
                                    <div class="col-sm-6 col-md-4 col-lg-3 mb-3">
                                        <div class="card bg-light h-100">
                                            <div class="card-header bg-light">
                                                <h6 class="text-capitalize text-info">{{$value}}</h6>
                                            </div>
                                            <div class="card-body">
                                                @foreach($permission as $key)
                                                    @if($key->page == $value )
                                                        <div>
                                                            {{ Form::checkbox('permission[]', $key->id, false, array('class' => 'name', ($key->name == 'dashboard-list') ? 'checked' : '')) }}
                                                            {{ $key->name }}
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary text-uppercase">Add Role</button>
                    </div>
                </div>
                {!! Form::close() !!}



            </div><!-- /.container-fluid -->
</section>
<!-- /.content -->
@endsection
@push('styles')
@endpush
@push('scripts')
@endpush