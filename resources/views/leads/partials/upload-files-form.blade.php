<div class="card-body p-2 p-lg-3">
    {!! Form::open(array('route' => ['leads.fileUpload',$lead->id],'method'=>'POST','enctype'=> 'multipart/form-data'))
    !!}

    @csrf
    @if ($message = Session::get('success'))
    <div class="alert alert-success">
        <strong>{{ $message }}</strong>
    </div>
    @endif

    @if (count($errors) > 0)
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif
    {{--💩 laravel collective 💩 --}}
    {{-- <div class="form-group ">
        <div>
            {!! Form::label('Select file') !!}
        </div>
        {!! Form::file('file', null, array('placeholder' => 'Upload File','class'=> 'form-control-file')) !!}
    </div> --}}
    <div class="form-group">
        <label for="exampleInputFile">File(s) input</label>
        <div class="input-group">
            <div class="custom-file">
                <input type="file" name="files[]" multiple class="custom-file-input" id="exampleInputFile">
                <label class="custom-file-label" for="exampleInputFile">Choose or drop file(s)</label>
            </div>

        </div>
    </div>
    <div class="form-group mb-0">
        <div>
            {!! Form::label('Description') !!}
        </div>
        {!! Form::textarea('description', null, array('placeholder' => 'Add a description for your file ','class' =>
        'form-control','rows'=>'3','maxlength'=>'188')) !!}
    </div>



</div>
<div class="card-footer p-2 p-lg-3">
    <button type="submit" name="submit" class="btn btn-outline-info btn-sm">
        <i class="fas fa-file-upload"></i> Upload File
    </button>
</div>

{!! Form::close() !!}

@push('scripts')
<script>
$('.custom-file-input').on('change', function() {
    var $this = $(this),
        $files = $this[0].files,
        $name = '',
        $separation = '',
        $i = 0;
    if ($files.length > 1) {
        $separation = ', ';
    }
    while ($i < $files.length) {
        $name += $files[$i].name + $separation;
        $i++;
    }
    if ($name.length > 80) {
        $name = $files.length + ' files';
    }
    $this.next().html($name);
});
</script>
@endpush