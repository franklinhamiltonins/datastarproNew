@extends('layouts.app')
@section('pagetitle', 'Dashboard')
@push('breadcrumbs')
{{-- <li class="breadcrumb-item active">Starter Page</li> --}}
@endpush
@section('content')
<section class="content">
    <div class="container-fluid dashboard-sec">
        <div class="mt-2">
            <div class="row">
                <div class="col-12 col-sm-6 col-md-3 d-flex mb-3">
                    <div
                        class="info-box mb-0 p-1 p-lg-4 py-3 py-lg-5 overflow-hidden d-flex flex-column align-items-center">
                        <span class="info-box-icon rounded-0 mb-3 text-cyan"><i
                                class=" fas fa-chalkboard-teacher text-xl"></i></span>
                        <div class="info-box-content px-0 px-lg-3 position-relative text-center">
                            <span class="info-box-number mb-2 pb-1">
                                {{ $leads }}
                                {{-- <small>%</small> --}}
                            </span>
                            <span class="info-box-text text-gray">Leads</span>
                        </div>
                        <!-- /.info-box-content -->
                    </div>
                    <!-- /.info-box -->
                </div>
                <!-- /.col -->
                <div class="col-12 col-sm-6 col-md-3 d-flex mb-3">
                    <div
                        class="info-box mb-0 p-1 p-lg-4 py-3 py-lg-5 overflow-hidden d-flex flex-column align-items-center">
                        <span class="info-box-icon rounded-0 mb-3 text-danger"><i
                                class="fas fa-chart-bar text-xl"></i></span>

                        <div class="info-box-content px-0 px-lg-3 position-relative text-center">
                            <span class="info-box-number mb-2 pb-1"> {{ $campaigns }}</span>
                            <span class="info-box-text text-gray">Marketing Campaigns</span>
                        </div>
                        <!-- /.info-box-content -->
                    </div>
                    <!-- /.info-box -->
                </div>
                <!-- /.col -->

                <!-- fix for small devices only -->
                <div class="clearfix hidden-md-up"></div>

                <div class="col-12 col-sm-6 col-md-3 d-flex mb-3">
                    <div
                        class="info-box mb-0 p-1 p-lg-4 py-3 py-lg-5 overflow-hidden d-flex flex-column align-items-center">
                        <span class="info-box-icon rounded-0 mb-3 text-primary"><i
                                class="fas fa-ellipsis-h text-xl"></i></span>

                        <div class="info-box-content px-0 px-lg-3 position-relative text-center">
                            <span class="info-box-number mb-2 pb-1">{{ $pendingCampaigns }}</span>
                            <span class="info-box-text text-gray">Pending Campaigns</span>
                        </div>
                        <!-- /.info-box-content -->
                    </div>
                    <!-- /.info-box -->
                </div>
                <!-- /.col -->
                <div class="col-12 col-sm-6 col-md-3 d-flex mb-3">
                    <div
                        class="info-box mb-0 p-1 p-lg-4 py-3 py-lg-5 overflow-hidden d-flex flex-column align-items-center">
                        <span class="info-box-icon rounded-0 mb-3 text-success"><i
                                class="fas fa-check text-xl"></i></span>

                        <div class="info-box-content px-0 px-lg-3 position-relative text-center">
                            <span class="info-box-number mb-2 pb-1">{{ $completedCampaigns }}</span>
                            <span class="info-box-text text-gray">Completed Campaigns</span>
                        </div>
                        <!-- /.info-box-content -->
                    </div>
                    <!-- /.info-box -->
                </div>
                <!-- /.col -->
            </div>
        </div>
    </div>
</section>
@endsection
@push('styles')
@endpush
@push('scripts')
@endpush