@extends('layouts.app')
@section('pagetitle', $isClone ? 'Clone Role' : 'Edit Role')
@push('breadcrumbs')
<li class="breadcrumb-item"><a href="{{route('roles.index')}}">All Roles</a></li>

<li class="breadcrumb-item active">
    @if($isClone)
        Clone Role
    @else
        Edit Role
    @endif
</li>
@endpush
@section('content')
<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12 mb-3">
                <div class="pull-right">
                    <a class="btn btn-sm btn-primary" href="{{ route('roles.index') }}"><i class="fas fa-arrow-circle-left"></i> Back</a>
                </div>
            </div>
        </div>
        <div class="row mt-2 mt-md-4">
            <div class="col-12">
                <div class="card card-secondary">
                    <div class="card-header mb-3">
                        @if($isClone)
                            <h3 class="card-title">Clone Role Permissions</h3>
                        @else
                            <h3 class="card-title">Change Role Permissions</h3>
                        @endif
                    </div>
                    {!! Form::model($role, ['method' => 'PATCH','route' => ['roles.update', $role->id]]) !!}
                        <div class="card-body">
                            <div class="form-group">
                                <strong>Name:</strong>
                                {!! Form::text('name', null, array('placeholder' => 'Name','class' => 'form-control')) !!}
                                <input type="hidden" name="is_clone" value="{{$isClone}}">
                            </div>
                            @if($isClone)
                                <div class="alert alert-info mt-2 mb-0 p-2 small">
                                    <i class="fa fa-info-circle me-1"></i>
                                    Please update the name of the cloned role to match your purpose.
                                </div>
                            @endif
                            <br/>
                            <div class="form-group ">
                                <strong>Permissions:</strong>
                                <br/>
                                @if ($role->name == "Super Admin")
                                 <label class="label text-secondary">Super Admin role has all permissions granted</label>
                                @else
                                    <div class="d-flex flex-wrap  justify-content-center  justify-content-md-start">
                                        @foreach($permissionPage as $value)
                                            <div class="d-flex p-2 page-{{$value}}"  style="width: 25%;min-width:200px;max-width:300px" >
                                                <div class="card bg-light  mb-3 w-100" >
                                                    <div class="card-header bg-light"><h6 class="text-info">{{$value}}</h6></div>
                                                    <div class="card-body">
                                                        @foreach($permission as $key)                          
                                                            @if($key->page == $value )
                                                                <div>
                                                                    {{ Form::checkbox('permission[]', $key->id, in_array($key->id, $rolePermissions) ? true : false, array('class' => 'name')), }}
                                                                    {{ $key->name }}
                                                                </div> 
                                                            @endif   
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                        @if ($role->name != "Super Admin")
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">
                                @if($isClone)
                                    {{'Clone Role'}}
                                @else
                                    {{'Update Role'}}
                                @endif
                            </button>
                        </div>
                        @endif
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
</section>
<!-- /.content -->
@endsection
@push('styles')
@endpush
@push('scripts')
<script>
</script>
@endpush