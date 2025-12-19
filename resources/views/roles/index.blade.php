@extends('layouts.app')
@section('pagetitle', 'All Roles')
@push('breadcrumbs')

<li class="breadcrumb-item active">Role Management</li>
<li class="breadcrumb-item active">All Roles</li>
@endpush
@section('content')
<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12 margin-tb  mb-3">
                <div class="pull-right">
                    @can('role-create')
                    <a class="btn btn-secondary btn-sm" href="{{ route('roles.create') }}"><i class="fas fa-plus-circle"></i>
                        <span class="d-none d-md-inline"> Create New Role
                        </span>
                    </a>
                    @endcan
                </div>
            </div>
        </div>
        <div class="table-container pt-0 pb-4">
            <table class="table table-bordered mt-0" id="rolesTable">
                <tr>
                    <th>No</th>
                    <th>Name</th>
                    <th>Action</th>
                </tr>
                @foreach ($roles as $key => $role)
                    <tr>
                        <td>{{ ++$i }}</td>
                        <td>{{ $role->name }}</td>
                        <td>
                            <a class="btn btn-sm  btn-info action-btn" href="{{ route('roles.show',base64_encode($role->id)) }}" title="Show">
                                <i class="fa fa-eye"></i>
                            </a>
                            @if ($role->name != "Super Admin")
                                @can('role-edit')
                                    <a class="btn btn-sm btn-success action-btn" href="{{ route('roles.edit',base64_encode($role->id)) }}" title="Edit">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                @endcan
                                @can('role-clone')
                                    <a class="btn btn-sm btn-warning action-btn" href="{{ route('roles.edit', base64_encode($role->id)) }}?action=clone" title="Clone">
                                        <i class="fa fa-clone"></i>
                                    </a>
                                @endcan
                                @can('role-delete')
                                    {!! Form::open(['method' => 'DELETE','route' => ['roles.destroy',
                                        $role->id],'style'=>'display:inline','class'=>['roleForm_'.$role->id]]) !!}
                                        <a href="#" data-bs-toggle="modal" data-bs-target="#deleteModal" onclick="setModal(this,'{{$role->id}}')" class="btn btn-sm btn-danger deletebtn action-btn" title="Delete">
                                            <i class="fa fa-trash"></i>
                                        </a>
                                    {!! Form::close() !!}
                                @endcan
                            @endif
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>
        {!! $roles->render() !!}
    </div>
    @include('partials.delete-modal')
</section>
@endsection
@push('styles')
@endpush
@push('scripts')
@endpush