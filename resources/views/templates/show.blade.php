@extends('layouts.app')
@section('pagetitle', 'Show Template' )
@push('breadcrumbs')
<li class="breadcrumb-item"><a href="{{route('templates.index')}}">Templates Management</a></li>
<li class="breadcrumb-item active">Show Template </li>
@endpush
@section('content')
<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12 margin-tb mb-3">
                <div class="pull-right">
                    <a class="btn btn-info btn-sm px-2" href="{{ route('templates.index') }}"><i class="fas fa-arrow-circle-left"></i> Back</a>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xl-12">
                <div class="card card-secondary">
                    <div class="card-header">
                        <h3 class="card-title">Template Information</h3>
                    </div>
                    <div class="card-body p-2 p-lg-3">
                        <div class="form-group">
                            <strong>Template Name:</strong>
                            {{ $template->template_name }}
                        </div>
                        <div class="form-group">
                            <strong>Template Slug:</strong>
                            {{ $template->template_name_slug }}
                        </div>

                        <div class="form-group">
                            <strong>Template Type:</strong>
                            {{ $template->template_type == 'sms' ? 'SMS' : 'Email' }}
                        </div>

                        @if($template->template_type == 'mail')
                        <div class="form-group">
                            <strong>Template Subject:</strong>
                            {!! $template->template_subject !!}
                        </div>
                        @endif

                        <div class="form-group">
                            <strong>Template Content:</strong>
                            {!! $template->template_content !!}
                        </div>

                        @if($is_admin)
                            <div class="form-group">
                                <strong>Set For All:</strong>
                                {{ $template->set_for_all == 'yes' ? "Yes" : "No" }}
                            </div>
                            <div class="form-group">
                                <strong>User:</strong>
                                @if($template->set_for_all == 'yes')
                                    All
                                @else
                                    <p>{!! $agent_name !!}</p>
                                @endif
                            </div>
                        @endif

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
