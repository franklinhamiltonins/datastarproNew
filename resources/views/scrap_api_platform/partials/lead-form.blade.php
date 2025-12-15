<div class="card-body lead-update">
	<div class="form-row">
		<div class="form-group col">
			<strong>Platform Name<sup class="mandatoryClass">*</sup>:</strong>
			{!! Form::text('platform_name', null, array('placeholder' => 'Platform Name ','class'=> 'form-control', isset($scrapApi)? 'disabled' : '')) !!}
		</div>
	</div>
	<div class="form-row">
		<div class="form-group col">
			<strong>Api Key:</strong>
			{!! Form::text('api_key', null, array('placeholder' => 'Api Key ','class'=> 'form-control')) !!}
		</div>
		<div class="form-group col">
			<strong>Api Username:</strong>
			{!! Form::text('api_username', null, array('placeholder' => 'Api Username ','class'=> 'form-control')) !!}
		</div>
	</div>
	<div class="form-row">
		<div class="form-group col">
			<strong>Api Auth Url:</strong>
			{!! Form::text('api_auth_url', null, array('placeholder' => 'Api Auth Url ','class'=> 'form-control')) !!}
		</div>
		<div class="form-group col">
			<strong>Api Contact Search Url:</strong>
			{!! Form::text('api_contact_search_url', null, array('placeholder' => 'Api Contact Search Url ','class'=> 'form-control')) !!}
		</div>

	</div>

	<div class="form-row">
		<div class="form-group col">
			<strong>Auth Token Required</strong>
			{!! Form::select('status',array(
			1=>'Yes',
			0=>'No',
			),isset($scrapApi)? $scrapApi->auth_token_required : [], array('class' => 'form-control ')) !!}
		</div>
		<div class="form-group col">
			<strong>Api Auth Token:</strong>
			{!! Form::text('api_auth_token', null, array('placeholder' => 'Api Auth Token ','class'=> 'form-control')) !!}
		</div>
		<div class="form-group col">
			<strong>Auth Token Expiry Date :</strong>
			{!! Form::date('auth_expiry_date', null,array('class' => 'form-control '))
			!!}
		</div>
	</div>

	<div class="form-row">
		<div class="form-group col">
			<strong>Priority Order<sup class="mandatoryClass">*</sup></strong>
			{!! Form::number('priority_order', null, array('placeholder' => 'Priority Order ','class'=> 'form-control')) !!}
		</div>
		<div class="form-group col">
			<strong>Platform Type</strong>
			{!! Form::select('platform_type',array(
			'Scrap'=>'Scrap',
			'Others'=>'Others',
			),isset($scrapApi)? $scrapApi->platform_type : [], array('class' => 'form-control ')) !!}
		</div>
		<div class="form-group col">
			<strong>Status</strong>
			{!! Form::select('status',array(
			1=>'Active',
			0=>'Inactive',
			),isset($scrapApi)? $scrapApi->status : [], array('class' => 'form-control ')) !!}
		</div>
	</div>
</div>

@push('scripts')

@endpush