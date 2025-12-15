@extends('layouts.app')
@section('pagetitle')
    @if($page_type == 1)
        Create Contact Status
    @elseif($page_type == 2)
        Edit Contact Status
    @else
        Show Contact Status
    @endif
@endsection
@push('breadcrumbs')
<li class="breadcrumb-item"><a href="{{route('contactstatus.index')}}">All Contact Status</a></li>
<li class="breadcrumb-item active">@if($page_type == 1) Create Contact Status @elseif($page_type == 2) Edit Contact Status @else Show Contact Status @endif </li>
@endpush
@section('content')
<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12 mb-3">

                <div class="pull-right">
                    <a class="btn btn-sm btn-primary" href="{{ route('contactstatus.index') }}"><i
                            class="fas fa-arrow-circle-left"></i> Back</a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card card-secondary">

                    <div class="card-header">
                        <h3 class="card-title">
                            @if($page_type == 1)
                                Create New Contact Status
                            @elseif($page_type == 2)
                                Edit Contact Status
                            @else
                                Show Contact Status
                            @endif
                        </h3>
                    </div>
                    @if($page_type == 1)
                        {!! Form::open(array('route' => 'contactstatus.store','method'=>'POST', 'id' => 'contactstatus')) !!}
                        @csrf
                    @elseif($page_type == 2)
                        {!! Form::open(array('route' => 'contactstatus.update','method'=>'POST', 'id' => 'contactstatus')) !!}
                        @csrf
                    @endif
                        <div class="card-body p-2 p-lg-3 @if($page_type == 3) noactivityClass @endif">
                            <div class="row">
                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <input type="hidden" name="id" value="{{$id}}">
                                        <strong>Contact Status Name<sup class="mandatoryClass">*</sup>:</strong>
                                        {!! Form::text('status_name', $page_type != 1 ? $ContactStatus->name : null, array('placeholder' => 'Name','class' => 'form-control', 'id' => 'status_name')) !!}
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <strong>Priority<sup class="mandatoryClass">*</sup>:</strong>
                                        {!! Form::text('priority', $page_type != 1 ? $ContactStatus->priority : null, array('placeholder' => 'Priority','class' =>
                                        'form-control numericfield' ,'id' => 'priority','maxlength' => 3)) !!}
                                    </div>
                                </div>
                                <br>
                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label for="false_status">
                                            {!! Form::checkbox('false_status', 1, $page_type != 1 && $ContactStatus->false_status == 1, ['id' => 'false_status']) !!}
                                            <strong>False Status</strong>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label for="display_in_pipedrive" id="display_in_pipedrive_label" class=" @if($page_type != 1 && $ContactStatus->false_status == 1) disabledClass @endif">
                                            {!! Form::checkbox('display_in_pipedrive', 1, $page_type != 1 && $ContactStatus->display_in_pipedrive == 1, ['id' => 'display_in_pipedrive']) !!}
                                            <strong>Display In PipeDrive</strong>
                                        </label>
                                        <p class="notifymsg" @if($page_type == 1 || ($page_type != 1 && $ContactStatus->false_status != 1)) style="display: none;" @endif>False status cannot be displayed in PipeDrive.</p>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-group  contact_status_type @if($page_type != 1 && $ContactStatus->false_status == 1) disabledClass @endif">
                                        <strong>Contact Status Type :</strong>
                                        {!! Form::select('status_type',array(
                                        null=>'Select Contact Status Type',
                                        '1'=>'Dialing',
                                        '2'=>'Own'
                                        ),!empty($ContactStatus->status_type) ? $ContactStatus->status_type : null, array('class' => 'form-control ','id'=>'status_type')) !!}
                                    </div>
                                </div>
                            </div>

                        </div>
                    @if($page_type != 3)
                        <div class="card-footer">
                            <button type="submit" id="updateContactStatus" class="btn btn-primary">Save Contact Status</button>
                        </div>
                    @endif
                    @if($page_type == 1)
                        {!! Form::close() !!}
                    @elseif($page_type == 2)
                        {!! Form::close() !!}
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
<script>
jQuery(document).ready(function() {
    $('.numericfield').on('input', function () {
        this.value = this.value.replace(/[^0-9]/g, ''); // Allow only numbers
    });
    $(document).on('click','#updateContactStatus',function(event) {
        event.preventDefault();

        if($("#status_name").val() == ''){
            toastr.error('Contact Status Name should not be blank');
            return false; // Prevent form submission
        }
        if($("#priority").val() == ''){
            toastr.error('Priority should not be blank');
            return false; // Prevent form submission
        }
        $('#contactstatus').submit();
    });

    $(document).on('click','#false_status',function(){
        if($(this).is(":checked")){
            $("#display_in_pipedrive").prop("checked",false);
            $("#status_type").val(null);
            $("#display_in_pipedrive_label").addClass("disabledClass");
            $(".contact_status_type").addClass("disabledClass");
            $(".notifymsg").show();
        }
        else{
            $("#display_in_pipedrive_label").removeClass("disabledClass");
            $(".contact_status_type").removeClass("disabledClass");
            $(".notifymsg").hide();
        }
    })

})
</script>
@endpush