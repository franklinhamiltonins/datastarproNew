@can($deleteLead)
{!! Form::open(['method' => 'DELETE','route' => ['dialings.destroy', $row->id],'style'=>'display:inline','class' => ['leadForm-'.$row->id]]) !!}
{{-- trigger confirmation modal --}}
<a href="#" title="Delete Dialing List" data-toggle="modal" data-target="#deleteModal" onclick="setModal(this,'{{$row->id}}')" class="btn btn-sm btn-danger deletebtn action-btn">
	<i class="fa fa-trash"></i>
</a>
{!! Form::close() !!}
@endcan