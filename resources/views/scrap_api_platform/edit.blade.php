@extends('layouts.app')
@section('pagetitle', 'Update Scrap Api Platform')
@push('breadcrumbs')
<li class="breadcrumb-item"><a href="{{route('platform_setting.index')}}">All Scrap Api Platform</a></li>
<li class="breadcrumb-item active">Update Scrap Api Platform</li>
@endpush
@section('content')
<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12 mb-3">
                <div class="pull-right">
                    <a class="btn btn-info btn-sm px-2 mb-3 mb-md-0" href="{{ route('platform_setting.index') }}"><i class="fas fa-arrow-circle-left"></i> Back</a>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xl-12">
                <div class="card card-secondary">
                    <div class="card-header">
                        <h3 class="card-title">Update Api Platform</h3>
                    </div>

                    {!! Form::model($scrapApi, ['method' => 'POST','route' => ['platform_setting.update', $scrapApi->id], 'id'
                    => 'lead_update_form']) !!}
                    @include('scrap_api_platform.partials.lead-form')
                    <div class="card-footer">
                        <button type="submit " class="btn btn-primary">Update</button>

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