@extends('layouts.app')
@section('pagetitle', 'Show User' )
@push('breadcrumbs')
<li class="breadcrumb-item"><a href="{{route('users.index')}}">All Users</a></li>
<li class="breadcrumb-item active">Show User </li>
@endpush
@section('content')
<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12 margin-tb mb-3">
               
                <div class="pull-right">
                    <a class="btn btn-sm btn-primary" href="{{ route('users.index') }}"><i class="fas fa-arrow-circle-left"></i> Back</a>
                </div>
            </div>
        </div>
        
        <div class="row  mt-2 mt-md-4">
            <div class="col-xl-6">
                <div class="card card-secondary">
                    <div class="card-header mb-3">
                        <h3 class="card-title">User Information</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <strong>Name:</strong>
                            {{ $user->name }}
                        </div>
                    
                        <div class="form-group">
                            <strong>Email:</strong>
                            {{ $user->email }}
                        </div>
                    
                    
                   
                        <div class="form-group">
                            <strong>Role:</strong>
                            @if(!empty($user->getRoleNames()))
                                @foreach($user->getRoleNames() as $v)
                                    <label class="label text-secondary">{{ $v }}</label>
                                @endforeach
                            @endif
                            
                       
                        </div>
                    </div>
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
