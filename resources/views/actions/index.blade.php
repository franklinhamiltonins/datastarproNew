@extends('layouts.app')
@section('pagetitle', 'Leads Contact Report')
@push('breadcrumbs')
<li class="breadcrumb-item"><a href="{{route('actions.index')}}">Leads Contact Report</a></li>
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
		<div class="row">
			<div class="col-lg-12 margin-tb mb-3 d-flex flex-wrap">


				<button class="btn btn-primary mb-3 " type="button" data-bs-toggle="collapse" data-bs-target="#filterActions" aria-expanded="false" aria-controls="filterActions">
					<i class="fas fa-filter"></i>
					<span class="d-none d-md-inline"> Filter Contact Report
					</span>
				</button>
			</div>
		</div>


		<div class="collapse" id="filterActions">
			<div class="card card-body">
				<div class="search-filter">

					@include('actions.partials.search-action-form')
				</div>
			</div>
		</div>



		<div class="filteredTable mt-4" style="display:none">
			<i class="fas fa-filter f-icon"></i>
			<span class="filtered"></span>
			<sup class="btn" onclick=" closeInfoSearch()">
				<i class="fas fa-times-circle text-danger"></i>
			</sup>
		</div>

		<div class="table-container pt-4 pb-4">
			<table class="row-border order-column compact hover searchHighlight" id="action_report_datatable">
				<thead style="font-size: 0.93rem;">
					<tr>
						<th>No</th>
						<th>ID</th>
						<th style="width:99px">Contact Name</th>
						<th style="min-width: 192px;">Business Name</th>
						<th style="width: 99px;">Contact Phone</th>
						<th>Contact Email</th>
						<th style="width: 150px;">Date of Contact</th>
						<th>Campaign</th>
						<th>Current Client</th>

					</tr>
				</thead>

			</table>
		</div>
	</div>
	@include('partials.delete-modal')
	@include('leads.partials.save-campaign-modal')
</section>
<!-- /.content -->
@endsection
@push('styles')
@endpush
@push('scripts')
@if(!isset($leadId))
{{$leadId = ''}}
@endif
<script>
	var actionFilters;
	/***************************************
	    Document Ready
	**************************************/
	jQuery(document).ready(function() {

		sessionStorage.setItem("actionFilters", '')

		draw_table();



	});

	/***************************************
	    Draw dataTable Ajax
	**************************************/

	function draw_table() {



		// ajax setup for table ajax
		jQuery.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
			}

		});
		// stateSave- when there are no filters
		jQuery('#action_report_datatable').DataTable({
			// dom: 'lBfrtip',
			dom: 'Bfrtip',
			buttons: [{
					extend: 'csv',
					className: 'exportreport',
					text: 'Export Report'
				},

			],
			processing: true,
			oLanguage: {
				sProcessing: `{!! trim(preg_replace('/\s+/', ' ', view('partials.datatable_loader')->render())) !!}`
			},
			serverSide: true,
			responsive: true,
			autoWidth: false,
			searchHighlight: true,
			stateSave: true,
			paging: false,
			ajax: {
				url: "{{ url('/actions/datatable') }}",
				type: 'POST',
				data: function(d) {
					if (!isEmpty(sessionStorage.getItem("actionFilters"))) {
						d.filters = JSON.parse(sessionStorage.getItem("actionFilters"));
					}
				}

			},
			columns: [
				//set table columns
				{
					data: 'DT_RowIndex',
					name: 'DT_RowIndex',
					"targets": [0],
					"searchable": false,
					"orderable": false,
				},
				{
					data: 'id',
					name: 'id',
					'visible': false
				},
				{
					data: 'contact_name',
					name: 'contact_name'
				},
				{
					data: 'leads',
					name: 'leads.name'
				},
				{
					data: 'contacts',
					name: 'contacts.c_phone'
				},
				{
					data: 'email',
					name: 'contacts.c_email'
				},
				{
					data: 'contact_date',
					name: 'contact_date'
				},
				{
					data: 'campaigns',
					name: 'campaigns.name'
				},
				{
					data: 'c_is_client',
					name: 'c_is_client'
				},

			],
			order: [
				[6, 'desc']
			],

		});


	} //end of datatable Ajax

	function filter_table() {

		var filters = {};
		filters.startDate = $('#start_date').val();
		filters.endDate = $('#end_date').val();
		console.log(JSON.stringify(filters));

		sessionStorage.setItem("actionFilters", JSON.stringify(filters));
		$('#action_report_datatable').DataTable().draw(true);
	}



	function resetCloseFiltersTab(elem) {
		// hide accordion
		$('#filterActions').collapse('hide');
		$('#filterActions .controls input').val('');
		sessionStorage.setItem("actionFilters", '');
		sessionStorage.setItem("dialing_filters_clicked", 0);
		console.log('abc');
		jQuery('#action_report_datatable').DataTable().draw(true);

	}



	/***************************************
	         Check if  empty obj
	     **************************************/
	function isEmpty(obj) {
		for (var prop in obj) {
			if (obj.hasOwnProperty(prop)) {
				return false;
			}
		}

		return true;

	}
</script>
<script src="https://cdn.datatables.net/plug-ins/1.11.3/features/searchHighlight/dataTables.searchHighlight.min.js"></script>
<script src="//bartaz.github.io/sandbox.js/jquery.highlight.js"></script>
<script src="https://cdn.datatables.net/buttons/2.0.1/js/dataTables.buttons.min.js" defer></script>
<script src="https://cdn.datatables.net/buttons/2.0.1/js/buttons.html5.min.js" defer></script>
<script src="https://cdn.datatables.net/buttons/2.0.1/js/buttons.print.min.js" defer></script>
{{--
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.0.1/css/buttons.dataTables.min.css"> --}}
@endpush