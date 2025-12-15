@extends('layouts.app')
@section('pagetitle', 'Agent Call Report')
@push('breadcrumbs')
<li class="breadcrumb-item"><a href="{{route('dialings.index')}}">Lists</a></li>
<li class="breadcrumb-item active">List Management</li>
@endpush
@section('content')
<link href="/css/jquery.dataTables.min.css" rel="stylesheet">
<script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js" defer></script>
<style>
	table.dataTable span.highlight {
		background-color: #FFFF88;
		border-radius: 0.28571429rem;
	}
	table.dataTable span.column_highlight {
		background-color: #ffcc99;
		border-radius: 0.28571429rem;
	}
</style>
<!-- Main content -->
<section class="content">
	<div class="container-fluid">
		<div class="filteredTable mt-4" style="display:none">
			<i class="fas fa-filter f-icon"></i>
			<span class="filtered"></span>
			<sup class="btn" onclick="closeInfoSearch()">
				<i class="fas fa-times-circle text-danger"></i>
			</sup>
		</div>
		<div class="table-container pt-2 pb-2">
			<table class="row-border order-column hover searchHighlight" id="agents_datatable">
				<thead style="font-size: 0.93rem;">
					<tr>

						<th style="min-width: 30px;">No</th>
						<th></th>

						<th style="min-width: 80px;">Agent</th>
						<th style="min-width: 80px;">Business</th>
						<th style="min-width: 80px;">Contact</th>
						<th style="min-width: 192px;">Message</th>
						<!-- <th style="min-width: 80px;">Status</th>
						<th style="min-width: 77px !important;">Action</th> -->
					</tr>
				</thead>
			</table>
		</div>
	</div>
</section>
<!-- @include('dialings.partials.ownagentlead-modal') -->

<!-- /.content -->
@endsection
@push('styles')
@endpush
@push('scripts')
<script>
	var rows_selected = [];
	var selectedCheckboxes = [];

	/****  Document Ready ****/
	jQuery(document).ready(function() {
		jQuery.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
			}
		});
		draw_table();

	});





	/**** Draw dataTable ****/
	function draw_table() {
		var selectedCheckboxes = [];
		// stateSave- when there are no filters
		var table = jQuery('#agents_datatable').DataTable({
			// dom: 'lBfrtip',
			processing: true,
			oLanguage: {
				sProcessing: `{!! trim(preg_replace('/\s+/', ' ', view('partials.datatable_loader')->render())) !!}`
			},
			serverSide: true,
			responsive: true,
			autoWidth: false,
			searchHighlight: true,
			pageLength: 25,
			ajax: {
				url: "{{ url('agents/getReportsDataApi') }}",
				type: 'GET',
			},

			columns: [
				//set table columns

				{
					data: 'DT_RowIndex',
					name: 'DT_RowIndex',
					targets: [1],
					searchable: false,
					orderable: false,
				},
				{
					data: 'id',
					name: 'id',
					visible: false
				},

				{
					data: 'agent_name',
					name: 'agent_name'
				},
				{
					data: 'business_name',
					name: 'business_name'
				},
				{
					data: 'contact_name',
					name: 'contact_name'
				},
				{
					data: 'message',
					name: 'message'
				},
				// {
				// 	data: 'action',
				// 	name: 'action',
				// 	orderable: false,
				// 	searchable: false
				// },
			],
			order: [
				[1, 'desc']
			],
			rowCallback: function(row, data, dataIndex) {
				// Get row ID
				var rowId = data[0];
				// If row ID is in the list of selected row IDs
				if ($.inArray(rowId, rows_selected) !== -1) {
					$(row).find('input[type="checkbox"]').prop('checked', true);
					$(row).addClass('selected');
				}
			}
		});

		// Handle table draw event
		table.on('draw', function() {
			// Update state of "Select all" control
			updateDataTableSelectAllCtrl(table);
		});

	}



	// Updates "Select all" control in a data table 
	//
	function updateDataTableSelectAllCtrl(table) {
		var $table = table.table().node();
		var $chkbox_all = $('#agents_datatable tbody input[type="checkbox"]', $table);
		var $chkbox_checked = $('#agents_datatable tbody input[type="checkbox"]:checked', $table);
		var chkbox_select_all = $('#agents_datatable thead input[name="select_all"]', $table).get(0);

		if (chkbox_select_all) {

			// If none of the checkboxes are checked
			if ($chkbox_checked.length === 0) {
				chkbox_select_all.checked = false;
				if ('indeterminate' in chkbox_select_all) {
					chkbox_select_all.indeterminate = false;
				}

				// If all of the checkboxes are checked
			} else if ($chkbox_checked.length === $chkbox_all.length) {
				chkbox_select_all.checked = true;
				if ('indeterminate' in chkbox_select_all) {
					chkbox_select_all.indeterminate = false;
				}

				// If some of the checkboxes are checked
			} else {
				chkbox_select_all.checked = true;
				if ('indeterminate' in chkbox_select_all) {
					chkbox_select_all.indeterminate = true;
				}
			}
		}
	}
</script>
<script src="https://code.jquery.com/jquery-migrate-3.0.0.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.0/jquery-ui.min.css" integrity="sha512-LDB28UFxGU7qq5q67S1iJbTIU33WtOJ61AVuiOnM6aTNlOLvP+sZORIHqbS9G+H40R3Pn2wERaAeJrXg+/nu6g==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<script src="https://cdn.datatables.net/plug-ins/1.11.3/features/searchHighlight/dataTables.searchHighlight.min.js"></script>
@endpush