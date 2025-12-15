<div id="agentassignmodal" class="modal" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content p-0">
			<div class="modal-header p-2 px-3">
				<h5 class="modal-title">Assign to</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">×</span>
				</button>
			</div>
			<div class="modal-body p-2 p-lg-3">
				<!-- <div class="form-group">
					<strong>List Name:</strong>
					{!! Form::text('agent_list_name', null, array('placeholder' => 'List Name','class' => 'form-control', 'required')) !!}
				</div> -->
				<div class="form-group m-0">
					<strong class="mb-2 d-inline-block">Agent:</strong>
					{!! Form::select("agent_list", $agent_users, [], array('id' => 'agent_list','class' => 'form-control ml-0', 'required', 'multiple' => 'multiple')) !!}
				</div>

			</div>
			<div class="modal-footer justify-content-start p-2 p-lg-3">
				<button class="btn btn-primary btn-sm m-0" id="save_agent_list_button">Assign<span class="spinner-border spinner-border-sm navicon d-none" role="status" aria-hidden="true"></span></button>
			</div>
		</div>
	</div>
</div>