@extends('layouts.app')
@section('pagetitle')
    @if($pending == 1) 
        @if($page_type == 1) Create Carrier
        @elseif($page_type == 2) Edit Carrier 
        @else Show Carrier 
        @endif 
    @elseif($pending == 2) Carrier Approval 
    @endif
@endsection
@push('breadcrumbs')
<li class="breadcrumb-item"><a href="{{route('carrier.index')}}">Carrier</a></li>
<li class="breadcrumb-item active">
    @if($pending == 1) 
        @if($page_type == 1) Create Carrier
        @elseif($page_type == 2) Edit Carrier 
        @else Show Carrier 
        @endif 
    @elseif($pending == 2) Carrier Approval 
    @endif
</li>
@endpush
@section('content')
<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12 mb-3">

                <div class="pull-right">
                    @if($pending == 1)
                        <a class="btn btn-sm btn-primary" href="{{ route('carrier.index',1) }}"><i class="fas fa-arrow-circle-left"></i> Back</a>
                    @else
                        <a class="btn btn-sm btn-primary" href="{{ route('carrier.index',2) }}"><i class="fas fa-arrow-circle-left"></i> Back</a>
                    @endif
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card card-secondary">

                    <div class="card-header">
                        <h3 class="card-title">
                            @if($pending == 1) 
                                @if($page_type == 1) Create Carrier
                                @elseif($page_type == 2) Edit Carrier 
                                @else Show Carrier 
                                @endif 
                            @elseif($pending == 2) Carrier Approval 
                            @endif
                        </h3>
                    </div>
                    @if($page_type == 1)
                        {!! Form::open(array('route' => 'carrier.store','method'=>'POST', 'id' => 'carrier')) !!}
                        @csrf
                    @elseif($page_type == 2)
                        {!! Form::open(array('route' => 'carrier.update','method'=>'POST', 'id' => 'carrier')) !!}
                        <input type="hidden" name="id" value="{{$carrier->id}}">
                        <input type="hidden" name="pending" value="{{$pending}}">
                        @csrf
                    @endif
                        <div class="card-body p-2 p-lg-3 @if($page_type == 3) noactivityClass @endif">
                            <div class="row">
                                <div class="col-12 col-md-6 mb-4 mb-md-0">
                                    <div class="form-group">
                                        <strong>Carrier Name<sup class="mandatoryClass">*</sup>:</strong>
                                        {!! Form::text('carrier_name', $page_type != 1 ? $carrier->name : null, array('placeholder' => 'Name','class' => 'form-control', 'id' => 'carrier_name')) !!}
                                    </div>
                                </div>
                                @if($pending == 2)
                                    <div class="col-12 col-md-6 mb-4 mb-md-0">
                                        <div class="form-group">
                                            <strong>Acceptance<sup class="mandatoryClass">*</sup>:</strong>
                                            <select name="acceptance" class="form-control input" id="acceptance_data" >
                                                <option value="1">Accept</option>
                                                <option value="3">Reject</option>
                                            </select>
                                        </div>
                                    </div>
                                @endif
                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <strong>Insurance Type / Policy Type<sup class="mandatoryClass">*</sup>:</strong>
                                        {!! Form::select("insurance_type[]", $insurance_type, $selected_insurance_types, [
                                            "class" => "form-control input",
                                            "id" => "insutance_type_selection",
                                            "multiple" => "multiple",
                                        ]) !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @if($page_type != 3)
                        <div class="card-footer">
                            <button type="submit" id="updateCarrier" class="btn btn-primary text-uppercase">
                                @if($pending == 1)
                                    Save Carrier
                                @else
                                    Approve Carrier
                                @endif
                            </button>
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
document.addEventListener('DOMContentLoaded', function () {
    var insutance_type_selection = document.getElementById('insutance_type_selection');
    var pageType = <?= $page_type ?>;
    var gl_choices = new Choices(insutance_type_selection, {
        removeItemButton: true,  // Show remove button for selected items
        placeholder: pageType != 3,  // Show placeholder text
        placeholderValue: 'Select Insurance Type / Policy Type'
    });
});
jQuery(document).ready(function() {
    $('.numericfield').on('input', function () {
        this.value = this.value.replace(/[^0-9]/g, ''); // Allow only numbers
    });
    $(document).on('click','#updateCarrier',function(event) {
        event.preventDefault();

        if($("#carrier_name").val() == ''){
            toastr.error('Carrier Name should not be blank');
            return false; // Prevent form submission
        }
        var check_validation = 1;
        if(parseInt("{{$pending}}") == 2){
            if(parseInt($('#acceptance_data').val()) == 3){
                check_validation = 0;
            }
        }
        // console.log(check_validation);
        if(check_validation == 1 && ($("#insutance_type_selection").val().length == 0 || $("#insutance_type_selection").val()[0] == '')){
            toastr.error('Please Select Insurance Type');
            return false; // Prevent form submission
        }
        // console.log($("#insutance_type_selection").val());
        // if($("#priority").val() == ''){
        //     toastr.error('Priority should not be blank');
        //     return false; // Prevent form submission
        // }
        $('#carrier').submit();
    });

    $(document).on('change','#acceptance_data',function(event) {
        acceptance_data_function();
    });

    acceptance_data_function();

    function acceptance_data_function() {
        if(parseInt("{{$pending}}") != 1){
            if(parseInt($('#acceptance_data').val()) == 3){
                $("#updateCarrier").text("Reject Carrier");
            }
            else{
                $("#updateCarrier").text("Approve Carrier");
            }
        }
    }



})
</script>
@endpush