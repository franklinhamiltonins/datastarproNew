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
                        <a class="btn btn-sm btn-info action-btn" href="{{ route('users.show',base64_encode($user->id)) }}"><i
                                class="fa fa-eye"></i></a>
                        @can('user-edit')
                        <a class="btn btn-sm  btn-success action-btn" href="{{ route('users.edit',base64_encode($user->id)) }}"><i
                                class="fa fa-edit"></i></a>
                        @endcan
                        @can('user-delete')
                        {!! Form::open(['method' => 'DELETE','route' => ['users.destroy',
                        $user->id],'style'=>'display:inline','class' => ['userForm-'.$user->id]]) !!}
                        {{-- {!! Form::submit('<i class="fa fa-trash"></i>', ['class' => 'btn  btn-sm btn-danger deletebtn']) !!} --}}

                        {{-- trigger confirmation modal --}}
                        <a href="#" data-toggle="modal" data-target="#deleteModal"
                            onclick="setModal(this,'{{$user->id}}')"
                            class="btn  btn-sm btn-danger deletebtn action-btn">
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



    </div><!-- /.container-fluid -->
    @include('partials.delete-modal')
</section>
<!-- /.content -->
@endsection
@push('styles')
@endpush
@push('scripts')

@endpush