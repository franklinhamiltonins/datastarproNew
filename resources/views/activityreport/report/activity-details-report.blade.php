@extends('layouts.app')
@section('pagetitle', 'Agent Activity Report Details')
@push('breadcrumbs')
<li class="breadcrumb-item active"><a href="javascript: void(0);">Agent Activity Details</a></li>
<li class="breadcrumb-item">Reports</li>
@endpush
@section('content')
<section class="content">
    <div class="container-fluid dashboard-sec">
        <a href="{{ route('agentreport.activityReport') }}" class="btn btn-info btn-sm px-2 mb-3 mb-md-0">
            <i class="fas fa-arrow-circle-left"></i>
            Back
        </a>
        <div class="card mt-3">
            <!-- <div class="card-header bg-secondary text-white">
                <h5 class="mb-0">Agent Activity Report Details</h5>
            </div> -->
            <div class="card-body">
                <div class="border p-3 mb-4 rounded">
                    <div class="form-row">
                        <div class="form-group col-12 col-sm-6 col-lg-4 col-xl-3 px-3 mb-2">
                            <strong>Agent:</strong>
                            <span class="small">{{ $report->agent->name ?? '' }}</span>
                        </div>
                        <div class="form-group col-12 col-sm-6 col-lg-4 col-xl-3 px-3 mb-2">
                            <strong>Date:</strong>
                            <span class="small"> {{!empty($report->date)?date('m/d/Y',strtotime($report->date)):""}}</span>
                        </div>
                        <div class="form-group col-12 col-sm-6 col-lg-4 col-xl-3 px-3 mb-2">
                            <strong>Appointments:</strong>
                            <span class="small">{{ $report->appointments ?? '' }}</span>
                        </div>
                        <div class="form-group col-12 col-sm-6 col-lg-4 col-xl-3 px-3 mb-2">
                            <strong>Policies:</strong>
                            <span class="small">{{ $report->policies ?? '' }}</span>
                        </div>
                        <div class="form-group col-12 col-sm-6 col-lg-4 col-xl-3 px-3 mb-2 mb-lg-2 mb-xl-0">
                            <strong>Expiring Policy Premium:</strong>
                            <span class="small">{{!empty($report->expiry_policies_premium)?'$'.formatUSNumber($report->expiry_policies_premium,2):"$0.00"}}</span>
                        </div>
                        <div class="form-group col-12 col-sm-6 col-lg-4 col-xl-3 px-3 mb-2 mb-lg-2 mb-xl-0">
                            <strong>Community Name:</strong>
                            <span class="small">
                                @if(!empty($report->community_id))
                                    <a  class="anchortag" href="/leads/edit/{{base64_encode($report->community_id)}}" >{{ $report->leads->name ?? '' }}</a>
                                @else 
                                    {{ $report->community_name ?? '' }}
                                @endif

                            </span>
                        </div>
                        <div class="form-group col-12 col-sm-6 col-lg-4 col-xl-3 px-3 mb-0">
                            <strong>AOR Break Down:</strong>
                            <span class="small">{{ $report->aor_breakdown ?? '' }}</span>
                        </div>
                    </div>
                </div>
                <h5 class="mb-3">AOR Details</h5>
                <div class="mb-1">
                    @if($aor->isNotEmpty())
                        @forelse($aor as $index => $item)
                            <div class="border p-3 mb-3 rounded">
                                <div class="form-row">
                                    <div class="form-group col-12 col-sm-6 col-lg-4 col-xl-3 px-3 mb-2 mb-xl-0">
                                        <strong class="mb-0">AOR:</strong>
                                        <span class="small">{{ $item->aor ?? '' }}</span>
                                    </div>
                                    <div class="form-group col-12 col-sm-6 col-lg-4 col-xl-3 px-3 mb-2 mb-xl-0">
                                        <strong class="mb-0">Community Name:</strong>
                                        <span class="small">{{ $item->aor_community_name ?? '' }}</span>
                                    </div>
                                    <div class="form-group col-12 col-sm-6 col-lg-4 col-xl-3 px-3 mb-2 mb-sm-0 mb-lg-2 mb-xl-0">
                                        <strong class="mb-0">Effective Date:</strong>
                                        <span class="small">{{!empty($item->aor_effective_date)?date('m/d/Y',strtotime($item->aor_effective_date)):""}}</span>
                                    </div>
                                    <div class="form-group col-12 col-sm-6 col-lg-4 col-xl-3 px-3 mb-0">
                                        <strong class="mb-0">Expiring AOR Premium:</strong>
                                        <span class="small">{{!empty($item->expiring_aor_premium)?'$'.formatUSNumber($item->expiring_aor_premium,2):"$0.00"}}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="alert alert-secondary text-center mb-3" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            No AOR entries found.
                        </div>
                    @endif
                </div>
                <h5 class="mb-3">Uploaded Files</h5>
                @if($files->isNotEmpty())
                    <ul class="list-group">
                        @foreach($files as $file)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-file-alt text-primary mr-2"></i>
                                    {{ $file->original_name }}
                                </div>
                                <a href="{{ route('agentreport.file_download',$file->id) }}"  download="{{$file->original_name}}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-download"></i> Download
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="alert alert-secondary text-center mb-3" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        No files uploaded.
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection
@push('styles')
@endpush
@push('scripts')
<script >
    setInSessionStorage("backpage_url",window.location.href);
</script>
@endpush