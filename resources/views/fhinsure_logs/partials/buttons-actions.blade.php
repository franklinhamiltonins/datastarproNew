<div class="d-flex justify-content-center action-btns">
	@if($row->phone)
	<a class="btn btn-sm btn-success  contact-actions action-btn m-0 sendMessagePopup"
		title="Send Message" href="javascript:void(0);"
		data-contact-id="{{$row->id}}"
		data-message-to-contact="{{$row->first_name.' '.$row->last_name}} ({{$row->phone}})">
		<i class="fa fa-comments"></i>
	</a>
	@endif

	<a href="javascript:void(0)" title="Send Email"
		class="btn btn-primary contact-actions btn-sm m-0 text-light action-btn openEmailPopup"
		data-contact-id="{{$row->id}}"
		data-email-to-contact="{{$row->first_name.' '.$row->last_name}} ({{$row->email}})">
		<i class="fas fa-envelope"></i>
	</a>
	
	{!! Form::open(['method' => 'DELETE','route' => ['newsletter.destroy', $row->id],'style'=>'display:inline','class' => ['leadForm-'.$row->id]]) !!}
	{{-- trigger confirmation modal --}}
	<a href="#" title="Delete SMTP Configuration" data-bs-toggle="modal" data-bs-target="#deleteModal" onclick="setModal(this,'{{$row->id}}')" 
		class="btn btn-sm btn-danger deletebtn action-btn m-0 d-flex justify-content-center align-items-center">
		<i class="fa fa-trash"></i>
	</a>
	{!! Form::close() !!}
</div>