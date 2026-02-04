@extends('layouts.app')
@section('pagetitle', 'Merge Leads')
@push('breadcrumbs')
<li class="breadcrumb-item"><a href="{{route('leads.index')}}">All Leads</a></li>

<li class="breadcrumb-item active">View Lead</li>
@endpush
@section('content')

<!-- Main content -->
<section class="content pb-2">
	<div class="container-fluid">


		<table class="merge-table mb-4 table table-bordered bg-white">
			<thead>
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
			</thead>
            <tbody>
				{{-- Generate table rows for each index (assuming $compareArr[0] exists) --}}
				@foreach ($compareArr[0] as $index => $value)
				<tr class="merge-tr">
					<td class="merge-td bg-gray-light p-2">{{ $index }}</td>

					{{-- Generate table cells for each attribute value at the current index --}}
					@foreach ($compareArr as $key => $values)
					<td class="merge-td p-2  dynamic-td" {!! ($index=='id' ) ? 'id="' . $values[$index] . '"' : '' !!}>{!! $values[$index] !!}</td>
					@endforeach
				</tr>
				@endforeach
			</tbody>
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
		var selectedColumnLeadId = '';
		var mergeColumnIndex = null;

		$('.merge_to_current_lead').click(function() {
			// Extract the index from the button's ID
			var buttonId = $(this).attr('id');
			var columnIndex = parseInt(buttonId.replace('assign_slug_to_lead', ''));

			// Retrieve the lead ID from the first row of the table
			var mergeLeadId = $('table tr:eq(1) td:eq(' + columnIndex + ')').attr('id');

			// Retrieve the lead ID from the first row of the table for the column being merged to
			var mergeLeadIdTo = $('table tr:eq(1) td:eq(' + selectedColumnIndex + ')').attr('id');

			// Check if all necessary values are not null and greater than 1
			if (mergeLeadId && mergeLeadIdTo && columnIndex && selectedColumnIndex) {
				// Show confirmation dialog
				if (confirm('Are you sure you want to merge the contacts from lead ' + mergeLeadId + ' to ' + mergeLeadIdTo + ' data?')) {
					var csrfToken = $('meta[name="csrf-token"]').attr('content');
					// User confirmed, proceed with AJAX request
					$.ajax({
						url: '/leads/moveContacts',
						method: 'POST',
						headers: {
							'X-CSRF-TOKEN': csrfToken
						},
						data: {
							mergeLeadIdFrom: mergeLeadId,
							mergeLeadIdTo: mergeLeadIdTo,
							mergeColumnIndexFrom: columnIndex,
							mergeColumnIndexTo: selectedColumnIndex
						},
						success: function(response) {
							toastr.success(response.message);
							location.reload();
						},
						error: function(xhr, status, error) {
							// Handle error response
							toastr.error(xhr.responseText);
						}
					});
				} else {
					// User canceled, do nothing
					toastr.error('Merge canceled by user.');
				}
			} else {
				toastr.error('Please select a lead first to merge.');
			}
		});



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
			$('.merge_to_current_lead').show();
			if (selectedColumnIndex) {
				$('#assign_slug_to_lead' + selectedColumnIndex).hide();
			}
			// console.log(selectedColumnIndex);
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
					url: '/leads/completemerge',
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
				toastr.error("Please select one of the record to merge the contacts.");

			}
		});
	});
</script>

@endpush