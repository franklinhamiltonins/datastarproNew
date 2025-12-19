<div class="modal fade" id="saveagentlist" tabindex="-1" role="dialog" aria-labelledby="saveagentlistTitle" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content p-0">
			<div class="modal-header p-2 p-lg-3 align-items-center">
				<h5 class="modal-title" id="exampleModalLongTitle">Save Dialing List</h5>
				<button  id="close_saveagentlist" type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
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
					<!-- {!! Form::select("agent_list", $agent_users, [], array('class' => 'form-control ml-0', 'required','multiple' => 'multiple')) !!} -->

					{!! Form::select('agent_list[]',  $agent_users, [], ['id'=>'agent_list_dialing','class' => 'form-control  px-1', 'multiple' => true, 'size' => 1, 'style' => 'height: 2rem']) !!}
				</div>
			</div>
			<div class="modal-footer flex-column">
				<button class="btn btn-primary" id="save_agent_list_button" disabled>Save Dialing List<span class="spinner-border spinner-border-sm navicon d-none" role="status" aria-hidden="true"></span></button>
			</div>
		</div>
	</div>
</div>


<script>
    let agent_choices_dialing;

    document.addEventListener('DOMContentLoaded', function () {

        const agent_list_dialing = document.getElementById('agent_list_dialing');
        const saveagentModal = document.getElementById('saveagentlist');

        agent_choices_dialing = new Choices(agent_list_dialing, {
            removeItemButton: true,
            placeholder: true,
            placeholderValue: 'Select Agent'
        });

        // Clear selections when modal is closed
        saveagentModal.addEventListener('hidden.bs.modal', function () {
            agent_choices_dialing.removeActiveItems();
        });
    });
</script>