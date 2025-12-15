<div id="reassignagentlist" class="modal" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header p-2 p-lg-3 align-items-center">
				<h5 class="modal-title">Reassign Agent</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">×</span>
				</button>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<strong>Agent:</strong>
					{!! Form::select("agent_list", $agent_users, [], array('class' => 'form-control ml-0', 'required','multiple' => 'multiple')) !!}
				</div>
			</div>
			<div class="modal-footer flex-column">
				<button class="btn btn-primary" id="reassign_agent_list_button">Reassign Agent<span class="spinner-border spinner-border-sm navicon d-none" role="status" aria-hidden="true"></span></button>
			</div>
		</div>
	</div>
</div>