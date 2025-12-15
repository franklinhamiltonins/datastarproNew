<div class="modal fade" id="addNoteModal" tabindex="-1" role="dialog" aria-labelledby="addNoteModalTitle" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content p-0">
			<div class="modal-header p-2 p-lg-3 align-items-center">
				<h5 class="modal-title" id="exampleModalLongTitle">Add New Note</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			{!! Form::open(array('route' => ['leads.note_store',$lead->id],'method'=>'POST','onsubmit' => 'return handleSubmit();')) !!}

			<div class="modal-body p-2 p-lg-3">

				{{-- <div class="form-group" style="display:none">
					<strong>Title:</strong>
					{!! Form::text('title', null, array('placeholder' => 'Note title - max 191 chars ','class' => 'form-control','maxlength'=>'191')) !!}
				</div> --}}
				<div class="form-group mb-2">
					<label class="form-label small mb-1 font-weight-normal">Contacts:</label>
					<select class="commonClass form-control leadscontactstatus contact-info-list-select px-2" class="form-control leadselectstatus" name="contact_id">
						<option value="">Select contact</option>
						@foreach($contacts as $contact)
						<option value="{{$contact->id}}">
							{{$contact->c_full_name}}
						</option>
						@endforeach
					</select> 
				</div>
				<div class="form-group mb-2">
					<label class="form-label small mb-1 font-weight-normal">Description<sup class="mandatoryClass">*</sup>:</label>
					{!! Form::textarea('description', null, array('placeholder' => 'Note description','class' => 'form-control px-2','id'=> 'note_desc','rows'=>'15')) !!}
				</div>

				<!-- <div class="form-group">
					<strong>Status:</strong>
					<select class="commonClass form-control leadscontactstatus contact-info-list-select" class="form-control leadselectstatus" name="contact_status">
						<option value="">Select Status</option>
						@foreach($statusOptions as $statusOption)
						<option value="{{ $statusOption }}">
							{{ $statusOption }}
						</option>
						@endforeach
					</select> 
				</div> -->

				<!-- {!! Form::hidden('selected_contact_id', null, ['id' => 'selected_contact_id']) !!}
				{!! Form::hidden('watchlist_id', null, ['id' => 'watchlist_id']) !!} -->



			</div>
			<div class="modal-footer p-2 p-lg-3">
				<button type="submit" class="btn btn-primary btn-sm">Save Note</button>
				<button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
			</div>
			{!! Form::close() !!}
		</div>
	</div>
</div>
<!-- <script>
let theNoteEditor;

document.addEventListener('DOMContentLoaded', function () {
    ClassicEditor
        .create(document.getElementById('note_desc'))
        .then(editor => {
            theNoteEditor = editor;
        })
        .catch(error => {
            console.error(error);
        });
});

function handleSubmit() {
    if (theNoteEditor) {
        const editorData = theNoteEditor.getData().trim();

        // console.log(editorData);

        if (!editorData || editorData === '<p></p>') {
            toastr.error('Description is required');
            return false;
        }

        // manually set value back to textarea (very important!)
        document.getElementById('note_desc').value = editorData;
    }

    return true; // form is valid
}

</script> -->
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    ClassicEditor
        .create(document.getElementById('note_desc'), {
            extraPlugins: [ MyCustomUploadAdapterPlugin ],
        })
        .then(editor => {
            theNoteEditor = editor;
        })
        .catch(error => {
            console.error(error);
        });
});

function handleSubmit() {
    if (theNoteEditor) {
        const editorData = theNoteEditor.getData().trim();

        // console.log(editorData);

        if (!editorData || editorData === '<p></p>') {
            toastr.error('Description is required');
            return false;
        }

        document.getElementById('note_desc').value = editorData;
    }

    return true; // form is valid
}
</script>
