<div id="agentassignmodal" class="modal" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content p-0">
			<div class="modal-header p-2 px-3">
				<h5 class="modal-title">Assign to</h5>
				<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
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
					<!-- {!! Form::select("agent_list", $agent_users, [], array('id' => 'agent_list','class' => 'form-control ml-0', 'required', 'multiple' => 'multiple')) !!} -->

					{!! Form::select('agent_list[]',  $agent_users, [], ['id'=>'agent_list','class' => 'form-control  px-1', 'multiple' => true, 'size' => 1, 'style' => 'height: 2rem']) !!}
				</div>

			</div>
			<div class="modal-footer justify-content-start p-2 p-lg-3">
				<button class="btn btn-primary btn-sm m-0" id="save_agent_list_button">Assign<span class="spinner-border spinner-border-sm navicon d-none" role="status" aria-hidden="true"></span></button>
			</div>
		</div>
	</div>
</div>

<script>
    let agent_choices;

    document.addEventListener('DOMContentLoaded', function () {

        const agent_list = document.getElementById('agent_list');
        const agentModal = document.getElementById('agentassignmodal');

        agent_choices = new Choices(agent_list, {
            removeItemButton: true,
            placeholder: true,
            placeholderValue: 'Select Agent'
        });

        // Clear selections when modal is closed
        agentModal.addEventListener('hidden.bs.modal', function () {
            agent_choices.removeActiveItems();
        });

        // Delegate click event for assign_agent buttons
        document.addEventListener('click', function (e) {

            const btn = e.target.closest('.assign_agent');
            if (!btn) return;

            const id = btn.getAttribute('data-current');
            const currently_assigned_to = btn.getAttribute('data-assigned_agents');

            // console.log(id);
            // console.log(currently_assigned_to);

            // Clear previous selections
            agent_choices.removeActiveItems();

            const agents_id_arr = currently_assigned_to
            .split(',')
            .map(v => v.trim());

           	const validValues = agents_id_arr.filter(v =>
			    agent_choices._store.choices.some(c => c.value === v)
			);
			agent_choices.setChoiceByValue(validValues);

            // Session storage logic (unchanged)
            let selectedCheckboxes = sessionStorage.getItem('selectedAgentIds');
            selectedCheckboxes = selectedCheckboxes ? JSON.parse(selectedCheckboxes) : [];

            if (!selectedCheckboxes.includes(id)) {
                selectedCheckboxes.push(id);
                sessionStorage.setItem(
                    'selectedAgentIds',
                    JSON.stringify(selectedCheckboxes)
                );
            }
        });
    });
</script>