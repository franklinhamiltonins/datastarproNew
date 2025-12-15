@extends('layouts.app')
@section('pagetitle', 'System Setting')
@push('breadcrumbs')
<li class="breadcrumb-item active">System Setting</li>
@endpush
@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">


                <!-- <h3 class="d-block mb-3">All System Settings </h3> -->
                <div class="bg-white rounded shadow-sm border">
                    {!! Form::open(['method' => 'POST', 'route' => ['settings.storesystemsetting']]) !!}
                    @csrf


                    <div class="form-group p-3 mb-0">
                        <div class="row">
                            <div class="col-md-4">
                                <label class="mb-1">Proceed Time In Minute<sup class="mandatoryClass">*</sup>:</label>
                                <input placeholder="Proceed Time In Minute" class="form-control"
                                    name="proceed_time_in_minute" type="text"
                                    value="{{ isset($setting_time_data) && $setting_time_data->proceed_time_in_minute ? $setting_time_data->proceed_time_in_minute : 'No-data' }}">
                            </div>
                            <div class="col-md-4">
                                <label class="mb-1">Notify Email<sup class="mandatoryClass">*</sup>:</label>
                                <input placeholder="Notify Email" class="form-control"
                                    name="notify_email" type="text"
                                    value="{{ isset($setting_time_data) && $setting_time_data->notify_email ? $setting_time_data->notify_email : '' }}">
                                <span class="notifySpan">You can save multiple email with csv format eg- "testxx.com,test2xx.com"</span>
                            </div>
                            <div class="col-md-4">
                                <label class="mb-1">Proceed Time In Day Pipeline<sup class="mandatoryClass">*</sup>:</label>
                                <input placeholder="Proceed Time In Day" class="form-control"
                                    name="process_time_in_day_pipeline" type="text"
                                    value="{{ isset($setting_time_data) && $setting_time_data->process_time_in_day_pipeline ? $setting_time_data->process_time_in_day_pipeline : '0' }}">
                            </div>
                            <div class="col-md-4">
                                <label class="mb-1">Notify Email Pipeline<sup class="mandatoryClass">*</sup>:</label>
                                <input placeholder="Notify Email Pipeline" class="form-control"
                                    name="notify_email_pipeline" type="text"
                                    value="{{ isset($setting_time_data) && $setting_time_data->notify_email_pipeline ? $setting_time_data->notify_email_pipeline : '' }}">
                                <span class="notifySpan">You can save multiple email with csv format eg- "testxx.com,test2xx.com"</span>
                            </div>
                            <div class="col-md-4">
                                <label class="mb-1">Renewal Notification In Day Pipeline<sup class="mandatoryClass">*</sup>:</label>
                                <input placeholder="Renewal Notification In Day Pipeline" class="form-control"
                                    name="renewal_days_in_pipeline" type="text"
                                    value="{{ isset($setting_time_data) && $setting_time_data->renewal_days_in_pipeline ? $setting_time_data->renewal_days_in_pipeline : '' }}">
                                <span class="notifySpan">You can set this to a maximum of 365 days.</span>
                            </div>
                            
                        </div>
                    </div>
                    <div class="p-3 border-top text-right">
                        <button type="submit" class="btn btn-sm btn-primary">Submit</button>
                    </div>

                    {!! Form::close() !!}
                </div>
            </div>

        </div>
    </div>
</section>

@endsection
@push('styles')
@endpush
@push('scripts')

<script>


</script>
@endpush