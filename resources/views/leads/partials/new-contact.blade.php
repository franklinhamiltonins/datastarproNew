<div class="modal fade" id="addContactModal" tabindex="-1" role="dialog" aria-labelledby="addContactModalTitle" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content p-0">
			<div class="modal-header p-2 p-lg-3 align-items-center">
				<h5 class="modal-title" id="exampleModalLongTitle">Add New Contact</h5>
				<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>

			{!! Form::open(array('route' => ['leads.contact_store',$lead->id],'method'=>'POST')) !!}

			<div class="modal-body p-2 p-lg-3">
				@php
				//set contact to empty, since this is a form for new contact
				$contact = [];
				@endphp
				@include('leads.partials.contact-form')

			</div>
			<div class="modal-footer p-2 p-lg-3">
				<button type="submit" class="btn btn-primary btn-sm" onclick="unsetSessionStorage('contact_id')">Save contact</button>
				<button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
			</div>
			{!! Form::close() !!}
		</div>
	</div>
</div>