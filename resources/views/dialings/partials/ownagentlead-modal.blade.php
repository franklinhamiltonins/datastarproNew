{{-- Delete Modal --}}
<div class="modal fade" id="assignagentModal" data-source="" style="display: none;">
	<div class="modal-dialog">
		<div class="modal-content bg-danger">
			<div class="modal-header p-2 p-lg-3 align-items-center">
				<h5 class="modal-title">Are you Suresssss?</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">×</span>
				</button>
			</div>
			<div class="modal-body p-2 p-lg-3">
				<p>The record will be deleted and You cannot undo this!</p>
			</div>
			<div class="modal-footer justify-content-between p-2 p-lg-3">
				<button type="button" class="btn btn-outline-light" data-dismiss="modal">No</button>
				<button type="button" id="confirm" class="btn btn-outline-light">Yes, Delete</button>
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
	function setAgentOwnModal(elem, $id) {
		$('#deleteModal').attr('data-source', '#ordr_' + $id);

		$tar = $(elem).parents('form');
		$('#confirm').click(function() {


			//submit form
			$($tar).submit();
		});

	}
</script>
@endpush