<div class="d-flex justify-content-center action-btns">
    @if($is_admin)
    <a class="btn btn-sm btn-primary action-btn assign_agent m-0 d-flex justify-content-center align-items-center @if($row->referral_marker) {{'disabledClass'}} @endif"
        data-bs-toggle="modal" data-bs-target="#agentassignmodal" data-current="{{ $row->id }}"
        data-assigned_agents="{{ $row->agent_ids }}" title="Assign Agent" data-hid="{{ route('dialings.show', base64_encode($row->id)) }}" href="#"
        ><i class="fa fa-tags"></i></a>
    @endif

    <a class="btn btn-sm btn-info action-btn m-0 d-flex justify-content-center align-items-center"
        title="Show Dialing List" href="/dialings/show/{{base64_encode($row->id) }}"><i class="fa fa-eye"></i></a>




    @can($deleteLead)
    {!! Form::open(['method' => 'DELETE','route' => ['dialings.destroy', $row->id],'style'=>'display:inline','class' =>
    ['leadForm-'.$row->id]]) !!}
    {{-- trigger confirmation modal --}}
    <a href="#" title="Delete Dialing List" data-bs-toggle="modal" data-bs-target="#deleteModal"
        onclick="setModal(this,'{{$row->id}}')"
        class="btn btn-sm btn-danger deletebtn action-btn m-0 d-flex justify-content-center align-items-center @if($row->referral_marker) {{'disabledClass'}} @endif">
        <i class="fa fa-trash"></i>
    </a>
    {!! Form::close() !!}
    @endcan
</div>