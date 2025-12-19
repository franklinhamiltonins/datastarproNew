<div class="d-flex justify-content-center action-btns">
    <a class="btn btn-sm  btn-warning action-btn m-0 d-flex justify-content-center align-items-center"
        href="{{ route('leads.index',['campaign' => $row->id]) }}" target="_blank" title="View Campaign Leads"><i
            class="fas fa-list-alt"></i></a>
    @if($row->status == "COMPLETED")
    <a class="btn btn-sm  btn-info action-btn m-0 d-flex justify-content-center align-items-center"
        href="{{ route('campaigns.show', $row->id) }}" target="_blank" title="View Campaign Details"><i
            class="fa fa-eye"></i></a>
    @if(count($row->files) != 0)
    @foreach ($row->files as $file)
    <a class="btn btn-sm btn-primary action-btn m-0 d-flex justify-content-center align-items-center"
        href="{{ route('file.retrieve_files_fromStorage',['id'=>$file->id,'filename'=>$file->name])  }}" target="_blank"
        title="View Creative"><i class="fas fa-image"></i></a>
    @endforeach
    @endif
    @endif
    @can($updateCampaign)

    {{-- @if($row->status == "PENDING") --}}
    <a class="btn btn-sm  btn-success action-btn m-0 d-flex justify-content-center align-items-center"
        href="{{ route('campaigns.edit',$row->id) }}" target="_blank" title="Update Campaign"> <i
            class="fa fa-edit"></i></a>
    {{-- @endif --}}
    @endcan
    @can($deleteCampaign)
    @if($row->status == "PENDING")
    {!! Form::open(['method' => 'DELETE','route' => ['campaigns.destroy', $row->id],'style'=>'display:inline','class' =>
    ['campaignForm-'.$row->id]]) !!}


    {{-- trigger confirmation modal --}}
    <a href="#" data-bs-toggle="modal" data-bs-target="#deleteModal" onclick="setModal(this,'{{$row->id}}')"
        class="btn btn-sm btn-danger deletebtn action-btn m-0 d-flex justify-content-center align-items-center"
        title="Delete Campaign">
        <i class="fa fa-trash"></i>
    </a>


    {!! Form::close() !!}
    @endif
    @endcan
</div>
{{-- @push('scripts') --}}
<script>
$(function() {
    $('[data-toggle="tooltip"]').tooltip()
})
</script>
{{-- @endpush --}}