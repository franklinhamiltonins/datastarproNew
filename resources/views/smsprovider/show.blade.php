@extends('layouts.app')
@section('pagetitle', 'Show SMS Provider' )
@push('breadcrumbs')

<li class="breadcrumb-item active">Show SMS Provider </li>
@endpush
@section('content')
<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12 margin-tb mb-3">
               
                <div class="pull-right">
                    <a class="btn btn-sm btn-primary" href="{{ route('smsprovider.index') }}"><i class="fas fa-arrow-circle-left"></i> Back</a>
                </div>
            </div>
        </div>
        
        <div class="row  mt-2 mt-md-4">
            <div class="col-xl-6">
                <div class="card card-secondary">
                    <div class="card-header mb-3">
                        <h3 class="card-title">SMS Provider Information</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <strong>Cycle Name:</strong>
                            {{ $smsprovider->cycle_name }}
                        </div>
                        <div class="form-group">
                            <strong>Minute delay :</strong>
                            {{ $smsprovider->minute_delay }}
                        </div>
                        <div class="form-group">
                            <strong>Day delay :</strong>
                            {{ $smsprovider->day_delay }}
                        </div>
                        <div class="form-group">
                            <strong>Text :</strong>
                            {{ $smsprovider->text }}
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
