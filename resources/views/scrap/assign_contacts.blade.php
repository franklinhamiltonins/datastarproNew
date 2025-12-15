@extends('layouts.app')
@section('pagetitle', 'Assign Contacts')
@push('breadcrumbs')
<li class="breadcrumb-item"><a href="{{route('contacts.index')}}">All Contacts</a></li>

<li class="breadcrumb-item active">View Contact</li>
@endpush
@section('content')

<!-- Main content -->
<section class="content pb-2">
	<div class="container-fluid">


		<table class="assign-table mb-4 table table-bordered bg-white">
			<tr class="assign-tr bg-gray">
				<th class="assign-th p-2"><input type="checkbox" name="select_all_assign" value="" class="select_all_assign"></th>
				<th class="assign-th p-2 "> Name</th>
				<th class="assign-th p-2 "> First Name</th>
				<th class="assign-th p-2 "> Last Name</th>
				<th class="assign-th p-2 "> Title</th>
				<th class="assign-th p-2 "> Email</th>

				<th class="assign-th p-2 "> Address 1</th>
				<th class="assign-th p-2 "> Address 2</th>
				<th class="assign-th p-2 "> City</th>
				<th class="assign-th p-2 "> State</th>
				<th class="assign-th p-2 "> County</th>
				<th class="assign-th p-2 "> Phone</th>
				{{-- <th class="assign-th p-2 "> Email</th> --}}
				<th class="assign-th p-2 "> Status</th>
			</tr>


			@foreach ($contacts as $contact)
			<tr class="assign-tr">
				<td class="assign-td bg-gray-light p-2"><input type="checkbox" name="contacts_checkbox" value="{{$contact->id}}" class="column-checkbox"></td>
				<td class="assign-td bg-gray-light p-2">{{$contact->c_full_name}}</td>
				<td class="assign-td bg-gray-light p-2">{{$contact->c_first_name}}</td>
				<td class="assign-td bg-gray-light p-2">{{$contact->c_last_name}}</td>
				<td class="assign-td bg-gray-light p-2">{{$contact->c_title}}</td>
				<td class="assign-td bg-gray-light p-2">{{$contact->c_email}}</td>
				<td class="assign-td bg-gray-light p-2">{{$contact->c_address1}}</td>
				<td class="assign-td bg-gray-light p-2">{{$contact->c_address2}}</td>
				<td class="assign-td bg-gray-light p-2">{{$contact->c_city}}</td>
				<td class="assign-td bg-gray-light p-2">{{$contact->c_state}}</td>
				<td class="assign-td bg-gray-light p-2">{{$contact->c_county}}</td>
				<td class="assign-td bg-gray-light p-2">{{$contact->c_phone}}</td>
				<td class="assign-td bg-gray-light p-2">{{$contact->c_status}}</td>
			</tr>
			@endforeach

			@foreach ($tempContacts as $tempContact)
			<tr class="assign-tr">
				<td class="assign-td bg-gray-light p-2"><input type="checkbox" name="contacts_checkbox" value="temp-{{$tempContact->id}}" class="column-checkbox"></td>
				<td class="assign-td  p-2">{{$tempContact->c_full_name}}</td>
				<td class="assign-td  p-2">{{$tempContact->c_first_name}}</td>
				<td class="assign-td  p-2">{{$tempContact->c_last_name}}</td>
				<td class="assign-td  p-2">{{$tempContact->c_title}}</td>
				<td class="assign-td  p-2">{{$tempContact->c_email}}</td>
				<td class="assign-td  p-2">{{$tempContact->c_address1}}</td>
				<td class="assign-td  p-2">{{$tempContact->c_address2}}</td>
				<td class="assign-td  p-2">{{$tempContact->c_city}}</td>
				<td class="assign-td  p-2">{{$tempContact->c_state}}</td>
				<td class="assign-td  p-2">{{$tempContact->c_county}}</td>
				<td class="assign-td  p-2">{{$tempContact->c_phone}}</td>
				<td class="assign-td  p-2">{{$tempContact->c_status}}</td>
			</tr>
			@endforeach
		</table>
		@if (count($tempContacts) >= 1)
		<div class="text-right">
			<button id="sendDataBtn" class="btn btn-primary">Assign</button>
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
		var currentPageLeadId = "";

		var url = window.location.href; // Get the full URL
		var path = url.split('?')[0]; // Remove the query parameters
		var parts = path.split('/'); // Split the URL by '/'

		// Find the index of "compare" in the parts array
		var compareIndex = parts.indexOf('compare');

		// Check if "compare" exists and there is a part after "compare"
		if (compareIndex !== -1 && parts.length > compareIndex + 1) {
			currentPageLeadId = parts[compareIndex + 1]; // Get the part after "compare"

		} else {
			toastr.error("Url doesn't seems to be valid one. Please contact administrator");
			return false;
		}

		// Event handler for column checkboxes
		$('.select_all_assign').on('change', function() {
			$('.column-checkbox').prop('checked', $(this).prop('checked'));
		});

		// Event handler for sending data via AJAX
		$('#sendDataBtn').click(function() {
			var selectedValues = $('.column-checkbox:checked').map(function() {
				return (this.value) ? this.value : '';
			}).get();
			console.log(selectedValues);

			if (currentPageLeadId <= 0) {
				toastr.error("Url doesn't seems to be valid one. Please contact administrator");
				return false;
			}


			if (selectedValues.length > 0) {
				var dataToSend = {};
				// Loop through each row in the selected column
				dataToSend['contactsId'] = selectedValues;
				dataToSend['currentPageLeadId'] = currentPageLeadId;

				// Include CSRF token in AJAX request headers
				var csrfToken = $('meta[name="csrf-token"]').attr('content');

				// Send the data via AJAX
				$.ajax({
					url: '/scrap/migratecontacts',
					method: 'POST',
					headers: {
						'X-CSRF-TOKEN': csrfToken
					},
					data: dataToSend,
					success: function(response) {
						// toastr.success(response.message);
						sessionStorage.setItem('assignContactKey', response.message);
						window.location.href = '/scrap_sunbiz';
						// location.reload();
						console.log(response);
					},
					error: function(xhr, status, error) {
						toastr.error(response.message);
						console.error(xhr.responseText);
					}
				});
			} else {
				toastr.error('Please check at least one checkbox to continue');
				return false;
			}
		});
	});
</script>

@endpush