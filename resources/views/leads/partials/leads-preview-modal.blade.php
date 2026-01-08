<style>
	.modal-90-center .modal-dialog {
	    width: 90%;
	    max-width: 793px; /* A4 width in pixels at 96 DPI */
	    height: auto;
	    margin: auto;
	}

	.modal-90-center .modal-content {
	    height: auto;
	    border-radius: 10px;
	    aspect-ratio: 1 / 1.414; /* A4 aspect ratio (1:√2) */
	    padding: 20px; /* Add some padding for A4 look */
	    box-sizing: border-box;
	    background-color: white; /* Ensure it's white for printing */
	}

	.modal-90-center .modal-body {
	    overflow-y: auto;
	    height: auto;
	    max-height: calc(100% - 120px);
	    font-family: Arial, sans-serif;
	    font-size: 14px;
	    line-height: 1.5;
	    margin: 0;
	    padding: 10px;
	}

	.modal-90-center .modal-header,
	.modal-90-center .modal-footer {
	    flex-shrink: 0;
	    padding: 10px;
	    background-color: #f5f5f5; /* Light gray background for header/footer */
	    border: none;
	}

	.modal-90-center .modal-header h5 {
	    margin: 0;
	    font-size: 16px;
	    font-weight: bold;
	    text-align: center;
	    width: 100%;
	}

	.modal-90-center .modal-footer {
	    text-align: center;
	}
	#previewLeadModal .card-body .form-group strong, .a4-style .card-body .form-group strong, #previewLeadModal .card-body .form-group .small, .a4-style .card-body .form-group .small{
		font-size: 10px;
	}
	.a4-style {
	    width: 793px; /* A4 width in pixels */
	    max-width: 100%;
	    height: auto;
	    aspect-ratio: 1 / 1.414; /* A4 aspect ratio */
	    background-color: white; /* Ensure white background for PDF */
	    padding: 20px; /* Padding for content */
	    font-family: Arial, sans-serif;
	    font-size: 14px;
	    line-height: 1.5;
	    box-sizing: border-box;
	}

	.a4-style-header,
	.a4-style-footer {
	    text-align: center;
	    font-weight: bold;
	    font-size: 16px;
	    margin-bottom: 10px;
	}

	.a4-style-body {
	    overflow-y: auto;
	    height: auto;
	    margin: 0;
	    padding: 10px;
	}
	.a4-style .gap{
		padding-bottom: 22px !important;
	}
	/* A4-specific print styles */
	@media print {
	    .modal-90-center .modal-dialog {
	        width: 100%;
	        max-width: 793px;
	        height: auto;
	    }

	    .modal-90-center .modal-content {
	        box-shadow: none; /* Remove shadow for print */
	        border: none; /* Remove border for print */
	    }

	    body {
	        margin: 0;
	        padding: 0;
	        overflow: hidden; /* Prevent scrollbars */
	    }
	}
</style>

<div class="modal fade modal-90-center" id="previewLeadModal" tabindex="-1" role="dialog" aria-labelledby="previewLeadModal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content p-0">
        	<div class="modal-header p-3 align-items-center d-flex justify-content-between bg-light">
			    <!-- Title -->
			    <h5 class="modal-title" id="exampleModalLongTitle">Lead Preview</h5>

			    <!-- Action Buttons -->
			    <div class="d-flex gap-2 align-items-center">
			        <!-- Download PDF Button -->
			        @can('lead-download')
				        <button id="downloadBtn" class="btn bg-info mr-2 btn-sm d-flex align-items-center" title="Download">
				            <i class="fas fa-file-pdf me-2"></i>
				        </button>
			        @endcan

			        <!-- Print PDF Button -->
			        @can('lead-print')
				        <button id="printBtn" class="btn bg-info btn-sm d-flex align-items-center" title="Print">
				            <i class="fas fa-print me-2"></i>
				        </button>
			        @endcan
			    </div>

			    <!-- Close Button -->
			    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
			</div>
            <div class="modal-body p-3" id="printSection">
			    <!-- Business Section -->
			    <div class="section mb-3">
			        <h5 class="section-title text-primary h6 mb-1 pb-1 border-bottom border-primary" style="color: #1f78a0; border-color: #1f78a0;">Bussiness</h5>
				    <div class="card-body lead-update p-0 pt-1">
					    @if(isset($lead))
						    <!-- Current Client Status -->
							 <div class="d-flex">
								<p class="font-weight-bold mb-2" id="name_preview"> {{ !empty($lead->name) ? $lead->name : "" }} </p>
								@if($lead && $lead->is_client == 1 && $lead->contacts()->NotClient()->count() == 0)
									<div class="form-group mb-2 ml-2" id="current_client_area">
										<p class="font-weight-bold text-success mb-0">Current Client</p>
									</div>
								@endif
							</div>
					    @endif

					    <!-- Type, Year Built, and Unit Count -->
					    <div class="form-row m-0 mb-2">
					        <div class="form-group col-12 col-md-4 mb-0 py-1 px-0 border-top border-bottom">
					            <strong>Type:</strong> <span class="small" id="type_preview">{{ !empty($lead->type) ? $lead->type : "N/A" }}</span>
					        </div>
					        <div class="form-group col-12 col-md-3 mb-0 py-1 px-0 border-top border-bottom">
					            <strong>Year Built:</strong> <span class="small" id="creation_date_preview">{{ !empty($lead->creation_date) ? date('d/m/Y',strtotime($lead->creation_date)) : "N/A" }}</span>
					        </div>
					        <div class="form-group col-12 col-lg-5 mb-0 py-1 px-0 border-top border-bottom">
					            <strong>Unit Count:</strong> <span class="small" id="unit_count_preview">{{ !empty($lead->unit_count) ? $lead->unit_count : "N/A" }}</span>
					        </div>
					    </div>

					    <!-- Address -->
					    <div class="form-row">
					        <div class="form-group col-12 px-2 mb-1">
					            <strong>Business Address 1:</strong> <span class="small" id="address1_preview">{{ !empty($lead->address1) ? $lead->address1 : "N/A" }}</span>
					        </div>
					        <div class="form-group col-12 px-2 mb-1">
					            <strong>Business Address 2:</strong> <span class="small" id="address2_preview">{{ !empty($lead->address2) ? $lead->address2 : "N/A" }}</span>
					        </div>
					    </div>

					    <!-- Location Details -->
					    <div class="form-row">
					        <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
					            <strong>City:</strong> <span class="small" id="city_preview">{{ !empty($lead->city) ? $lead->city : "N/A" }}</span>
					        </div>
					        <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
					            <strong>County:</strong> <span class="small" id="county_preview">{{ !empty($lead->county) ? $lead->county : "N/A" }}</span>
					        </div>
					        <div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
					            <strong>Coastal / Non Coastal:</strong> <span class="small" id="coastal_preview">{{ $lead->coastal ? 'Coastal' : 'Non Coastal' }}</span>
					        </div>
					    </div>
					    <div class="form-row">
					        <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
					            <strong>State:</strong> <span class="small" id="state_preview">{{ !empty($lead->state) ? $lead->state : "N/A" }}</span>
					        </div>
					        <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
					            <strong>Zip:</strong> <span class="small" id="zip_preview">{{ !empty($lead->zip) ? $lead->zip : "N/A" }}</span>
					        </div>
					        <div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
					        	<strong>Total Square Footage:</strong> <span class="small" id="total_square_footage_preview">{{ !empty($lead->total_square_footage) ? $lead->total_square_footage : "N/A" }}</span>
					        </div>
					    </div>
					    <div class="form-row">
					        <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
					        	<strong>Total insured value:</strong> <span class="small" id="business_tiv_preview">{{ !empty($lead->business_tiv) ? $lead->business_tiv : "N/A" }}</span>
					        </div>
					        <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
					        	<strong>Appraiser Name:</strong> <span class="small" id="appraisal_name_preview">{{ !empty($lead->appraisal_name) ? $lead->appraisal_name : "N/A" }}</span>
					        </div>
					        <div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
					        	<strong>Appraisal Company :</strong>  <span class="small" id="appraisal_company_preview">{{!empty($lead->appraisal_company)?$lead->appraisal_company:"N/A"}}</span>
					        </div>
					    </div>

					    <div class="form-row">
					        <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
					        	<strong>Appraisal Date:</strong>  <span class="small" id="appraisal_date_preview">{{!empty($lead->appraisal_date)?date('d/m/Y',strtotime($lead->appraisal_date)):"N/A"}}</span>
					        </div>
					        <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
					        	<strong>Flood Zone:</strong> <span class="small" id="ins_flood_preview">{{ !empty($lead->ins_flood) ? $lead->ins_flood : "No" }}</span>
					        </div>
					        <div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
					        	<strong>Property Floors:</strong> <span class="small" id="prop_floor_preview">{{ !empty($lead->prop_floor) ? $lead->prop_floor : "N/A" }}</span>
					        </div>
					    </div>

					    <div class="form-row">
					        <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
					        	<strong>Pool:</strong> <span class="small" id="pool_preview">{{ !empty($lead->pool) ? $lead->pool : "No" }}</span>
					        </div>
					        <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
					        	<strong>Lakes:</strong> <span class="small" id="lakes_preview">{{ !empty($lead->lakes) ? $lead->lakes : "No" }}</span>
					        </div>
					        <div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
					        	<strong>Clubhouse:</strong> <span class="small" id="clubhouse_preview">{{ !empty($lead->clubhouse) ? $lead->clubhouse : "No" }}</span>
					        </div>
					    </div>

					    <div class="form-row">
					    	<div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
					        	<strong>Tennis/Basketball Court:</strong> <span class="small" id="tennis_basketball_preview">{{ !empty($lead->tennis_basketball) ? $lead->tennis_basketball : "No" }}</span>
					        </div>
					        <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
					        	<strong>ISO:</strong> <span class="small" id="iso_preview">{{ !empty($lead->iso) ? $lead->iso : "N/A" }}</span>
					        </div>
					        <div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
					        	<strong>Lead Source:</strong> <span class="small" id="lead_source_preview">{{ !empty($lead->leadSource->name) ? $lead->leadSource->name : "N/A" }}</span>
					        </div>
					    </div>
					</div>
				</div>

			    <!-- Community Section -->
			    <div class="section mb-3">
			        <h5 class="section-title text-primary h6 mb-1 pb-1 border-bottom border-primary" style="color: #1f78a0; border-color: #1f78a0;">Community</h5>
			        <div class="card-body lead-update p-0 pt-2">
			        	<div class="form-row">
					        <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
					            <strong> Wind Mitigation Date:</strong>  <span class="small" id="wind_mitigation_date_preview">{{!empty($lead->wind_mitigation_date)?$lead->wind_mitigation_date:"N/A"}}</span>
					        </div>
					        <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
					            <strong>Roof Year:</strong> <span class="small" id="roof_year_preview">{{ !empty($lead->roof_year) ? $lead->roof_year : "N/A" }}</span>
					        </div>
					        <div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
					            <strong>Roof Covering:</strong> <span class="small" id="roof_covering_preview">{{ !empty($lead->roof_covering) ? $lead->roof_covering : "N/A" }}</span>
					        </div>
					    </div>
					    <div class="form-row">
					        <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
					            <strong>Roof Connection:</strong> <span class="small" id="roof_connection_preview">{{ !empty($lead->roof_connection) ? $lead->roof_connection : "N/A" }}</span>
					        </div>
					        <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
					            <strong>Roof Geometry:</strong> <span class="small" id="roof_geom_preview">{{ !empty($lead->roof_geom) ? $lead->roof_geom : "N/A" }}</span>
					        </div>
					        <div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
					        	<strong> SWR:</strong>  <span class="small" id="secondary_water_insurance_preview">{{!empty($lead->secondary_water_insurance)?$lead->secondary_water_insurance:"No"}}</span>
					        </div>
					    </div>
					    <div class="form-row">
					        <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
					        	<strong>Opening Protection:</strong>  <span class="small" id="opening_protection_preview">{{!empty($lead->opening_protection)?$lead->opening_protection:"No"}}</span>
					        </div>
					    </div>
					    <div class="form-row">
					        <div class="form-group col-12 col-md-12 col-lg-12 mb-1 px-2">
					            <strong>Report Notes:</strong> <span class="small longtextarea" id="other_community_info_preview">{{ !empty($lead->other_community_info) ? $lead->other_community_info : "N/A" }}</span>
					        </div>
					    </div>
			        </div>
			    </div>

			    <!-- Prospect's Insurance Section -->
			    <div class="section">
			        <h5 class="section-title text-primary h6 mb-1 pb-1 border-bottom border-primary" style="color: #1f78a0; border-color: #1f78a0;">Prospect’s Insurance</h5>
			        <div class="card-body lead-update p-0 pt-3 preview_card_body preview_card_body_append">
						 <!-- Prospect Insurance  -->
						<div class="p-2 pt-3 pb-0 mx-0 rounded border position-relative general_liability">
						 	<div class="section-head position-absolute m-0 p-0 z-5 d-inline-block font-weight-bold px-1 bg-white">Property :</div>
							<div class="form-row">
						        <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
						            <strong>Carrier: </strong>  <span class="small" id="ins_prop_carrier_preview">{{!empty($lead->ins_prop_carrier)?$lead->ins_prop_carrier:"N/A"}}</span>
						        </div>
						        <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
						            <strong>Month: </strong>  <span class="small" id="renewal_carrier_month_preview">{{!empty($lead->renewal_carrier_month)?$lead->renewal_carrier_month:"N/A"}}</span>
						        </div>
						        <div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
						        	<strong>Expiring Premium:</strong> <span class="small" id="premium_preview">{{ !empty($lead->premium) ?'$'. $lead->premium : "N/A" }}</span>
						        </div>
					    	</div>
				        	<div class="form-row">
						        <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
						            <strong>Expiring Premium Year:</strong> <span class="small" id="premium_year_preview">{{ !empty($lead->premium_year) ? $lead->premium_year : "N/A" }}</span>
						        </div>
						        <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
						            <strong>Total insured value:</strong> <span class="small" id="insured_amount_preview">{{ !empty($lead->insured_amount) ? '$'.$lead->insured_amount : "N/A" }}</span>
						        </div>
						        <div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
						            <strong>T.I.V. – Year:</strong> <span class="small" id="insured_year_preview">{{ !empty($lead->insured_year) ? '$'.$lead->insured_year : "N/A" }}</span>
						        </div>
						    </div>
						    <div class="form-row">
						        <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
						            <strong>Price Per SqFt:</strong> <span class="small" id="price_per_sqft_preview">N/A</span>
						        </div>
						        <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
						            <strong>Policy Renew:</strong>  <span class="small" id="policy_renewal_date_preview">{{!empty($lead->policy_renewal_date)?date('d/m/Y',strtotime($lead->policy_renewal_date)):"N/A"}}</span>
						        </div>
						        <div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
						            <strong>Incumbent Agency:</strong>  <span class="small" id="incumbent_agency_preview">{{!empty($lead->incumbent_agency)?$lead->incumbent_agency:"N/A"}}</span>
						        </div>
						    </div>
						    <div class="form-row">
						        <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
						            <strong>Incumbent Agent:</strong>  <span class="small" id="incumbent_agent_preview">{{!empty($lead->incumbent_agent)?$lead->incumbent_agent:"N/A"}}</span>
						        </div>
						        <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
						            <strong>Rating:</strong>  <span class="small" id="rating_preview">{{!empty($lead->rating)?$lead->rating:"N/A"}}</span>
						        </div>
						        <div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
						            <strong> Sinkhole:</strong>  <span class="small" id="skin_hole_preview">{{!empty($lead->skin_hole)?$lead->skin_hole:"No"}}</span>
						        </div>
						    </div>
						    <div class="form-row">
						    	<div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
						            <strong>All other Perils:</strong>  <span class="small" id="all_other_perils_preview">{{!empty($lead->all_other_perils)?'$'.$lead->all_other_perils:"N/A"}}</span>
						        </div>
						        <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
						            <strong> Hurricane Deductible:</strong>  <span class="small" id="hurricane_deductible_preview">{{!empty($lead->hurricane_deductible)?$lead->hurricane_deductible:"N/A"}}</span>
						        </div>
						        <div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
						            <strong>Hurricane Deductible (Per Occ/Year):</strong>  <span class="small" id="hurricane_deductible_occurrence_preview">{{!empty($lead->hurricane_deductible_occurrence)?$lead->hurricane_deductible_occurrence:"N/A"}}</span>
						        </div>
						    </div>
						    <div class="form-row">
						        <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
						            <strong>Ordinance of Law:</strong>  <span class="small" id="ordinance_of_law_preview">{{!empty($lead->ordinance_of_law)?$lead->ordinance_of_law:"N/A"}}</span>
						        </div>
						        <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
						           	<strong>T.I.V. Matches Appraisal:</strong>  <span class="small" id="tiv_matches_appraisal_preview">{{!empty($lead->tiv_matches_appraisal)?$lead->tiv_matches_appraisal:"No"}}</span>
						        </div>
						        <div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
						        </div>
						    </div>
						    <div class="form-row">
						        <div class="form-group col-12 mb-1 px-2">
						            <strong>Property Notes:</strong>  <span class="small" id="property_insurance_coverage_preview">{{!empty($lead->property_insurance_coverage)?$lead->property_insurance_coverage:"No"}}</span>
						        </div>
						    </div>
						</div>
			        	<div class="gap"></div>
					    <!-- General Liability  -->
					    <div class="p-2 mt-4 pt-3 pb-0 mx-0 rounded border position-relative general_liability">
						    <div class="section-head position-absolute m-0 p-0 z-5 d-inline-block font-weight-bold px-1 bg-white">General Liability :</div>
						    <div class="form-row">
						        <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
						            <strong>Carrier:</strong> <span class="small" id="general_liability_preview">{{ !empty($lead->general_liability) ? $lead->general_liability : "N/A" }}</span>
						        </div>
						        <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
						            <strong>Month:</strong> <span class="small" id="GL_ren_month_preview">{{ !empty($lead->GL_ren_month) ? $lead->GL_ren_month : "N/A" }}</span>
						        </div>
						        <div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
						            <strong>Expiring Premium:</strong> <span class="small" id="gl_expiry_premium_preview">{{ !empty($lead->gl_expiry_premium) ? '$' . formatUSNumber($lead->gl_expiry_premium, 2) : "N/A" }}</span>
						        </div>
						    </div>
						    <div class="form-row">
						        <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
						            <strong>Policy Renewal Date:</strong> <span class="small" id="gl_policy_renewal_date_preview">{{ !empty($lead->gl_policy_renewal_date) ? $lead->gl_policy_renewal_date : "N/A" }}</span>
						        </div>
						        <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
						            <strong>Rating:</strong> <span class="small" id="gl_rating_preview">{{ !empty($lead->gl_rating) ? $lead->gl_rating : "N/A" }}</span>
						        </div>
						        <div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
						        	<strong>Price Per Unit:</strong> <span class="small" id="gl_price_per_unit_preview">N/A</span>
						        </div>
						    </div>
						    <div class="form-row">
						        <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
						            <strong>Exclusions:</strong> <span class="small" id="gl_exclusions_preview">{{ !empty($lead->gl_exclusions) ? $lead->gl_exclusions : "N/A" }}</span>
						        </div>
						        <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
						            <strong>Other Exclusions:</strong> <span class="small" id="gl_other_exclusions_preview">{{ !empty($lead->gl_other_exclusions) ? $lead->gl_other_exclusions : "N/A" }}</span>
						        </div>
						    </div>
						    <div class="form-row">
						    	<div class="form-group col-12 col-md-12 col-lg-12 mb-2 px-2">
						        	<strong>General Liability Notes:</strong> <span class="small longtextarea" id="gl_insurance_coverage_preview"></span>
						        </div>
						    </div>
						</div>
						<!-- Crime Insurance -->
						<div class="p-2 mt-4 pt-3 pb-0 mx-0 rounded border position-relative">
						    <div class="section-head position-absolute m-0 p-0 z-5 d-inline-block font-weight-bold px-1 bg-white">Crime Insurance:</div>
						    <div class="form-row">
						        <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
						            <strong>Carrier:</strong> <span class="small" id="crime_insurance_preview">{{ !empty($lead->crime_insurance) ? $lead->crime_insurance : "N/A" }}</span>
						        </div>
						        <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
						            <strong>Month:</strong> <span class="small" id="CI_ren_month_preview">{{ !empty($lead->CI_ren_month) ? $lead->CI_ren_month : "N/A" }}</span>
						        </div>
						        <div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
						            <strong>Expiring Premium:</strong> <span class="small" id="ci_expiry_premium_preview">{{ !empty($lead->ci_expiry_premium) ? '$' . formatUSNumber($lead->ci_expiry_premium, 2) : "N/A" }}</span>
						        </div>
						    </div>
						    <div class="form-row">
						        <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
						            <strong>Policy Renewal Date:</strong> <span class="small" id="ci_policy_renewal_date_preview">{{ !empty($lead->ci_policy_renewal_date) ? $lead->ci_policy_renewal_date : "N/A" }}</span>
						        </div>
						        <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
						            <strong>Rating:</strong> <span class="small" id="ci_rating_preview">{{ !empty($lead->ci_rating) ? $lead->ci_rating : "N/A" }}</span>
						        </div>
						        <div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
						            <strong>Employee Theft:</strong> <span class="small" id="employee_theft_preview">{{ !empty($lead->employee_theft) ? $lead->employee_theft : "N/A" }}</span>
						        </div>
						    </div>
						    <div class="form-row">
						        <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
						            <strong>Operating Reserves:</strong> <span class="small" id="operating_reserves_preview">{{ !empty($lead->operating_reserves) ? $lead->operating_reserves : "N/A" }}</span>
						        </div>
						    </div>
						    <div class="form-row">
						        <div class="form-group col-12 col-md-12 col-lg-12 mb-1 px-2">
						        	<strong>Crime Insurance Notes:</strong> <span class="small longtextarea" id="ci_insurance_coverage_preview"></span>
						        </div>
						    </div>
						</div>
						<div class="print_gap"></div>
						<!-- Directors & Officers -->
						<div class="p-2 mt-4 pt-3 pb-0 mx-0 rounded border position-relative">
						    <div class="section-head position-absolute m-0 p-0 z-5 d-inline-block font-weight-bold px-1 bg-white">Directors & Officers :</div>
						    <div class="form-row">
						        <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
						            <strong>Carrier:</strong> <span class="small" id="directors_officers_preview">{{ !empty($lead->directors_officers) ? $lead->directors_officers : "N/A" }}</span>
						        </div>
						        <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
						            <strong>Month:</strong> <span class="small" id="DO_ren_month_preview">{{ !empty($lead->DO_ren_month) ? $lead->DO_ren_month : "N/A" }}</span>
						        </div>
						        <div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
						            <strong>Expiring Premium:</strong> <span class="small" id="do_expiry_premium_preview">{{ !empty($lead->do_expiry_premium) ? '$' . formatUSNumber($lead->do_expiry_premium, 2) : "N/A" }}</span>
						        </div>
						    </div>
						    <div class="form-row">
						        <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
						            <strong>Policy Renewal Date:</strong> <span class="small" id="do_policy_renewal_date_preview">{{ !empty($lead->do_policy_renewal_date) ? $lead->do_policy_renewal_date : "N/A" }}</span>
						        </div>
						        <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
						            <strong>Rating:</strong> <span class="small" id="do_rating_preview">{{ !empty($lead->do_rating) ? $lead->do_rating : "N/A" }}</span>
						        </div>
						        <div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
						            <strong>Claims Made:</strong> <span class="small" id="claims_made_preview">{{ !empty($lead->claims_made) ? $lead->claims_made : "No" }}</span>
						        </div>
						    </div>
						    <div class="form-row">
						        <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
						            <strong>Pending Litigation:</strong> <span class="small" id="pending_litigation_preview">{{ !empty($lead->pending_litigation) ? $lead->pending_litigation : "No" }}</span>
						        </div>
						        <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
						            <strong>Litigation Date:</strong> <span class="small" id="litigation_date_preview">{{ !empty($lead->litigation_date) ? $lead->litigation_date : "N/A" }}</span>
						        </div>
						    </div>
						    <div class="form-row">
						        <div class="form-group col-12 col-md-12 col-lg-12 mb-1 px-2">
						        	<strong>Directors & Officers Notes:</strong> <span class="small longtextarea" id="do_insurance_coverage_preview"></span>
						        </div>
						    </div>
						</div>
						<!-- Umbrella -->
						<div class="p-2 mt-4 mx-0 pb-0 pt-3 rounded border position-relative">
						    <div class="section-head position-absolute m-0 p-0 z-5 d-inline-block font-weight-bold px-1 bg-white">Umbrella :</div>
						    <div class="form-row">
						        <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
						            <strong>Carrier:</strong> <span class="small" id="umbrella_preview">{{ !empty($lead->umbrella) ? $lead->umbrella : "N/A" }}</span>
						        </div>
						        <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
						            <strong>Month:</strong> <span class="small" id="U_ren_month_preview">{{ !empty($lead->U_ren_month) ? $lead->U_ren_month : "N/A" }}</span>
						        </div>
						        <div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
						            <strong>Expiring Premium:</strong> <span class="small" id="umbrella_expiry_premium_preview">{{ !empty($lead->umbrella_expiry_premium) ? '$' . formatUSNumber($lead->umbrella_expiry_premium, 2) : "N/A" }}</span>
						        </div>
						    </div>
						    <div class="form-row">
						        <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
						            <strong>Policy Renewal Date:</strong> <span class="small" id="umbrella_policy_renewal_date_preview">{{ !empty($lead->umbrella_policy_renewal_date) ? $lead->umbrella_policy_renewal_date : "N/A" }}</span>
						        </div>
						        <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
						            <strong>Exclusions:</strong> <span class="small" id="umbrella_exclusions_preview">{{ !empty($lead->umbrella_exclusions) ? $lead->umbrella_exclusions : "N/A" }}</span>
						        </div>
						        <div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
						            <strong>Other Exclusions:</strong> <span class="small" id="umbrella_other_exclusions_preview">{{ !empty($lead->umbrella_other_exclusions) ? $lead->umbrella_other_exclusions : "N/A" }}</span>
						        </div>
						    </div>
						    <div class="form-row">
						        <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
						        	<strong>Rating:</strong> <span class="small" id="umbrella_rating_preview">{{ !empty($lead->umbrella_rating) ? $lead->umbrella_rating : "N/A" }}</span>
						        </div>
						        <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
						        	<strong>Correct Underlying:</strong> <span class="small" id="correct_underlying_preview">{{ !empty($lead->correct_underlying) ? $lead->correct_underlying : "N/A" }}</span>
						        </div>
						    </div>
						    <div class="form-row">
						        <div class="form-group col-12 col-md-12 col-lg-12 mb-1 px-2">
						        	<strong>Umbrella Notes:</strong> <span class="small longtextarea" id="u_insurance_coverage_preview"></span>
						        </div>
						    </div>
						</div>
						<!-- Workers Compensation  -->
						<div class="p-2 mt-4 pt-3 pb-0 mx-0 rounded border position-relative">
						    <div class="section-head position-absolute m-0 p-0 z-5 d-inline-block font-weight-bold px-1 bg-white">Workers Compensation :</div>
						    <div class="form-row">
						        <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
						            <strong>Carrier:</strong> <span class="small" id="workers_compensation_preview">{{ !empty($lead->workers_compensation) ? $lead->workers_compensation : "N/A" }}</span>
						        </div>
						        <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
						            <strong>Month:</strong> <span class="small" id="WC_ren_month_preview">{{ !empty($lead->WC_ren_month) ? $lead->WC_ren_month : "NA" }}</span>
						        </div>
						        <div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
						            <strong>Expiring Premium:</strong> <span class="small" id="wc_expiry_premium_preview">{{ !empty($lead->wc_expiry_premium) ? '$' . formatUSNumber($lead->wc_expiry_premium, 2) : "N/A" }}</span>
						        </div>
						    </div>
						    <div class="form-row">
						        <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
						            <strong>Policy Renewal Date:</strong> <span class="small" id="wc_policy_renewal_date_preview">{{ !empty($lead->wc_policy_renewal_date) ? $lead->wc_policy_renewal_date : "N/A" }}</span>
						        </div>
						        <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
						            <strong>Rating:</strong> <span class="small" id="wc_rating_preview">{{ !empty($lead->wc_rating) ? $lead->wc_rating : "N/A" }}</span>
						        </div>
						        <div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
						            <strong>Employee Count:</strong> <span class="small" id="employee_count_preview">{{ !empty($lead->employee_count) ? $lead->employee_count : "N/A" }}</span>
						        </div>
						    </div>
						    <div class="form-row">
						        <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
						            <strong>Employee Payroll:</strong> <span class="small" id="employee_payroll_preview">{{ !empty($lead->employee_payroll) ? '$' . formatUSNumber($lead->employee_payroll) : "N/A" }}</span>
						        </div>
						    </div>
						    <div class="form-row">
						        <div class="form-group col-12 col-md-12 col-lg-12 mb-1 px-2">
						        	<strong>Workers Compensation Notes:</strong> <span class="small longtextarea" id="wc_insurance_coverage_preview"></span>
						        </div>
						    </div>
						</div>
						<!-- Flood -->
						<div class="p-2 mt-4 pt-3 pb-0 mx-0 rounded border position-relative">
						    <div class="section-head position-absolute m-0 p-0 z-5 d-inline-block font-weight-bold px-1 bg-white">Flood :</div>
						    <div class="form-row">
						        <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
						            <strong>Carrier:</strong> <span class="small" id="flood_preview">{{ !empty($lead->flood) ? $lead->flood : "N/A" }}</span>
						        </div>
						        <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
						            <strong>Month:</strong> <span class="small" id="F_ren_month_preview">{{ !empty($lead->F_ren_month) ? $lead->F_ren_month : "N/A" }}</span>
						        </div>
						        <div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
						            <strong>Expiring Premium:</strong> <span class="small" id="flood_expiry_premium_preview">{{ !empty($lead->flood_expiry_premium) ? '$' . formatUSNumber($lead->flood_expiry_premium) : "N/A" }}</span>
						        </div>
						    </div>
						    <div class="form-row">
						        <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
						            <strong>Policy Renewal Date:</strong> <span class="small" id="flood_policy_renewal_date_preview">{{ !empty($lead->flood_policy_renewal_date) ? $lead->flood_policy_renewal_date : "N/A" }}</span>
						        </div>
						        <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
						            <strong>Rating:</strong> <span class="small" id="flood_rating_preview">{{ !empty($lead->flood_rating) ? $lead->flood_rating : "N/A" }}</span>
						        </div>
						        <div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
						            <strong>Elevation Certificate:</strong> <span class="small" id="elevation_certificate_preview">{{ !empty($lead->elevation_certificate) ? $lead->elevation_certificate : "No" }}</span>
						        </div>
						    </div>
						    <div class="form-row">
						        <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
						            <strong>Loma Letter:</strong> <span class="small" id="loma_letter_preview">{{ !empty($lead->loma_letter) ? $lead->loma_letter : "No" }}</span>
						        </div>
						    </div>
						    <div class="form-row">
						        <div class="form-group col-12 col-md-12 col-lg-12 mb-1 px-2">
						        	<strong>Flood Notes:</strong> <span class="small longtextarea" id="f_insurance_coverage_preview"></span>
						        </div>
						    </div>
						</div>
						<!--  Difference In Conditions -->
						<div class="p-2 mt-4 pt-3 pb-0 mx-0 rounded border position-relative">
						    <div class="section-head position-absolute m-0 p-0 z-5 d-inline-block font-weight-bold px-1 bg-white">Difference In Conditions :</div>
						    <div class="form-row">
						        <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
						            <strong>Carrier:</strong> <span class="small" id="difference_in_condition_preview">{{ !empty($lead->difference_in_condition) ? $lead->difference_in_condition : "N/A" }}</span>
						        </div>
						        <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
						            <strong>Month:</strong> <span class="small" id="dic_ren_month_preview">{{ !empty($lead->dic_ren_month) ? $lead->dic_ren_month : "N/A" }}</span>
						        </div>
						        <div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
						            <strong>Expiring Premium:</strong> <span class="small" id="dic_expiry_premium_preview">{{ !empty($lead->dic_expiry_premium) ? '$' . formatUSNumber($lead->dic_expiry_premium) : "N/A" }}</span>
						        </div>
						    </div>
						    <div class="form-row">
						        <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
						            <strong>Policy Renewal Date:</strong> <span class="small" id="dic_policy_renewal_date_preview">{{ !empty($lead->dic_policy_renewal_date) ? $lead->dic_policy_renewal_date : "N/A" }}</span>
						        </div>
						        <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
						            <strong>Hurricane Deductible:</strong> <span class="small" id="dic_hurricane_deductible_preview">{{ !empty($lead->dic_hurricane_deductible) ? $lead->dic_hurricane_deductible : "N/A" }}</span>
						        </div>
						        <div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
						            <strong>All Other Perils Deductible:</strong> <span class="small" id="dic_all_other_perils_preview">{{ !empty($lead->dic_all_other_perils) ? $lead->dic_all_other_perils : "N/A" }}</span>
						        </div>
						    </div>
						    <div class="form-row">
						        <div class="form-group col-12 col-md-12 col-lg-12 mb-1 px-2">
						        	<strong>Difference In Conditions Notes:</strong> <span class="small longtextarea" id="dic_insurance_coverage_preview">{{ !empty($lead->dic_insurance_coverage) ? $lead->dic_insurance_coverage : "N/A" }}</span>
						        </div>
						    </div>
						</div>
						<!--  X-Wind -->
						<div class="p-2 mt-4 pt-3 pb-0 mx-0 rounded border position-relative">
						    <div class="section-head position-absolute m-0 p-0 z-5 d-inline-block font-weight-bold px-1 bg-white">X-Wind :</div>
						    <div class="form-row">
						        <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
						            <strong>Carrier:</strong> <span class="small" id="x_wind_preview">{{ !empty($lead->x_wind) ? $lead->x_wind : "N/A" }}</span>
						        </div>
						        <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
						            <strong>Month:</strong> <span class="small" id="xw_ren_month_preview">{{ !empty($lead->xw_ren_month) ? $lead->xw_ren_month : "N/A" }}</span>
						        </div>
						        <div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
						            <strong>Expiring Premium:</strong> <span class="small" id="xw_expiry_premium_preview">{{ !empty($lead->xw_expiry_premium) ? '$' . formatUSNumber($lead->xw_expiry_premium) : "N/A" }}</span>
						        </div>
						    </div>
						    <div class="form-row">
						        <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
						            <strong>Policy Renewal Date:</strong> <span class="small" id="xw_policy_renewal_date_preview">{{ !empty($lead->xw_policy_renewal_date) ? $lead->xw_policy_renewal_date : "N/A" }}</span>
						        </div>
						        <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
						            <strong>Hurricane Deductible:</strong> <span class="small" id="xw_hurricane_deductible_preview">{{ !empty($lead->xw_hurricane_deductible) ? $lead->xw_hurricane_deductible : "N/A" }}</span>
						        </div>
						        <div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
						            <strong>All Other Perils Deductible:</strong> <span class="small" id="xw_all_other_perils_preview">{{ !empty($lead->xw_all_other_perils) ? $lead->xw_all_other_perils : "N/A" }}</span>
						        </div>
						    </div>
						    <div class="form-row">
						        <div class="form-group col-12 col-md-12 col-lg-12 mb-1 px-2">
						        	<strong>X-Wind Notes:</strong> <span class="small longtextarea" id="xw_insurance_coverage_preview">{{ !empty($lead->xw_insurance_coverage) ? $lead->xw_insurance_coverage : "N/A" }}</span>
						        </div>
						    </div>
						</div>
						<!--  Equipment Breakdown -->
						<div class="p-2 mt-4 pt-3 pb-0 mx-0 rounded border position-relative">
						    <div class="section-head position-absolute m-0 p-0 z-5 d-inline-block font-weight-bold px-1 bg-white">Equipment Breakdown :</div>
						    <div class="form-row">
						        <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
						            <strong>Carrier:</strong> <span class="small" id="equipment_breakdown_preview">{{ !empty($lead->equipment_breakdown) ? $lead->equipment_breakdown : "N/A" }}</span>
						        </div>
						        <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
						            <strong>Month:</strong> <span class="small" id="eb_ren_month_preview">{{ !empty($lead->eb_ren_month) ? $lead->eb_ren_month : "N/A" }}</span>
						        </div>
						        <div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
						            <strong>Expiring Premium:</strong> <span class="small" id="eb_expiry_premium_preview">{{ !empty($lead->eb_expiry_premium) ? '$' . formatUSNumber($lead->flood_expiry_premium) : "N/A" }}</span>
						        </div>
						    </div>
						    <div class="form-row">
						        <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
						            <strong>Policy Renewal Date:</strong> <span class="small" id="eb_policy_renewal_date_preview">{{ !empty($lead->eb_policy_renewal_date) ? $lead->eb_policy_renewal_date : "N/A" }}</span>
						        </div>
						        <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
						            <strong>Hurricane Deductible:</strong> <span class="small" id="eb_hurricane_deductible_preview">{{ !empty($lead->eb_hurricane_deductible) ? $lead->eb_hurricane_deductible : "N/A" }}</span>
						        </div>
						        <div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
						            <strong>All Other Perils Deductible:</strong> <span class="small" id="eb_all_other_perils_preview">{{ !empty($lead->eb_all_other_perils) ? $lead->eb_all_other_perils : "N/A" }}</span>
						        </div>
						    </div>
						    <div class="form-row">
						        <div class="form-group col-12 col-md-12 col-lg-12 mb-1 px-2">
						        	<strong>Equipment Breakdown Notes:</strong> <span class="small longtextarea" id="eb_insurance_coverage_preview">{{ !empty($lead->eb_insurance_coverage) ? $lead->eb_insurance_coverage : "N/A" }}</span>
						        </div>
						    </div>
						</div>
						<!--  Commercial AutoMobile -->
						<div class="p-2 mt-4 pt-3 pb-0 mx-0 rounded border position-relative">
						    <div class="section-head position-absolute m-0 p-0 z-5 d-inline-block font-weight-bold px-1 bg-white">Commercial AutoMobile :</div>
						    <div class="form-row">
						        <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
						            <strong>Carrier:</strong> <span class="small" id="commercial_automobiles_preview">{{ !empty($lead->commercial_automobiles) ? $lead->commercial_automobiles : "N/A" }}</span>
						        </div>
						        <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
						            <strong>Month:</strong> <span class="small" id="ca_ren_month_preview">{{ !empty($lead->ca_ren_month) ? $lead->ca_ren_month : "N/A" }}</span>
						        </div>
						        <div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
						            <strong>Expiring Premium:</strong> <span class="small" id="ca_expiry_premium_preview">{{ !empty($lead->ca_expiry_premium) ? '$' . formatUSNumber($lead->ca_expiry_premium) : "N/A" }}</span>
						        </div>
						    </div>
						    <div class="form-row">
						        <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
						            <strong>Policy Renewal Date:</strong> <span class="small" id="ca_policy_renewal_date_preview">{{ !empty($lead->ca_policy_renewal_date) ? $lead->ca_policy_renewal_date : "N/A" }}</span>
						        </div>
						        <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
						            <strong>Hurricane Deductible:</strong> <span class="small" id="ca_hurricane_deductible_preview">{{ !empty($lead->ca_hurricane_deductible) ? $lead->ca_hurricane_deductible : "N/A" }}</span>
						        </div>
						        <div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
						            <strong>All Other Perils Deductible:</strong> <span class="small" id="ca_all_other_perils_preview">{{ !empty($lead->ca_all_other_perils) ? $lead->ca_all_other_perils : "N/A" }}</span>
						        </div>
						    </div>
						    <div class="form-row">
						        <div class="form-group col-12 col-md-12 col-lg-12 mb-1 px-2">
						        	<strong>Commercial AutoMobile Notes:</strong> <span class="small longtextarea" id="ca_insurance_coverage_preview">{{ !empty($lead->ca_insurance_coverage) ? $lead->ca_insurance_coverage : "N/A" }}</span>
						        </div>
						    </div>
						</div>
						<!--  Marina -->
						<div class="p-2 mt-4 pt-3 pb-0 mx-0 rounded border position-relative">
						    <div class="section-head position-absolute m-0 p-0 z-5 d-inline-block font-weight-bold px-1 bg-white">Marina :</div>
						    <div class="form-row">
						        <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
						            <strong>Carrier:</strong> <span class="small" id="marina_preview">{{ !empty($lead->marina) ? $lead->marina : "N/A" }}</span>
						        </div>
						        <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
						            <strong>Month:</strong> <span class="small" id="m_ren_month_preview">{{ !empty($lead->m_ren_month) ? $lead->m_ren_month : "N/A" }}</span>
						        </div>
						        <div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
						            <strong>Expiring Premium:</strong> <span class="small" id="m_expiry_premium_preview">{{ !empty($lead->m_expiry_premium) ? '$' . formatUSNumber($lead->m_expiry_premium) : "N/A" }}</span>
						        </div>
						    </div>
						    <div class="form-row">
						        <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
						            <strong>Policy Renewal Date:</strong> <span class="small" id="m_policy_renewal_date_preview">{{ !empty($lead->m_policy_renewal_date) ? $lead->m_policy_renewal_date : "N/A" }}</span>
						        </div>
						        <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
						            <strong>Hurricane Deductible:</strong> <span class="small" id="m_hurricane_deductible_preview">{{ !empty($lead->m_hurricane_deductible) ? $lead->m_hurricane_deductible : "N/A" }}</span>
						        </div>
						        <div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
						            <strong>All Other Perils Deductible:</strong> <span class="small" id="m_all_other_perils_preview">{{ !empty($lead->m_all_other_perils) ? $lead->m_all_other_perils : "N/A" }}</span>
						        </div>
						    </div>
						    <div class="form-row">
						        <div class="form-group col-12 col-md-12 col-lg-12 mb-1 px-2">
						        	<strong>Marina Notes:</strong> <span class="small longtextarea" id="m_insurance_coverage_preview">{{ !empty($lead->m_insurance_coverage) ? $lead->m_insurance_coverage : "N/A" }}</span>
						        </div>
						    </div>
						</div>
			        </div>
			        
				    <div class="form-row text-md mt-2">
				        <div class="form-group col-12 mb-1 px-2">
				           	<strong  class="text-success m-0">Total Premium: </strong> <span class="text-base" id="total_premium_sum_preview"></span>
				        </div>
				    </div>

				    @if(count($notes) > 0)
					    <div class="p-2 mt-4 pt-3 pb-2 mx-0 rounded border position-relative">
						    <div class="section-head position-absolute m-0 p-0 z-5 d-inline-block font-weight-bold px-1 bg-white">
						        Notes :
						    </div>

						    <div class="form-row">
						        <div class="form-group col-12 mb-1 px-2">
						            <div class="notearea">
						                @foreach ($notes as $note)
						                    <div class="small longtextarea notes mb-1">
						                        @if($note->contact_name)
						                            <strong>{{ $note->contact_name }} - </strong>
						                        @endif

						                        @if($note->created_at)
						                            <span>{{ date('m/d/Y - H:i', strtotime($note->created_at)) }} - </span>
						                        @endif

						                        <strong >{!! $note->description !!}</strong>
						                    </div>
						                @endforeach
						            </div>
						        </div>
						    </div>
						</div>

				    @endif
			    </div>

			    <!-- footer section -->
		        <!-- <div class="modal-footer p-3 bg-light">
		            <button type="button" class="btn btn-sm btn-info mx-2 closeNote" data-bs-dismiss="modal">Close</button>
		        </div> -->
			</div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script src="{{ asset('js/leads/custom-print.js') }}" defer></script>
<script src="{{ asset('js/leads/custom-preview.js') }}" defer></script>
@endpush