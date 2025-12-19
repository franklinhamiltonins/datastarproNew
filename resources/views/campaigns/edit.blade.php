@extends('layouts.app')
@section('pagetitle', 'Update Mailing List')
@push('breadcrumbs')
<li class="breadcrumb-item"><a href="{{route('campaigns.index')}}">Mailing Lists</a></li>
<li class="breadcrumb-item active">Update Mailing List </li>
@endpush
@section('content')
<!-- Main content -->
<section class="content">
	<div class="container-fluid">
		<div class="row">
			<div class="col-lg-12 mb-3">
				<div class="pull-right">
					<a class="btn btn-info btn-sm px-2" href="{{ route('campaigns.index') }}"><i class="fas fa-arrow-circle-left"></i> Back</a>
				</div>
			</div>
		</div>

		<div class="row">
			<div class="col-xl-12">
				<div class="card card-secondary">
					<div class="card-header">
						<h3 class="card-title">Update Mailing List</h3>
					</div>
					{!! Form::model($campaign, ['method' => 'PATCH','route' => ['campaigns.update', $campaign->id],'enctype'=> 'multipart/form-data']) !!}

					<div class="card-body p-2 p-lg-3">
						<div class="form-row">
							<div class="form-group col-md-8">
								<strong>Name:</strong>
								{!! Form::text('name', null, array('placeholder' => 'Name','class' => 'form-control')) !!}
							</div>
							<div class="form-group col-md-4">
								<strong>Status:</strong>
								{!! Form::select('status',array(
								'PENDING' =>'PENDING',
								'COMPLETED' =>'COMPLETED',
								), isset($campaign)? $campaign->status : [], array('class' => 'form-control multiple ')) !!}
							</div>
							<div class="form-group col-md-4">
								<strong>Campaign Date (sendout date):</strong>
								{!! Form::date('campaign_date', null, array('class' => 'form-control '))
								!!}
							</div>
							<div class="form-group col-md-4">
								<strong>Type:</strong>
								{!! Form::select('type',array(
								''=>'Select Type',
								'Postcard'=>'Postcard',
								'Letter'=>'Letter',
								'Letter/Postcard'=>'Letter/Postcard',
								'Jan Letter'=>'Jan Letter'
								), isset($campaign)? $campaign->type : [], array('class' => 'form-control multiple ')) !!}
							</div>
							<div class="form-group col-md-4">
								<strong>Size:</strong>
								{!! Form::select('size',array(
								''=>'Select Size',
								'4.25" x 5.5"' => '4.25" x 5.5"',
								'4" x 6"' => '4" x 6"',
								'4.25" x 6"' => '4.25" x 6"',
								'5" x 7"' => '5" x 7"',
								'6" x 8"' => '6" x 8"',
								'5.5" x 8.5"' => '5.5" x 8.5"',
								'4" x 9"' => '4" x 9"',
								'6" x 9"' => '6" x 9"',
								'6" x 11' => '6" x 11"'
								), isset($campaign)? $campaign->size : [], array('class' => 'form-control multiple ')) !!}
							</div>
							@csrf
							@if(count($campaign->files) > 0)
							<div class="form-group col-md-6">
								<div>
									<strong>Uploaded Creative:</strong>

									@foreach ($campaign->files as $file)

									<a href="{{ route('file.retrieve_files_fromStorage',['id'=>$file->id,'filename'=>$file->name]) }}" target="_blank">{{ $file->name}}</a>


									<a href="#" data-bs-toggle="modal" data-bs-target="#deleteModal" onclick="delete_file(this,'{{$file->id}}')" class="btn btn-sm  deletebtn action-btn">
										<i class="fa fa-trash text-danger"></i>
									</a>



									@endforeach
								</div>

							</div>
							@endif
							<div class="form-group fileForm col-md-6">
								<div>
									{{-- {!! Form::label('Upload Creative') !!}  --}}
									<strong>Upload Creative:</strong>
								</div>
								{{-- {!! Form::file('file', null, array('placeholder' => 'Upload File','class'=> 'form-control-file')) !!} --}}
								<div class="d-flex justify-content-start">
								<div class="custom-file text-left mr-2">
									<label class="custom-file-label" for="customFile">Select File</label>
									<input name="file" type="file" class="custom-file-input" id="customFile">
								</div>
								<button class="btn btn-sm text-nowrap btn-outline-info" onClick="upload_file_campaign(this)"><i class="fas fa-upload"></i> Upload</button>
								</div>

							</div>
						</div>
						<div class="mt-2 text-dark small d-flex align-items-center">
							<div class="form-group mr-2 pr-2 mb-0 border-right border-secondary">
								<span>Export Date:</span>
								@if($campaign->created_at)
								<span class="text-primary font-weight-bold">{{date("m/d/Y", strtotime($campaign->created_at))}}</span>
								@endif
							</div>
							<div class="form-group mb-0">
								<span>Lead Number:</span>
								<span class="text-primary font-weight-bold">{{ $campaign->lead_number}}</span>
							</div>
						</div>

					</div>
					<div class="card-footer">
						<button type="submit" class="btn btn-sm btn-primary">Update Campaign</button>
					</div>

				</div>
				{!! Form::close() !!}



			</div>
		</div>
	</div>



	</div><!-- /.container-fluid -->
	@include('partials.delete-modal')
</section>
<!-- /.content -->
@endsection
@push('styles')
@endpush
@push('scripts')
<script>
	/** ************************************
        Delete File Ajax
    **************************************/
	//delete file from campaign
	function delete_file(elem, $id) {
		// set the confirmation modal

		$('#deleteModal').attr('data-source', '#ordr_' + $id);

		$tar = $(elem).parents('form');
		$('#confirm').click(function() {

			$.ajax({
				type: 'GET', //THIS NEEDS TO BE GET
				url: "{{ url('/marketing-campaigns/files/delete')}}",
				//dataType: 'json',
				data: {
					id: $id
				},
				success: function(response) {
					window.location.reload(); //reload in order to see toaster
				},
				error: function(response) {
					window.location.reload(); //reload in order to see toaster
				}

			});
		});

	}
	/** ************************************
	    Upload File Ajax
	**************************************/
	//upload file to campaign
	function upload_file_campaign(elem) {


		var campaignId = ' {{$campaign->id}}'; //eend the campaign id
		var myFormData = new FormData(); // create a new formData
		myFormData.append('file', $('input[name="file"]').prop('files')[0]); // append the file to the new formData
		myFormData.append('campaignId', campaignId); // append the campaign ID to the new formData
		// send data trough ajaxs
		$.ajax({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			type: 'POST', //THIS NEEDS TO BE GET
			url: "{{ url('/marketing-campaigns/files/upload')}}",
			dataType: "json",
			cache: false,
			contentType: false,
			processData: false,
			data: myFormData, // Setting the data attribute of ajax with file_data
			type: 'post',
			success: function(data) {
				location.reload();
			},
			error: function(response) {
				window.location.reload(); //reload in order to see toaster
			}
		});
	}
</script>

@endpush