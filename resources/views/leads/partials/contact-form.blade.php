<div class="form-row">
	<div class="form-group col mb-2">
		<label class="form-label small mb-1 font-weight-normal">First Name<sup class="mandatoryClass">*</sup>:</label>
		{!! Form::text('c_first_name', null, array('placeholder' => 'First Name','class' => 'form-control px-2')) !!}
	</div>
	<div class="form-group col mb-2">
		<label class="form-label small mb-1 font-weight-normal">Last Name<sup class="mandatoryClass">*</sup>:</label>
		{!! Form::text('c_last_name', null, array('placeholder' => 'Last Name','class' => 'form-control px-2')) !!}
	</div>
</div>
<div class="form-group mb-2">
	<label class="form-label small mb-1 font-weight-normal">Title:</label>

	{!! Form::select('c_title',!empty($contact)?
	array_merge(array($contact->c_title=>$contact->c_title),$contactsTitle):$contactsTitle,!empty($contact)? $contact->c_title : [], array('class' => 'form-control multiple contactTitle px-2','onchange'=>'get_set_other_val(this)')) !!}

	<div id="contactTitleOther" class="mt-2 otherInput" style="display:none;text-transform: lowercase; ">
		<input placeholder="Other Contact Title" class="form-control capitalize" name="contact-title" type="text">
	</div>
</div>
<div class="form-group mb-2">
	<label class="form-label small mb-1 font-weight-normal">Address 1<sup class="mandatoryClass">*</sup>:</label>
	{!! Form::text('c_address1', null, array('placeholder' => 'Address - must start with a number','class' => 'form-control px-2','pattern'=>'^\d[0-9a-zA-Z\s\/#,._-:]*$','title'=>'Chars allowed: # . - _ ,')) !!}
</div>
<div class="form-group mb-2">
	<label class="form-label small mb-1 font-weight-normal">Address 2:</label>
	{!! Form::text('c_address2', null, array('placeholder' => 'Address2','class' => 'form-control px-2')) !!}
</div>
<div class="form-row">
	<div class="form-group col mb-2">
		<label class="form-label small mb-1 font-weight-normal">City:</label>
		{!! Form::text('c_city', null, array('placeholder' => 'City','class' => 'form-control px-2')) !!}
	</div>
	<div class="form-group col mb-2">
		<label class="form-label small mb-1 font-weight-normal">State:</label>
		{{-- dropdown with hardcoded states--}}
		{!! Form::select('c_state',$states,!empty($contact)? $contact->c_state : [], array('class' => 'form-control multiple USstates px-2')) !!}
	</div>
</div>
<div class="form-group mb-2">
	<label class="form-label small mb-1 font-weight-normal">Zip:</label>
	{!! Form::text('c_zip', null, array('placeholder' => 'Zip - 5 digits ','class' => 'form-control px-2','maxlength' => '5')) !!}
</div>
<div class="form-group mb-2">
	<label class="form-label small mb-1 font-weight-normal">County:</label>
	{{-- dropdown with hardcoded Florida Counties - it also contains "Other" option , to enter a county manually--}}
	{!! Form::select('c_county',!empty($contact) ? array_merge(array($contact->c_county=>$contact->c_county),$counties):$counties,!empty($contact)? $contact->c_county : [] , array('class' => 'form-control multiple floridaCountiesContact px-2','onchange'=>'get_set_other_val(this)')) !!}

	<div id="countyOtherContact" class="mt-2 otherInput" style="display:none;text-transform: lowercase; ">
		<input placeholder="Other County" class="form-control capitalize px-2" name="county-other-contact" type="text">
	</div>
</div>
<div class="form-row">
	<div class="form-group col mb-2">
		<label class="form-label small mb-1 font-weight-normal">Phone: </label>
		{!! Form::text('c_phone', null, array('placeholder' => 'Phone','class' =>'form-control px-2','step'=>'any','maxlength'=>'16' )) !!}
	</div>
	<div class="form-group col mb-2">
		<label class="form-label small mb-1 font-weight-normal">Email: </label>
		{!! Form::email('c_email', null, array('placeholder' => 'Email Address','class' => 'form-control px-2', 'step'=>'any')) !!}
	</div>
</div>
<div class="form-group mb-2">
	<label class="form-label small mb-1 font-weight-normal">Status:</label>


	<select class="commonClass form-control leadscontactstatus contact-info-list-select px-2" name="c_status">
	    {{-- False Status Options (Displayed on Top) --}}
	    @foreach($statusOptions as $keyStatus => $statusOption)
	        @if(!empty($statusOption->false_status) && $statusOption->false_status == 1)
	            <option value="{{ $statusOption->id }}" {{ !empty($contact) && $contact->c_status == $statusOption->id ? 'selected' : '' }}>
	                {{ $statusOption->name }}
	            </option>
	        @endif
	    @endforeach

	    <optgroup label="Prospecting">
	        @foreach($statusOptions as $keyStatus => $statusOption)
	            @if($statusOption->false_status != 1 && $statusOption->display_in_pipedrive == null)
	                <option value="{{ $statusOption->id }}" {{ !empty($contact) && $contact->c_status == $statusOption->id ? 'selected' : '' }}>
	                    {{ $statusOption->name }}
	                </option>
	            @endif
	        @endforeach
	    </optgroup>

	    <optgroup label="Pipeline">
	        @foreach($statusOptions as $keyStatus => $statusOption)
	            @if($statusOption->false_status != 1 && $statusOption->display_in_pipedrive != null)
	                <option value="{{ $statusOption->id }}" {{ !empty($contact) && $contact->c_status == $statusOption->id ? 'selected' : '' }}>
	                    {{ $statusOption->name }}
	                </option>
	            @endif
	        @endforeach
	    </optgroup>
	</select>


</div>
    <div class="form-group mb-2">
		<label class="form-label small mb-1 font-weight-normal">Assign Agent:</label>

		<select class="commonClass form-control leadscontactassignagent contact-info-list-select px-2" name="c_agent_id">
			@if(count($agentlist) > 1)
				<option value="0" selected>Select Agent</option>
			@endif
			@foreach($agentlist as $keyagent => $agentvalue)
			<option value="{{ $keyagent }}" {{ !empty($contact) && $contact->c_agent_id == $keyagent ? 'selected' : '' }}>
				{{ $agentvalue }}
			</option>
			@endforeach
		</select>

	</div>

@push('scripts')
<script>
	$(document).ready(function() {
		$('input[name="c_phone"]').each(function() {
			$(this).on('keyup', function(evt) {

				var phoneNumber = $(this);
				var charCode = (evt.which) ? evt.which : evt.keyCode;

				var fomratted = $(this).val(phoneFormat($(phoneNumber).val()));

			});
		});
	});

	function phoneFormat(input) {
		// Strip all characters from the input except digits
		input = input.replace(/\D/g, '');

		// // Trim the remaining input to ten characters, to preserve phone number format
		// input = input.substring(0,10);

		// // Based upon the length of the string, we add formatting as necessary
		// var size = input.length;
		// if(size == 0){
		//         input = input;
		// }else if(size < 4){
		//         input = input;
		// }else if(size < 7){
		//         input = input.substring(0,3)+'-'+input.substring(3,6);
		// }else{
		//         input = input.substring(0,3)+'-'+input.substring(3,6)+'-'+input.substring(6,10);
		// }
		return input;
	}
</script>


@endpush