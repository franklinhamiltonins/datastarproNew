{{-- Delete Modal --}}
<div class="modal " id="moduledeleteModal" data-source="" style="display: none;">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<!--div class="modal-header">
				<h4 class="modal-title">Are you Sure?</h4>
				<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">×</span>
				</button>
			</div-->
			<div class="modal-body text-center p-2 p-lg-3">
				<div class="caution mb-2">
					<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" fill="#f00" height="30px" width="30px" version="1.1" id="Capa_1" viewBox="0 0 212.715 212.715" xml:space="preserve">
						<g>
							<path d="M211.436,187.771L112.843,17.002c-1.34-2.32-3.815-3.75-6.495-3.75c-2.68,0-5.155,1.43-6.495,3.75L1.005,188.213   c-1.34,2.32-1.34,5.18,0,7.5c1.34,2.32,3.816,3.75,6.495,3.75h197.695c0.007,0,0.015,0,0.02,0c4.143,0,7.5-3.357,7.5-7.5   C212.715,190.41,212.243,188.968,211.436,187.771z M20.49,184.463l85.857-148.711l85.857,148.711H20.49z"/>
							<path d="M98.848,76.58v63.879c0,4.143,3.357,7.5,7.5,7.5s7.5-3.357,7.5-7.5V76.58c0-4.143-3.357-7.5-7.5-7.5   S98.848,72.438,98.848,76.58z"/>
							<circle cx="106.348" cy="164.007" r="9.328"/>
						</g>
					</svg>
				</div>
				<h4 class="modal-title mb-2">Are you Sure?</h4>
				<p>This action cannot be undone. All values<br>associated with this field will be lost.</p>
				<div class="d-flex flex-column">
					<button type="button" id="confirmDelete" class="btn btn-outline-light bg-danger py-2 mb-2">Yes, Delete</button>
					<button type="button" class="btn btn-outline-light border-danger text-danger" data-bs-dismiss="modal">No</button>
				</div>
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
	function setModal(elem, id, moduleName) {
		var deleteUrl = '/' + moduleName + '/delete/' + id;
		$('#moduledeleteModal').attr('data-source', '#ordr_' + id);
		var source = $(elem).data('source');

		$('#confirmDelete').off().on('click', function() {
			$.ajax({
				url: deleteUrl,
				type: 'POST',
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				data: {
					id: id,
					moduleName: moduleName
				},
				success: function(response) {
					toastr.success(response.message);

					$('#moduledeleteModal').modal('hide');
					var element = document.getElementById('moduleRefresh');
					if (element) {
						element.click(); // Trigger click event
					}
				},
				error: function(xhr, status, error) {
					toastr.error(response.message);
					$('#moduledeleteModal').modal('hide');
					var element = document.getElementById('moduleRefresh');
					if (element) {
						element.click(); // Trigger click event 
					}

				}

			});
		});

	}
</script>
@endpush