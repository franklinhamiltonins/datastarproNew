@can($leadFileDownload)
<a class="btn btn-sm " href="{{ route('leads.file_download',$row->id) }}"  download="{{$row->name}}"><i class="text-secondary fas fa-download"></i></a>
                                            
@endcan
@can($leadFileDelete)
    {!! Form::open(['method' => 'DELETE','route' => ['files.file_destroy_onlead', $row->id],'style'=>'display:inline','class' => ['leadFileForm-'.$row->id]]) !!} 
    

        
            <a href="#" data-toggle="modal" data-target="#deleteModal" onclick="setModal(this,'{{$row->id}}')" class="btn btn-sm  deletebtn ">
                <i class="fa fa-trash text-danger"></i>
            </a>
        
        
    {!! Form::close() !!}
@endcan