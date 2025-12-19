<div class="d-flex justify-content-center action-btns">
	<a class="btn btn-sm  btn-info action-btn m-0 d-flex justify-content-center align-items-center"
		href="{{ route('smtps.show',base64_encode($row->id)) }}">
		<i class="fa fa-eye"></i>
	</a>
	<a class="btn btn-sm  btn-success action-btn m-0 d-flex justify-content-center align-items-center"
		href="{{ route('smtps.edit',base64_encode($row->id)) }}">
		<i class="fa fa-edit"></i>
	</a>
	{!! Form::open(['method' => 'DELETE','route' => ['smtps.destroy', $row->id],'style'=>'display:inline','class' => ['leadForm-'.$row->id]]) !!}
	{{-- trigger confirmation modal --}}
	<a href="#" title="Delete SMTP Configuration" data-bs-toggle="modal" data-bs-target="#deleteModal" onclick="setModal(this,'{{$row->id}}')" 
		class="btn btn-sm btn-danger deletebtn action-btn m-0 d-flex justify-content-center align-items-center">
		<i class="fa fa-trash"></i>
	</a>
	{!! Form::close() !!}
</div>