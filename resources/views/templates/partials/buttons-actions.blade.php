<div class="d-flex justify-content-center action-btns">
	<a class="btn btn-sm  btn-info action-btn m-0 d-flex justify-content-center align-items-center"
		href="{{ route('templates.show',base64_encode($row->id)) }}">
		<i class="fa fa-eye"></i>
	</a>
	@if($row->created_by == auth()->user()->id)
		<a class="btn btn-sm  btn-success action-btn m-0 d-flex justify-content-center align-items-center"
			href="{{ route('templates.edit',base64_encode($row->id)) }}" {{$row->created_by == auth()->user()->id ? "" : "disabled"}}>
			<i class="fa fa-edit"></i>
		</a>
		{!! Form::open(['method' => 'DELETE','route' => ['templates.destroy', $row->id],'style'=>'display:inline','class' => ['leadForm-'.$row->id]]) !!}
			{{-- trigger confirmation modal --}}
			<a href="#" title="Delete Template" data-bs-toggle="modal" data-bs-target="#deleteModal" onclick="setModal(this,'{{$row->id}}')" 
				class="btn btn-sm btn-danger deletebtn action-btn m-0 d-flex justify-content-center align-items-center">
				<i class="fa fa-trash"></i>
			</a>
		{!! Form::close() !!}
	@else
		<a class="btn btn-sm  btn-success action-btn m-0 d-flex justify-content-center align-items-center disabled" href="#">
			<i class="fa fa-edit"></i>
		</a>
		{{-- trigger confirmation modal --}}
		<a href="#" title="Delete Template" 
			class="btn btn-sm btn-danger deletebtn action-btn m-0 d-flex justify-content-center align-items-center disabled">
			<i class="fa fa-trash"></i>
		</a>
	@endif
	
</div>