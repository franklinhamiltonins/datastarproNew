<div class="d-flex justify-content-center action-btns">
    @if($row->merge_status == 1)
    <a class="btn btn-sm btn-info merge-btn d-flex justify-content-center align-items-center"
        href="{{ route('leads.merge',$row->lead_slug) }}" target="_blank"><i class="fa fa-compress"></i></a>
    @endif
    <a class="btn btn-sm  btn-info action-btn m-0 d-flex justify-content-center align-items-center"
        href="{{ route('leads.show',base64_encode($row->id)) }}"
        onclick="sessionStorage.setItem('lastLeadsManagementUrl', '');"><i class="fa fa-eye"></i></a>
    @can($editLead)
    <a class="btn btn-sm  btn-success action-btn m-0 d-flex justify-content-center align-items-center"
        href="{{ route('leads.edit',base64_encode($row->id)) }}"
        onclick="sessionStorage.setItem('lastLeadsManagementUrl', '');"><i class="fa fa-edit"></i></a>
    @endcan
    @can($deleteLead)
    {!! Form::open(['method' => 'DELETE','route' => ['leads.destroy', $row->id],'style'=>'display:inline','class' =>
    ['leadForm-'.$row->id]]) !!}


    {{-- trigger confirmation modal --}}
    <a href="#" data-toggle="modal" data-target="#deleteModal" onclick="setModal(this,'{{$row->id}}')"
        class="btn btn-sm btn-danger deletebtn action-btn m-0 d-flex justify-content-center align-items-center">
        <i class="fa fa-trash"></i>
    </a>
</div>

{!! Form::close() !!}
@endcan