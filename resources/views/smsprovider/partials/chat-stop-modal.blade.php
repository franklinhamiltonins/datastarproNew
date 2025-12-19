<div class="modal fade" id="chat-stop-model" data-source="" style="display: none;">
	<div class="modal-dialog">
		<div class="modal-content border border-danger p-0">
			<div class="modal-header p-2 p-lg-3 align-items-center">
				<h5 class="modal-title">Are you Sure?</h5>
				<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">×</span>
				</button>
			</div>
			<div class="modal-body p-2 p-lg-3">
				<p class="mb-0">Do you want to stop further conversation on this contact?</p>
			</div>
			<input type="hidden" id="chat_stop_value">
			<div class="modal-footer p-2 p-lg-3 justify-content-between">
				<button type="button" class="btn btn-dark" data-bs-dismiss="modal">No</button>
				<button type="button" id="stop_chat" class="btn btn-danger">Yes</button>
			</div>
		</div>
		<!-- /.modal-content -->
	</div>
	<!-- /.modal-dialog -->
</div>
@push('scripts')
<script>
	/** ************************************
            Script for Confirm Modal
        **************************************/
	// set the confirmation modal
	function setModal(elem, $id) {
		$('#deleteModal').attr('data-source', '#ordr_' + $id);

		$tar = $(elem).parents('form');
		$('#confirm').click(function() {


			//submit form
			$($tar).submit();
		});

	}
</script>
@endpush