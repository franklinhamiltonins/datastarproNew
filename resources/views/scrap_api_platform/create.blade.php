@extends('layouts.app')
@section('pagetitle', 'Create Scrap Api Platform')
@push('breadcrumbs')
<li class="breadcrumb-item"><a href="{{route('platform_setting.index')}}">All Scrap Api Platform</a></li>
<li class="breadcrumb-item active">Create Scrap Api Platform</li>
@endpush
@section('content')
<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12 mb-3">
                <div class="pull-right">
                    <a class="btn btn-sm btn-primary" href="{{ route('platform_setting.index') }}"><i class="fas fa-arrow-circle-left"></i> Back</a>
                </div>
            </div>
        </div>
        <div class="row mt-2 mt-md-4">
            <div class="col-xl-12">
                <div class="card card-secondary">
                    <div class="card-header mb-3">
                        <h3 class="card-title">Create Api Platform</h3>
                    </div>
                    {!! Form::open(array('route' => 'platform_setting.store','method'=>'POST')) !!}
                    @include('scrap_api_platform.partials.lead-form')
                    <div class="card-footer">
                        <button type="submit " class="btn btn-primary">Add Platform Settings</button>

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