@extends('layouts.app')
@section('pagetitle', 'Show Role')
@push('breadcrumbs')
<li class="breadcrumb-item"><a href="{{route('roles.index')}}">All Roles</a></li>

<li class="breadcrumb-item active">Show Role</li>
@endpush
@section('content')
<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12 margin-tb mb-3">
                <div class="pull-right">
                    <a class="btn btn-sm btn-primary" href="{{ route('roles.index') }}"><i class="fas fa-arrow-circle-left"></i> Back</a>
                </div>
            </div>
        </div>
        
        <div class="row mt-2 mt-md-4">
            <div class="col-xl-6">
                <div class="card card-secondary">
                    <div class="card-header mb-3">
                        <h3 class="card-title">Role Information</h3>
                    </div>
                    <div class="card-body">
                            <div class="form-group">
                                <strong>Name:</strong>
                                {{ $role->name }}
                            </div>
                        
                            <div class="form-group">
                                <strong>Permissions:</strong>
                                @if(!empty($rolePermissions))
                                    @if ($role->name == "Super Admin")
                                    <label class="label text-secondary">Super Admin role has all permissions granted</label>
                                    @else
                                        @foreach($rolePermissions as $v)
                                            <label class="label text-secondary">{{ $v->name }},</label>
                                        @endforeach
                                    @endif
                                @endif
                            </div>
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