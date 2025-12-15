@extends('layouts.app')
@section('pagetitle', 'Show campaign' )
@push('breadcrumbs')
<li class="breadcrumb-item"><a href="{{route('campaigns.index')}}">Marketing Campaigns</a></li>
<li class="breadcrumb-item active">Campaign Details </li>
@endpush
@section('content')
<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12 margin-tb mb-3">

                <div class="pull-right d-flex flex-wrap flex-nowrap-md align-items-center justify-content-between">
                    <a class="btn btn-info btn-sm px-2" href="{{ route('campaigns.index') }}"><i
                            class="fas fa-arrow-circle-left"></i> Back</a>
                    @can('campaign-update')
                    <a class="btn btn-success btn-sm action-btn mb-3 mb-md-0"
                        href="{{ route('campaigns.edit',$campaign->id) }}"><i class="fa fa-edit"></i>
                        <span class="d-none d-lg-inline"> Edit Campaign</span>
                    </a>
                    @endcan
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-12">
                <div class="card card-secondary campaign_info">
                    <div class="card-header">
                        <h3 class="card-title">Campaign Information</h3>
                    </div>
                    <div class="card-body p-2 p-lg-3">
                    <div class="form-row">
                        <div class="form-group col-md-12 mb-3">
                            <strong>Name:</strong>
                            {{ $campaign->name }}
                        </div>
                        <div class="form-group col-md-12 mb-3">
                            <strong>Status:</strong>
                            <span
                                class="{{($campaign->status == "PENDING") ? 'text-info' : 'text-success'}}">&nbsp;&nbsp;&nbsp;
                                <i class="fas fa-circle nav-icon"></i> {{ $campaign->status }} </span>
                        </div>
                        <div class="form-group col-md-4 mb-3">
                            <strong>Export date:</strong>
                            @if($campaign->created_at)
                            {{date("m/d/Y", strtotime($campaign->created_at))}}
                            @endif
                        </div>
                        <div class="form-group col-md-4 mb-3">
                            <strong>Campaign Date:</strong>
                            @if($campaign->campaign_date)
                            {{date("m/d/Y", strtotime($campaign->campaign_date))}}
                            @endif
                        </div>
                        <div class="form-group col-md-4 mb-3">
                            <strong>Type:</strong>
                            {{ $campaign->type }}
                        </div>
                        <div class="form-group col-md-4 mb-3">
                            <strong>Size:</strong>
                            {{ $campaign->size }}
                        </div>
                        <div class="form-group col-md-4 mb-3">
                            <strong>Lead Number:</strong>
                            {{ $campaign->lead_number }}
                        </div>
                        <div class="form-group col-md-4 mb-3">
                            <strong>User Actions:</strong>
                            {{ $actions }}
                        </div>
                        <div class="form-group col-md-12 mb-3">
                            <strong>Uploaded Creative:</strong>

                            @foreach ($campaign->files as $file)

                            <a href=" {{ route('file.retrieve_files_fromStorage',['id'=>$file->id,'filename'=>$file->name])  }}"
                                target="_blank">{{ $file->name}}</a>

                            @endforeach
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