@extends('layouts.app')
@section('pagetitle', 'Add New Business')
@push('breadcrumbs')
<li class="breadcrumb-item"><a href="{{route('leads.index')}}">Business</a></li>
<li class="breadcrumb-item active">Add New Business</li>
@endpush
@section('content')
<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="pull-right">
                    <a class="btn btn-info btn-sm px-2" href="{{ route('leads.index') }}"><i
                            class="fas fa-arrow-circle-left"></i>
                        Back</a>
                </div>
            </div>
        </div>
        <div class="row mt-2 mt-md-3">
            <div class="col-xl-12">
                <div class="card card-secondary">
                    <div class="card-header">
                        <h3 class="card-title">Create New Business</h3>
                    </div>
                    {!! Form::open(array('route' => 'leads.store','method'=>'POST')) !!}
                        <div class="p-3">
                            @include('leads.partials.lead-form')
                        </div>
                        
                        <div class="card-footer p-2 p-lg-3">
                            <button type="submit " class="btn btn-primary btn-sm">Add Lead</button>
                            <p class="text-muted mt-2 small mb-0">* You will be redirected to the new lead Edit page, in
                                order to
                                add
                                contacts </p>
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
    <script src="{{ asset('js/custom-helper.js') }}"></script>
@endpush