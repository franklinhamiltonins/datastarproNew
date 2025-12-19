{{-- Delete Modal --}}
<div class="modal" id="newreassignModal" data-source="" style="display: none;">
	<div class="modal-dialog">
		<div class="modal-content border border-danger p-0">
			<div class="modal-header p-2 p-lg-3 align-items-center">
				<h5 class="modal-title">Are you Sure?</h5>
				<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">×</span>
				</button>
			</div>
			<form id="reassignForm">
				<input type="hidden" name="reassign_dialing_id" id="reassign_dialing_id">
				<input type="hidden" name="reassign_lead_id" id="reassign_lead_id">
				<input type="hidden" name="old_agent_id" id="old_agent_id">
				<div class="modal-body p-2 p-lg-3 reassignBodyContent" >
					<select class="form-control mb-2" name="reassign_agent_id" id="reassign_agent_id" >
						@foreach($agent_users as $key => $agent)
							<option value="{{$key}}">{{$agent}}</option>
						@endforeach
					</select>
					<div class="reassignArea">
						
					</div>
				</div>
			</form>
			
			<div class="modal-footer p-2 p-lg-3 justify-content-between">
				<button type="button" id="close_delete_modal" class="btn btn-dark" data-bs-dismiss="modal">No</button>
				<button type="button" id="confirmclick" class="btn btn-warning">Reassign</button>
			</div>
		</div>
		<!-- /.modal-content -->
	</div>
	<!-- /.modal-dialog -->
</div>
@push('scripts')
<script>
	const content = $(".reassignArea");
	$(document).on("click", "#confirmclick", function () {
		const leadId = $("#reassign_lead_id").val();
	    let formData = $("#reassignForm").serialize();
	    
	    $.ajax({
	        url: `/dialings/update-owner/${leadId}`,
	        method: "POST",
	        data: formData,
	        dataType: "json",
	        success: function (response) {
	            if (response.status) {
	            	$("#newreassignModal").modal("hide");
	                toastr.success(response.message);
	                content.empty();
	                
	                // Reload page after 3 seconds (3000ms)
	                setTimeout(function () {
	                    location.reload();
	                }, 3000);
	            } else {
	                // console.log(response);
                    content.empty();

                    content.append(`
                        <div class="alert reassign-alert mt-2 p-3 text-right">
                            <div class="d-flex reassign-alert-cntnt text-left">
                                <i class="fa fa-exclamation-triangle pt-1 pr-2 text-warning"></i>
                                <p class="mb-2 small">${response.message}</p>
                            </div>
                           
                            <button type="button" id="forcefullyassign" class="btn btn-sm btn-danger mt-2">Reassign Anyway</button>
                        </div>
                    `);
	            }
	        },
	        error: function (xhr, status, error) {
	            toastr.error("Something went wrong. Please try again.");
	            console.error(xhr.responseText);
	        }
	    });
	});

	$(document).on("click", "#forcefullyassign", function () {
		const leadId = $("#reassign_lead_id").val();
		const reassign_dialing_id = $("#reassign_dialing_id").val();
		const reassign_agent_id = $("#reassign_agent_id").val();
		const old_agent_id = $("#old_agent_id").val();
        $.ajax({
            url: `/dialings/update-owner/${leadId}`,
            method: "POST",
            data: {
                reassign_dialing_id: reassign_dialing_id,
                reassign_agent_id: reassign_agent_id,
                old_agent_id: old_agent_id,
                force_update: 1,

            },
            success: function(response) {
                // console.log(response);
                if (response.status) {
	            	$("#newreassignModal").modal("hide");
	                toastr.success(response.message);
	                content.empty();
	                
	                // Reload page after 3 seconds (3000ms)
	                setTimeout(function () {
	                    location.reload();
	                }, 3000);
	            } else {
	                toastr.error("Error: " + response.message);
	            }
            },
	        error: function (xhr, status, error) {
	            toastr.error("Something went wrong. Please try again.");
	            console.error(xhr.responseText);
	        }
        });
         
	});

</script>
@endpush