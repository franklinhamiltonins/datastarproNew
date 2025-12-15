<div class="modal fade" id="contactStatusModal" tabindex="-1" role="dialog" aria-labelledby="contactStatusModalTitle" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content p-0">
			<div class="modal-header p-2 p-lg-3 align-items-center">
				<h5 class="modal-title" id="exampleModalLongTitle">Change All Contact Status</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>

			{!! Form::open(array('route' => ['leads.contact_status_update',$lead->id],'method'=>'POST')) !!}

			<div class="modal-body p-2 p-lg-3">
				@php
				//set contact to empty, since this is a form for new contact
				$contact = [];
				@endphp
                <div class="form-group mb-2">
                    <label class="form-label small mb-1 font-weight-normal">Status:</label>
                    <select class="commonClass form-control leadscontactstatus contact-info-list-select px-2" class="form-control leadselectstatus" name="c_status">
                        {{-- False Status Options (Displayed on Top) --}}
					    @foreach($statusOptions as $keyStatus => $statusOption)
					        @if(!empty($statusOption->false_status) && $statusOption->false_status == 1)
					            <option value="{{ $statusOption->id }}" {{ !empty($contact) && $contact->c_status == $statusOption->id ? 'selected' : '' }}>
					                {{ $statusOption->name }}
					            </option>
					        @endif
					    @endforeach

					    {{-- Group Options for Prospecting --}}
					    <optgroup label="Prospecting">
					        @foreach($statusOptions as $keyStatus => $statusOption)
					            @if($statusOption->false_status != 1 && $statusOption->display_in_pipedrive == null)
					                <option value="{{ $statusOption->id }}" {{ !empty($contact) && $contact->c_status == $statusOption->id ? 'selected' : '' }}>
					                    {{ $statusOption->name }}
					                </option>
					            @endif
					        @endforeach
					    </optgroup>

					    {{-- Group Options for Pipeline --}}
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
			    <div class="form-group mb-0">
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
			</div>
			<div class="modal-footer p-2 p-lg-3">
				<button type="submit" class="btn btn-primary btn-sm">Save contact Status</button>
				<button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
			</div>
			{!! Form::close() !!}
		</div>
	</div>
</div>