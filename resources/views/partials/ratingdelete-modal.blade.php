{{-- Delete Modal --}}
<div class="modal" id="newdeleteModal" data-source="" style="display: none;">
	<div class="modal-dialog">
		<div class="modal-content border border-danger p-0">
			<div class="modal-header p-2 p-lg-3 align-items-center">
				<h5 class="modal-title">Are you Sure?</h5>
				<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">×</span>
				</button>
			</div>
			<form id="ratingForm">
				<input type="hidden" name="previous_id" id="previous_rating_id">
				<div class="modal-body p-2 p-lg-3 deleteBodyContent" >
					<!-- <p class="mb-0">The record will be deleted and You cannot undo this!</p> -->
				</div>
			</form>
			
			<div class="modal-footer p-2 p-lg-3 justify-content-between">
				<button type="button" id="close_delete_modal" class="btn btn-dark" data-bs-dismiss="modal">No</button>
				<button type="button" id="confirmdelete" class="btn btn-warning">Reassign and Delete</button>
			</div>
		</div>
		<!-- /.modal-content -->
	</div>
	<!-- /.modal-dialog -->
</div>
@push('scripts')
<script>
	$(document).on("click", "#confirmdelete", function () {
	    let formData = $("#ratingForm").serialize(); // Get all form data
	    
	    $.ajax({
	        url: "/rating/ratingFormSubmission", // Replace with your API URL
	        method: "POST",
	        data: formData,
	        dataType: "json",
	        success: function (response) {
	            if (response.status) {
	            	$("#newdeleteModal").modal("hide");
	                toastr.success(response.message);
	                
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

	$(document).on("click", "#forcefullydelete", function () {
		const data_id = $(this).data("id");
        $("#previous_rating_id").val(data_id);
        $.ajax({
            url: '/rating/forceDelete',
            method: "POST",
            data: {
                data_id: data_id,

            },
            success: function(response) {
                // console.log(response);
                if(response.status){
                    $("#newdeleteModal").modal("hide");
                    toastr.success("Rating deleted successfully!");
	                
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