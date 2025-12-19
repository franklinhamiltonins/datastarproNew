<div class="d-flex justify-content-center action-btns">
	<a class="btn btn-sm  btn-info action-btn m-0 d-flex justify-content-center align-items-center"
		href="{{ route('smsprovider.show',base64_encode($row->id)) }}">
		<i class="fa fa-eye"></i>
	</a>
	@can('template-edit')
	<a class="btn btn-sm  btn-success action-btn m-0 d-flex justify-content-center align-items-center"
		href="{{ route('smsprovider.edit',base64_encode($row->id)) }}" >
		<i class="fa fa-edit"></i>
	</a>
	@endcan
	@can('template-delete')
	{!! Form::open(['method' => 'DELETE','route' => ['smsprovider.destroy', $row->id],'style'=>'display:inline','class' => ['leadForm-'.$row->id]]) !!}
	{{-- trigger confirmation modal --}}
	<a href="#" title="Delete Sms Provider" data-bs-toggle="modal" data-bs-target="#deleteModal" onclick="setModal(this,'{{$row->id}}')" 
		class="btn btn-sm btn-danger deletebtn action-btn m-0 d-flex justify-content-center align-items-center">
		<i class="fa fa-trash"></i>
	</a>
	{!! Form::close() !!}
	@endcan
</div>