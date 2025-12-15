<div class="modal fade" id="saveagentlist" tabindex="-1" role="dialog" aria-labelledby="saveagentlistTitle" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content p-0">
			<div class="modal-header p-2 p-lg-3 align-items-center">
				<h5 class="modal-title" id="exampleModalLongTitle">Save Dialing List</h5>
				<button  id="close_saveagentlist" type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<strong>List Name:</strong>
					{!! Form::text('agent_list_name', null, array('placeholder' => 'List Name','class' => 'form-control', 'required')) !!}
				</div>
				<div class="form-group">
					<strong>Agent:</strong>
					{!! Form::select("agent_list", $agent_users, [], array('class' => 'form-control ml-0', 'required','multiple' => 'multiple')) !!}
				</div>
			</div>
			<div class="modal-footer flex-column">
				<button class="btn btn-primary" id="save_agent_list_button" disabled>Save Dialing List<span class="spinner-border spinner-border-sm navicon d-none" role="status" aria-hidden="true"></span></button>
			</div>
		</div>
	</div>
</div>