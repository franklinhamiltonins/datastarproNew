<div class="card-body lead-update p-0">



    @if(isset($lead))
    <div class="card card-secondary mt-0 mb-0 border-0 shadow-none">
        
            <!-- <h3 class="card-title fs-2 mb-3 pb-2 border-bottom"> Prospect’s Insurance Information </h3> -->
       
        <div class="card-body p-0 pt-2">
            <div class="p-2 mt-2 pt-3 pb-0 mx-2 rounded border position-relative">
                <div class="section-head position-absolute m-0 p-0 z-5 d-inline-block font-weight-bold px-1 bg-white">Property:</div>
                <div class="form-row px-2">
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>Carrier: </strong>  <span class="small">{{!empty($lead->propertyCarrier->name)?$lead->propertyCarrier->name:"N/A"}}</span>
                    </div>
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>Month: </strong>  <span class="small">{{!empty($lead->renewal_carrier_month)?$lead->renewal_carrier_month:"N/A"}}</span>
                    </div>
                </div>
                <div class="form-group px-2">
                    <div class="row align-items-end">
                        <div class="col-12 col-md-6">
                            <strong>Expiring Premium: </strong> <span class="small">{{!empty($lead->premium)?'$'.formatUSNumber($lead->premium,2):"N/A"}}</span>
                        </div>
                        <div class="col-12 col-md-6">
                            <strong>Expiring Premium Year: </strong> <span class="small">{{!empty($lead->premium_year)?$lead->premium_year:"N/A"}}</span>
                        </div>
                    </div>
                </div>
                <div class="form-group px-2">
                    <div class="row">
                        <div class="col-12 col-md-6">
                            <strong>Total insured value: </strong> <span class="small">{{!empty($lead->insured_amount)?'$'.formatUSNumber($lead->insured_amount,2):"N/A"}}</span>
                        </div>
                        <div class="col-12 col-md-6">
                            <strong>Total Insured Value – Year: </strong> <span class="small">{{!empty($lead->insured_year)?$lead->insured_year:"N/A"}}</span>
                        </div>
                    </div>
                </div>

                <div class="form-row px-2">
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>Price Per SqFt: </strong> <span class="small" id="price_per_sqft"></span>
                    </div>
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>Policy Renewal Date:</strong>  <span class="small">{{!empty($lead->policy_renewal_date)?date('m/d/Y',strtotime($lead->policy_renewal_date)):"N/A"}}</span>
                    </div>
                </div>
                <div class="form-row px-2">
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>Incumbent Agency:</strong>  <span class="small">{{!empty($lead->incumbent_agency)?$lead->incumbent_agency:"N/A"}}</span>
                    </div>
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>Incumbent Agent:</strong>  <span class="small">{{!empty($lead->incumbent_agent)?$lead->incumbent_agent:"N/A"}}</span>
                    </div>
                </div>
                <div class="form-row px-2">
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>Rating:</strong>  <span class="small">{{!empty($lead->propertyRating->name)?$lead->propertyRating->name:"N/A"}}</span>
                    </div>
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong> Sinkhole:</strong>  <span class="small">{{!empty($lead->skin_hole)?$lead->skin_hole:"No"}}</span>
                    </div>
                </div>
                <div class="form-row px-2">
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>Hurricane Deductible (Per Occ/Year):</strong>  <span class="small">{{!empty($lead->hurricane_deductible_occurrence)?$lead->hurricane_deductible_occurrence:"N/A"}}</span>
                    </div>
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong> Hurricane Deductible:</strong>  <span class="small">{{!empty($lead->hurricane_deductible)?$lead->hurricane_deductible."%":"N/A"}}</span>
                    </div>
                </div>
                <div class="form-row px-2">
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>All other Perils:</strong>  <span class="small">{{!empty($lead->all_other_perils)?'$'.formatUSNumber($lead->all_other_perils,2):"N/A"}}</span>
                    </div>
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>Ordinance of Law:</strong>  <span class="small">{{!empty($lead->ordinance_of_law)?$lead->ordinance_of_law."%":"N/A"}}</span>
                    </div>
                </div>
                <div class="form-row px-2">
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>T.I.V. Matches Appraisal:</strong>  <span class="small">{{!empty($lead->tiv_matches_appraisal)?$lead->tiv_matches_appraisal:"No"}}</span>
                    </div>
                    <div class="form-group col-12 col-md-6 mb-2">
                        
                    </div>
                </div>
                <div class="form-row px-2">
                    <div class="form-group col-12 mb-2">
                        <strong>Property Notes:</strong>  <span class="small">{{!empty($lead->property_insurance_coverage)?$lead->property_insurance_coverage:"No"}}</span>
                    </div>
                </div>
            </div>
            <!-- General Liability  -->
            
            <div class="p-2 mt-4 pt-3 pb-0 mx-2 rounded border position-relative">
                <div class="section-head position-absolute m-0 p-0 z-5 d-inline-block font-weight-bold px-1 bg-white">General Liability:</div>
                <div class="form-row">
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>Carrier: </strong> <span class="small">{{!empty($lead->glCarrier->name)?$lead->glCarrier->name:"N/A"}}</span>
                    </div>
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>Month: </strong> <span class="small">{{!empty($lead->GL_ren_month)?$lead->GL_ren_month:"N/A"}}</span>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>Expiring Premium: </strong> <span class="small">{{!empty($lead->gl_expiry_premium)?'$'.formatUSNumber($lead->gl_expiry_premium,2):"N/A"}}</span>
                    </div>
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>Policy Renewal Date: </strong> <span class="small">{{!empty($lead->gl_policy_renewal_date)?date('m/d/Y',strtotime($lead->gl_policy_renewal_date)):"N/A"}}</span>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>Rating:</strong> <span class="small">{{!empty($lead->generaLiablityRating->name)?$lead->generaLiablityRating->name:"N/A"}}</span>
                    </div>
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>Price Per Unit:</strong> <span class="small" id="gl_price_per_unit"></span>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>Exclusions:</strong> <span class="small">{{!empty($lead->gl_exclusions)?$lead->gl_exclusions:"N/A"}}</span>
                    </div>
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>Other Exclusions:</strong> <span class="small">{{!empty($lead->gl_other_exclusions)?$lead->gl_other_exclusions:"N/A"}}</span>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group col-12 col-md-12 mb-2">
                        <strong>General Liability Notes:</strong> <span class="small">{{!empty($lead->gl_insurance_coverage)?$lead->gl_insurance_coverage:"N/A"}}</span>
                    </div>
                </div>
            </div>
            <!-- Crime Insurance -->
            <div class="p-2 mt-4 pt-3 pb-0 mx-2 rounded border position-relative">
                <div class="section-head position-absolute m-0 p-0 z-5 d-inline-block font-weight-bold px-1 bg-white">Crime Insurance:</div>
                <div class="form-row">
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>Carrier: </strong> <span class="small">{{!empty($lead->ciCarrier->name)?$lead->ciCarrier->name:"N/A"}}</span>
                    </div>
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>Month: </strong> <span class="small">{{!empty($lead->CI_ren_month)?$lead->CI_ren_month:"N/A"}}</span>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>Expiring Premium: </strong> <span class="small">{{!empty($lead->ci_expiry_premium)?'$'.formatUSNumber($lead->ci_expiry_premium,2):"N/A"}}</span>
                    </div>
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>Policy Renewal Date: </strong> <span class="small">{{!empty($lead->ci_policy_renewal_date)?date('m/d/Y',strtotime($lead->ci_policy_renewal_date)):"N/A"}}</span>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>Rating:</strong> <span class="small">{{!empty($lead->crimeInsuranceRating->name)?$lead->crimeInsuranceRating->name:"N/A"}}</span>
                    </div>
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>Employee Theft:</strong> <span class="small">{{!empty($lead->employee_theft)?$lead->employee_theft:"N/A"}}</span>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>Operating Reserves:</strong> <span class="small">{{!empty($lead->operating_reserves)?$lead->operating_reserves:"N/A"}}</span>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-12 col-md-12 mb-2">
                        <strong>Crime Insurance Notes:</strong> <span class="small">{{!empty($lead->ci_insurance_coverage)?$lead->ci_insurance_coverage:"N/A"}}</span>
                    </div>
                </div>
            </div>
            <!-- <div class="mb-2">
                <div class="form-row">
                    
                </div>
            </div> -->
            <!-- Directors & Officers -->
            <div class="p-2 mt-4 pt-3 pb-0 mx-2 rounded border position-relative">
                <div class="section-head position-absolute m-0 p-0 z-5 d-inline-block font-weight-bold px-1 bg-white">Directors & Officers:</div>
                <div class="form-row">
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>Carrier: </strong> <span class="small">{{!empty($lead->doCarrier->name)?$lead->doCarrier->name:"N/A"}}</span>
                    </div>
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>Month: </strong> <span class="small">{{!empty($lead->DO_ren_month)?$lead->DO_ren_month:"N/A"}}</span>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>Expiring Premium: </strong> <span class="small">{{!empty($lead->do_expiry_premium)?'$'.formatUSNumber($lead->do_expiry_premium,2):"N/A"}}</span>
                    </div>
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>Policy Renewal Date: </strong> <span class="small">{{!empty($lead->do_policy_renewal_date)?date('m/d/Y',strtotime($lead->do_policy_renewal_date)):"N/A"}}</span>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>Rating:</strong> <span class="small">{{!empty($lead->directorOfficerRating->name)?$lead->directorOfficerRating->name:"N/A"}}</span>
                    </div>
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>Claims Made:</strong> <span class="small">{{!empty($lead->claims_made)?$lead->claims_made:"No"}}</span>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>Pending Litigation:</strong> <span class="small">{{!empty($lead->pending_litigation)?$lead->pending_litigation:"No"}}</span>
                    </div>
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong> Litigation Date:</strong> <span class="small">{{!empty($lead->litigation_date)?date('m/d/Y',strtotime($lead->litigation_date)):"N/A"}}</span>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-12 col-md-12 mb-2">
                        <strong>Directors & Officers Notes:</strong> <span class="small">{{!empty($lead->do_insurance_coverage)?$lead->do_insurance_coverage:"N/A"}}</span>
                    </div>
                </div>
            </div>
            <!-- Umbrella -->
            <div class="p-2 mt-4 mx-2 pb-0 pt-3 rounded border position-relative">
                <div class="section-head position-absolute m-0 p-0 z-5 d-inline-block font-weight-bold px-1 bg-white">Umbrella:</div>
                <div class="form-row">
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>Carrier: </strong> <span class="small">{{!empty($lead->umbrellaCarrier->name)?$lead->umbrellaCarrier->name:"N/A"}}</span>
                    </div>
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>Month: </strong> <span class="small">{{!empty($lead->U_ren_month)?$lead->U_ren_month:"N/A"}}</span>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>Expiring Premium: </strong> <span class="small">{{!empty($lead->umbrella_expiry_premium)?'$'.formatUSNumber($lead->umbrella_expiry_premium,2):"N/A"}}</span>
                    </div>
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>Policy Renewal Date: </strong> <span class="small">{{!empty($lead->umbrella_policy_renewal_date)?date('m/d/Y',strtotime($lead->umbrella_policy_renewal_date)):"N/A"}}</span>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>Rating:</strong> <span class="small">{{!empty($lead->uRating->name)?$lead->uRating->name:"N/A"}}</span>
                    </div>
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>Correct Underlying:</strong> <span class="small">{{!empty($lead->correct_underlying)?$lead->correct_underlying:"N/A"}}</span>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>Exclusions:</strong> <span class="small">{{!empty($lead->umbrella_exclusions)?$lead->umbrella_exclusions:"N/A"}}</span>
                    </div>
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>Other Exclusions:</strong> <span class="small">{{!empty($lead->umbrella_other_exclusions)?$lead->umbrella_other_exclusions:"N/A"}}</span>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-12 col-md-12 mb-2">
                        <strong>Umbrella Notes:</strong> <span class="small">{{!empty($lead->u_insurance_coverage)?$lead->u_insurance_coverage:"N/A"}}</span>
                    </div>
                </div>
            </div>
            <!-- Workers Compensation  -->
            <div class="p-2 mt-4 pt-3 pb-0 mx-2 rounded border position-relative">
                <div class="section-head position-absolute m-0 p-0 z-5 d-inline-block font-weight-bold px-1 bg-white">Workers Compensation:</div>
                <div class="form-row">
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>Carrier: </strong> <span class="small">{{!empty($lead->wcCarrier->name)?$lead->wcCarrier->name:"N/A"}}</span>
                    </div>
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>Month: </strong> <span class="small">{{!empty($lead->WC_ren_month)?$lead->WC_ren_month:"NA"}}</span>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>Expiring Premium: </strong> <span class="small">{{!empty($lead->wc_expiry_premium)?'$'.formatUSNumber($lead->wc_expiry_premium,2):"N/A"}}</span>
                    </div>
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>Policy Renewal Date: </strong> <span class="small">{{!empty($lead->wc_policy_renewal_date)?date('m/d/Y',strtotime($lead->wc_policy_renewal_date)):"N/A"}}</span>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>Rating:</strong> <span class="small">{{!empty($lead->workerCompansestionRating->name)?$lead->workerCompansestionRating->name:"N/A"}}</span>
                    </div>
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>Employee Count:</strong> <span class="small">{{!empty($lead->employee_count)?$lead->employee_count:"N/A"}}</span>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong> Employee Payroll:</strong> <span class="small">{{!empty($lead->employee_payroll)?'$'.formatUSNumber($lead->employee_payroll):"N/A"}}</span>
                    </div>
                    <div class="form-group col-12 col-md-12 mb-2">
                        <strong>Workers Compensation Notes:</strong> <span class="small">{{!empty($lead->wc_insurance_coverage)?$lead->wc_insurance_coverage:"N/A"}}</span>
                    </div>
                </div>
            </div>
            <!-- Flood -->
            <div class="p-2 mt-4 pt-3 pb-0 mx-2 rounded border position-relative">
                <div class="section-head position-absolute m-0 p-0 z-5 d-inline-block font-weight-bold px-1 bg-white">Flood:</div>
                <div class="form-row">
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>Carrier: </strong> <span class="small">{{!empty($lead->floodCarrier->name)?$lead->floodCarrier->name:"N/A"}}</span>
                    </div>
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>Month: </strong> <span class="small">{{!empty($lead->F_ren_month)?$lead->F_ren_month:"N/A"}}</span>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>Expiring Premium: </strong> <span class="small">{{!empty($lead->flood_expiry_premium)?'$'.formatUSNumber($lead->flood_expiry_premium):"N/A"}}</span>
                    </div>
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>Policy Renewal Date: </strong> <span class="small">{{!empty($lead->flood_policy_renewal_date)?date('m/d/Y',strtotime($lead->flood_policy_renewal_date)):"N/A"}}</span>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>Rating:</strong> <span class="small">{{!empty($lead->fRating->name)?$lead->fRating->name:"N/A"}}</span>
                    </div>
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>Elevation Certificate:</strong> <span class="small">{{!empty($lead->elevation_certificate)?$lead->elevation_certificate:"No"}}</span>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>Loma Letter:</strong> <span class="small">{{!empty($lead->loma_letter)?$lead->loma_letter:"No"}}</span>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-12 col-md-12 mb-2">
                        <strong>Flood Notes:</strong> <span class="small">{{!empty($lead->f_insurance_coverage)?$lead->f_insurance_coverage:"N/A"}}</span>
                    </div>
                </div>
            </div>
            <!-- Difference In Conditions  -->
            <div class="p-2 mt-4 pt-3 pb-0 mx-2 rounded border position-relative">
                <div class="section-head position-absolute m-0 p-0 z-5 d-inline-block font-weight-bold px-1 bg-white">Difference In Conditions:</div>
                <div class="form-row">
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>Carrier: </strong> <span class="small">{{!empty($lead->dcCarrier->name)?$lead->dcCarrier->name:"N/A"}}</span>
                    </div>
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>Month: </strong> <span class="small">{{!empty($lead->dic_ren_month)?$lead->dic_ren_month:"N/A"}}</span>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>Expiring Premium: </strong> <span class="small">{{!empty($lead->dic_expiry_premium)?'$'.formatUSNumber($lead->dic_expiry_premium):"N/A"}}</span>
                    </div>
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>Policy Renewal Date: </strong> <span class="small">{{!empty($lead->dic_policy_renewal_date)?date('m/d/Y',strtotime($lead->dic_policy_renewal_date)):"N/A"}}</span>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>Hurricane Deductible:</strong> <span class="small">{{!empty($lead->dic_hurricane_deductible)?$lead->dic_hurricane_deductible."%":"N/A"}}</span>
                    </div>
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>All Other Perils Deductible:</strong> <span class="small">{{!empty($lead->dic_all_other_perils)?$lead->dic_all_other_perils:"No"}}</span>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-12 col-md-12 mb-2">
                        <strong>Difference In Conditions Notes:</strong> <span class="small">{{!empty($lead->dic_insurance_coverage)?$lead->dic_insurance_coverage:"N/A"}}</span>
                    </div>
                </div>
            </div>
            <!-- X-Wind  -->
            <div class="p-2 mt-4 pt-3 pb-0 mx-2 rounded border position-relative">
                <div class="section-head position-absolute m-0 p-0 z-5 d-inline-block font-weight-bold px-1 bg-white">X-Wind:</div>
                <div class="form-row">
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>Carrier: </strong> <span class="small">{{!empty($lead->xwindCarrier->name)?$lead->xwindCarrier->name:"N/A"}}</span>
                    </div>
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>Month: </strong> <span class="small">{{!empty($lead->xw_ren_month)?$lead->xw_ren_month:"N/A"}}</span>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>Expiring Premium: </strong> <span class="small">{{!empty($lead->xw_expiry_premium)?'$'.formatUSNumber($lead->xw_expiry_premium):"N/A"}}</span>
                    </div>
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>Policy Renewal Date: </strong> <span class="small">{{!empty($lead->xw_policy_renewal_date)?date('m/d/Y',strtotime($lead->xw_policy_renewal_date)):"N/A"}}</span>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>Hurricane Deductible:</strong> <span class="small">{{!empty($lead->xw_hurricane_deductible)?$lead->xw_hurricane_deductible."%":"N/A"}}</span>
                    </div>
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>All Other Perils Deductible:</strong> <span class="small">{{!empty($lead->xw_all_other_perils)?$lead->xw_all_other_perils:"No"}}</span>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-12 col-md-12 mb-2">
                        <strong>X-Wind Notes:</strong> <span class="small">{{!empty($lead->xw_insurance_coverage)?$lead->xw_insurance_coverage:"N/A"}}</span>
                    </div>
                </div>
            </div>
            <!-- Equipment Breakdown  -->
            <div class="p-2 mt-4 pt-3 pb-0 mx-2 rounded border position-relative">
                <div class="section-head position-absolute m-0 p-0 z-5 d-inline-block font-weight-bold px-1 bg-white">Equipment Breakdown:</div>
                <div class="form-row">
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>Carrier: </strong> <span class="small">{{!empty($lead->ebCarrier->name)?$lead->ebCarrier->name:"N/A"}}</span>
                    </div>
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>Month: </strong> <span class="small">{{!empty($lead->eb_ren_month)?$lead->eb_ren_month:"N/A"}}</span>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>Expiring Premium: </strong> <span class="small">{{!empty($lead->eb_expiry_premium)?'$'.formatUSNumber($lead->eb_expiry_premium):"N/A"}}</span>
                    </div>
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>Policy Renewal Date: </strong> <span class="small">{{!empty($lead->eb_policy_renewal_date)?date('m/d/Y',strtotime($lead->eb_policy_renewal_date)):"N/A"}}</span>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>Hurricane Deductible:</strong> <span class="small">{{!empty($lead->eb_hurricane_deductible)?$lead->eb_hurricane_deductible."%":"N/A"}}</span>
                    </div>
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>All Other Perils Deductible:</strong> <span class="small">{{!empty($lead->eb_all_other_perils)?$lead->eb_all_other_perils:"No"}}</span>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-12 col-md-12 mb-2">
                        <strong>Equipment Breakdown Notes:</strong> <span class="small">{{!empty($lead->eb_insurance_coverage)?$lead->eb_insurance_coverage:"N/A"}}</span>
                    </div>
                </div>
            </div>
            <!-- Commercial AutoMobile  -->
            <div class="p-2 mt-4 pt-3 pb-0 mx-2 rounded border position-relative">
                <div class="section-head position-absolute m-0 p-0 z-5 d-inline-block font-weight-bold px-1 bg-white">Commercial AutoMobile:</div>
                <div class="form-row">
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>Carrier: </strong> <span class="small">{{!empty($lead->caCarrier->name)?$lead->caCarrier->name:"N/A"}}</span>
                    </div>
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>Month: </strong> <span class="small">{{!empty($lead->ca_ren_month)?$lead->ca_ren_month:"N/A"}}</span>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>Expiring Premium: </strong> <span class="small">{{!empty($lead->ca_expiry_premium)?'$'.formatUSNumber($lead->ca_expiry_premium):"N/A"}}</span>
                    </div>
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>Policy Renewal Date: </strong> <span class="small">{{!empty($lead->ca_policy_renewal_date)?date('m/d/Y',strtotime($lead->ca_policy_renewal_date)):"N/A"}}</span>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>Hurricane Deductible:</strong> <span class="small">{{!empty($lead->ca_hurricane_deductible)?$lead->ca_hurricane_deductible."%":"N/A"}}</span>
                    </div>
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>All Other Perils Deductible:</strong> <span class="small">{{!empty($lead->ca_all_other_perils)?$lead->ca_all_other_perils:"No"}}</span>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-12 col-md-12 mb-2">
                        <strong>Commercial AutoMobile Notes:</strong> <span class="small">{{!empty($lead->ca_insurance_coverage)?$lead->ca_insurance_coverage:"N/A"}}</span>
                    </div>
                </div>
            </div>
            <!-- Marina  -->
            <div class="p-2 mt-4 pt-3 pb-0 mx-2 rounded border position-relative">
                <div class="section-head position-absolute m-0 p-0 z-5 d-inline-block font-weight-bold px-1 bg-white">Marina:</div>
                <div class="form-row">
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>Carrier: </strong> <span class="small">{{!empty($lead->marinaCarrier->name)?$lead->marinaCarrier->name:"N/A"}}</span>
                    </div>
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>Month: </strong> <span class="small">{{!empty($lead->m_ren_month)?$lead->m_ren_month:"N/A"}}</span>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>Expiring Premium: </strong> <span class="small">{{!empty($lead->m_expiry_premium)?'$'.formatUSNumber($lead->m_expiry_premium):"N/A"}}</span>
                    </div>
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>Policy Renewal Date: </strong> <span class="small">{{!empty($lead->m_policy_renewal_date)?date('m/d/Y',strtotime($lead->m_policy_renewal_date)):"N/A"}}</span>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>Hurricane Deductible:</strong> <span class="small">{{!empty($lead->m_hurricane_deductible)?$lead->m_hurricane_deductible."%":"N/A"}}</span>
                    </div>
                    <div class="form-group col-12 col-md-6 mb-2">
                        <strong>All Other Perils Deductible:</strong> <span class="small">{{!empty($lead->m_all_other_perils)?$lead->m_all_other_perils:"No"}}</span>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-12 col-md-12 mb-2">
                        <strong>Marina Notes:</strong> <span class="small">{{!empty($lead->m_insurance_coverage)?$lead->m_insurance_coverage:"N/A"}}</span>
                    </div>
                </div>
            </div>
            <?php $iloop = 0; ?>
            @foreach($additonalPolicy as $adpolicy)
                <div class="p-2 mt-4 pt-3 pb-0 mx-2 rounded border position-relative">
                    <div class="section-head position-absolute m-0 p-0 z-5 d-inline-block font-weight-bold px-1 bg-white">Additional Policy {{$iloop + 1}}:</div>
                    <div class="form-row">
                        <div class="form-group col-12 col-md-6 mb-2">
                            <strong>Policy Type: </strong> <span class="small">{{!empty($adpolicy->policy_type)?$adpolicy->policy_type:"N/A"}}</span>
                        </div>
                        <div class="form-group col-12 col-md-6 mb-2">
                            <strong>Carrier: </strong> <span class="small">{{!empty($adpolicy->listCarrier->name)?$adpolicy->listCarrier->name:"N/A"}}</span>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-12 col-md-6 mb-2">
                            <strong>Expiring Premium: </strong> <span class="small">{{!empty($adpolicy->expiry_premium)?'$'.formatUSNumber($adpolicy->expiry_premium):"N/A"}}</span>
                        </div>
                        <div class="form-group col-12 col-md-6 mb-2">
                            <strong>Policy Renewal Date: </strong> <span class="small">{{!empty($adpolicy->policy_renewal_date)?date('m/d/Y',strtotime($adpolicy->policy_renewal_date)):"N/A"}}</span>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-12 col-md-6 mb-2">
                            <strong>Hurricane Deductible:</strong> <span class="small">{{!empty($adpolicy->hurricane_deductible)?$adpolicy->hurricane_deductible."%":"N/A"}}</span>
                        </div>
                        <div class="form-group col-12 col-md-6 mb-2">
                            <strong>All Other Perils Deductible:</strong> <span class="small">{{!empty($adpolicy->all_other_perils)?$adpolicy->all_other_perils:"No"}}</span>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-12 col-md-12 mb-2">
                            <strong>Notes:</strong> <span class="small">{{!empty($adpolicy->insurance_coverage)?$adpolicy->insurance_coverage:"N/A"}}</span>
                        </div>
                    </div>
                </div>
                <?php $iloop++; ?>
            @endforeach
            <div class="my-3">
                <div class="form-row px-2">
                    <div class="form-group col-12 mb-0">
                        <?php
                            $total_premium_sum = totalPremiumCalculationLeadWise($lead, $additonalPolicy);
                        ?>
                        <strong  class="text-success mb-0">Total Premium: </strong> <span id="total_premium_sum" class="text-base">{{!empty($total_premium_sum)?"$".$total_premium_sum:"N/A"}}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script src="{{ asset('js/custom-helper.js') }}"></script>
<script>
    const total_premium_ele = document.getElementById('total_premium_sum');

    // Remove '$' and convert to number
    const total_premium = parseFloat(total_premium_ele.textContent.replace(/[^0-9.]/g, '')) || 0;
    total_premium_ele.textContent = assign_value_numberformat(total_premium, 5);

function pricepersquarefootcalculation(total_square,toal_insured) {
    if(total_square == '' || toal_insured == ''){
        $("#price_per_sqft").text('N/A');
    }
    else{
        total_square = parseFloat(total_square);
        toal_insured = parseFloat(toal_insured);

        const price_ppt = (toal_insured/total_square).toFixed(2);

        $("#price_per_sqft").text('$'+formatUSNumberJs(price_ppt));
    }

}
function priceperunitcalculation(expiry,total) {
    if(expiry == '' || total == ''){
        $("#gl_price_per_unit").text('N/A');
    }
    else{
        expiry = parseFloat(expiry);
        total = parseFloat(total);

        const price = (expiry/total).toFixed(2);

        $("#gl_price_per_unit").text('$'+formatUSNumberJs(price));
    }

}

pricepersquarefootcalculation("{{ $lead->total_square_footage}}","{{ $lead->insured_amount}}");
priceperunitcalculation("{{ $lead->gl_expiry_premium}}","{{ $lead->unit_count}}");


</script>
@endpush