<div class="d-flex justify-content-center align-items-center action-btns">
<a class="btn btn-sm  btn-success action-btn m-0 d-flex justify-content-center align-items-center" href="{{ route('platform_setting.edit',$row->id) }}"><i class="fa fa-edit"></i></a>


{!! Form::open(['method' => 'DELETE','route' => ['platform_setting.delete', $row->id],'style'=>'display:inline','class' => ['leadForm-'.$row->id,'mb-0']]) !!}
{{-- trigger confirmation modal --}}
<a href="#" data-toggle="modal" data-target="#deleteModal" onclick="setModal(this,'{{$row->id}}')" class="btn btn-sm btn-danger deletebtn action-btn m-0 d-flex justify-content-center align-items-center">
	<i class="fa fa-trash"></i>
</a>
</div>
{!! Form::close() !!}