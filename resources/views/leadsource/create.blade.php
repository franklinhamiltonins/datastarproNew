@extends('layouts.app')
@section('pagetitle') 
    @if($page_type == 1) Create Lead Source
    @elseif($page_type == 2) Edit Lead Source 
    @else Show Lead Source 
    @endif 
@endsection
@push('breadcrumbs')
<li class="breadcrumb-item"><a href="{{route('leadsource.index')}}">All Lead Source</a></li>
<li class="breadcrumb-item active">
    @if($page_type == 1) Create Lead Source
    @elseif($page_type == 2) Edit Lead Source 
    @else Show Lead Source 
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
                    <a class="btn btn-sm btn-primary" href="{{ route('leadsource.index') }}"><i class="fas fa-arrow-circle-left"></i> Back</a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card card-secondary">

                    <div class="card-header">
                        <h3 class="card-title">
                            @if($page_type == 1) Create Lead Source
                            @elseif($page_type == 2) Edit Lead Source 
                            @else Show Lead Source 
                            @endif 
                        </h3>
                    </div>
                    @if($page_type == 1)
                        {!! Form::open(array('route' => 'leadsource.store','method'=>'POST', 'id' => 'leadsource')) !!}
                        @csrf
                    @elseif($page_type == 2)
                        {!! Form::open(array('route' => 'leadsource.update','method'=>'POST', 'id' => 'leadsource')) !!}
                        <input type="hidden" name="id" value="{{$leadsource->id}}">
                        @csrf
                    @endif
                        <div class="card-body p-2 p-lg-3 @if($page_type == 3) noactivityClass @endif">
                            <div class="row">
                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <strong>Lead Source Name<sup class="mandatoryClass">*</sup>:</strong>
                                        {!! Form::text('leadsource_name', $page_type != 1 ? $leadsource->name : null, array('placeholder' => 'Name','class' => 'form-control', 'id' => 'leadsource_name')) !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @if($page_type != 3)
                        <div class="card-footer">
                            <button type="submit" id="updateleadsource" class="btn btn-primary">
                                Save Lead Source
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
    var gl_choices = new Choices(insutance_type_selection, {
        removeItemButton: true,  // Show remove button for selected items
        placeholder: true,  // Show placeholder text
        placeholderValue: 'Select Insurance Type'
    });
});
jQuery(document).ready(function() {
    $('.numericfield').on('input', function () {
        this.value = this.value.replace(/[^0-9]/g, ''); // Allow only numbers
    });
    $(document).on('click','#updateleadsource',function(event) {
        event.preventDefault();

        if($("#leadsource_name").val() == ''){
            toastr.error('Lead Source Name should not be blank');
            return false; // Prevent form submission
        }
        $('#leadsource').submit();
    });

})
</script>
@endpush