@extends('layouts.app')
@section('pagetitle', 'Merge Contacts')
@push('breadcrumbs')
<li class="breadcrumb-item"><a href="{{route('contacts.index')}}">All Contacts</a></li>

<li class="breadcrumb-item active">View Contact</li>
@endpush
@section('content')

<!-- Main content -->
<section class="content pb-2">
	<div class="container-fluid">


		<table class="merge-table mb-4 table table-bordered bg-white">
			<tr class="merge-tr bg-gray">
				<th class="merge-th p-2">Attribute</th>

				{{-- Generate table headers for each attribute --}}
				@foreach ($compareArr as $key => $values)
				<th class="merge-th p-2 dynamic-th">
					@if (count($compareArr) > 1)
					<input type="checkbox" name="attributes[]" value="{{ $key }}" class="column-checkbox">
					@endif

					{{ $key }}
				</th>
				@endforeach
			</tr>

			{{-- Generate table rows for each index (assuming $compareArr[0] exists) --}}
			@foreach ($compareArr[0] as $index => $value)
			<tr class="merge-tr">
				<td class="merge-td bg-gray-light p-2">{{ $index }}</td>

				{{-- Generate table cells for each attribute value at the current index --}}
				@foreach ($compareArr as $key => $values)
				<td class="merge-td p-2  dynamic-td">{{ $values[$index] }}</td>
				@endforeach
			</tr>
			@endforeach
		</table>
		@if (count($compareArr) > 1)
		<div class="text-right">
			<button id="sendDataBtn" class="btn btn-primary">Merge</button>
		</div>
		@endif




	</div>

</section>
<!-- /.content -->
@endsection
@push('styles')
@endpush
@push('scripts')

<script>
	$(document).ready(function() {
		var selectedColumnIndex = null;

		// Event handler for column checkboxes
		$('.column-checkbox').change(function() {
			var columnIndex = $(this).closest('th').index();
			// Remove the class from previously selected checkbox
			$('.column-checkbox.selected').removeClass('selected_to_merge');

			// Add class to the currently selected checkbox
			$(this).toggleClass('selected_to_merge');

			$(".merge-td").removeClass('selected_to_merge_highlight');

			// Deselect the previously selected column
			if (selectedColumnIndex !== null && selectedColumnIndex !== columnIndex) {
				$('table th:eq(' + selectedColumnIndex + ') input').prop('checked', false);
			}

			// Update the selected column index
			selectedColumnIndex = $(this).is(':checked') ? columnIndex : null;
		});

		// Event handler for rows in other columns
		$('table tr td:not(:first-child)').click(function() {
			// Check if a column is selected
			if (selectedColumnIndex !== null) {
				// Get the value of the clicked row
				var clickedValue = $(this).text();
				// Get the index of the clicked row
				var rowIndex = $(this).closest('tr').index();
				// Replace the value of the clicked row in the selected column
				var $targetCell = $('table tr:eq(' + rowIndex + ') td:eq(' + selectedColumnIndex + ')');
				$targetCell.text(clickedValue);
				// Highlight the value being moved
				$targetCell.addClass('selected_to_merge_highlight');

			}
		});

		// Event handler for sending data via AJAX
		$('#sendDataBtn').click(function() {
			if (selectedColumnIndex !== null) {
				var dataToSend = {};
				// Loop through each row in the selected column
				$('table tr:not(:first-child)').each(function(index) {
					// Get the value of the row in the first column
					var paramName = $('table tr:eq(' + (index + 1) + ') td:first-child').text();
					// Get the value of the row in the selected column
					var paramValue = $('table tr:eq(' + (index + 1) + ') td:eq(' + selectedColumnIndex + ')').text();
					// Add the row data to the dataToSend object
					dataToSend[paramName] = paramValue;
				});

				// Include CSRF token in AJAX request headers
				var csrfToken = $('meta[name="csrf-token"]').attr('content');

				// Send the data via AJAX
				$.ajax({
					url: '/contacts/completemerge',
					method: 'POST',
					headers: {
						'X-CSRF-TOKEN': csrfToken
					},
					data: dataToSend,
					success: function(response) {
						toastr.success(response.message);
						location.reload();
						console.log(response);
					},
					error: function(xhr, status, error) {
						toastr.error(response.message);
						console.error(xhr.responseText);
					}
				});
			} else {
				console.log('No column selected.');
			}
		});
	});







	// $(document).ready(function() {
	// 	var selectedColumnIndex = null;

	// 	// Event handler for column checkboxes
	// 	$('.column-checkbox').change(function() {
	// 		var columnIndex = $(this).closest('th').index();

	// 		// Deselect the previously selected column
	// 		if (selectedColumnIndex !== null && selectedColumnIndex !== columnIndex) {
	// 			$('table th:eq(' + selectedColumnIndex + ') input').prop('checked', false);
	// 		}

	// 		// Update the selected column index
	// 		selectedColumnIndex = $(this).is(':checked') ? columnIndex : null;
	// 	});

	// 	// Event handler for rows in other columns
	// 	$('table tr td:not(:first-child)').click(function() {
	// 		// Check if a column is selected
	// 		if (selectedColumnIndex !== null) {
	// 			// Get the value of the clicked row
	// 			var clickedValue = $(this).text();
	// 			// Get the index of the clicked row
	// 			var rowIndex = $(this).closest('tr').index();
	// 			// Replace the value of the clicked row in the selected column
	// 			$('table tr:eq(' + rowIndex + ') td:eq(' + selectedColumnIndex + ')').text(clickedValue);
	// 		}
	// 	});


	// 	$('#sendDataBtn').click(function() {
	// 		if (selectedColumnIndex !== null) {
	// 			var dataToSend = {};
	// 			// Loop through each row in the selected column
	// 			$('table tr td:eq(' + selectedColumnIndex + ')').each(function(index) {
	// 				// Get the value of the row in the first column
	// 				var paramName = $('table tr:eq(' + (index + 1) + ') td:first-child').text();
	// 				// Get the value of the row in the selected column
	// 				var paramValue = $(this).text();
	// 				// Add the row data to the dataToSend object
	// 				dataToSend[paramName] = paramValue;
	// 			});
	// 			console.log(dataToSend);

	// 			// Include CSRF token in AJAX request headers
	// 			var csrfToken = $('meta[name="csrf-token"]').attr('content');

	// 			// Send the data via AJAX
	// 			$.ajax({
	// 				url: '/leads/completemerge',
	// 				method: 'POST',
	// 				headers: {
	// 					'X-CSRF-TOKEN': csrfToken
	// 				},
	// 				data: dataToSend,
	// 				success: function(response) {
	// 					// Handle success response
	// 					console.log(response);
	// 				},
	// 				error: function(xhr, status, error) {
	// 					// Handle error response
	// 					console.error(xhr.responseText);
	// 				}
	// 			});
	// 		} else {
	// 			console.log('No column selected.');
	// 		}
	// 	});
	// });
</script>

@endpush