<div class="loaderarea">
	<div class="tab-area d-flex align-items-center mb-3 border-bottom">
		@foreach($previous_lead_date_list as $key => $lead_date)
			<span class="sm text-info fs-5 px-3 py-2 border-right cursor-pointer font-weight-bold renewal_date_btn @if($key == 0) {{'bg-primary text-white'}} @endif">{{date("m/d/Y",strtotime($lead_date->renewal_date))}}</span>
		@endforeach
	</div>
	<div class="px-3">
		<!-- Business Section -->
		<div class="section mb-3">
			<h5 class="section-title text-primary h6 mb-1 pb-1 border-bottom border-primary" style="color: #1f78a0; border-color: #1f78a0;">Bussiness</h5>
			<div class="card-body lead-update p-0 pt-1">
				@if(isset($previous_lead))
					<!-- Current Client Status -->
					<div class="d-flex">
						<p class="font-weight-bold mb-2" id="name_previousvalue"> {{ !empty($previous_lead->name) ? $previous_lead->name : "" }} </p>
						@if($previous_lead && $previous_lead->is_client == 1)
							<div class="form-group mb-2 ml-2" id="current_client_area_previousvalue">
								<p class="font-weight-bold text-success mb-0">Current Client</p>
							</div>
						@endif
					</div>
				@endif

				<!-- Type, Year Built, and Unit Count -->
				<div class="form-row m-0 mb-2">
					<div class="form-group col-12 col-md-4 mb-0 py-1 px-0 border-top border-bottom">
						<strong>Type:</strong> <span class="small" id="type_previousvalue">{{ !empty($previous_lead->type) ? $previous_lead->type : "N/A" }}</span>
					</div>
					<div class="form-group col-12 col-md-3 mb-0 py-1 px-0 border-top border-bottom">
						<strong>Year Built:</strong> <span class="small" id="creation_date_previousvalue">{{ !empty($previous_lead->creation_date) ? date('m/d/Y',strtotime($previous_lead->creation_date)) : "N/A" }}</span>
					</div>
					<div class="form-group col-12 col-lg-5 mb-0 py-1 px-0 border-top border-bottom">
						<strong>Unit Count:</strong> <span class="small" id="unit_count_previousvalue">{{ !empty($previous_lead->unit_count) ? $previous_lead->unit_count : "N/A" }}</span>
					</div>
				</div>

				<!-- Address -->
				<div class="form-row">
					<div class="form-group col-12 px-2 mb-1">
						<strong>Business Address 1:</strong> <span class="small" id="address1_previousvalue">{{ !empty($previous_lead->address1) ? $previous_lead->address1 : "N/A" }}</span>
					</div>
					<div class="form-group col-12 px-2 mb-1">
						<strong>Business Address 2:</strong> <span class="small" id="address2_previousvalue">{{ !empty($previous_lead->address2) ? $previous_lead->address2 : "N/A" }}</span>
					</div>
				</div>

				<!-- Location Details -->
				<div class="form-row">
					<div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
						<strong>City:</strong> <span class="small" id="city_previousvalue">{{ !empty($previous_lead->city) ? $previous_lead->city : "N/A" }}</span>
					</div>
					<div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
						<strong>County:</strong> <span class="small" id="county_previousvalue">{{ !empty($previous_lead->county) ? $previous_lead->county : "N/A" }}</span>
					</div>
					<div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
						<strong>Coastal / Non Coastal:</strong> <span class="small" id="coastal_previousvalue">{{ !empty($previous_lead->coastal) ? 'Coastal' : 'Non Coastal' }}</span>
					</div>
				</div>
				<div class="form-row">
					<div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
						<strong>State:</strong> <span class="small" id="state_previousvalue">{{ !empty($previous_lead->state) ? $previous_lead->state : "N/A" }}</span>
					</div>
					<div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
						<strong>Zip:</strong> <span class="small" id="zip_previousvalue">{{ !empty($previous_lead->zip) ? $previous_lead->zip : "N/A" }}</span>
					</div>
					<div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
						<strong>Total Square Footage:</strong> <span class="small" id="total_square_footage_previousvalue">{{ !empty($previous_lead->total_square_footage) ? $previous_lead->total_square_footage : "N/A" }}</span>
					</div>
				</div>

				<div class="form-row">
					<div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
							<strong>Total insured value:</strong> <span class="small" id="business_tiv_previousvalue">{{ !empty($previous_lead->business_tiv) ? '$'.$previous_lead->business_tiv : "N/A" }}</span>
					</div>
					<div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
						<strong>Appraiser Name:</strong> <span class="small" id="appraisal_name_previousvalue">{{ !empty($previous_lead->appraisal_name) ? $previous_lead->appraisal_name : "N/A" }}</span>
					</div>
					<div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
						<strong>Appraisal Company:</strong>  <span class="small" id="appraisal_company_previousvalue">{{!empty($previous_lead->appraisal_company)?$previous_lead->appraisal_company:"N/A"}}</span>
					</div>
				</div>
				<div class="form-row">
					<div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
						<strong>Appraisal Date:</strong>  <span class="small" id="appraisal_date_previousvalue">{{!empty($previous_lead->appraisal_date)?date('m/d/Y',strtotime($previous_lead->appraisal_date)):"N/A"}}</span>
					</div>
					<div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
						<strong>Flood Zone:</strong> <span class="small" id="ins_flood_previousvalue">{{ !empty($previous_lead->ins_flood) ? $previous_lead->ins_flood : "No" }}</span>
					</div>
					<div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
						<strong>Property Floors:</strong> <span class="small" id="prop_floor_previousvalue">{{ !empty($previous_lead->prop_floor) ? $previous_lead->prop_floor : "N/A" }}</span>
					</div>
				</div>
				<div class="form-row">
					<div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
						<strong>Pool:</strong> <span class="small" id="pool_previousvalue">{{ !empty($previous_lead->pool) ? $previous_lead->pool : "No" }}</span>
					</div>
					<div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
						<strong>Lakes:</strong> <span class="small" id="lakes_previousvalue">{{ !empty($previous_lead->lakes) ? $previous_lead->lakes : "No" }}</span>
					</div>
					<div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
						<strong>Clubhouse:</strong> <span class="small" id="clubhouse_previousvalue">{{ !empty($previous_lead->clubhouse) ? $previous_lead->clubhouse : "No" }}</span>
					</div>
				</div>

				<!-- Property Details + roof -->
				<div class="form-row">
					<div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
						<strong>Tennis/Basketball Court:</strong> <span class="small" id="tennis_basketball_previousvalue">{{ !empty($previous_lead->tennis_basketball) ? $previous_lead->tennis_basketball : "No" }}</span>
					</div>
					<div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
						<strong>ISO:</strong> <span class="small" id="iso_previousvalue">{{ !empty($previous_lead->iso) ? $previous_lead->iso : "N/A" }}</span>
					</div>
					<div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
						<strong>Lead Source:</strong> <span class="small" id="lead_source_previousvalue">{{ !empty($previous_lead->leadSource->name) ? $previous_lead->leadSource->name : "N/A" }}</span>
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
						<strong> Wind Mitigation Date:</strong>  <span class="small" id="wind_mitigation_date_previousvalue">{{!empty($previous_lead->wind_mitigation_date)?$previous_lead->wind_mitigation_date:"N/A"}}</span>
					</div>
					<div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
						<strong>Roof Year:</strong> <span class="small" id="roof_year_previousvalue">{{ !empty($previous_lead->roof_year) ? $previous_lead->roof_year : "N/A" }}</span>
					</div>
					<div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
						<strong>Roof Covering:</strong> <span class="small" id="roof_covering_previousvalue">{{ !empty($previous_lead->roof_covering) ? $previous_lead->roof_covering : "N/A" }}</span>
					</div>
				</div>
				<div class="form-row">
					<div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
						<strong>Roof Connection:</strong> <span class="small" id="roof_connection_previousvalue">{{ !empty($previous_lead->roof_connection) ? $previous_lead->roof_connection : "N/A" }}</span>
					</div>
					<div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
						<strong>Roof Geometry:</strong> <span class="small" id="roof_geom_previousvalue">{{ !empty($previous_lead->roof_geom) ? $previous_lead->roof_geom : "N/A" }}</span>
					</div>
					<div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
						<strong> SWR:</strong>  <span class="small" id="secondary_water_insurance_previousvalue">{{!empty($previous_lead->secondary_water_insurance)?$previous_lead->secondary_water_insurance:"No"}}</span>
					</div>
				</div>
				<div class="form-row">
					<div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
						<strong>Opening Protection:</strong>  <span class="small" id="opening_protection_previousvalue">{{!empty($previous_lead->opening_protection)?$previous_lead->opening_protection:"No"}}</span>
					</div>
				</div>
				<div class="form-row">
					<div class="form-group col-12 col-md-12 col-lg-12 mb-1 px-2">
						<strong>Report Notes:</strong> <span class="small longtextarea" id="other_community_info_previousvalue">{{ !empty($previous_lead->other_community_info) ? $previous_lead->other_community_info : "N/A" }}</span>
					</div>
				</div>
			</div>
		</div>

		<!-- Prospect's Insurance Section -->
		<div class="section mb-3">
			<h5 class="section-title text-primary h6 mb-1 pb-1 border-bottom border-primary" style="color: #1f78a0; border-color: #1f78a0;">Prospect’s Insurance</h5>
			<div class="card-body card-body-appended lead-update p-0 pt-2">
				<div class="p-2 mt-4 pt-3 pb-0 mx-0 rounded border position-relative general_liability">
				<div class="section-head position-absolute m-0 p-0 z-5 d-inline-block font-weight-bold px-1 bg-white">Property :</div>
					<div class="form-row">
						<div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
							<strong>Carrier: </strong>  <span class="small" id="ins_prop_carrier_previousvalue">{{!empty($previous_lead->ins_prop_carrier)?$previous_lead->ins_prop_carrier:"N/A"}}</span>
						</div>
						<div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
							<strong>Month: </strong>  <span class="small" id="renewal_carrier_month_previousvalue">{{!empty($previous_lead->renewal_carrier_month)?$previous_lead->renewal_carrier_month:"N/A"}}</span>
						</div>
						<div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
							<strong>Expiring Premium:</strong> <span class="small" id="premium_previousvalue">{{ !empty($previous_lead->premium) ?'$'. $previous_lead->premium : "N/A" }}</span>
						</div>
					</div>
					<div class="form-row">
						<div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
							<strong>Expiring Premium Year:</strong> <span class="small" id="premium_year_previousvalue">{{ !empty($previous_lead->premium_year) ? $previous_lead->premium_year : "N/A" }}</span>
						</div>
						<div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
							<strong>Total insured value:</strong> <span class="small" id="insured_amount_previousvalue">{{ !empty($previous_lead->insured_amount) ? '$'.$previous_lead->insured_amount : "N/A" }}</span>
						</div>
						<div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
							<strong>T.I.V. – Year:</strong> <span class="small" id="insured_year_previousvalue">{{ !empty($previous_lead->insured_year) ? '$'.$previous_lead->insured_year : "N/A" }}</span>
						</div>
					</div>
					<div class="form-row">
						<?php
							$price_per_sqft = 0;
							if(!empty($previous_lead->insured_amount) && !empty($previous_lead->total_square_footage)){
								$price_per_sqft = round(($previous_lead->insured_amount / $previous_lead->total_square_footage), 2);
							}
						?>
						<div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
							<strong>Price Per SqFt:</strong> <span class="small" id="price_per_sqft_previousvalue">{{!empty($price_per_sqft)?$price_per_sqft:"N/A"}}</span>
						</div>
						<div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
							<strong>Policy Renew:</strong>  <span class="small" id="policy_renewal_date_previousvalue">{{!empty($previous_lead->policy_renewal_date)?date('m/d/Y',strtotime($previous_lead->policy_renewal_date)):"N/A"}}</span>
						</div>
						<div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
							<strong>Incumbent Agency:</strong>  <span class="small" id="incumbent_agency_previousvalue">{{!empty($previous_lead->incumbent_agency)?$previous_lead->incumbent_agency:"N/A"}}</span>
						</div>
					</div>
					<div class="form-row">
						<div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
							<strong>Incumbent Agent:</strong>  <span class="small" id="incumbent_agent_previousvalue">{{!empty($previous_lead->incumbent_agent)?$previous_lead->incumbent_agent:"N/A"}}</span>
						</div>
						<div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
							<strong>Rating:</strong>  <span class="small" id="rating_previousvalue">{{!empty($previous_lead->rating)?$previous_lead->rating:"N/A"}}</span>
						</div>
						<div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
							<strong> Sinkhole:</strong>  <span class="small" id="skin_hole_previousvalue">{{!empty($previous_lead->skin_hole)?$previous_lead->skin_hole:"No"}}</span>
						</div>
					</div>
					<div class="form-row">
						<div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
							<strong> Hurricane Deductible:</strong>  <span class="small" id="hurricane_deductible_previousvalue">{{!empty($previous_lead->hurricane_deductible)?$previous_lead->hurricane_deductible."%":"N/A"}}</span>
						</div>
						<div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
							<strong>Hurricane Deductible (Per Occ/Year):</strong>  <span class="small" id="hurricane_deductible_occurrence_previousvalue">{{!empty($previous_lead->hurricane_deductible_occurrence)?$previous_lead->hurricane_deductible_occurrence:"N/A"}}</span>
						</div>
						<div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
							<strong>All other Perils:</strong>  <span class="small" id="all_other_perils_previousvalue">{{!empty($previous_lead->all_other_perils)?'$'.$previous_lead->all_other_perils:"N/A"}}</span>
						</div>
					</div>
					<div class="form-row">
						<div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
							<strong>Ordinance of Law:</strong>  <span class="small" id="ordinance_of_law_previousvalue">{{!empty($previous_lead->ordinance_of_law)?$previous_lead->ordinance_of_law."%":"N/A"}}</span>
						</div>
						<div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
							<strong>T.I.V. Matches Appraisal:</strong>  <span class="small" id="tiv_matches_appraisal_previousvalue">{{!empty($previous_lead->tiv_matches_appraisal)?$previous_lead->tiv_matches_appraisal:"No"}}</span>
						</div>
					</div>
					<div class="form-row">
						<div class="form-group col-12 col-md-12 col-lg-12 mb-1 px-2">
							<strong>Property Notes:</strong> <span class="small longtextarea" id="property_insurance_coverage_previousvalue">{{ !empty($previous_lead->property_insurance_coverage) ? $previous_lead->property_insurance_coverage : "N/A" }}</span>
						</div>
					</div>
				</div>
				<!-- General Liability  -->
				<div class="p-2 mt-4 pt-3 pb-0 mx-0 rounded border position-relative general_liability">
					<div class="section-head position-absolute m-0 p-0 z-5 d-inline-block font-weight-bold px-1 bg-white">General Liability :</div>
					<div class="form-row">
						<div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
							<strong>Carrier:</strong> <span class="small" id="general_liability_previousvalue">{{ !empty($previous_lead->general_liability) ? $previous_lead->general_liability : "N/A" }}</span>
						</div>
						<div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
							<strong>Month:</strong> <span class="small" id="GL_ren_month_previousvalue">{{ !empty($previous_lead->GL_ren_month) ? $previous_lead->GL_ren_month : "N/A" }}</span>
						</div>
						<div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
							<strong>Expiring Premium:</strong> <span class="small" id="gl_expiry_premium_previousvalue">{{ !empty($previous_lead->gl_expiry_premium) ? '$' . formatUSNumber($previous_lead->gl_expiry_premium, 2) : "N/A" }}</span>
						</div>
					</div>
					<div class="form-row">
						<div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
							<strong>Policy Renewal Date:</strong> <span class="small" id="gl_policy_renewal_date_previousvalue">{{ !empty($previous_lead->gl_policy_renewal_date) ? date('m/d/Y',strtotime($previous_lead->gl_policy_renewal_date)) : "N/A" }}</span>
						</div>
						<div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
							<strong>Rating:</strong> <span class="small" id="gl_rating_previousvalue">{{ !empty($previous_lead->gl_rating) ? $previous_lead->gl_rating : "N/A" }}</span>
						</div>
						<?php
							$gl_price_per_unit = 0;
							if(!empty($previous_lead->gl_expiry_premium) && !empty($previous_lead->unit_count)){
								$gl_price_per_unit = round(($previous_lead->gl_expiry_premium / $previous_lead->unit_count), 2);
							}
						?>
						<div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
							<strong>Price Per Unit:</strong> <span class="small" id="gl_price_per_unit_previousvalue">{{ !empty($gl_price_per_unit) ? $gl_price_per_unit : "N/A" }}</span>
						</div>
					</div>
					<div class="form-row">
						<div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
							<strong>Exclusions:</strong> <span class="small" id="gl_exclusions_previousvalue">{{ !empty($previous_lead->gl_exclusions) ? $previous_lead->gl_exclusions : "N/A" }}</span>
						</div>
						<div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
							<strong>Other Exclusions:</strong> <span class="small" id="gl_other_exclusions_previousvalue">{{ !empty($previous_lead->gl_other_exclusions) ? $previous_lead->gl_other_exclusions : "N/A" }}</span>
						</div>
					</div>
					<div class="form-row">
						<div class="form-group col-12 col-md-12 col-lg-12 mb-2 px-2">
							<strong>General Liability Notes:</strong> <span class="small longtextarea" id="gl_insurance_coverage_previousvalue">{{ !empty($previous_lead->gl_insurance_coverage) ? $previous_lead->gl_insurance_coverage : "N/A" }}</span>
						</div>
					</div>
				</div>
				<div class="gap"></div>
				<!-- Crime Insurance -->
				<div class="p-2 mt-4 pt-3 pb-0 mx-0 rounded border position-relative">
					<div class="section-head position-absolute m-0 p-0 z-5 d-inline-block font-weight-bold px-1 bg-white">Crime Insurance :</div>
					<div class="form-row">
						<div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
							<strong>Carrier:</strong> <span class="small" id="crime_insurance_previousvalue">{{ !empty($previous_lead->crime_insurance) ? $previous_lead->crime_insurance : "N/A" }}</span>
						</div>
						<div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
							<strong>Month:</strong> <span class="small" id="CI_ren_month_previousvalue">{{ !empty($previous_lead->CI_ren_month) ? $previous_lead->CI_ren_month : "N/A" }}</span>
						</div>
						<div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
							<strong>Expiring Premium:</strong> <span class="small" id="ci_expiry_premium_previousvalue">{{ !empty($previous_lead->ci_expiry_premium) ? '$' . formatUSNumber($previous_lead->ci_expiry_premium, 2) : "N/A" }}</span>
						</div>
					</div>
					<div class="form-row">
						<div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
							<strong>Policy Renewal Date:</strong> <span class="small" id="ci_policy_renewal_date_previousvalue">{{ !empty($previous_lead->ci_policy_renewal_date) ? date('m/d/Y',strtotime($previous_lead->ci_policy_renewal_date)) : "N/A" }}</span>
						</div>
						<div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
							<strong>Rating:</strong> <span class="small" id="ci_rating_previousvalue">{{ !empty($previous_lead->ci_rating) ? $previous_lead->ci_rating : "N/A" }}</span>
						</div>
						<div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
							<strong>Employee Theft:</strong> <span class="small" id="employee_theft_previousvalue">{{ !empty($previous_lead->employee_theft) ? $previous_lead->employee_theft : "N/A" }}</span>
						</div>
					</div>
					<div class="form-row">
						<div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
							<strong>Operating Reserves:</strong> <span class="small" id="operating_reserves_previousvalue">{{ !empty($previous_lead->operating_reserves) ? $previous_lead->operating_reserves : "N/A" }}</span>
						</div>
					</div>
					<div class="form-row">
						<div class="form-group col-12 col-md-12 col-lg-12 mb-1 px-2">
							<strong>Crime Insurance Notes:</strong> <span class="small longtextarea" id="ci_insurance_coverage_previousvalue">{{ !empty($previous_lead->ci_insurance_coverage) ? $previous_lead->ci_insurance_coverage : "N/A" }}</span>
						</div>
					</div>
				</div>
				<div class="print_gap"></div>
				<!-- Directors & Officers -->
				<div class="p-2 mt-4 pt-3 pb-0 mx-0 rounded border position-relative">
					<div class="section-head position-absolute m-0 p-0 z-5 d-inline-block font-weight-bold px-1 bg-white">Directors & Officers :</div>
					<div class="form-row">
						<div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
							<strong>Carrier:</strong> <span class="small" id="directors_officers_previousvalue">{{ !empty($previous_lead->directors_officers) ? $previous_lead->directors_officers : "N/A" }}</span>
						</div>
						<div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
							<strong>Month:</strong> <span class="small" id="DO_ren_month_previousvalue">{{ !empty($previous_lead->DO_ren_month) ? $previous_lead->DO_ren_month : "N/A" }}</span>
						</div>
						<div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
							<strong>Expiring Premium:</strong> <span class="small" id="do_expiry_premium_previousvalue">{{ !empty($previous_lead->do_expiry_premium) ? '$' . formatUSNumber($previous_lead->do_expiry_premium, 2) : "N/A" }}</span>
						</div>
					</div>
					<div class="form-row">
						<div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
							<strong>Policy Renewal Date:</strong> <span class="small" id="do_policy_renewal_date_previousvalue">{{ !empty($previous_lead->do_policy_renewal_date) ? date('m/d/Y',strtotime($previous_lead->do_policy_renewal_date)) : "N/A" }}</span>
						</div>
						<div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
							<strong>Rating:</strong> <span class="small" id="do_rating_previousvalue">{{ !empty($previous_lead->do_rating) ? $previous_lead->do_rating : "N/A" }}</span>
						</div>
						<div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
							<strong>Claims Made:</strong> <span class="small" id="claims_made_previousvalue">{{ !empty($previous_lead->claims_made) ? $previous_lead->claims_made : "No" }}</span>
						</div>
					</div>
					<div class="form-row">
						<div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
							<strong>Pending Litigation:</strong> <span class="small" id="pending_litigation_previousvalue">{{ !empty($previous_lead->pending_litigation) ? $previous_lead->pending_litigation : "No" }}</span>
						</div>
						<div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
							<strong>Litigation Date:</strong> <span class="small" id="litigation_date_previousvalue">{{ !empty($previous_lead->litigation_date) ? date('m/d/Y',strtotime($previous_lead->litigation_date)) : "N/A" }}</span>
						</div>
					</div>
					<div class="form-row">
						<div class="form-group col-12 col-md-12 col-lg-12 mb-1 px-2">
							<strong>Directors & Officers Notes:</strong> <span class="small longtextarea" id="do_insurance_coverage_previousvalue">{{ !empty($previous_lead->do_insurance_coverage) ? $previous_lead->do_insurance_coverage : "N/A" }}</span>
						</div>
					</div>
				</div>
				<!-- Umbrella -->
				<div class="p-2 mt-4 mx-0 pb-0 pt-3 rounded border position-relative">
					<div class="section-head position-absolute m-0 p-0 z-5 d-inline-block font-weight-bold px-1 bg-white">Umbrella :</div>
					<div class="form-row">
						<div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
							<strong>Carrier:</strong> <span class="small" id="umbrella_previousvalue">{{ !empty($previous_lead->umbrella) ? $previous_lead->umbrella : "N/A" }}</span>
						</div>
						<div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
							<strong>Month:</strong> <span class="small" id="U_ren_month_previousvalue">{{ !empty($previous_lead->U_ren_month) ? $previous_lead->U_ren_month : "N/A" }}</span>
						</div>
						<div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
							<strong>Expiring Premium:</strong> <span class="small" id="umbrella_expiry_premium_previousvalue">{{ !empty($previous_lead->umbrella_expiry_premium) ? '$' . formatUSNumber($previous_lead->umbrella_expiry_premium, 2) : "N/A" }}</span>
						</div>
					</div>
					<div class="form-row">
						<div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
							<strong>Policy Renewal Date:</strong> <span class="small" id="umbrella_policy_renewal_date_previousvalue">{{ !empty($previous_lead->umbrella_policy_renewal_date) ? date('m/d/Y',strtotime($previous_lead->umbrella_policy_renewal_date)) : "N/A" }}</span>
						</div>
						<div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
							<strong>Rating:</strong> <span class="small" id="umbrella_rating_previousvalue">{{ !empty($previous_lead->umbrella_rating) ? $previous_lead->umbrella_rating : "N/A" }}</span>
						</div>
						<div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
							<strong>Exclusions:</strong> <span class="small" id="umbrella_exclusions_previousvalue">{{ !empty($previous_lead->umbrella_exclusions) ? $previous_lead->umbrella_exclusions : "N/A" }}</span>
						</div>
					</div>
					<div class="form-row">
						<div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
							<strong>Other Exclusions:</strong> <span class="small" id="umbrella_other_exclusions_previousvalue">{{ !empty($previous_lead->umbrella_other_exclusions) ? $previous_lead->umbrella_other_exclusions : "N/A" }}</span>
						</div>
						<div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
							<strong>Correct Underlying:</strong> <span class="small" id="correct_underlying_previousvalue">{{ !empty($previous_lead->correct_underlying) ? $previous_lead->correct_underlying : "No" }}</span>
						</div>
					</div>
					<div class="form-row">
						<div class="form-group col-12 col-md-12 col-lg-12 mb-1 px-2">
							<strong>Umbrella Notes:</strong> <span class="small longtextarea" id="u_insurance_coverage_previousvalue">{{ !empty($previous_lead->u_insurance_coverage) ? $previous_lead->u_insurance_coverage : "N/A" }}</span>
						</div>
					</div>
				</div>
				<!-- Workers Compensation  -->
				<div class="p-2 mt-4 pt-3 pb-0 mx-0 rounded border position-relative">
					<div class="section-head position-absolute m-0 p-0 z-5 d-inline-block font-weight-bold px-1 bg-white">Workers Compensation :</div>
					<div class="form-row">
						<div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
							<strong>Carrier:</strong> <span class="small" id="workers_compensation_previousvalue">{{ !empty($previous_lead->workers_compensation) ? $previous_lead->workers_compensation : "N/A" }}</span>
						</div>
						<div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
							<strong>Month:</strong> <span class="small" id="WC_ren_month_previousvalue">{{ !empty($previous_lead->WC_ren_month) ? $previous_lead->WC_ren_month : "NA" }}</span>
						</div>
						<div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
							<strong>Expiring Premium:</strong> <span class="small" id="wc_expiry_premium_previousvalue">{{ !empty($previous_lead->wc_expiry_premium) ? '$' . formatUSNumber($previous_lead->wc_expiry_premium, 2) : "N/A" }}</span>
						</div>
					</div>
					<div class="form-row">
						<div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
							<strong>Policy Renewal Date:</strong> <span class="small" id="wc_policy_renewal_date_previousvalue">{{ !empty($previous_lead->wc_policy_renewal_date) ? date('m/d/Y',strtotime($previous_lead->wc_policy_renewal_date)) : "N/A" }}</span>
						</div>
						<div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
							<strong>Rating:</strong> <span class="small" id="wc_rating_previousvalue">{{ !empty($previous_lead->wc_rating) ? $previous_lead->wc_rating : "N/A" }}</span>
						</div>
						<div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
							<strong>Employee Count:</strong> <span class="small" id="employee_count_previousvalue">{{ !empty($previous_lead->employee_count) ? $previous_lead->employee_count : "N/A" }}</span>
						</div>
					</div>
					<div class="form-row">
						<div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
							<strong>Employee Payroll:</strong> <span class="small" id="employee_payroll_previousvalue">{{ !empty($previous_lead->employee_payroll) ? '$' . formatUSNumber($previous_lead->employee_payroll) : "N/A" }}</span>
						</div>
					</div>
					<div class="form-row">
						<div class="form-group col-12 col-md-12 col-lg-12 mb-1 px-2">
							<strong>Workers Compensation Notes:</strong> <span class="small longtextarea" id="wc_insurance_coverage_previousvalue">{{ !empty($previous_lead->wc_insurance_coverage) ? $previous_lead->wc_insurance_coverage : "N/A" }}</span>
						</div>
					</div>
				</div>
				<!-- Flood -->
				<div class="p-2 mt-4 pt-3 pb-0 mx-0 rounded border position-relative">
					<div class="section-head position-absolute m-0 p-0 z-5 d-inline-block font-weight-bold px-1 bg-white">Flood :</div>
					<div class="form-row">
						<div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
							<strong>Carrier:</strong> <span class="small" id="flood_previousvalue">{{ !empty($previous_lead->flood) ? $previous_lead->flood : "N/A" }}</span>
						</div>
						<div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
							<strong>Month:</strong> <span class="small" id="F_ren_month_previousvalue">{{ !empty($previous_lead->F_ren_month) ? $previous_lead->F_ren_month : "N/A" }}</span>
						</div>
						<div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
							<strong>Expiring Premium:</strong> <span class="small" id="flood_expiry_premium_previousvalue">{{ !empty($previous_lead->flood_expiry_premium) ? '$' . formatUSNumber($previous_lead->flood_expiry_premium) : "N/A" }}</span>
						</div>
					</div>
					<div class="form-row">
						<div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
							<strong>Policy Renewal Date:</strong> <span class="small" id="flood_policy_renewal_date_previousvalue">{{ !empty($previous_lead->flood_policy_renewal_date) ? date('m/d/Y',strtotime($previous_lead->flood_policy_renewal_date)) : "N/A" }}</span>
						</div>
						<div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
							<strong>Rating:</strong> <span class="small" id="flood_rating_previousvalue">{{ !empty($previous_lead->flood_rating) ? $previous_lead->flood_rating : "N/A" }}</span>
						</div>
						<div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
							<strong>Elevation Certificate:</strong> <span class="small" id="elevation_certificate_previousvalue">{{ !empty($previous_lead->elevation_certificate) ? $previous_lead->elevation_certificate : "No" }}</span>
						</div>
					</div>
					<div class="form-row">
						<div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
							<strong>Loma Letter:</strong> <span class="small" id="loma_letter_previousvalue">{{ !empty($previous_lead->loma_letter) ? $previous_lead->loma_letter : "No" }}</span>
						</div>
					</div>
					<div class="form-row">
						<div class="form-group col-12 col-md-12 col-lg-12 mb-1 px-2">
							<strong>Flood Notes:</strong> <span class="small longtextarea" id="f_insurance_coverage_previousvalue">{{ !empty($previous_lead->f_insurance_coverage) ? $previous_lead->f_insurance_coverage : "N/A" }}</span>
						</div>
					</div>
				</div>
				<!--  Difference In Conditions -->
				<div class="p-2 mt-4 pt-3 pb-0 mx-0 rounded border position-relative">
					<div class="section-head position-absolute m-0 p-0 z-5 d-inline-block font-weight-bold px-1 bg-white"> Difference In Conditions :</div>
					<div class="form-row">
						<div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
							<strong>Carrier:</strong> <span class="small" id="difference_in_condition_previousvalue">{{ !empty($previous_lead->difference_in_condition) ? $previous_lead->difference_in_condition : "N/A" }}</span>
						</div>
						<div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
							<strong>Month:</strong> <span class="small" id="dic_ren_month_previousvalue">{{ !empty($previous_lead->dic_ren_month) ? $previous_lead->dic_ren_month : "N/A" }}</span>
						</div>
						<div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
							<strong>Expiring Premium:</strong> <span class="small" id="dic_expiry_premium_previousvalue">{{ !empty($previous_lead->dic_expiry_premium) ? '$' . formatUSNumber($previous_lead->dic_expiry_premium) : "N/A" }}</span>
						</div>
					</div>
					<div class="form-row">
						<div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
							<strong>Policy Renewal Date:</strong> <span class="small" id="dic_policy_renewal_date_previousvalue">{{ !empty($previous_lead->dic_policy_renewal_date) ? date('m/d/Y',strtotime($previous_lead->dic_policy_renewal_date)) : "N/A" }}</span>
						</div>
						<div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
							<strong>Hurricane Deductible:</strong> <span class="small" id="dic_hurricane_deductible_previousvalue">{{ !empty($previous_lead->dic_hurricane_deductible) ? $previous_lead->dic_hurricane_deductible : "N/A" }}</span>
						</div>
						<div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
							<strong>All Other Perils Deductible:</strong> <span class="small" id="dic_all_other_perils_previousvalue">{{ !empty($previous_lead->dic_all_other_perils) ? $previous_lead->dic_all_other_perils : "N/A" }}</span>
						</div>
					</div>
					<div class="form-row">
						<div class="form-group col-12 col-md-12 col-lg-12 mb-1 px-2">
							<strong> Difference In Conditions Notes:</strong> <span class="small longtextarea" id="dic_insurance_coverage_previousvalue">{{ !empty($previous_lead->dic_insurance_coverage) ? $previous_lead->dic_insurance_coverage : "N/A" }}</span>
						</div>
					</div>
				</div>
				<!--  X-Wind -->
				<div class="p-2 mt-4 pt-3 pb-0 mx-0 rounded border position-relative">
					<div class="section-head position-absolute m-0 p-0 z-5 d-inline-block font-weight-bold px-1 bg-white"> X-Wind :</div>
					<div class="form-row">
						<div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
							<strong>Carrier:</strong> <span class="small" id="x_wind_previousvalue">{{ !empty($previous_lead->x_wind) ? $previous_lead->x_wind : "N/A" }}</span>
						</div>
						<div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
							<strong>Month:</strong> <span class="small" id="xw_ren_month_previousvalue">{{ !empty($previous_lead->xw_ren_month) ? $previous_lead->xw_ren_month : "N/A" }}</span>
						</div>
						<div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
							<strong>Expiring Premium:</strong> <span class="small" id="xw_expiry_premium_previousvalue">{{ !empty($previous_lead->xw_expiry_premium) ? '$' . formatUSNumber($previous_lead->xw_expiry_premium) : "N/A" }}</span>
						</div>
					</div>
					<div class="form-row">
						<div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
							<strong>Policy Renewal Date:</strong> <span class="small" id="xw_policy_renewal_date_previousvalue">{{ !empty($previous_lead->xw_policy_renewal_date) ? date('m/d/Y',strtotime($previous_lead->xw_policy_renewal_date)) : "N/A" }}</span>
						</div>
						<div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
							<strong>Hurricane Deductible:</strong> <span class="small" id="xw_hurricane_deductible_previousvalue">{{ !empty($previous_lead->xw_hurricane_deductible) ? $previous_lead->xw_hurricane_deductible : "N/A" }}</span>
						</div>
						<div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
							<strong>All Other Perils Deductible:</strong> <span class="small" id="xw_all_other_perils_previousvalue">{{ !empty($previous_lead->xw_all_other_perils) ? $previous_lead->xw_all_other_perils : "N/A" }}</span>
						</div>
					</div>
					<div class="form-row">
						<div class="form-group col-12 col-md-12 col-lg-12 mb-1 px-2">
							<strong> X-Wind Notes:</strong> <span class="small longtextarea" id="xw_insurance_coverage_previousvalue">{{ !empty($previous_lead->xw_insurance_coverage) ? $previous_lead->xw_insurance_coverage : "N/A" }}</span>
						</div>
					</div>
				</div>
				<!--  Equipment Breakdown -->
				<div class="p-2 mt-4 pt-3 pb-0 mx-0 rounded border position-relative">
					<div class="section-head position-absolute m-0 p-0 z-5 d-inline-block font-weight-bold px-1 bg-white"> Equipment Breakdown :</div>
					<div class="form-row">
						<div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
							<strong>Carrier:</strong> <span class="small" id="equipment_breakdown_previousvalue">{{ !empty($previous_lead->equipment_breakdown) ? $previous_lead->equipment_breakdown : "N/A" }}</span>
						</div>
						<div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
							<strong>Month:</strong> <span class="small" id="eb_ren_month_previousvalue">{{ !empty($previous_lead->eb_ren_month) ? $previous_lead->eb_ren_month : "N/A" }}</span>
						</div>
						<div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
							<strong>Expiring Premium:</strong> <span class="small" id="eb_expiry_premium_previousvalue">{{ !empty($previous_lead->eb_expiry_premium) ? '$' . formatUSNumber($previous_lead->eb_expiry_premium) : "N/A" }}</span>
						</div>
					</div>
					<div class="form-row">
						<div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
							<strong>Policy Renewal Date:</strong> <span class="small" id="eb_policy_renewal_date_previousvalue">{{ !empty($previous_lead->eb_policy_renewal_date) ? date('m/d/Y',strtotime($previous_lead->eb_policy_renewal_date)) : "N/A" }}</span>
						</div>
						<div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
							<strong>Hurricane Deductible:</strong> <span class="small" id="eb_hurricane_deductible_previousvalue">{{ !empty($previous_lead->eb_hurricane_deductible) ? $previous_lead->eb_hurricane_deductible : "N/A" }}</span>
						</div>
						<div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
							<strong>All Other Perils Deductible:</strong> <span class="small" id="eb_all_other_perils_previousvalue">{{ !empty($previous_lead->eb_all_other_perils) ? $previous_lead->eb_all_other_perils : "N/A" }}</span>
						</div>
					</div>
					<div class="form-row">
						<div class="form-group col-12 col-md-12 col-lg-12 mb-1 px-2">
							<strong> Equipment Breakdown Notes:</strong> <span class="small longtextarea" id="eb_insurance_coverage_previousvalue">{{ !empty($previous_lead->eb_insurance_coverage) ? $previous_lead->eb_insurance_coverage : "N/A" }}</span>
						</div>
					</div>
				</div>
				<!--  Commercial AutoMobile -->
				<div class="p-2 mt-4 pt-3 pb-0 mx-0 rounded border position-relative">
					<div class="section-head position-absolute m-0 p-0 z-5 d-inline-block font-weight-bold px-1 bg-white"> Commercial AutoMobile :</div>
					<div class="form-row">
						<div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
							<strong>Carrier:</strong> <span class="small" id="commercial_automobiles_previousvalue">{{ !empty($previous_lead->commercial_automobiles) ? $previous_lead->commercial_automobiles : "N/A" }}</span>
						</div>
						<div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
							<strong>Month:</strong> <span class="small" id="ca_ren_month_previousvalue">{{ !empty($previous_lead->ca_ren_month) ? $previous_lead->ca_ren_month : "N/A" }}</span>
						</div>
						<div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
							<strong>Expiring Premium:</strong> <span class="small" id="ca_expiry_premium_previousvalue">{{ !empty($previous_lead->ca_expiry_premium) ? '$' . formatUSNumber($previous_lead->ca_expiry_premium) : "N/A" }}</span>
						</div>
					</div>
					<div class="form-row">
						<div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
							<strong>Policy Renewal Date:</strong> <span class="small" id="ca_policy_renewal_date_previousvalue">{{ !empty($previous_lead->ca_policy_renewal_date) ? date('m/d/Y',strtotime($previous_lead->ca_policy_renewal_date)) : "N/A" }}</span>
						</div>
						<div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
							<strong>Hurricane Deductible:</strong> <span class="small" id="ca_hurricane_deductible_previousvalue">{{ !empty($previous_lead->ca_hurricane_deductible) ? $previous_lead->ca_hurricane_deductible : "N/A" }}</span>
						</div>
						<div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
							<strong>All Other Perils Deductible:</strong> <span class="small" id="ca_all_other_perils_previousvalue">{{ !empty($previous_lead->ca_all_other_perils) ? $previous_lead->ca_all_other_perils : "N/A" }}</span>
						</div>
					</div>
					<div class="form-row">
						<div class="form-group col-12 col-md-12 col-lg-12 mb-1 px-2">
							<strong> Commercial AutoMobile Notes:</strong> <span class="small longtextarea" id="ca_insurance_coverage_previousvalue">{{ !empty($previous_lead->ca_insurance_coverage) ? $previous_lead->ca_insurance_coverage : "N/A" }}</span>
						</div>
					</div>
				</div>
				<!--  Marina -->
				<div class="p-2 mt-4 pt-3 pb-0 mx-0 rounded border position-relative">
					<div class="section-head position-absolute m-0 p-0 z-5 d-inline-block font-weight-bold px-1 bg-white"> Marina :</div>
					<div class="form-row">
						<div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
							<strong>Carrier:</strong> <span class="small" id="marina_previousvalue">{{ !empty($previous_lead->marina) ? $previous_lead->marina : "N/A" }}</span>
						</div>
						<div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
							<strong>Month:</strong> <span class="small" id="m_ren_month_previousvalue">{{ !empty($previous_lead->m_ren_month) ? $previous_lead->m_ren_month : "N/A" }}</span>
						</div>
						<div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
							<strong>Expiring Premium:</strong> <span class="small" id="m_expiry_premium_previousvalue">{{ !empty($previous_lead->m_expiry_premium) ? '$' . formatUSNumber($previous_lead->m_expiry_premium) : "N/A" }}</span>
						</div>
					</div>
					<div class="form-row">
						<div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
							<strong>Policy Renewal Date:</strong> <span class="small" id="m_policy_renewal_date_previousvalue">{{ !empty($previous_lead->m_policy_renewal_date) ? date('m/d/Y',strtotime($previous_lead->m_policy_renewal_date)) : "N/A" }}</span>
						</div>
						<div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
							<strong>Hurricane Deductible:</strong> <span class="small" id="m_hurricane_deductible_previousvalue">{{ !empty($previous_lead->m_hurricane_deductible) ? $previous_lead->m_hurricane_deductible : "N/A" }}</span>
						</div>
						<div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
							<strong>All Other Perils Deductible:</strong> <span class="small" id="m_all_other_perils_previousvalue">{{ !empty($previous_lead->m_all_other_perils) ? $previous_lead->m_all_other_perils : "N/A" }}</span>
						</div>
					</div>
					<div class="form-row">
						<div class="form-group col-12 col-md-12 col-lg-12 mb-1 px-2">
							<strong> Marina Notes:</strong> <span class="small longtextarea" id="m_insurance_coverage_previousvalue">{{ !empty($previous_lead->m_insurance_coverage) ? $previous_lead->m_insurance_coverage : "N/A" }}</span>
						</div>
					</div>
				</div>
				<?php $iloopcount = 0; ?>
				@foreach($previous_lead_policy_list as $policy)
					<div class="p-2 mt-4 pt-3 pb-0 mx-0 rounded border position-relative area_appended_area">
					    <div class="section-head position-absolute m-0 p-0 z-5 d-inline-block font-weight-bold px-1 bg-white">
					        Additional Policy {{$policy->policy_type}} :
					    </div>
					    <div class="form-row">
					        <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
					            <strong>Policy Type:</strong> 
					            <span class="small" id="policy_type_previousvalue{{$iloopcount}}">{{ !empty($policy->policy_type) ? $policy->policy_type : "N/A" }}</span>
					        </div>
					        <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
					            <strong>Carrier:</strong> 
					            <span class="small" id="carrier_previousvalue{{$iloopcount}}">{{ !empty($policy->listCarrier->name) ? $policy->listCarrier->name : "N/A" }}</span>
					        </div>
					        <div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
					            <strong>Expiring Premium:</strong> 
					            <span class="small" id="expiry_premium_previousvalue{{$iloopcount}}">
					                {{ !empty($policy->expiry_premium) ? '$' . formatUSNumber($policy->expiry_premium) : "N/A" }}
					            </span>
					        </div>
					    </div>
					    <div class="form-row">
					        <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
					            <strong>Policy Renewal Date:</strong> 
					            <span class="small" id="policy_renewal_date_previousvalue{{$iloopcount}}">
					                {{ !empty($policy->policy_renewal_date) ? date('m/d/Y', strtotime($policy->policy_renewal_date)) : "N/A" }}
					            </span>
					        </div>
					        <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
					            <strong>Hurricane Deductible:</strong> 
					            <span class="small" id="hurricane_deductible_previousvalue{{$iloopcount}}">
					                {{ !empty($policy->hurricane_deductible) ? $policy->hurricane_deductible."%" : "N/A" }}
					            </span>
					        </div>
					        <div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
					            <strong>All Other Perils Deductible:</strong> 
					            <span class="small" id="all_other_perils_previousvalue{{$iloopcount}}">
					                {{ !empty($policy->all_other_perils) ? $policy->all_other_perils : "No" }}
					            </span>
					        </div>
					    </div>
					    <div class="form-row">
					        <div class="form-group col-12 col-md-12 col-lg-12 mb-1 px-2">
					            <strong>Notes:</strong> 
					            <span class="small longtextarea" id="insurance_coverage_previousvalue{{$iloopcount}}">
					                {{ !empty($policy->insurance_coverage) ? $policy->insurance_coverage : "N/A" }}
					            </span>
					        </div>
					    </div>
					</div>
					<?php $iloopcount++; ?>
				@endforeach
			</div>
			<div class="my-2">
            	<div class="form-row px-2">
                	<div class="form-group col-12 mb-0">
						<?php
							$total_premium_sum = 0;
							if(!empty($previous_lead->premium)){
								$total_premium_sum += $previous_lead->premium;
							}
							if(!empty($previous_lead->gl_expiry_premium)){
								$total_premium_sum += $previous_lead->gl_expiry_premium;
							}
							if(!empty($previous_lead->ci_expiry_premium)){
								$total_premium_sum += $previous_lead->ci_expiry_premium;
							}
							if(!empty($previous_lead->do_expiry_premium)){
								$total_premium_sum += $previous_lead->do_expiry_premium;
							}
							if(!empty($previous_lead->umbrella_expiry_premium)){
								$total_premium_sum += $previous_lead->umbrella_expiry_premium;
							}
							if(!empty($previous_lead->wc_expiry_premium)){
								$total_premium_sum += $previous_lead->wc_expiry_premium;
							}
							if(!empty($previous_lead->flood_expiry_premium)){
								$total_premium_sum += $previous_lead->flood_expiry_premium;
							}

							if(!empty($previous_lead->dic_expiry_premium)){
								$total_premium_sum += $previous_lead->dic_expiry_premium;
							}
							if(!empty($previous_lead->xw_expiry_premium)){
								$total_premium_sum += $previous_lead->xw_expiry_premium;
							}
							if(!empty($previous_lead->eb_expiry_premium)){
								$total_premium_sum += $previous_lead->eb_expiry_premium;
							}
							if(!empty($previous_lead->ca_expiry_premium)){
								$total_premium_sum += $previous_lead->ca_expiry_premium;
							}
							if(!empty($previous_lead->m_expiry_premium)){
								$total_premium_sum += $previous_lead->m_expiry_premium;
							}

							foreach ($previous_lead_policy_list as $key => $policy) {
								$total_premium_sum += $policy->expiry_premium;
							}

						?>
						<div class="form-group col-12 mb-1 px-2">
							<strong class="text-success mb-0">Total Premium: </strong> <span id="total_premium_sum_previousvalue" class="text-base">{{!empty($total_premium_sum)?"$".$total_premium_sum:"N/A"}}</span>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@push('scripts')
<script>
	const previousValueElem = document.getElementById('total_premium_sum_previousvalue');

	// Remove '$' and convert to number
	const previousValue = parseFloat(previousValueElem.textContent.replace(/[^0-9.]/g, '')) || 0;
	previousValueElem.textContent = assign_value_inputbased_previous(previousValue, 5);


	function convertDateFormatYmd(dateStr) {
    	let [month, day, year] = dateStr.split('/');
    	return `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}`;
	}
	
	$(document).on("click",".renewal_date_btn",function (event) {
		const e1 = $(this);
		event.preventDefault();
		var date = convertDateFormatYmd($(this).text());
		// Remove classes from all buttons
    	$(".renewal_date_btn").removeClass("bg-primary text-white");

    	// Show Loader (Same as DataTables loader)
    	let loaderHtml = '<div class="loader-bg position-absolute loader-section"> <figure class="loader-img"><img src="/images/logo.png" alt=""></figure> </div>';
    	$(".loaderarea").after(loaderHtml); // Append loader to a container (Ensure .loader-container exists in your layout)


    	$.ajax({
	        type: 'POST',
	        url: '/leads/fetchDateWiseOlderData',
	        data: {
	        	lead_id : "{{!empty($previous_lead->id) ? $previous_lead->id: 0}}",
	        	renewal_date: date
	        },
	        success: function(response) {
	        	$(".loader-section").remove();
	        	$(".area_appended_area").remove();
	        	if(response.lead_found == 1){
	        		// Add classes to the clicked button
    				e1.addClass("bg-primary text-white");
    				// console.log("clicked");
    				assignValueToFields(response.previous_lead);
    				displayAdditionalFields(response.previous_lead_policy_list);
    				totalPremiumSumPrevious(response.previous_lead,response.previous_lead_policy_list);
    				pricepersquarefootcalculationPrevious(response.previous_lead.total_square_footage,response.previous_lead.insured_amount);
    				priceperunitcalculationPrevious(response.previous_lead.gl_expiry_premium,response.previous_lead.unit_count);
	        	}
	        },
	        error: function() {
            	// Remove Loader on error as well
            	$(".loader-section").remove();
        	}
	    });
	});
	const checkifNAthenNotAddpercantage_pre = (value) => {
		// console.log(value);
		if(value == "N/A"){
			return value;
		}
		else{
			return value+"%";
		}
	};

	function displayAdditionalFields(previous_lead_policy_list) {
		let count_loop = previous_lead_policy_list.length;

		let appended_data = '';

		for (let i = 0; i < count_loop; i++) {
			const carrier = assign_value_inputbased_previous(previous_lead_policy_list[i].carrier,1);
			const policy_type = assign_value_inputbased_previous(previous_lead_policy_list[i].policy_type,1);
			const expiry_premium = assign_value_inputbased_previous(previous_lead_policy_list[i].expiry_premium,5);
			const polcy_renewal = assign_value_inputbased_previous(previous_lead_policy_list[i].policy_renewal_date,6);
			const huricane_deductable = checkifNAthenNotAddpercantage_pre(assign_value_inputbased_previous(previous_lead_policy_list[i].hurricane_deductible,1));
			const all_other_perlis = assign_value_inputbased_previous(previous_lead_policy_list[i].all_other_perils,1);
			const notes = assign_value_inputbased_previous(previous_lead_policy_list[i].insurance_coverage,1);

			let additional_name;

            if(policy_type == ""){
                additional_name = `Additional Policy ${index + 1}`;
            }
            else{
                additional_name = `Additional Policy (${policy_type})`;
            }

			appended_data += `<div class="p-2 mt-4 pt-3 pb-0 mx-0 rounded border position-relative area_appended_area">
					    <div class="section-head position-absolute m-0 p-0 z-5 d-inline-block font-weight-bold px-1 bg-white">
					        ${additional_name} :
					    </div>
					    <div class="form-row">
					        <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
					            <strong>Policy Type:</strong> 
					            <span class="small" id="policy_type_previousvalue${i}">${policy_type}</span>
					        </div>
					        <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
					            <strong>Carrier:</strong> 
					            <span class="small" id="carrier_previousvalue${i}">${carrier}</span>
					        </div>
					        <div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
					            <strong>Expiring Premium:</strong> 
					            <span class="small" id="expiry_premium_previousvalue${i}">
					                ${expiry_premium}
					            </span>
					        </div>
					    </div>
					    <div class="form-row">
					        <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
					            <strong>Policy Renewal Date:</strong> 
					            <span class="small" id="policy_renewal_date_previousvalue${i}">
					                ${polcy_renewal}
					            </span>
					        </div>
					        <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
					            <strong>Hurricane Deductible:</strong> 
					            <span class="small" id="hurricane_deductible_previousvalue${i}">
					                ${huricane_deductable}
					            </span>
					        </div>
					        <div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
					            <strong>All Other Perils Deductible:</strong> 
					            <span class="small" id="all_other_perils_previousvalue${i}">
					                ${all_other_perlis}
					            </span>
					        </div>
					    </div>
					    <div class="form-row">
					        <div class="form-group col-12 col-md-12 col-lg-12 mb-1 px-2">
					            <strong>Notes:</strong> 
					            <span class="small longtextarea" id="insurance_coverage_previousvalue${i}">
					                ${notes}
					            </span>
					        </div>
					    </div>
					</div>`;
		}

		$(".card-body-appended").append(appended_data);
	}

	function currentClientBannerDisplay(isChecked) {
		let element = document.getElementById("current_client_area_previousvalue");
		if (element) {
    		element.remove();
		}
		if(isChecked){
			const htmlContent = `
                    <div class="form-group mb-2 ml-2 " id="current_client_area_previousvalue">
                        <p class="font-weight-bold text-success mb-0">Current Client</p>
                    </div>
                `;
            document.getElementById('name_previousvalue').insertAdjacentHTML('afterend', htmlContent);
		}
	}
	function formatDateToDMY(date) {
	    let parsedDate;

	    // Check if the input is a string in the format "YYYY-MM-DD"
	    if (typeof date === 'string' && /^\d{4}-\d{2}-\d{2}$/.test(date)) {
	        parsedDate = new Date(date); // Parse the date string
	    } else if (date instanceof Date) {
	        parsedDate = date; // Use the Date object directly
	    } else {
	        console.warn('Invalid date input:', date);
	        return 'Invalid Date'; // Handle invalid input
	    }

	    // Ensure the parsed date is valid
	    if (isNaN(parsedDate)) {
	        console.warn('Invalid date input:', date);
	        return 'Invalid Date';
	    }

	    const day = String(parsedDate.getDate()).padStart(2, '0');
	    const month = String(parsedDate.getMonth() + 1).padStart(2, '0');
	    const year = parsedDate.getFullYear();

	    return `${month}/${day}/${year}`;
	}

	function assign_value_inputbased_previous(value,type,comp='') {
		if(!value || value == null){
			value = '';
		}
		if(type == 1){
			if(value == ''){
				return "N/A";
			}
		}
		else if(type == 2){
			if(value == ''){
				return "No";
			}
		}
		else if(type == 3){
			if(value == ''){
				return "N/A";
			}
			else if(value == 'other'){
				return document.getElementById(comp).value;
			}
		}
		else if(type == 4){
			// console.log(value,type,comp);
			var exclusions = document.getElementById(comp);
			var exclusions_value = '';
		    if (exclusions) {
		        // Get all selected options
		        let selectedValues = Array.from(exclusions.selectedOptions).map(option => option.value);
		        let selval = selectedValues.join(', ');

		        if(selval == ''){
		        	return "N/A";
		        }
		        else{
		        	return selval;
		        }
		    }
		    return "N/A";
		}
		else if(type == 5){
			if(value == ''){
				return "N/A";
			}
			else{
				return "$"+formatUSNumberJs(value);
			}
		}
		else if(type == 6){
			if(value == ''){
				return "N/A";
			}
			return formatDateToDMY(value);
		}
		else if(type == 7){
			if(value){
				return "Coastal";
			}
			return "Non Coastal";
		}
		return value;
	}

	function pricepersquarefootcalculationPrevious(total_square,toal_insured) {
	    if(total_square == '' || toal_insured == ''){
	        $("#price_per_sqft").text('N/A');
	    }
	    else{
	        total_square = parseFloat(total_square);
	        toal_insured = parseFloat(toal_insured);

	        const price_ppt = (toal_insured/total_square).toFixed(2);

	        $("#price_per_sqft_previousvalue").text('$'+formatUSNumberJs(price_ppt));
	    }

	}

	function priceperunitcalculationPrevious(expiry,total) {
	    if(expiry == '' || total == ''){
	        $("#gl_price_per_unit").text('N/A');
	    }
	    else{
	        expiry = parseFloat(expiry);
	        total = parseFloat(total);

	        const price = (expiry/total).toFixed(2);

	        $("#gl_price_per_unit_previousvalue").text('$'+formatUSNumberJs(price));
	    }

	}

	function totalPremiumSumPrevious(lead, previous_lead_policy_list) {
	    let sum = 0;

	    // Define all premium field names
	    const premiumFields = [
	        "premium", "gl_expiry_premium", "ci_expiry_premium",
	        "do_expiry_premium", "umbrella_expiry_premium", "wc_expiry_premium",
	        "flood_expiry_premium", "dic_expiry_premium", "xw_expiry_premium",
	        "eb_expiry_premium", "ca_expiry_premium", "m_expiry_premium"
	    ];

	    // Loop through premium fields and sum values
	    premiumFields.forEach(field => {
	        sum += parseFloat(lead[field]) || 0;
	    });

	    // Loop through previous lead policy list
	    previous_lead_policy_list.forEach(policy => {
	        sum += parseFloat(policy.expiry_premium) || 0;
	    });

	    // Update UI based on sum value
	    if (sum === 0) {
	        $("#total_premium_sum_previousvalue").text("N/A");
	    } else {
	        $("#total_premium_sum_previousvalue").text('$' + formatUSNumberJs(sum));
	    }
	}


	const assignValueToFields = (lead) => {
		currentClientBannerDisplay(lead.is_client);
		document.getElementById('name_previousvalue').textContent = assign_value_inputbased_previous(lead.name,1);
		document.getElementById('type_previousvalue').textContent = assign_value_inputbased_previous(lead.type,1);
		document.getElementById('creation_date_previousvalue').textContent = assign_value_inputbased_previous(lead.creation_date,6);
		document.getElementById('unit_count_previousvalue').textContent = assign_value_inputbased_previous(lead.unit_count,1);
		document.getElementById('address1_previousvalue').textContent = assign_value_inputbased_previous(lead.address1,1);
		document.getElementById('address2_previousvalue').textContent = assign_value_inputbased_previous(lead.address2,1);
		document.getElementById('city_previousvalue').textContent = assign_value_inputbased_previous(lead.city,1);
		document.getElementById('county_previousvalue').textContent = assign_value_inputbased_previous(lead.county,1);
		document.getElementById('coastal_previousvalue').textContent = assign_value_inputbased_previous(lead.coastal,7);
		document.getElementById('state_previousvalue').textContent = assign_value_inputbased_previous(lead.state,1);
		document.getElementById('zip_previousvalue').textContent = assign_value_inputbased_previous(lead.zip,1);
		document.getElementById('ins_flood_previousvalue').textContent = assign_value_inputbased_previous(lead.ins_flood,2);
		document.getElementById('prop_floor_previousvalue').textContent = assign_value_inputbased_previous(lead.prop_floor,1);
		document.getElementById('total_square_footage_previousvalue').textContent = assign_value_inputbased_previous(lead.total_square_footage,1);
		document.getElementById('roof_connection_previousvalue').textContent = assign_value_inputbased_previous(lead.roof_connection,1);
		document.getElementById('roof_geom_previousvalue').textContent = assign_value_inputbased_previous(lead.roof_geom,1);
		document.getElementById('roof_covering_previousvalue').textContent = assign_value_inputbased_previous(lead.roof_covering,1);
		document.getElementById('roof_year_previousvalue').textContent = assign_value_inputbased_previous(lead.roof_year,1);
		document.getElementById('lead_source_previousvalue').textContent = assign_value_inputbased_previous(lead.lead_source,1);
		document.getElementById('business_tiv_previousvalue').textContent = assign_value_inputbased_previous(lead.business_tiv,5);

		document.getElementById('pool_previousvalue').textContent = assign_value_inputbased_previous(lead.pool,2);
		document.getElementById('lakes_previousvalue').textContent = assign_value_inputbased_previous(lead.lakes,2);
		document.getElementById('clubhouse_previousvalue').textContent = assign_value_inputbased_previous(lead.clubhouse,2);
		document.getElementById('tennis_basketball_previousvalue').textContent = assign_value_inputbased_previous(lead.tennis_basketball,2);
		document.getElementById('other_community_info_previousvalue').textContent = assign_value_inputbased_previous(lead.other_community_info,1);
		document.getElementById('iso_previousvalue').textContent = assign_value_inputbased_previous(lead.iso,1);

		document.getElementById('premium_previousvalue').textContent = assign_value_inputbased_previous(lead.premium,5);
		document.getElementById('premium_year_previousvalue').textContent = assign_value_inputbased_previous(lead.premium_year,1);
		document.getElementById('insured_amount_previousvalue').textContent = assign_value_inputbased_previous(lead.insured_amount,5);
		document.getElementById('insured_year_previousvalue').textContent = assign_value_inputbased_previous(lead.insured_year,1);
		document.getElementById('appraisal_name_previousvalue').textContent = assign_value_inputbased_previous(lead.appraisal_name,1);
		document.getElementById('appraisal_company_previousvalue').textContent = assign_value_inputbased_previous(lead.appraisal_company,1);
		document.getElementById('appraisal_date_previousvalue').textContent = assign_value_inputbased_previous(lead.appraisal_date,6);
		document.getElementById('incumbent_agency_previousvalue').textContent = assign_value_inputbased_previous(lead.incumbent_agency,1);
		document.getElementById('incumbent_agent_previousvalue').textContent = assign_value_inputbased_previous(lead.incumbent_agent,1);
		document.getElementById('policy_renewal_date_previousvalue').textContent = assign_value_inputbased_previous(lead.policy_renewal_date,6);
		document.getElementById('wind_mitigation_date_previousvalue').textContent = assign_value_inputbased_previous(lead.wind_mitigation_date,6);
		document.getElementById('rating_previousvalue').textContent = assign_value_inputbased_previous(lead.rating,1);
		document.getElementById('skin_hole_previousvalue').textContent = assign_value_inputbased_previous(lead.skin_hole,2);
		document.getElementById('all_other_perils_previousvalue').textContent = assign_value_inputbased_previous(lead.all_other_perils,5);
		document.getElementById('ordinance_of_law_previousvalue').textContent = checkifNAthenNotAddpercantage_pre(assign_value_inputbased_previous(lead.ordinance_of_law,1));
		document.getElementById('tiv_matches_appraisal_previousvalue').textContent = assign_value_inputbased_previous(lead.tiv_matches_appraisal,2);
		document.getElementById('secondary_water_insurance_previousvalue').textContent = assign_value_inputbased_previous(lead.secondary_water_insurance,2);
		document.getElementById('opening_protection_previousvalue').textContent = assign_value_inputbased_previous(lead.opening_protection,2);

		// carrier input need to be added - ins_prop_carrier
		document.getElementById('ins_prop_carrier_previousvalue').textContent = assign_value_inputbased_previous(lead.ins_prop_carrier,1);
		document.getElementById('renewal_carrier_month_previousvalue').textContent = assign_value_inputbased_previous(lead.renewal_carrier_month,1);
		document.getElementById('hurricane_deductible_previousvalue').textContent = checkifNAthenNotAddpercantage_pre(assign_value_inputbased_previous(lead.hurricane_deductible,1));
		document.getElementById('hurricane_deductible_occurrence_previousvalue').textContent = assign_value_inputbased_previous(lead.hurricane_deductible_occurrence,1);
		document.getElementById('property_insurance_coverage_previousvalue').textContent = assign_value_inputbased_previous(lead.property_insurance_coverage,1);
 

		document.getElementById('general_liability_previousvalue').textContent = assign_value_inputbased_previous(lead.general_liability,1);
		document.getElementById('GL_ren_month_previousvalue').textContent = assign_value_inputbased_previous(lead.GL_ren_month,1);
		document.getElementById('gl_expiry_premium_previousvalue').textContent = assign_value_inputbased_previous(lead.gl_expiry_premium,5);
		document.getElementById('gl_policy_renewal_date_previousvalue').textContent = assign_value_inputbased_previous(lead.gl_policy_renewal_date,6);
		document.getElementById('gl_rating_previousvalue').textContent = assign_value_inputbased_previous(lead.gl_rating,1);
		document.getElementById('gl_exclusions_previousvalue').textContent = assign_value_inputbased_previous(lead.gl_exclusions,1);
		document.getElementById('gl_other_exclusions_previousvalue').textContent = assign_value_inputbased_previous(lead.gl_other_exclusions,1);
		document.getElementById('gl_insurance_coverage_previousvalue').textContent = assign_value_inputbased_previous(lead.gl_insurance_coverage,1);

		// console.log(assign_value_inputbased_previous('',4,"gl_exclusions"));

		document.getElementById('crime_insurance_previousvalue').textContent = assign_value_inputbased_previous(lead.crime_insurance,1);
		document.getElementById('CI_ren_month_previousvalue').textContent = assign_value_inputbased_previous(lead.CI_ren_month,1);
		document.getElementById('ci_expiry_premium_previousvalue').textContent = assign_value_inputbased_previous(lead.ci_expiry_premium,5);
		document.getElementById('ci_policy_renewal_date_previousvalue').textContent = assign_value_inputbased_previous(lead.ci_policy_renewal_date,6);
		document.getElementById('ci_rating_previousvalue').textContent = assign_value_inputbased_previous(lead.ci_rating,1);
		document.getElementById('employee_theft_previousvalue').textContent = assign_value_inputbased_previous(lead.employee_theft,1);
		document.getElementById('operating_reserves_previousvalue').textContent = assign_value_inputbased_previous(lead.operating_reserves,1);
		document.getElementById('pending_litigation_previousvalue').textContent = assign_value_inputbased_previous(lead.pending_litigation,2);
		document.getElementById('litigation_date_previousvalue').textContent = assign_value_inputbased_previous(lead.litigation_date,6);
		document.getElementById('ci_insurance_coverage_previousvalue').textContent = assign_value_inputbased_previous(lead.ci_insurance_coverage,1);

		document.getElementById('directors_officers_previousvalue').textContent = assign_value_inputbased_previous(lead.directors_officers,1);
		document.getElementById('DO_ren_month_previousvalue').textContent = assign_value_inputbased_previous(lead.DO_ren_month,1);
		document.getElementById('do_expiry_premium_previousvalue').textContent = assign_value_inputbased_previous(lead.do_expiry_premium,5);
		document.getElementById('do_policy_renewal_date_previousvalue').textContent = assign_value_inputbased_previous(lead.do_policy_renewal_date,6);
		document.getElementById('do_rating_previousvalue').textContent = assign_value_inputbased_previous(lead.do_rating,1);
		document.getElementById('claims_made_previousvalue').textContent = assign_value_inputbased_previous(lead.claims_made,2);
		document.getElementById('do_insurance_coverage_previousvalue').textContent = assign_value_inputbased_previous(lead.do_insurance_coverage,1);

		document.getElementById('umbrella_previousvalue').textContent = assign_value_inputbased_previous(lead.umbrella,1);
		document.getElementById('U_ren_month_previousvalue').textContent = assign_value_inputbased_previous(lead.U_ren_month,1);
		document.getElementById('umbrella_expiry_premium_previousvalue').textContent = assign_value_inputbased_previous(lead.umbrella_expiry_premium,5);
		document.getElementById('umbrella_policy_renewal_date_previousvalue').textContent = assign_value_inputbased_previous(lead.umbrella_policy_renewal_date,6);
		document.getElementById('umbrella_exclusions_previousvalue').textContent = assign_value_inputbased_previous(lead.umbrella_exclusions,1);
		document.getElementById('umbrella_other_exclusions_previousvalue').textContent = assign_value_inputbased_previous(lead.umbrella_other_exclusions,1);
		document.getElementById('umbrella_rating_previousvalue').textContent = assign_value_inputbased_previous(lead.umbrella_rating,1);
		document.getElementById('correct_underlying_previousvalue').textContent = assign_value_inputbased_previous(lead.correct_underlying,2);
		document.getElementById('u_insurance_coverage_previousvalue').textContent = assign_value_inputbased_previous(lead.u_insurance_coverage,1);

		document.getElementById('workers_compensation_previousvalue').textContent = assign_value_inputbased_previous(lead.workers_compensation,1);
		document.getElementById('WC_ren_month_previousvalue').textContent = assign_value_inputbased_previous(lead.WC_ren_month,1);
		document.getElementById('wc_expiry_premium_previousvalue').textContent = assign_value_inputbased_previous(lead.wc_expiry_premium,5);
		document.getElementById('wc_policy_renewal_date_previousvalue').textContent = assign_value_inputbased_previous(lead.wc_policy_renewal_date,6);
		document.getElementById('wc_rating_previousvalue').textContent = assign_value_inputbased_previous(lead.wc_rating,1);
		document.getElementById('employee_count_previousvalue').textContent = assign_value_inputbased_previous(lead.employee_count,1);
		document.getElementById('employee_payroll_previousvalue').textContent = assign_value_inputbased_previous(lead.employee_payroll,1);
		document.getElementById('wc_insurance_coverage_previousvalue').textContent = assign_value_inputbased_previous(lead.wc_insurance_coverage,1);

		document.getElementById('flood_previousvalue').textContent = assign_value_inputbased_previous(lead.flood,1);
		document.getElementById('F_ren_month_previousvalue').textContent = assign_value_inputbased_previous(lead.F_ren_month,1);
		document.getElementById('flood_expiry_premium_previousvalue').textContent = assign_value_inputbased_previous(lead.flood_expiry_premium,5);
		document.getElementById('flood_policy_renewal_date_previousvalue').textContent = assign_value_inputbased_previous(lead.flood_policy_renewal_date,6);
		document.getElementById('flood_rating_previousvalue').textContent = assign_value_inputbased_previous(lead.flood_rating,1);
		document.getElementById('elevation_certificate_previousvalue').textContent = assign_value_inputbased_previous(lead.elevation_certificate,2);
		document.getElementById('loma_letter_previousvalue').textContent = assign_value_inputbased_previous(lead.loma_letter,2);
		document.getElementById('f_insurance_coverage_previousvalue').textContent = assign_value_inputbased_previous(lead.f_insurance_coverage,1);

		document.getElementById('difference_in_condition_previousvalue').textContent = assign_value_inputbased_previous(lead.difference_in_condition,1);
		document.getElementById('dic_ren_month_previousvalue').textContent = assign_value_inputbased_previous(lead.dic_ren_month,1);
		document.getElementById('dic_expiry_premium_previousvalue').textContent = assign_value_inputbased_previous(lead.dic_expiry_premium,5);
		document.getElementById('dic_policy_renewal_date_previousvalue').textContent = assign_value_inputbased_previous(lead.dic_policy_renewal_date,6);
		document.getElementById('dic_hurricane_deductible_previousvalue').textContent = checkifNAthenNotAddpercantage_pre(assign_value_inputbased_previous(lead.dic_hurricane_deductible,1));
		document.getElementById('dic_all_other_perils_previousvalue').textContent = assign_value_inputbased_previous(lead.dic_all_other_perils,1);
		document.getElementById('dic_insurance_coverage_previousvalue').textContent = assign_value_inputbased_previous(lead.dic_insurance_coverage,1);

		document.getElementById('x_wind_previousvalue').textContent = assign_value_inputbased_previous(lead.x_wind,1);
		document.getElementById('xw_ren_month_previousvalue').textContent = assign_value_inputbased_previous(lead.xw_ren_month,1);
		document.getElementById('xw_expiry_premium_previousvalue').textContent = assign_value_inputbased_previous(lead.xw_expiry_premium,5);
		document.getElementById('xw_policy_renewal_date_previousvalue').textContent = assign_value_inputbased_previous(lead.xw_policy_renewal_date,6);
		document.getElementById('xw_hurricane_deductible_previousvalue').textContent = checkifNAthenNotAddpercantage_pre(assign_value_inputbased_previous(lead.xw_hurricane_deductible,1));
		document.getElementById('xw_all_other_perils_previousvalue').textContent = assign_value_inputbased_previous(lead.xw_all_other_perils,1);
		document.getElementById('xw_insurance_coverage_previousvalue').textContent = assign_value_inputbased_previous(lead.xw_insurance_coverage,1);

		document.getElementById('equipment_breakdown_previousvalue').textContent = assign_value_inputbased_previous(lead.equipment_breakdown,1);
		document.getElementById('eb_ren_month_previousvalue').textContent = assign_value_inputbased_previous(lead.eb_ren_month,1);
		document.getElementById('eb_expiry_premium_previousvalue').textContent = assign_value_inputbased_previous(lead.eb_expiry_premium,5);
		document.getElementById('eb_policy_renewal_date_previousvalue').textContent = assign_value_inputbased_previous(lead.eb_policy_renewal_date,6);
		document.getElementById('eb_hurricane_deductible_previousvalue').textContent = checkifNAthenNotAddpercantage_pre(assign_value_inputbased_previous(lead.eb_hurricane_deductible,1));
		document.getElementById('eb_all_other_perils_previousvalue').textContent = assign_value_inputbased_previous(lead.eb_all_other_perils,1);
		document.getElementById('eb_insurance_coverage_previousvalue').textContent = assign_value_inputbased_previous(lead.eb_insurance_coverage,1);

		document.getElementById('commercial_automobiles_previousvalue').textContent = assign_value_inputbased_previous(lead.commercial_automobiles,1);
		document.getElementById('ca_ren_month_previousvalue').textContent = assign_value_inputbased_previous(lead.ca_ren_month,1);
		document.getElementById('ca_expiry_premium_previousvalue').textContent = assign_value_inputbased_previous(lead.ca_expiry_premium,5);
		document.getElementById('ca_policy_renewal_date_previousvalue').textContent = assign_value_inputbased_previous(lead.ca_policy_renewal_date,6);
		document.getElementById('ca_hurricane_deductible_previousvalue').textContent = checkifNAthenNotAddpercantage_pre(assign_value_inputbased_previous(lead.ca_hurricane_deductible,1));
		document.getElementById('ca_all_other_perils_previousvalue').textContent = assign_value_inputbased_previous(lead.ca_all_other_perils,1);
		document.getElementById('ca_insurance_coverage_previousvalue').textContent = assign_value_inputbased_previous(lead.ca_insurance_coverage,1);

		document.getElementById('marina_previousvalue').textContent = assign_value_inputbased_previous(lead.marina,1);
		document.getElementById('m_ren_month_previousvalue').textContent = assign_value_inputbased_previous(lead.m_ren_month,1);
		document.getElementById('m_expiry_premium_previousvalue').textContent = assign_value_inputbased_previous(lead.m_expiry_premium,5);
		document.getElementById('m_policy_renewal_date_previousvalue').textContent = assign_value_inputbased_previous(lead.m_policy_renewal_date,6);
		document.getElementById('m_hurricane_deductible_previousvalue').textContent = checkifNAthenNotAddpercantage_pre(assign_value_inputbased_previous(lead.m_hurricane_deductible,1));
		document.getElementById('m_all_other_perils_previousvalue').textContent = assign_value_inputbased_previous(lead.m_all_other_perils,1);
		document.getElementById('m_insurance_coverage_previousvalue').textContent = assign_value_inputbased_previous(lead.m_insurance_coverage,1);
	};
</script>
@endpush