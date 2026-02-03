@extends('layouts.app')
@section('pagetitle', 'All Users')
@push('breadcrumbs')
    <li class="breadcrumb-item">Users Management</li>
    <li class="breadcrumb-item active">All Users</li>
@endpush
@section('content')
<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12 margin-tb mb-3">
                <div class="pull-right">
                    <a class=" btn btn-secondary btn-sm" href="{{ route('users.create') }}"><i class="fas fa-plus-circle"></i>
                        <span class="d-none d-md-inline"> Create New User</span>
                    </a>
                </div>
            </div>
        </div>
        <div class="table-container pt-0 pb-4">
            <table class="table table-bordered m-0" id="usersTable">
                <tr>
                    <th style="width:56px;">No</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Roles</th>
                    <th style="width: 100px;">2FA</th>
                    <th style="width: 138px">Action</th>
                </tr>
                @foreach ($data as $key => $user)
                    <tr>
                        <td>{{ ++$i }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @if(!empty($user->getRoleNames()))
                                @foreach($user->getRoleNames() as $v)
                                    <label class="btn btn-sm  btn-secondary">{{ $v }}</label>
                                @endforeach
                            @endif
                        </td>
                        <td>
                            <div class="form-check form-switch">
                                <input class="form-check-input toggle-2fa" 
                                       type="checkbox" 
                                       role="switch"
                                       data-user_id="{{ $user->id }}"
                                       {{ $user->twofactor_authentication ? 'checked' : '' }}>
                            </div>
                        </td>
                        <td>
                            <a class="btn btn-sm btn-info action-btn" title="Show User" href="{{ route('users.show',base64_encode($user->id)) }}"><i class="fa fa-eye"></i></a>
                            @can('user-edit')
                                <a class="btn btn-sm  btn-success action-btn" title="Edit User" href="{{ route('users.edit',base64_encode($user->id)) }}"><i class="fa fa-edit"></i></a>
                            @endcan
                            @if($user->hasRole('Manager'))
                                <a class="btn btn-sm btn-warning action-btn" 
                                   title="Assign Team Member" 
                                   href="{{ route('users.assignTeam', base64_encode($user->id)) }}">
                                    <i class="fas fa-users"></i>
                                </a>
                            @endif
                            @can('user-delete')
                                {!! Form::open(['method' => 'DELETE','route' => ['users.destroy', $user->id],'style'=>'display:inline','class' => ['userForm-'.$user->id]]) !!}
                                    {{-- trigger confirmation modal --}}
                                    <a href="#" title="Delete User" data-bs-toggle="modal" data-bs-target="#deleteModal" onclick="setModal(this,'{{$user->id}}')" class="btn  btn-sm btn-danger deletebtn action-btn">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                {!! Form::close() !!}
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>
        {!! $data->render() !!}
    </div>
    @include('partials.delete-modal')
</section>
<!-- / Main content -->
@endsection
@push('styles')
@endpush
@push('scripts')
<script>
$(document).on('change', '.toggle-2fa', function () {
    const userId = $(this).data('user_id');
    const status = $(this).is(':checked') ? 1 : 0;

    $.ajax({
        url: "{{ route('users.update2fa') }}",
        type: "POST",
        data: {
            user_id: userId,
            status: status,
            _token: "{{ csrf_token() }}"
        },
        success: function (response) {
            if(response.status){
                toastr.success(response.message || '2FA updated successfully');
            }
            else{
                toastr.error(response.message || 'Something went wrong! Please try again.');
            }
            
        },
        error: function () {
            toastr.error('Something went wrong! Please try again.');
        }
    });
});
</script>
@endpush