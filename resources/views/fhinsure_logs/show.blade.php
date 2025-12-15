@extends('layouts.app')
@section('pagetitle', 'Show Newsletter' )
@push('breadcrumbs')

<li class="breadcrumb-item active">Show Newsletter</li>
@endpush
@section('content')
<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12 margin-tb mb-3">
                <div class="pull-right">
                    <a class="btn btn-info btn-sm px-2" href="{{ route('newsletter.index') }}"><i class="fas fa-arrow-circle-left"></i> Back</a>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-xl-12">
                <div class="card card-secondary">
                    <div class="card-header">
                        <h3 class="card-title">Newsletter</h3>
                    </div>
                    <div class="card-body p-2 p-lg-3">
                        <div class="form-row">
                            <div class="form-group col-lg-6">
                                <strong>First Name:</strong>
                                {{ isset($fhinsureLog->first_name) ? $fhinsureLog->first_name : "" }}
                            </div>
                            <div class="form-group col-lg-6">
                                <strong>Last Name:</strong>
                                {{ isset($fhinsureLog->last_name) ? $fhinsureLog->last_name : "" }}
                            </div>
                            <div class="form-group col-lg-6">
                                <strong>Email:</strong>
                                {{ isset($fhinsureLog->email) ? $fhinsureLog->email : "" }}
                            </div>

                            <div class="form-group col-lg-6">
                                <strong>Phone:</strong>
                                {{ isset($fhinsureLog->phone) ? $fhinsureLog->phone : "" }}
                            </div>

                            <div class="form-group col-lg-6">
                                <strong>Zip:</strong>
                                {{ isset($fhinsureLog->zip) ? $fhinsureLog->zip : "" }}
                            </div>

                            <div class="form-group col-lg-6">
                                <strong>Site Name:</strong>
                                {{ isset($fhinsureLog->site_name) ? $fhinsureLog->site_name : "" }}
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
<script>
jQuery(document).ready(function() {

    $('#showPassword').on('click', function() {
        $("#showPassword").css('display','none');
        $("#hidePassword").css('display','inline-block');
        $("#showData").css('display','block');
        $("#hideData").css('display','none');
    });

    $('#hidePassword').on('click', function() {
        $("#showPassword").css('display','inline-block');
        $("#hidePassword").css('display','none');
        $("#hideData").css('display','block');
        $("#showData").css('display','none');
    });
})
</script>
@endpush
