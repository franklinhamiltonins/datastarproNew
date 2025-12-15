@extends('layouts.app')
@section('pagetitle', 'Edit User')
@push('breadcrumbs')
<li class="breadcrumb-item"><a href="{{route('users.index')}}">All Users</a></li>
<li class="breadcrumb-item active">Edit User </li>
@endpush
@section('content')
<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12 mb-3">
               
                <div class="pull-right">
                    <a class="btn btn-sm btn-primary" href="{{ route('users.index') }}"><i class="fas fa-arrow-circle-left"></i> Back</a>
                </div>
            </div>
        </div>

        <div class="row mt-2 mt-md-4">
            <div class="col-xl-6">
                <div class="card card-secondary">
                    <div class="card-header mb-3">
                        <h3 class="card-title">Change Profile Information</h3>
                    </div>
                    {!! Form::model($user, ['method' => 'PATCH','route' => ['users.update', $user->id]]) !!}
                    
                        <div class="card-body">
                                <div class="form-group">
                                    <strong>Name:</strong>
                                    {!! Form::text('name', null, array('placeholder' => 'Name','class' => 'form-control')) !!}
                                </div>
                            
                            
                                <div class="form-group">
                                    <strong>Email:</strong>
                                    {!! Form::text('email', null, array('placeholder' => 'Email','class' => 'form-control')) !!}
                                </div>
                            
                            
                                <div class="form-group">
                                    <strong>Password:</strong>
                                    {!! Form::password('password', array('placeholder' => 'Password','class' => 'form-control')) !!}
                                </div>
                            
                                <div class="form-group">
                                    <strong>Confirm Password:</strong>
                                    {!! Form::password('confirm-password', array('placeholder' => 'Confirm Password','class' =>
                                    'form-control')) !!}
                                </div>
                            
                            
                                <div class="form-group">
                                    <strong>Role:</strong>
                                    {!! Form::select('roles[]', $roles,$userRole, array('class' => 'form-control multiple')) !!}
                                </div>

                                <div class="form-group">
                                    <strong>Master Access:</strong>
                                    {!! Form::select("master_access[]", $agents, $assignedUser, [
                                        "class" => "form-control input",
                                        "id" => "master_access",
                                        "multiple" => "multiple",
                                        "autocomplete" => "off",
                                    ]) !!}
                                </div>

                                @can('user-map-bigocean-id')
                                    <div class="form-group">
                                        <strong>Big Ocean user ID:</strong>
                                        {!! Form::text('bigoceanuser_id', null, array('placeholder' => 'Big Ocean user ID','class' => 'form-control')) !!}
                                    </div>
                                @endcan
                            
                        </div>
                        
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">Update User</button>
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
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const master_access = document.getElementById('master_access');
        const gl_choices = new Choices(master_access, {
            removeItemButton: true,  // Show remove button for selected items
            placeholder: true,  // Show placeholder text
            placeholderValue: 'Select User'
        });
    });
</script>
@endpush
