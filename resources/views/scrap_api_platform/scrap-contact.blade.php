@extends('layouts.app')
@section('pagetitle', 'Scrap Contact')
@push('breadcrumbs')
<li class="breadcrumb-item"><a href="{{route('platform_setting.index')}}">Scrap Contact</a></li>
<li class="breadcrumb-item active">Scrap Contact</li>
@endpush
@section('content')
<?php //dd($scrap_vars);
// foreach ($scrap_vars['all_scrap'] as $key => $val) {
//     echo $key;
// foreach ($scrap_vars['all_scrap'][$key] as $innerkey => $innerval) {
//     print_r($innerval['contacts']);
// }
// }
?>
<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12 mb-3">
                <div class="pull-right">
                    <a class="btn btn-info btn-sm px-2 text-white" href="{{ route('platform_setting.index') }}"><i class="fas fa-arrow-circle-left"></i> Back</a>
                </div>
            </div>
        </div>
        <div class="row">


            <!-- <div class="row"> -->
            <div class="col-xl-12">
                <div class="card card-secondary">
                    <div class="card-header">
                        <h3 class="card-title mb-0">Scrap Contact</h3>
                    </div>

                    {!! Form::open(['id' => 'scrap_contact_form','url'=>'#']) !!}
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group col">
                                <strong>Platform Name<sup class="mandatoryClass">*</sup>:</strong>
                                {!! Form::text('platform_name', isset($scrap_vars['platform_name'])? $scrap_vars['platform_name'] : '', array('placeholder' => 'Platform Name ','class'=> 'form-control','id'=>'scrap_platform_name' ,isset($scrap_vars['platform_name'])? 'disabled' : '')) !!}
                            </div>
                            <div class="form-group col">
                                <strong>Contact Limit<sup class="mandatoryClass">*</sup></strong>
                                {!! Form::number('limit', isset($scrap_vars['limit'])? $scrap_vars['limit'] : '', array('placeholder' => 'Contact Limit','class'=> 'form-control','id'=>'scrap_limit' )) !!}
                            </div>
                        </div>

                    </div>
                    {!! Form::close() !!}
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary" id="scrap_contact_submit">Scrap Contact</button>

                        <button class="btn btn-primary" id="spinner-div" type="button" disabled>
                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            Loading...
                        </button>
                    </div>

                </div>
            </div>
            <!-- </div> -->

            <!-- Accordian -->
                <div class="col-xl-12">
                    <div class="card card-secondary">
                        <div class="card-header">
                            <h3 class="card-title">All scrapped records :</h3>
                        </div>
                        <div class="card-body p-2 p-lg-3">
                            <div class="scrap_count">
                                <div class="accordion" id="myAccordion">
                                    <div class="row">
                                        <?php $key_id = 0; ?>
                                        @foreach($scrap_vars['all_scrap'] as $key=>$val)

                                        <div class="col-12 col-md-6 {{count($scrap_vars['all_scrap']) > 3 ? 'col-lg-3' : 'col-lg-4'}}">
                                            <div class="accordion-item">
                                                <div class="position-relative">
                                                    <h2 class="accordion-header" id="header1">
                                                        <button class="accordion-button p-2" type="button" data-bs-toggle="collapse" data-bs-target="#panel-{{$key_id}}">
                                                            {{ucfirst($key)}} :
                                                            {{count($scrap_vars['all_scrap'][$key])}}

                                                        </button>
                                                    </h2>
                                                    <a class="btn btn-sm btn-primary position-absolute" href="/platform_setting/scrap_export/{{$key}}"><i class="fas fa-file-download"></i> CSV</a>
                                                </div>

                                                <div id="panel-{{$key_id}}" class="accordion-collapse collapse" aria-labelledby="panel1Heading">

                                                    <div class="accordion-body">
                                                        <div class="scrap-lists">
                                                            @if(count($scrap_vars['all_scrap'][$key]) > 0)
                                                            @foreach($scrap_vars['all_scrap'][$key] as $inner_key=>$inner_val)
                                                            <ul class="list-unstyled">
                                                                <!-- <li>Api Used : {{$inner_val['apiPlatform']['platform_name']}}</li> -->
                                                                <li>Lead : {{ucwords(strtolower($inner_val['leads']['name']))}}</li>
                                                                <li>First name : {{$inner_val['c_first_name']}}</li>
                                                                <li>Last name : {{$inner_val['c_last_name']}}</li>
                                                            </ul>
                                                            @endforeach
                                                            @else
                                                            <div class="accordion-body text-center"> No record found!</div>
                                                            @endif
                                                        </div>

                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                        <?php $key_id++; ?>
                                        @endforeach
                                    </div>
                                    <!-- Add your more panels here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <!-- <div id="spinner-div" class="pt-5">
                <div class="spinner-border text-primary" role="status">
                </div>
            </div> -->

        </div><!-- /.container-fluid -->

</section>

<!-- /.content -->
@endsection
@push('styles')
@endpush
@push('scripts')

<script>
    $(document).ready(function() {

        $('#scrap_contact_submit').click(function(e) {
            var platform_name = $('#scrap_platform_name').val();
            var limit = $('#scrap_limit').val();
            scrapContactApiCall(platform_name, limit);
        });
        //scrap api call
        function scrapContactApiCall(platform_name, limit) {
            $('#spinner-div').show();
            $('#scrap_contact_submit').hide();
            jQuery.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                type: 'post',
                url: "{{ url('/platform_setting/scrap_contact/')}}",
                data: {
                    platform_name: platform_name,
                    limit: limit
                },
                success: function(response) {
                    $('#spinner-div').hide();
                    $('#scrap_contact_submit').show();
                    console.log(response);
                    if (response.status) {
                        toastr.success(response.message + ' Contacts Updated : ' + response.data.contacts_updated_count + ', Contacts Skipped : ' + response.data.contacts_skipped_count);
                    }
                    setTimeout(function() {
                        window.location.reload();
                    }, 2000);
                    // reload in order to see toaster 
                },
                error: function(response) {
                    $('#spinner-div').hide();
                    $('#scrap_contact_submit').show();
                    console.log(response);
                    // window.location.reload();
                    // reload in order to see toaster
                }
            });
        };
    });
</script>

<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js">

</script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.0/jquery-ui.min.css" integrity="sha512-LDB28UFxGU7qq5q67S1iJbTIU33WtOJ61AVuiOnM6aTNlOLvP+sZORIHqbS9G+H40R3Pn2wERaAeJrXg+/nu6g==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" /> -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js">
</script>
@endpush