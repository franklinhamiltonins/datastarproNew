<div class="modal fade" id="editNoteModal" tabindex="-1" role="dialog" aria-labelledby="addNoteModalTitle" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content p-0">
			<div class="modal-header p-0 p-lg-3 align-items-center">
				<h5 class="modal-title" id="exampleModalLongTitle">Update Note</h5>
				<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>

			<div class="modal-body p-0 p-lg-3">

				{{-- <div class="form-group" style="display:none">
					<strong>Title:</strong>
					{!! Form::text('title', null, array('placeholder' => 'Note title - max 191 chars','class' => 'form-control noteTitleEdit','maxlength'=>'191')) !!}
				</div> --}}
				<div class="form-group">
					<strong>Contacts:</strong>
					<select class="commonClass form-control leadscontactstatus contact-info-list-select noteContactEdit" class="form-control leadselectstatus" name="contact_id">
						<option value="">Select contact</option>
						@foreach($contacts as $contact)
						<option value="{{$contact->id}}">
							{{$contact->c_full_name}}
						</option>
						@endforeach
					</select> 
				</div>
				<div class="form-group">
					<strong>Description<sup class="mandatoryClass">*</sup>:</strong>
					{!! Form::textarea('description', null, array('placeholder' => 'Note description ','class' => 'form-control noteDescriptionEdit','id'=> 'note_desc_edit','rows'=>'10')) !!}
				</div>

			</div>
			<div class="modal-footer p-0 p-lg-3">
				<button type="button" class="btn btn-sm btn-secondary closeNote" data-bs-dismiss="modal">Close</button>
				<button class="btn btn-sm btn-primary" id="saveNote">Update Note</button>
			</div>

		</div>
	</div>
</div>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

});
</script>
@endpush