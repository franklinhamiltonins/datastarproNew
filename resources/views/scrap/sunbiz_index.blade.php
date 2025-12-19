@extends('layouts.app')
@section('pagetitle', 'Contacts Scrap')
@push('breadcrumbs')
<li class="breadcrumb-item active">Contact Scrap</li>
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
		<div class="card">
			<div class="card-body p-0 pb-3">



				<div class="px-3 pt-3 pb-1">
					<div class="row">
						<div class="col-lg-12 margin-tb d-flex flex-wrap justify-content-between table-top-sec">
							<div class="d-flex flex-wrap align-items-center">
								{{-- <a href="javascript:void(0)" class="bulk_lead_remove rounded-left-0 btn btn-danger btn-sm btn-sm closebtn mr-1" id="bulk_lead_remove">
									Migrate
								</a> --}}
								<div class="dropdown">
                                    <button class="btn btn-info btn-sm dropdown-toggle" type="button" id="actionbtn"
                                        data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        Actions
                                    </button>
                                    <div class="dropdown-menu p-0 m-0 text-nowrap" aria-labelledby="actionbtn">
                                        <a href="javascript:void(0)"
                                            class="rounded-0 btn-block btn btn-danger text-left btn-sm mt-0"
                                            id="bulk_aggent_delete">
                                            <i class="fas fa-trash mr-1"></i>
                                            <span class="d-none d-md-inline">Delete</span>
                                        </a>
                                    </div>
                                </div>
								<div class="custom_search_page d-flex align-items-center justify-content-between ml-1">
									<div id="custom_length_menu">
										<label class="d-flex align-items-center justify-content-between mb-0">Show
											<select id="customPageLength" class="form-control form-control-sm mx-1 px-0 bg-transparent" aria-controls="leads_datatable">
												<option value="10">10</option>
												<option value="25" selected>25</option>
												<option value="50">50</option>
												<option value="100">100</option>
											</select>
											entries
										</label>
									</div>
								</div>
							</div>
							<div class="d-flex flex-wrap pb-2">
								<div id="leads_datatable_filter" class="dataTables_filter search-sec mb-0">
									<label class="d-flex align-items-center justify-content-end mb-0 position-relative">
										<input type="search" id="customSearchBox" placeholder="Search for Entries" aria-controls="leads_datatable" class="form-control">
										<i class="fas fa-search position-absolute"></i>
									</label>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="px-3 pt-3 border-top">
					<div class="table-container pb-2">
						<table class="order-column compact hover searchHighlight mt-3" id="contact_scrap_datatable">
							<thead class="text-nowrap" style="font-size: 0.93rem;">
								<tr>
									{{-- <th id="serial_no">#</th> --}}
									<th></th>
									<th id="serial_no"></th>
									<th>Business Name <span class="arrow"></span></th>
									<th>List Url</th>
									<th>Details Url</th>
									<th>Retrieved Contacts</th>
									<th>Scrap Data</th>
									<th style="text-align: center;">Action</th>
								</tr>
							</thead>

						</table>
					</div>
				</div>

				{{-- <div class="table-container pt-2 pb-2">
					<a href="javascript:void(0)" class="bulk_lead_remove rounded-left-0 btn btn-danger btn-sm btn-sm closebtn mr-1" id="bulk_lead_remove">
						Migrate
					</a>
					<table class="row-border order-column compact hover searchHighlight mt-5 border-top" id="contact_scrap_datatable">
						<thead style="font-size: 0.93rem;">
							<tr>								
								<th></th>
								<th id="serial_no"></th>
								<th>Business Name</th>
								<th>List Url</th>
								<th>Details Url</th>
								<th>Contacts</th>
								<th>Scrap</th>
							</tr>
						</thead>
					</table>
				</div> --}}

			</div>
		</div>
	</div>
	@include('partials.delete-modal')

</section>
<!-- /.content -->
@endsection
@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
@endpush
@push('scripts')

<script>

$(document).ready(function() {
    // Set up CSRF token for all AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    window.addEventListener('pageshow', function(event) {
        $('body').find('#customSearchBox').val('');
        $('body').find('#customPageLength').val('25');
    });
});

	var base_url = "{{url('/')}}";
	var dataTableId = 'contact_scrap_datatable';




	/****  Document Ready ****/
	jQuery(document).ready(function() {

		let assignContact = sessionStorage.getItem("assignContactKey");
		if (assignContact) {
			toastr.success(assignContact);
			sessionStorage.removeItem("assignContactKey");
		}
		draw_table();
		$('#' + dataTableId + ' tbody').on('click', 'tr', function() {
			// remove selected row on click and refresh page
			if ($(this).hasClass('selected')) {
				$(this).removeClass('selected');
				remove_params('id');
			}
		});

		jQuery(document).on('click', '.submit_business_button', function() {

			var inputFieldId = jQuery(this).data('text_id');
			var lead_name = jQuery('#' + inputFieldId).val();
			var lead_id = jQuery(this).data('lead_id');
			var formId = jQuery(this).closest('form').attr('id');
			var csrfToken = jQuery('#' + formId + ' input[name="_token"]').val();

			if (!lead_name) {
				toastr.error("Business name cannot be empty.");
				return false;
			}

			var prev_lead_name = jQuery('#businessName_' + lead_id).text().trim();
			let encryptedId = btoa(lead_id);  // base64 encode
			let prev_lead_anchor_name = `<a href="${window.location.origin}/leads/edit/${encryptedId}" target="_blank">${prev_lead_name}</a>`
			console.log('prev_lead_name    ---', prev_lead_anchor_name);

			jQuery.ajax({
				url: '/updateSingleBusinessName', // Replace with your actual endpoint
				type: 'POST',
				data: {
					lead_name: lead_name,
					lead_id: lead_id,
				},
				success: function(response) {
					$(`#formBusiness-${lead_id}`).remove();
					$(`.editbtn${lead_id}`).show();
					if (response.status == 'error') {
						$(`#formBusiness-${lead_id}`).remove();
						// $(`#businessName_${lead_id}`).text(prev_lead_name).show();
						$(`#businessName_${lead_id}`).html(prev_lead_anchor_name).show();
						toastr.error(response.message);
					} else {
						$(`#formBusiness-${lead_id}`).remove();
						// $(`#businessName_${lead_id}`).text(lead_name).show();
						$(`#businessName_${lead_id}`).html(`<a href="${window.location.origin}/leads/edit/${encryptedId}" target="_blank">${lead_name}</a>`).show();

						toastr.success(response.message);
					}
					// Handle success response

				},
				error: function(xhr) {
					// Handle error response
					console.error(xhr.responseText);
				}
			});
		});


	});


	jQuery(document).ready(function() {
		// Use a parent element that exists in the DOM when DataTables initializes
		jQuery(document).on('click', '.submit_fetch_button', function() {
			var inputFieldId = jQuery(this).data('text_id');
			var inputValue = jQuery('#' + inputFieldId).val();
			var leadId = jQuery(this).data('lead_id');
			var formId = jQuery(this).closest('form').attr('id');
			var csrfToken = jQuery('#' + formId + ' input[name="_token"]').val();

			if (!leadId || !inputValue) {
				toastr.error('Parameters mismatch.Please contact administrator.');
				return false;
			}

			jQuery.ajax({
				url: '/getDataBySunbizUrl', // Replace with your actual endpoint
				type: 'POST',
				data: {
					details_url: inputValue,
					lead_id: leadId,
					_token: csrfToken
				},
				success: function(response) {
					if (response.status == 'error') {
						toastr.error(response.message);
					} else {
						let membershtml = jQuery.trim(generateMemberList(response.data));
						if (membershtml) {
							jQuery('.members_' + leadId).html(membershtml);
						}
						toastr.success(response.message);
					}
					// Handle success response
					console.log(response);
				},
				error: function(xhr) {
					// Handle error response
					console.error(xhr.responseText);
				}
			});
		});
	});

	function generateMemberList(members) {
		let html = '<ul>';
		members.forEach((member, index) => {
			const colorClass = `color-${index % 4}`;
			html += `
            <li class="member-item ${colorClass}">
                <span class="member-title">${member.member_title}</span>
                <span class="member-name">${member.member_name}</span>
            </li>
        `;
		});
		html += '</ul>';
		return html;
	}




	/**** Draw dataTable Ajax ****/
	function draw_table() {
		// ajax setup for table ajax
		jQuery.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
			}
		});
		// stateSave- when there are no filters
		var table = jQuery('#' + dataTableId).DataTable({
			paging: true,
			processing: true,
			serverSide: true,
			responsive: true,
			autoWidth: false,
			searchHighlight: true,
			pageLength: 25,
			oLanguage: {
				sProcessing: `{!! trim(preg_replace('/\s+/', ' ', view('partials.datatable_loader')->render())) !!}`
			},
			ajax: {
				url: "{{ url('/getScrapSunbizGetLeadsApi') }}",
				type: 'POST',
				data: function(d) {
					// d.location_leads_id_search = true;
				}
			},
			rowCallback: function(row, data) {
				// if the lead id from param is the same with the row id, select it

			},
			columns: [
				//set table columns
				// {
				// 	data: 'DT_RowIndex',
				// 	name: 'DT_RowIndex',
				// 	"targets": [0],
				// 	"searchable": false,
				// 	"orderable": false,
				// 	render: function(data, type, row, meta) {
				// 		//return '<input type="checkbox" class="select-row" value="' + row.id + '">';
				// 	}
				// },
				{
					data: 'id',
					name: 'id',
					'visible': false,
					"searchable": true,
				},
				{ data: 'id', name: 'id', searchable: false, orderable: false, render: function(data, type, row, meta) {
                	return '<input type="checkbox" class="select-row" value="' + row.id + '">';
            	}},

				{
					data: 'name',
					name: 'name'
				},
				{
					data: 'list_url',
					name: 'list_url',
					"searchable": false,
					"orderable": false,
					render: function(data, type, row) {
						return data; // Render as HTML
					}
				},
				{
					data: 'details_url',
					name: 'details_url',
					"searchable": false,
					"orderable": false,
					render: function(data, type, row) {
						return data; // Render as HTML
					}
				},
				{
					data: 'contacts',
					name: 'contacts',
					"searchable": false,
					"orderable": false
				},
				{
					data: 'scrap',
					name: 'scrap',
					"searchable": false,
					"orderable": false
				},
				{
					data: 'actions',
					name: 'actions',
					"searchable": false,
					"orderable": false
				}
			],
			order: [
				[2, 'desc']
			],
			dom: 'rt<"bottom"ip><"clear">',
			initComplete: function() {

			}

		});

		function debounce(func, wait) {
			var timeout;
			return function() {
				var context = this,
					args = arguments;
				clearTimeout(timeout);
				timeout = setTimeout(function() {
					timeout = null;
					func.apply(context, args);
				}, wait);
			};
		}

		$('#customPageLength').on('change', function() {
			var length = $(this).val();
			table.page.len(length).draw();
		});

		$('#customSearchBox').on('keyup', debounce(function(event) {
			$(event.target).siblings('i.fas.fa-search.position-absolute').remove();
			if (!event.target.value) {
				$(event.target).after('<i class="fas fa-search position-absolute"></i>');
			}
			if (event.key === "Enter") {
				table.search(this.value).draw();
			} else {
				table.search(this.value).draw();
			}
		}, 500)); // 500ms debounce interval

		$('#customSearchBox').on('input', debounce(function(event) {
			if (!event.target.value) {
				console.log('contact search cross clicked');
				$(event.target).blur(); // to remove cursiour from search field.

				$(event.target).siblings('i.fas.fa-search.position-absolute').remove(); // remove search icon and the append
				$(event.target).after('<i class="fas fa-search position-absolute"></i>');
				table.search(event.target.value).draw(); // drow the table
			}
		}, 500));

		// Add select all checkbox to table header
		var $thead = jQuery('#contact_scrap_datatable thead #serial_no');
		$thead.prepend('<input type="checkbox" class="select-all">');

		// Select all checkboxes 
		jQuery('#contact_scrap_datatable').on('change', '.select-all', function() {
			var checked = this.checked;
			jQuery('.select-row').prop('checked', checked);
			// Log selected checkboxes
			if (checked) {
				var selectedValues = jQuery('.select-row:checked').map(function() {
					return this.value;
				}).get();

			} else {

			}
		});

		// Handle individual row selections
		jQuery('#contact_scrap_datatable').on('change', '.select-row', function() {
			var $checkboxes = jQuery('.select-row');
			jQuery('.select-all').prop('checked', $checkboxes.length === $checkboxes.filter(':checked').length);
			// Log selected checkboxes
			var selectedValues = jQuery('.select-row:checked').map(function() {
				return this.value;
			}).get();

		});

		jQuery('#' + dataTableId).on('click', '.edit_business_lead', function(event) {
			// console.log(event.currentTarget);
			const leadId = event.target.parentNode.getAttribute('data-id');
			const leadName = jQuery('#businessName_' + leadId).text().trim();
			jQuery('#businessName_' + leadId).hide();
			jQuery(`#formBusiness-${leadId}`).hide();
			jQuery(this).hide();
			const innerHtmlData = `<form method="post" class="form-group d-flex" id="formBusiness-${leadId}">
						@csrf 
						<input class="form-control rounded-right-0" value="${leadName}" type="text" name="business_name" id="input_${leadId}" />
						<button class="btn btn-sm btn-primary rounded-left-0 submit_business_button" type="button" data-text_id="input_${leadId}" data-lead_id="${leadId}"><i class="fas fa-save"></i></button>
					</form>
					`;
			jQuery(this).after(innerHtmlData);
		});

		// Button click event to submit selected checkbox values via AJAX
		// Button click event to submit selected checkbox values via AJAX
		jQuery('#bulk_lead_remove').on('click', function() {
			jQuery('#bulk_lead_remove').prop('disabled', true);

			var selectedValues = jQuery('.select-row:checked').map(function() {
				return this.value;
			}).get();
			console.log("Selected values:", selectedValues);
			if (selectedValues.length <= 0) {
				toastr.error('Please check at least one checkbox to continue');
				return false;
			}
			jQuery('#contact_scrap_datatable_processing').show();
			// Perform AJAX post request
			jQuery.ajax({
				url: '/scrap/migratecontacts',
				type: 'POST',
				data: {
					selectedValues: selectedValues
				},
				success: function(response) {
					if (response.leadsCount) {
						toastr.success(response.message);
					} else {
						toastr.error(response.message);
					}
					jQuery('#contact_scrap_datatable').DataTable().draw(true);
					jQuery('.select-all').prop('checked', false);
				},
				error: function(xhr, status, error) {
					toastr.error("Something went wrong.Please contact administrator.");
				},
				complete: function() {
					// Re-enable the button and hide loader after AJAX request completes
					jQuery('#bulk_lead_remove').prop('disabled', false);
					jQuery('#contact_scrap_datatable_processing').hide();
				}
			});
		});
	}

	$(document).on('click', '.delete_button', function() {
		var idarray = [$(this).data('id')];

		$('#deleteModal').modal('show');

		localStorage.setItem('idarray', idarray);


		// console.log(idarray);
	});

	$(document).on('click', '#confirm', function() {
		var idarray = localStorage.getItem('idarray');

		deletescrapleadsfunction(idarray);
	});

	$(document).on('click','#bulk_aggent_delete',function() {
        // jQuery('#bulk_aggent_delete').prop('disabled', true);
        jQuery('#leads_datatable_processing').show();
        var selectedValues = $('.select-row:checked').map(function() {
            return this.value;
        }).get();

        if (selectedValues.length > 0) {
            // Open the modal
            $('#deleteModal').modal('show');

            // console.log(selectedValues);

			localStorage.setItem('idarray', selectedValues);
        } else {
            toastr.error('Please check at least one checkbox to continue');
            // jQuery('#bulk_aggent_delete').prop('disabled', false);
            // jQuery('#leads_datatable_processing').hide();
            return false;
        }
    });

	function deletescrapleadsfunction(idarray) {
		$.ajax({
            url: "{{ route('scrap_sunbiz.delete') }}",
            type: 'POST',
            data: {
                selectedValues: idarray
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.status) {
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message);
                }
                jQuery('#bulk_aggent_delete').prop('disabled', false);
                $('#contact_scrap_datatable').DataTable().draw(true);
                $('.select-all').prop('checked', false);
                $('#deleteModal').modal('hide');
            },
            error: function(xhr, status, error) {
                toastr.error("Something went wrong.Please contact administrator.");
            },
            complete: function() {
                // Re-enable the button and hide loader after AJAX request completes
                $('#bulk_contact_remove').prop('disabled', false);
                $('#leads_datatable_processing').hide();
            }
        });
	}
</script>
<!-- <script src="https://code.jquery.com/jquery-migrate-3.0.0.min.js"></script> -->
<!-- <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script> -->
<!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.0/jquery-ui.min.css" integrity="sha512-LDB28UFxGU7qq5q67S1iJbTIU33WtOJ61AVuiOnM6aTNlOLvP+sZORIHqbS9G+H40R3Pn2wERaAeJrXg+/nu6g==" crossorigin="anonymous" referrerpolicy="no-referrer" /> -->
<script src="https://cdn.datatables.net/plug-ins/1.11.3/features/searchHighlight/dataTables.searchHighlight.min.js"></script>
<script src="//bartaz.github.io/sandbox.js/jquery.highlight.js"></script>
<!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css"> -->
<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js"></script> -->
@endpush