@extends('layouts.app')
@section('pagetitle', 'Update My Profile')
@push('breadcrumbs')
<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
<li class="breadcrumb-item active">Edit Profile </li>
@endpush
@section('content')
<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12 mb-3">
               
                <div class="pull-right">
                    <a class="btn btn-primary" href="{{ route('dashboard') }}"><i class="fas fa-arrow-circle-left"></i> Back</a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
        <div class="card card-secondary">
            <div class="card-header mb-3">
                <h3 class="card-title">Update Profile Information</h3>
            </div>
            {!! Form::model($user, ['method' => 'POST','route' => ['update_profile', $user->id]]) !!}
            
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
                            @can ('dashboard-list')
                                @can ('dashboard-list' && 'user-edit')
                                     {!! Form::select('roles[]', $roles,$userRole, array('class' => 'form-control multiple')) !!}
                                     
                                @else
                                @if(!empty($user->getRoleNames()))
                                    @foreach($user->getRoleNames() as $v)
                                        <label class="label text-secondary">{{ $v }}</label>
                                        <input type="hidden" name="roles[]" value="{{$v}}"/>
                                    @endforeach
                                @endif
                                @endcan
                            @endcan
                        </div>
                    
                </div>
                
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Submit</button>
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
