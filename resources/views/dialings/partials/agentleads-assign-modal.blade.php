<div id="assign_lead_to_agentmodal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content p-0">
			<div class="modal-header p-2 px-3">
				<h5 class="modal-title">Assign to</h5>
			</div>
			<div class="modal-body p-2 p-lg-3">
				<div class="form-group m-0">
					<strong class="mb-2 d-inline-block">Agent:</strong>
					{!! Form::select("agent_list[]", $agent_users, [], array('id' => 'agent_list','class' => 'form-control ml-0', 'required','multiple'=>'multiple')) !!}
				</div>
				<div class="form-group m-0" id="agentleads_dynamic">
					<div class="form-group m-2" id="ExistingAgentListSection" style="display: none;">
						<strong class="mb-2 d-inline-block">List Type:</strong>
						<label class="radio-inline">
							{!! Form::radio('list_type', 'existing', true) !!} Existing
						</label>
						<label class="radio-inline">
							{!! Form::radio('list_type', 'new') !!} New
						</label>
					</div>

					<div class="form-group m-0" id="ExistingAgentListPopulation" style="display: none;">
						<strong class="mb-2 d-inline-block">Existing Lists:</strong>
						{!! Form::select("existing_list", [], [], array('id' => 'existing_list','class' => 'form-control ml-0')) !!}

					</div>
				</div>


			</div>
			<div class="modal-footer justify-content-start p-2 p-lg-3">
				<button class="btn btn-primary btn-sm m-0" id="save_agentleads_list_button">Assign<span class="spinner-border spinner-border-sm navicon d-none" role="status" aria-hidden="true"></span></button>
			</div>
		</div>
	</div>
</div>