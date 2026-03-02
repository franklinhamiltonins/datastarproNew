<div class="loaderarea">
    <div class="tab-area d-flex align-items-center mb-3 border-bottom">
        @foreach($previousLeadDateList as $key => $lead_date)
            <span class="sm text-info fs-5 px-3 py-2 border-right cursor-pointer font-weight-bold renewal_date_btn
             @if($key == 0) {{'bg-primary text-white'}} @endif">
                {{date("m/d/Y",strtotime($lead_date->renewal_date))}}
            </span>
        @endforeach
    </div>
    <div class="px-3">
        <!-- Business Section -->
        <div class="section mb-3">
            <h5
                class="section-title text-primary h6 mb-1 pb-1 border-bottom border-primary"
                style="color: #1f78a0; border-color: #1f78a0;"
            >
                Bussiness
            </h5>
            <div class="card-body lead-update p-0 pt-1">
                @if(isset($previousLead))
                    <!-- Current Client Status -->
                    <div class="d-flex">
                        <p class="font-weight-bold mb-2" id="name_previousvalue">
                            {{ !empty($previousLead->name) ? $previousLead->name : "" }}
                        </p>
                        @if($previousLead && $previousLead->is_client == 1)
                            <div class="form-group mb-2 ml-2" id="current_client_area_previousvalue">
                                <p class="font-weight-bold text-success mb-0">Current Client</p>
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Type, Year Built, and Unit Count -->
                <div class="form-row m-0 mb-2">
                    <div class="form-group col-12 col-md-4 mb-0 py-1 px-0 border-top border-bottom">
                        <strong>Type:</strong>
                        <span class="small" id="type_previousvalue">
                            {{ !empty($previousLead->type) ? $previousLead->type : "N/A" }}
                        </span>
                    </div>
                    <div class="form-group col-12 col-md-3 mb-0 py-1 px-0 border-top border-bottom">
                        <strong>Year Built:</strong>
                        <span class="small" id="creation_date_previousvalue">
                            {{ !empty($previousLead->creation_date)
                                ? date('m/d/Y',strtotime($previousLead->creation_date))
                                : "N/A"
                            }}
                        </span>
                    </div>
                    <div class="form-group col-12 col-lg-5 mb-0 py-1 px-0 border-top border-bottom">
                        <strong>Unit Count:</strong>
                        <span class="small" id="unit_count_previousvalue">
                            {{ !empty($previousLead->unit_count) ? $previousLead->unit_count : "N/A" }}
                        </span>
                    </div>
                </div>

                <!-- Address -->
                <div class="form-row">
                    <div class="form-group col-12 px-2 mb-1">
                        <strong>Business Address 1:</strong>
                        <span class="small" id="address1_previousvalue">
                            {{ !empty($previousLead->address1) ? $previousLead->address1 : "N/A" }}
                        </span>
                    </div>
                    <div class="form-group col-12 px-2 mb-1">
                        <strong>Business Address 2:</strong>
                        <span class="small" id="address2_previousvalue">
                            {{ !empty($previousLead->address2) ? $previousLead->address2 : "N/A" }}
                        </span>
                    </div>
                </div>

                <!-- Location Details -->
                <div class="form-row">
                    <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
                        <strong>City:</strong>
                        <span class="small" id="city_previousvalue">
                            {{ !empty($previousLead->city) ? $previousLead->city : "N/A" }}
                        </span>
                    </div>
                    <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
                        <strong>County:</strong>
                        <span class="small" id="county_previousvalue">
                            {{ !empty($previousLead->county) ? $previousLead->county : "N/A" }}
                        </span>
                    </div>
                    <div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
                        <strong>Coastal / Non Coastal:</strong>
                        <span class="small" id="coastal_previousvalue">
                            {{ !empty($previousLead->coastal) ? 'Coastal' : 'Non Coastal' }}
                        </span>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
                        <strong>State:</strong>
                        <span class="small" id="state_previousvalue">
                            {{ !empty($previousLead->state) ? $previousLead->state : "N/A" }}
                        </span>
                    </div>
                    <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
                        <strong>Zip:</strong>
                        <span class="small" id="zip_previousvalue">
                            {{ !empty($previousLead->zip) ? $previousLead->zip : "N/A" }}
                        </span>
                    </div>
                    <div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
                        <strong>Total Square Footage:</strong>
                        <span class="small" id="total_square_footage_previousvalue">
                            {{ !empty($previousLead->total_square_footage)
                                ? $previousLead->total_square_footage
                                : "N/A"
                            }}
                        </span>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
                        <strong>Total insured value:</strong>
                        <span class="small" id="business_tiv_previousvalue">
                            {{ !empty($previousLead->business_tiv)
                                ? '$'.$previousLead->business_tiv
                                : "N/A"
                            }}
                        </span>
                    </div>
                    <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
                        <strong>Appraiser Name:</strong>
                        <span class="small" id="appraisal_name_previousvalue">
                            {{ !empty($previousLead->appraisal_name)
                                ? $previousLead->appraisal_name
                                : "N/A"
                            }}
                        </span>
                    </div>
                    <div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
                        <strong>Appraisal Company:</strong>
                        <span class="small" id="appraisal_company_previousvalue">
                            {{!empty($previousLead->appraisal_company)
                                ? $previousLead->appraisal_company
                                : "N/A"
                            }}
                        </span>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
                        <strong>Appraisal Date:</strong>
                        <span class="small" id="appraisal_date_previousvalue">
                            {{!empty($previousLead->appraisal_date)
                                ? date('m/d/Y',strtotime($previousLead->appraisal_date))
                                : "N/A"
                            }}
                        </span>
                    </div>
                    <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
                        <strong>Flood Zone:</strong>
                        <span class="small" id="ins_flood_previousvalue">
                            {{ !empty($previousLead->ins_flood)
                                ? $previousLead->ins_flood
                                : "No"
                            }}
                        </span>
                    </div>
                    <div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
                        <strong>Property Floors:</strong>
                        <span class="small" id="prop_floor_previousvalue">
                            {{ !empty($previousLead->prop_floor)
                                ? $previousLead->prop_floor
                                : "N/A"
                            }}
                        </span>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
                        <strong>Pool:</strong>
                        <span class="small" id="pool_previousvalue">
                            {{ !empty($previousLead->pool) ? $previousLead->pool : "No" }}
                        </span>
                    </div>
                    <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
                        <strong>Lakes:</strong>
                        <span class="small" id="lakes_previousvalue">
                            {{ !empty($previousLead->lakes) ? $previousLead->lakes : "No" }}
                        </span>
                    </div>
                    <div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
                        <strong>Clubhouse:</strong>
                        <span class="small" id="clubhouse_previousvalue">
                            {{ !empty($previousLead->clubhouse)
                                ? $previousLead->clubhouse
                                : "No"
                            }}
                        </span>
                    </div>
                </div>

                <!-- Property Details + roof -->
                <div class="form-row">
                    <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
                        <strong>Tennis/Basketball Court:</strong>
                        <span class="small" id="tennis_basketball_previousvalue">
                            {{ !empty($previousLead->tennis_basketball)
                                ? $previousLead->tennis_basketball
                                : "No"
                            }}
                        </span>
                    </div>
                    <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
                        <strong>ISO:</strong>
                        <span class="small" id="iso_previousvalue">
                            {{ !empty($previousLead->iso) ? $previousLead->iso : "N/A" }}
                        </span>
                    </div>
                    <div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
                        <strong>Lead Source:</strong>
                        <span class="small" id="lead_source_previousvalue">
                            {{ !empty($previousLead->leadSource->name)
                                ? $previousLead->leadSource->name
                                : "N/A"
                            }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Community Section -->
        <div class="section mb-3">
            <h5
                class="section-title text-primary h6 mb-1 pb-1 border-bottom border-primary"
                style="color: #1f78a0; border-color: #1f78a0;"
            >
                Community
            </h5>
            <div class="card-body lead-update p-0 pt-2">
                <div class="form-row">
                    <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
                        <strong> Wind Mitigation Date:</strong>
                        <span class="small" id="wind_mitigation_date_previousvalue">
                            {{!empty($previousLead->wind_mitigation_date)
                                ? $previousLead->wind_mitigation_date
                                : "N/A"
                            }}
                        </span>
                    </div>
                    <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
                        <strong>Roof Year:</strong>
                        <span class="small" id="roof_year_previousvalue">
                            {{ !empty($previousLead->roof_year)
                                ? $previousLead->roof_year
                                : "N/A"
                            }}
                        </span>
                    </div>
                    <div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
                        <strong>Roof Covering:</strong>
                        <span class="small" id="roof_covering_previousvalue">
                            {{ !empty($previousLead->roof_covering)
                                ? $previousLead->roof_covering
                                : "N/A"
                            }}
                        </span>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
                        <strong>Roof Connection:</strong>
                        <span class="small" id="roof_connection_previousvalue">
                            {{ !empty($previousLead->roof_connection)
                                ? $previousLead->roof_connection
                                : "N/A"
                            }}
                        </span>
                    </div>
                    <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
                        <strong>Roof Geometry:</strong>
                        <span class="small" id="roof_geom_previousvalue">
                            {{ !empty($previousLead->roof_geom)
                                ? $previousLead->roof_geom
                                : "N/A"
                            }}
                        </span>
                    </div>
                    <div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
                        <strong> SWR:</strong>
                        <span class="small" id="secondary_water_insurance_previousvalue">
                            {{!empty($previousLead->secondary_water_insurance)
                                ? $previousLead->secondary_water_insurance
                                : "No"
                            }}
                        </span>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
                        <strong>Opening Protection:</strong>
                        <span class="small" id="opening_protection_previousvalue">
                            {{!empty($previousLead->opening_protection)
                                ? $previousLead->opening_protection
                                : "No"
                            }}
                        </span>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-12 col-md-12 col-lg-12 mb-1 px-2">
                        <strong>Report Notes:</strong>
                        <span class="small longtextarea" id="other_community_info_previousvalue">
                            {{ !empty($previousLead->other_community_info)
                                ? $previousLead->other_community_info
                                : "N/A"
                            }}
                        </span>
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
                            <strong>Carrier: </strong>
                            <span class="small" id="ins_prop_carrier_previousvalue">
                                {{!empty($previousLead->ins_prop_carrier)
                                    ? $previousLead->ins_prop_carrier
                                    : "N/A"
                                }}
                            </span>
                        </div>
                        <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
                            <strong>Month: </strong>
                            <span class="small" id="renewal_carrier_month_previousvalue">
                                {{!empty($previousLead->renewal_carrier_month)
                                    ? $previousLead->renewal_carrier_month
                                    : "N/A"
                                }}
                            </span>
                        </div>
                        <div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
                            <strong>Expiring Premium:</strong>
                            <span class="small" id="premium_previousvalue">
                                {{ !empty($previousLead->premium)
                                    ? '$'. $previousLead->premium
                                    : "N/A"
                                }}
                            </span>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
                            <strong>Expiring Premium Year:</strong>
                            <span class="small" id="premium_year_previousvalue">
                                {{ !empty($previousLead->premium_year)
                                    ? $previousLead->premium_year
                                    : "N/A"
                                }}
                            </span>
                        </div>
                        <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
                            <strong>Total insured value:</strong>
                            <span class="small" id="insured_amount_previousvalue">
                                {{ !empty($previousLead->insured_amount)
                                    ? '$'.$previousLead->insured_amount
                                    : "N/A"
                                }}
                            </span>
                        </div>
                        <div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
                            <strong>T.I.V. – Year:</strong>
                            <span class="small" id="insured_year_previousvalue">
                                {{ !empty($previousLead->insured_year)
                                    ? '$'.$previousLead->insured_year
                                    : "N/A"
                                }}
                            </span>
                        </div>
                    </div>
                    <div class="form-row">
                        <?php
                            $price_per_sqft = 0;
                            if(!empty($previousLead->insured_amount)
                                && !empty($previousLead->total_square_footage)
                            ){
                                $price_per_sqft = round(
                                    ($previousLead->insured_amount / $previousLead->total_square_footage),
                                    2
                                );
                            }
                        ?>
                        <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
                            <strong>Price Per SqFt:</strong>
                            <span class="small" id="price_per_sqft_previousvalue">
                                {{!empty($price_per_sqft) ? $price_per_sqft : "N/A"}}
                            </span>
                        </div>
                        <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
                            <strong>Policy Renew:</strong>
                            <span class="small" id="policy_renewal_date_previousvalue">
                                {{!empty($previousLead->policy_renewal_date)
                                    ? date('m/d/Y',strtotime($previousLead->policy_renewal_date))
                                    : "N/A"
                                }}
                            </span>
                        </div>
                        <div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
                            <strong>Incumbent Agency:</strong>
                            <span class="small" id="incumbent_agency_previousvalue">
                                {{!empty($previousLead->incumbent_agency)
                                    ? $previousLead->incumbent_agency
                                    : "N/A"
                                }}
                            </span>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
                            <strong>Incumbent Agent:</strong>
                            <span class="small" id="incumbent_agent_previousvalue">
                                {{!empty($previousLead->incumbent_agent)
                                    ? $previousLead->incumbent_agent
                                    : "N/A"
                                }}
                            </span>
                        </div>
                        <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
                            <strong>Rating:</strong>
                            <span class="small" id="rating_previousvalue">
                                {{!empty($previousLead->rating) ? $previousLead->rating : "N/A"}}
                            </span>
                        </div>
                        <div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
                            <strong> Sinkhole:</strong>
                            <span class="small" id="skin_hole_previousvalue">
                                {{!empty($previousLead->skin_hole) ? $previousLead->skin_hole : "No"}}
                            </span>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
                            <strong> Hurricane Deductible:</strong>
                            <span class="small" id="hurricane_deductible_previousvalue">
                                {{!empty($previousLead->hurricane_deductible)
                                    ? $previousLead->hurricane_deductible."%"
                                    : "N/A"
                                }}
                            </span>
                        </div>
                        <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
                            <strong>Hurricane Deductible (Per Occ/Year):</strong>
                            <span class="small" id="hurricane_deductible_occurrence_previousvalue">
                                {{!empty($previousLead->hurricane_deductible_occurrence)
                                    ? $previousLead->hurricane_deductible_occurrence
                                    : "N/A"
                                }}
                            </span>
                        </div>
                        <div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
                            <strong>All other Perils:</strong>
                            <span class="small" id="all_other_perils_previousvalue">
                                {{!empty($previousLead->all_other_perils)
                                    ? '$'.$previousLead->all_other_perils
                                    : "N/A"
                                }}
                            </span>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
                            <strong>Ordinance of Law:</strong>
                            <span class="small" id="ordinance_of_law_previousvalue">
                                {{!empty($previousLead->ordinance_of_law)
                                    ? $previousLead->ordinance_of_law."%"
                                    : "N/A"
                                }}
                            </span>
                        </div>
                        <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
                            <strong>T.I.V. Matches Appraisal:</strong>
                            <span class="small" id="tiv_matches_appraisal_previousvalue">
                                {{!empty($previousLead->tiv_matches_appraisal)
                                    ? $previousLead->tiv_matches_appraisal
                                    : "No"
                                }}
                            </span>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-12 col-md-12 col-lg-12 mb-1 px-2">
                            <strong>Property Notes:</strong>
                            <span class="small longtextarea" id="property_insurance_coverage_previousvalue">
                                {{ !empty($previousLead->property_insurance_coverage)
                                    ? $previousLead->property_insurance_coverage
                                    : "N/A"
                                }}
                            </span>
                        </div>
                    </div>
                </div>
                <!-- General Liability  -->
                <div class="p-2 mt-4 pt-3 pb-0 mx-0 rounded border position-relative general_liability">
                    <div class="section-head position-absolute m-0 p-0 z-5 d-inline-block font-weight-bold px-1 bg-white">
                        General Liability :
                    </div>
                    <div class="form-row">
                        <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
                            <strong>Carrier:</strong>
                            <span class="small" id="general_liability_previousvalue">
                                {{ !empty($previousLead->general_liability)
                                    ? $previousLead->general_liability
                                    : "N/A"
                                }}
                            </span>
                        </div>
                        <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
                            <strong>Month:</strong>
                            <span class="small" id="GL_ren_month_previousvalue">
                                {{ !empty($previousLead->GL_ren_month)
                                    ? $previousLead->GL_ren_month
                                    : "N/A"
                                }}
                            </span>
                        </div>
                        <div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
                            <strong>Expiring Premium:</strong>
                            <span class="small" id="gl_expiry_premium_previousvalue">
                                {{ !empty($previousLead->gl_expiry_premium)
                                    ? '$' . formatUSNumber($previousLead->gl_expiry_premium, 2)
                                    : "N/A"
                                }}
                            </span>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
                            <strong>Policy Renewal Date:</strong>
                            <span class="small" id="gl_policy_renewal_date_previousvalue">
                                {{ !empty($previousLead->gl_policy_renewal_date)
                                    ? date('m/d/Y',strtotime($previousLead->gl_policy_renewal_date))
                                    : "N/A"
                                }}
                            </span>
                        </div>
                        <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
                            <strong>Rating:</strong>
                            <span class="small" id="gl_rating_previousvalue">
                                {{ !empty($previousLead->gl_rating)
                                    ? $previousLead->gl_rating
                                    : "N/A"
                                }}
                            </span>
                        </div>
                        <?php
                            $gl_price_per_unit = 0;
                            if(!empty($previousLead->gl_expiry_premium)
                                && !empty($previousLead->unit_count)
                            ){
                                $gl_price_per_unit = round(
                                    ($previousLead->gl_expiry_premium / $previousLead->unit_count),
                                    2
                                );
                            }
                        ?>
                        <div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
                            <strong>Price Per Unit:</strong>
                            <span class="small" id="gl_price_per_unit_previousvalue">
                                {{ !empty($gl_price_per_unit) ? $gl_price_per_unit : "N/A" }}
                            </span>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
                            <strong>Exclusions:</strong>
                            <span class="small" id="gl_exclusions_previousvalue">
                                {{ !empty($previousLead->gl_exclusions)
                                    ? $previousLead->gl_exclusions
                                    : "N/A"
                                }}
                            </span>
                        </div>
                        <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
                            <strong>Other Exclusions:</strong>
                            <span class="small" id="gl_other_exclusions_previousvalue">
                                {{ !empty($previousLead->gl_other_exclusions)
                                    ? $previousLead->gl_other_exclusions
                                    : "N/A"
                                }}
                            </span>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-12 col-md-12 col-lg-12 mb-2 px-2">
                            <strong>General Liability Notes:</strong>
                            <span class="small longtextarea" id="gl_insurance_coverage_previousvalue">
                                {{ !empty($previousLead->gl_insurance_coverage)
                                    ? $previousLead->gl_insurance_coverage
                                    : "N/A"
                                }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="gap"></div>
                <!-- Crime Insurance -->
                <div class="p-2 mt-4 pt-3 pb-0 mx-0 rounded border position-relative">
                    <div class="section-head position-absolute m-0 p-0 z-5 d-inline-block font-weight-bold px-1 bg-white">
                        Crime Insurance :
                    </div>
                    <div class="form-row">
                        <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
                            <strong>Carrier:</strong>
                            <span class="small" id="crime_insurance_previousvalue">
                                {{ !empty($previousLead->crime_insurance)
                                    ? $previousLead->crime_insurance
                                    : "N/A"
                                }}
                            </span>
                        </div>
                        <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
                            <strong>Month:</strong>
                            <span class="small" id="CI_ren_month_previousvalue">
                                {{ !empty($previousLead->CI_ren_month)
                                    ? $previousLead->CI_ren_month
                                    : "N/A"
                                }}
                            </span>
                        </div>
                        <div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
                            <strong>Expiring Premium:</strong>
                            <span class="small" id="ci_expiry_premium_previousvalue">
                                {{ !empty($previousLead->ci_expiry_premium)
                                    ? '$' . formatUSNumber($previousLead->ci_expiry_premium, 2)
                                    : "N/A"
                                }}
                            </span>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
                            <strong>Policy Renewal Date:</strong>
                            <span class="small" id="ci_policy_renewal_date_previousvalue">
                                {{ !empty($previousLead->ci_policy_renewal_date)
                                    ? date('m/d/Y',strtotime($previousLead->ci_policy_renewal_date))
                                    : "N/A"
                                }}
                            </span>
                        </div>
                        <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
                            <strong>Rating:</strong>
                            <span class="small" id="ci_rating_previousvalue">
                                {{ !empty($previousLead->ci_rating)
                                    ? $previousLead->ci_rating
                                    : "N/A"
                                }}
                            </span>
                        </div>
                        <div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
                            <strong>Employee Theft:</strong>
                            <span class="small" id="employee_theft_previousvalue">
                                {{ !empty($previousLead->employee_theft)
                                    ? $previousLead->employee_theft
                                    : "N/A"
                                }}
                            </span>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
                            <strong>Operating Reserves:</strong>
                            <span class="small" id="operating_reserves_previousvalue">
                                {{ !empty($previousLead->operating_reserves)
                                    ? $previousLead->operating_reserves
                                    : "N/A"
                                }}
                            </span>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-12 col-md-12 col-lg-12 mb-1 px-2">
                            <strong>Crime Insurance Notes:</strong>
                            <span class="small longtextarea" id="ci_insurance_coverage_previousvalue">
                                {{ !empty($previousLead->ci_insurance_coverage)
                                    ? $previousLead->ci_insurance_coverage
                                    : "N/A"
                                }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="print_gap"></div>
                <!-- Directors & Officers -->
                <div class="p-2 mt-4 pt-3 pb-0 mx-0 rounded border position-relative">
                    <div class="section-head position-absolute m-0 p-0 z-5 d-inline-block font-weight-bold px-1 bg-white">
                        Directors & Officers :
                    </div>
                    <div class="form-row">
                        <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
                            <strong>Carrier:</strong>
                            <span class="small" id="directors_officers_previousvalue">
                                {{ !empty($previousLead->directors_officers)
                                    ? $previousLead->directors_officers
                                    : "N/A"
                                }}
                            </span>
                        </div>
                        <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
                            <strong>Month:</strong>
                            <span class="small" id="DO_ren_month_previousvalue">
                                {{ !empty($previousLead->DO_ren_month)
                                    ? $previousLead->DO_ren_month
                                    : "N/A"
                                }}
                            </span>
                        </div>
                        <div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
                            <strong>Expiring Premium:</strong>
                            <span class="small" id="do_expiry_premium_previousvalue">
                                {{ !empty($previousLead->do_expiry_premium)
                                    ? '$' . formatUSNumber($previousLead->do_expiry_premium, 2)
                                    : "N/A"
                                }}
                            </span>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
                            <strong>Policy Renewal Date:</strong>
                            <span class="small" id="do_policy_renewal_date_previousvalue">
                                {{ !empty($previousLead->do_policy_renewal_date)
                                    ? date('m/d/Y',strtotime($previousLead->do_policy_renewal_date))
                                    : "N/A"
                                }}
                            </span>
                        </div>
                        <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
                            <strong>Rating:</strong>
                            <span class="small" id="do_rating_previousvalue">
                                {{ !empty($previousLead->do_rating)
                                    ? $previousLead->do_rating
                                    : "N/A"
                                }}
                            </span>
                        </div>
                        <div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
                            <strong>Claims Made:</strong>
                            <span class="small" id="claims_made_previousvalue">
                                {{ !empty($previousLead->claims_made)
                                    ? $previousLead->claims_made
                                    : "No"
                                }}
                            </span>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
                            <strong>Pending Litigation:</strong>
                            <span class="small" id="pending_litigation_previousvalue">
                                {{ !empty($previousLead->pending_litigation)
                                    ? $previousLead->pending_litigation
                                    : "No"
                                }}
                            </span>
                        </div>
                        <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
                            <strong>Litigation Date:</strong>
                            <span class="small" id="litigation_date_previousvalue">
                                {{ !empty($previousLead->litigation_date)
                                    ? date('m/d/Y',strtotime($previousLead->litigation_date))
                                    : "N/A"
                                }}
                            </span>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-12 col-md-12 col-lg-12 mb-1 px-2">
                            <strong>Directors & Officers Notes:</strong>
                            <span class="small longtextarea" id="do_insurance_coverage_previousvalue">
                                {{ !empty($previousLead->do_insurance_coverage)
                                    ? $previousLead->do_insurance_coverage
                                    : "N/A"
                                }}
                            </span>
                        </div>
                    </div>
                </div>
                <!-- Umbrella -->
                <div class="p-2 mt-4 mx-0 pb-0 pt-3 rounded border position-relative">
                    <div class="section-head position-absolute m-0 p-0 z-5 d-inline-block font-weight-bold px-1 bg-white">
                        Umbrella :
                    </div>
                    <div class="form-row">
                        <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
                            <strong>Carrier:</strong>
                            <span class="small" id="umbrella_previousvalue">
                                {{ !empty($previousLead->umbrella)
                                    ? $previousLead->umbrella
                                    : "N/A"
                                }}
                            </span>
                        </div>
                        <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
                            <strong>Month:</strong>
                            <span class="small" id="U_ren_month_previousvalue">
                                {{ !empty($previousLead->U_ren_month)
                                    ? $previousLead->U_ren_month
                                    : "N/A"
                                }}
                            </span>
                        </div>
                        <div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
                            <strong>Expiring Premium:</strong>
                            <span class="small" id="umbrella_expiry_premium_previousvalue">
                                {{ !empty($previousLead->umbrella_expiry_premium)
                                    ? '$' . formatUSNumber($previousLead->umbrella_expiry_premium, 2)
                                    : "N/A"
                                }}
                            </span>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
                            <strong>Policy Renewal Date:</strong>
                            <span class="small" id="umbrella_policy_renewal_date_previousvalue">
                                {{ !empty($previousLead->umbrella_policy_renewal_date)
                                    ? date('m/d/Y',strtotime($previousLead->umbrella_policy_renewal_date))
                                    : "N/A"
                                }}
                            </span>
                        </div>
                        <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
                            <strong>Rating:</strong>
                            <span class="small" id="umbrella_rating_previousvalue">
                                {{ !empty($previousLead->umbrella_rating)
                                    ? $previousLead->umbrella_rating
                                    : "N/A"
                                }}
                            </span>
                        </div>
                        <div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
                            <strong>Exclusions:</strong>
                            <span class="small" id="umbrella_exclusions_previousvalue">
                                {{ !empty($previousLead->umbrella_exclusions)
                                    ? $previousLead->umbrella_exclusions
                                    : "N/A"
                                }}
                            </span>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
                            <strong>Other Exclusions:</strong>
                            <span class="small" id="umbrella_other_exclusions_previousvalue">
                                {{ !empty($previousLead->umbrella_other_exclusions)
                                    ? $previousLead->umbrella_other_exclusions
                                    : "N/A"
                                }}
                            </span>
                        </div>
                        <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
                            <strong>Correct Underlying:</strong>
                            <span class="small" id="correct_underlying_previousvalue">
                                {{ !empty($previousLead->correct_underlying)
                                    ? $previousLead->correct_underlying
                                    : "No"
                                }}
                            </span>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-12 col-md-12 col-lg-12 mb-1 px-2">
                            <strong>Umbrella Notes:</strong>
                            <span class="small longtextarea" id="u_insurance_coverage_previousvalue">
                                {{ !empty($previousLead->u_insurance_coverage)
                                    ? $previousLead->u_insurance_coverage
                                    : "N/A"
                                }}
                            </span>
                        </div>
                    </div>
                </div>
                <!-- Workers Compensation  -->
                <div class="p-2 mt-4 pt-3 pb-0 mx-0 rounded border position-relative">
                    <div class="section-head position-absolute m-0 p-0 z-5 d-inline-block font-weight-bold px-1 bg-white">
                        Workers Compensation :
                    </div>
                    <div class="form-row">
                        <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
                            <strong>Carrier:</strong>
                            <span class="small" id="workers_compensation_previousvalue">
                                {{ !empty($previousLead->workers_compensation)
                                    ? $previousLead->workers_compensation
                                    : "N/A"
                                }}
                            </span>
                        </div>
                        <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
                            <strong>Month:</strong>
                            <span class="small" id="WC_ren_month_previousvalue">
                                {{ !empty($previousLead->WC_ren_month)
                                    ? $previousLead->WC_ren_month
                                    : "NA"
                                }}
                            </span>
                        </div>
                        <div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
                            <strong>Expiring Premium:</strong>
                            <span class="small" id="wc_expiry_premium_previousvalue">
                                {{ !empty($previousLead->wc_expiry_premium)
                                    ? '$' . formatUSNumber($previousLead->wc_expiry_premium, 2)
                                    : "N/A"
                                }}
                            </span>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
                            <strong>Policy Renewal Date:</strong>
                            <span class="small" id="wc_policy_renewal_date_previousvalue">
                                {{ !empty($previousLead->wc_policy_renewal_date)
                                    ? date('m/d/Y',strtotime($previousLead->wc_policy_renewal_date))
                                    : "N/A"
                                }}
                            </span>
                        </div>
                        <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
                            <strong>Rating:</strong>
                            <span class="small" id="wc_rating_previousvalue">
                                {{ !empty($previousLead->wc_rating)
                                    ? $previousLead->wc_rating
                                    : "N/A"
                                }}
                            </span>
                        </div>
                        <div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
                            <strong>Employee Count:</strong>
                            <span class="small" id="employee_count_previousvalue">
                                {{ !empty($previousLead->employee_count)
                                    ? $previousLead->employee_count
                                    : "N/A"
                                }}
                            </span>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
                            <strong>Employee Payroll:</strong>
                            <span class="small" id="employee_payroll_previousvalue">
                                {{ !empty($previousLead->employee_payroll)
                                    ? '$' . formatUSNumber($previousLead->employee_payroll)
                                    : "N/A"
                                }}
                            </span>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-12 col-md-12 col-lg-12 mb-1 px-2">
                            <strong>Workers Compensation Notes:</strong>
                            <span class="small longtextarea" id="wc_insurance_coverage_previousvalue">
                                {{ !empty($previousLead->wc_insurance_coverage)
                                    ? $previousLead->wc_insurance_coverage
                                    : "N/A"
                                }}
                            </span>
                        </div>
                    </div>
                </div>
                <!-- Flood -->
                <div class="p-2 mt-4 pt-3 pb-0 mx-0 rounded border position-relative">
                    <div class="section-head position-absolute m-0 p-0 z-5 d-inline-block font-weight-bold px-1 bg-white">
                        Flood :
                    </div>
                    <div class="form-row">
                        <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
                            <strong>Carrier:</strong>
                            <span class="small" id="flood_previousvalue">
                                {{ !empty($previousLead->flood) ? $previousLead->flood : "N/A" }}
                            </span>
                        </div>
                        <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
                            <strong>Month:</strong>
                            <span class="small" id="F_ren_month_previousvalue">
                                {{ !empty($previousLead->F_ren_month) ? $previousLead->F_ren_month : "N/A" }}
                            </span>
                        </div>
                        <div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
                            <strong>Expiring Premium:</strong>
                            <span class="small" id="flood_expiry_premium_previousvalue">
                                {{ !empty($previousLead->flood_expiry_premium)
                                    ? '$' . formatUSNumber($previousLead->flood_expiry_premium)
                                    : "N/A"
                                }}
                            </span>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
                            <strong>Policy Renewal Date:</strong>
                            <span class="small" id="flood_policy_renewal_date_previousvalue">
                                {{ !empty($previousLead->flood_policy_renewal_date)
                                    ? date('m/d/Y',strtotime($previousLead->flood_policy_renewal_date))
                                    : "N/A"
                                }}
                            </span>
                        </div>
                        <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
                            <strong>Rating:</strong>
                            <span class="small" id="flood_rating_previousvalue">
                                {{ !empty($previousLead->flood_rating)
                                    ? $previousLead->flood_rating
                                    : "N/A"
                                }}
                            </span>
                        </div>
                        <div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
                            <strong>Elevation Certificate:</strong>
                            <span class="small" id="elevation_certificate_previousvalue">
                                {{ !empty($previousLead->elevation_certificate)
                                    ? $previousLead->elevation_certificate
                                    : "No"
                                }}
                            </span>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
                            <strong>Loma Letter:</strong>
                            <span class="small" id="loma_letter_previousvalue">
                                {{ !empty($previousLead->loma_letter)
                                    ? $previousLead->loma_letter
                                    : "No"
                                }}
                            </span>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-12 col-md-12 col-lg-12 mb-1 px-2">
                            <strong>Flood Notes:</strong>
                            <span class="small longtextarea" id="f_insurance_coverage_previousvalue">
                                {{ !empty($previousLead->f_insurance_coverage)
                                    ? $previousLead->f_insurance_coverage
                                    : "N/A"
                                }}
                            </span>
                        </div>
                    </div>
                </div>
                <!--  Difference In Conditions -->
                <div class="p-2 mt-4 pt-3 pb-0 mx-0 rounded border position-relative">
                    <div class="section-head position-absolute m-0 p-0 z-5 d-inline-block font-weight-bold px-1 bg-white">
                        Difference In Conditions :
                    </div>
                    <div class="form-row">
                        <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
                            <strong>Carrier:</strong>
                            <span class="small" id="difference_in_condition_previousvalue">
                                {{ !empty($previousLead->difference_in_condition)
                                    ? $previousLead->difference_in_condition
                                    : "N/A"
                                }}
                            </span>
                        </div>
                        <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
                            <strong>Month:</strong>
                            <span class="small" id="dic_ren_month_previousvalue">
                                {{ !empty($previousLead->dic_ren_month)
                                    ? $previousLead->dic_ren_month
                                    : "N/A"
                                }}
                            </span>
                        </div>
                        <div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
                            <strong>Expiring Premium:</strong>
                            <span class="small" id="dic_expiry_premium_previousvalue">
                                {{ !empty($previousLead->dic_expiry_premium)
                                    ? '$' . formatUSNumber($previousLead->dic_expiry_premium)
                                    : "N/A"
                                }}
                            </span>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
                            <strong>Policy Renewal Date:</strong>
                            <span class="small" id="dic_policy_renewal_date_previousvalue">
                                {{ !empty($previousLead->dic_policy_renewal_date)
                                    ? date('m/d/Y',strtotime($previousLead->dic_policy_renewal_date))
                                    : "N/A"
                                }}
                            </span>
                        </div>
                        <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
                            <strong>Hurricane Deductible:</strong>
                            <span class="small" id="dic_hurricane_deductible_previousvalue">
                                {{ !empty($previousLead->dic_hurricane_deductible)
                                    ? $previousLead->dic_hurricane_deductible
                                    : "N/A"
                                }}
                            </span>
                        </div>
                        <div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
                            <strong>All Other Perils Deductible:</strong>
                            <span class="small" id="dic_all_other_perils_previousvalue">
                                {{ !empty($previousLead->dic_all_other_perils)
                                    ? $previousLead->dic_all_other_perils
                                    : "N/A"
                                }}
                            </span>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-12 col-md-12 col-lg-12 mb-1 px-2">
                            <strong>Difference In Conditions Notes:</strong>
                            <span class="small longtextarea" id="dic_insurance_coverage_previousvalue">
                                {{ !empty($previousLead->dic_insurance_coverage)
                                    ? $previousLead->dic_insurance_coverage
                                    : "N/A"
                                }}
                            </span>
                        </div>
                    </div>
                </div>
                <!--  X-Wind -->
                <div class="p-2 mt-4 pt-3 pb-0 mx-0 rounded border position-relative">
                    <div class="section-head position-absolute m-0 p-0 z-5 d-inline-block font-weight-bold px-1 bg-white">
                        X-Wind :
                    </div>
                    <div class="form-row">
                        <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
                            <strong>Carrier:</strong>
                            <span class="small" id="x_wind_previousvalue">
                                {{ !empty($previousLead->x_wind) ? $previousLead->x_wind : "N/A" }}
                            </span>
                        </div>
                        <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
                            <strong>Month:</strong>
                            <span class="small" id="xw_ren_month_previousvalue">
                                {{ !empty($previousLead->xw_ren_month)
                                    ? $previousLead->xw_ren_month
                                    : "N/A"
                                }}
                            </span>
                        </div>
                        <div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
                            <strong>Expiring Premium:</strong>
                            <span class="small" id="xw_expiry_premium_previousvalue">
                                {{ !empty($previousLead->xw_expiry_premium)
                                    ? '$' . formatUSNumber($previousLead->xw_expiry_premium)
                                    : "N/A"
                                }}
                            </span>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
                            <strong>Policy Renewal Date:</strong>
                            <span class="small" id="xw_policy_renewal_date_previousvalue">
                                {{ !empty($previousLead->xw_policy_renewal_date)
                                    ? date('m/d/Y',strtotime($previousLead->xw_policy_renewal_date))
                                    : "N/A"
                                }}
                            </span>
                        </div>
                        <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
                            <strong>Hurricane Deductible:</strong>
                            <span class="small" id="xw_hurricane_deductible_previousvalue">
                                {{ !empty($previousLead->xw_hurricane_deductible)
                                    ? $previousLead->xw_hurricane_deductible
                                    : "N/A"
                                }}
                            </span>
                        </div>
                        <div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
                            <strong>All Other Perils Deductible:</strong>
                            <span class="small" id="xw_all_other_perils_previousvalue">
                                {{ !empty($previousLead->xw_all_other_perils)
                                    ? $previousLead->xw_all_other_perils
                                    : "N/A"
                                }}
                            </span>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-12 col-md-12 col-lg-12 mb-1 px-2">
                            <strong>X-Wind Notes:</strong>
                            <span class="small longtextarea" id="xw_insurance_coverage_previousvalue">
                                {{ !empty($previousLead->xw_insurance_coverage)
                                    ? $previousLead->xw_insurance_coverage
                                    : "N/A"
                                }}
                            </span>
                        </div>
                    </div>
                </div>
                <!--  Equipment Breakdown -->
                <div class="p-2 mt-4 pt-3 pb-0 mx-0 rounded border position-relative">
                    <div class="section-head position-absolute m-0 p-0 z-5 d-inline-block font-weight-bold px-1 bg-white">
                        Equipment Breakdown :
                    </div>
                    <div class="form-row">
                        <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
                            <strong>Carrier:</strong>
                            <span class="small" id="equipment_breakdown_previousvalue">
                                {{ !empty($previousLead->equipment_breakdown)
                                    ? $previousLead->equipment_breakdown
                                    : "N/A"
                                }}
                            </span>
                        </div>
                        <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
                            <strong>Month:</strong>
                            <span class="small" id="eb_ren_month_previousvalue">
                                {{ !empty($previousLead->eb_ren_month)
                                    ? $previousLead->eb_ren_month
                                    : "N/A"
                                }}
                            </span>
                        </div>
                        <div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
                            <strong>Expiring Premium:</strong>
                            <span class="small" id="eb_expiry_premium_previousvalue">
                                {{ !empty($previousLead->eb_expiry_premium)
                                    ? '$' . formatUSNumber($previousLead->eb_expiry_premium)
                                    : "N/A"
                                }}
                            </span>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
                            <strong>Policy Renewal Date:</strong>
                            <span class="small" id="eb_policy_renewal_date_previousvalue">
                                {{ !empty($previousLead->eb_policy_renewal_date)
                                    ? date('m/d/Y',strtotime($previousLead->eb_policy_renewal_date))
                                    : "N/A"
                                }}
                            </span>
                        </div>
                        <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
                            <strong>Hurricane Deductible:</strong>
                            <span class="small" id="eb_hurricane_deductible_previousvalue">
                                {{ !empty($previousLead->eb_hurricane_deductible)
                                    ? $previousLead->eb_hurricane_deductible
                                    : "N/A"
                                }}
                            </span>
                        </div>
                        <div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
                            <strong>All Other Perils Deductible:</strong>
                            <span class="small" id="eb_all_other_perils_previousvalue">
                                {{ !empty($previousLead->eb_all_other_perils)
                                    ? $previousLead->eb_all_other_perils
                                    : "N/A"
                                }}
                            </span>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-12 col-md-12 col-lg-12 mb-1 px-2">
                            <strong>Equipment Breakdown Notes:</strong>
                            <span class="small longtextarea" id="eb_insurance_coverage_previousvalue">
                                {{ !empty($previousLead->eb_insurance_coverage)
                                    ? $previousLead->eb_insurance_coverage
                                    : "N/A"
                                }}
                            </span>
                        </div>
                    </div>
                </div>
                <!--  Commercial AutoMobile -->
                <div class="p-2 mt-4 pt-3 pb-0 mx-0 rounded border position-relative">
                    <div class="section-head position-absolute m-0 p-0 z-5 d-inline-block font-weight-bold px-1 bg-white">
                        Commercial AutoMobile :
                    </div>
                    <div class="form-row">
                        <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
                            <strong>Carrier:</strong>
                            <span class="small" id="commercial_automobiles_previousvalue">
                                {{ !empty($previousLead->commercial_automobiles)
                                    ? $previousLead->commercial_automobiles
                                    : "N/A"
                                }}
                            </span>
                        </div>
                        <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
                            <strong>Month:</strong>
                            <span class="small" id="ca_ren_month_previousvalue">
                                {{ !empty($previousLead->ca_ren_month)
                                    ? $previousLead->ca_ren_month
                                    : "N/A"
                                }}
                            </span>
                        </div>
                        <div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
                            <strong>Expiring Premium:</strong>
                            <span class="small" id="ca_expiry_premium_previousvalue">
                                {{ !empty($previousLead->ca_expiry_premium)
                                    ? '$' . formatUSNumber($previousLead->ca_expiry_premium)
                                    : "N/A"
                                }}
                            </span>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
                            <strong>Policy Renewal Date:</strong>
                            <span class="small" id="ca_policy_renewal_date_previousvalue">
                                {{ !empty($previousLead->ca_policy_renewal_date)
                                    ? date('m/d/Y',strtotime($previousLead->ca_policy_renewal_date))
                                    : "N/A"
                                }}
                            </span>
                        </div>
                        <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
                            <strong>Hurricane Deductible:</strong>
                            <span class="small" id="ca_hurricane_deductible_previousvalue">
                                {{ !empty($previousLead->ca_hurricane_deductible)
                                    ? $previousLead->ca_hurricane_deductible
                                    : "N/A"
                                }}
                            </span>
                        </div>
                        <div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
                            <strong>All Other Perils Deductible:</strong>
                            <span class="small" id="ca_all_other_perils_previousvalue">
                                {{ !empty($previousLead->ca_all_other_perils)
                                    ? $previousLead->ca_all_other_perils
                                    : "N/A"
                                }}
                            </span>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-12 col-md-12 col-lg-12 mb-1 px-2">
                            <strong>Commercial AutoMobile Notes:</strong>
                            <span class="small longtextarea" id="ca_insurance_coverage_previousvalue">
                                {{ !empty($previousLead->ca_insurance_coverage)
                                    ? $previousLead->ca_insurance_coverage
                                    : "N/A"
                                }}
                            </span>
                        </div>
                    </div>
                </div>
                <!--  Marina -->
                <div class="p-2 mt-4 pt-3 pb-0 mx-0 rounded border position-relative">
                    <div class="section-head position-absolute m-0 p-0 z-5 d-inline-block font-weight-bold px-1 bg-white">
                        Marina :
                    </div>
                    <div class="form-row">
                        <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
                            <strong>Carrier:</strong>
                            <span class="small" id="marina_previousvalue">
                                {{ !empty($previousLead->marina) ? $previousLead->marina : "N/A" }}
                            </span>
                        </div>
                        <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
                            <strong>Month:</strong>
                            <span class="small" id="m_ren_month_previousvalue">
                                {{ !empty($previousLead->m_ren_month) ? $previousLead->m_ren_month : "N/A" }}
                            </span>
                        </div>
                        <div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
                            <strong>Expiring Premium:</strong>
                            <span class="small" id="m_expiry_premium_previousvalue">
                                {{ !empty($previousLead->m_expiry_premium)
                                    ? '$' . formatUSNumber($previousLead->m_expiry_premium)
                                    : "N/A"
                                }}
                            </span>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
                            <strong>Policy Renewal Date:</strong>
                            <span class="small" id="m_policy_renewal_date_previousvalue">
                                {{ !empty($previousLead->m_policy_renewal_date)
                                    ? date('m/d/Y',strtotime($previousLead->m_policy_renewal_date))
                                    : "N/A"
                                }}
                            </span>
                        </div>
                        <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
                            <strong>Hurricane Deductible:</strong>
                            <span class="small" id="m_hurricane_deductible_previousvalue">
                                {{ !empty($previousLead->m_hurricane_deductible)
                                    ? $previousLead->m_hurricane_deductible
                                    : "N/A"
                                }}
                            </span>
                        </div>
                        <div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
                            <strong>All Other Perils Deductible:</strong>
                            <span class="small" id="m_all_other_perils_previousvalue">
                                {{ !empty($previousLead->m_all_other_perils)
                                    ? $previousLead->m_all_other_perils
                                    : "N/A"
                                }}
                            </span>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-12 col-md-12 col-lg-12 mb-1 px-2">
                            <strong>Marina Notes:</strong>
                            <span class="small longtextarea" id="m_insurance_coverage_previousvalue">
                                {{ !empty($previousLead->m_insurance_coverage)
                                    ? $previousLead->m_insurance_coverage
                                    : "N/A"
                                }}
                            </span>
                        </div>
                    </div>
                </div>
                <?php $iloopcount = 0; ?>
                @foreach($previousLeadPolicyList as $policy)
                    <div class="p-2 mt-4 pt-3 pb-0 mx-0 rounded border position-relative area_appended_area">
                        <div class="section-head position-absolute m-0 p-0 z-5 d-inline-block font-weight-bold px-1 bg-white">
                            Additional Policy {{$policy->policy_type}} :
                        </div>
                        <div class="form-row">
                            <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
                                <strong>Policy Type:</strong>
                                <span class="small" id="policy_type_previousvalue{{$iloopcount}}">
                                    {{ !empty($policy->policy_type) ? $policy->policy_type : "N/A" }}
                                </span>
                            </div>
                            <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
                                <strong>Carrier:</strong>
                                <span class="small" id="carrier_previousvalue{{$iloopcount}}">
                                    {{ !empty($policy->listCarrier->name)
                                        ? $policy->listCarrier->name
                                        : "N/A"
                                    }}
                                </span>
                            </div>
                            <div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
                                <strong>Expiring Premium:</strong>
                                <span class="small" id="expiry_premium_previousvalue{{$iloopcount}}">
                                    {{ !empty($policy->expiry_premium)
                                        ? '$' . formatUSNumber($policy->expiry_premium)
                                        : "N/A"
                                    }}
                                </span>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
                                <strong>Policy Renewal Date:</strong>
                                <span class="small" id="policy_renewal_date_previousvalue{{$iloopcount}}">
                                    {{ !empty($policy->policy_renewal_date)
                                        ? date('m/d/Y', strtotime($policy->policy_renewal_date))
                                        : "N/A"
                                    }}
                                </span>
                            </div>
                            <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
                                <strong>Hurricane Deductible:</strong>
                                <span class="small" id="hurricane_deductible_previousvalue{{$iloopcount}}">
                                    {{ !empty($policy->hurricane_deductible)
                                        ? $policy->hurricane_deductible."%"
                                        : "N/A"
                                    }}
                                </span>
                            </div>
                            <div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
                                <strong>All Other Perils Deductible:</strong>
                                <span class="small" id="all_other_perils_previousvalue{{$iloopcount}}">
                                    {{ !empty($policy->all_other_perils)
                                        ? $policy->all_other_perils
                                        : "No"
                                    }}
                                </span>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-12 col-md-12 col-lg-12 mb-1 px-2">
                                <strong>Notes:</strong>
                                <span class="small longtextarea" id="insurance_coverage_previousvalue{{$iloopcount}}">
                                    {{ !empty($policy->insurance_coverage)
                                        ? $policy->insurance_coverage
                                        : "N/A"
                                    }}
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
                            $totalPremiumSum = 0;
                            if(!empty($previousLead->premium)){
                                $totalPremiumSum += $previousLead->premium;
                            }
                            if(!empty($previousLead->gl_expiry_premium)){
                                $totalPremiumSum += $previousLead->gl_expiry_premium;
                            }
                            if(!empty($previousLead->ci_expiry_premium)){
                                $totalPremiumSum += $previousLead->ci_expiry_premium;
                            }
                            if(!empty($previousLead->do_expiry_premium)){
                                $totalPremiumSum += $previousLead->do_expiry_premium;
                            }
                            if(!empty($previousLead->umbrella_expiry_premium)){
                                $totalPremiumSum += $previousLead->umbrella_expiry_premium;
                            }
                            if(!empty($previousLead->wc_expiry_premium)){
                                $totalPremiumSum += $previousLead->wc_expiry_premium;
                            }
                            if(!empty($previousLead->flood_expiry_premium)){
                                $totalPremiumSum += $previousLead->flood_expiry_premium;
                            }

                            if(!empty($previousLead->dic_expiry_premium)){
                                $totalPremiumSum += $previousLead->dic_expiry_premium;
                            }
                            if(!empty($previousLead->xw_expiry_premium)){
                                $totalPremiumSum += $previousLead->xw_expiry_premium;
                            }
                            if(!empty($previousLead->eb_expiry_premium)){
                                $totalPremiumSum += $previousLead->eb_expiry_premium;
                            }
                            if(!empty($previousLead->ca_expiry_premium)){
                                $totalPremiumSum += $previousLead->ca_expiry_premium;
                            }
                            if(!empty($previousLead->m_expiry_premium)){
                                $totalPremiumSum += $previousLead->m_expiry_premium;
                            }

                            foreach ($previousLeadPolicyList as $key => $policy) {
                                $totalPremiumSum += $policy->expiry_premium;
                            }

                        ?>
                        <div class="form-group col-12 mb-1 px-2">
                            <strong class="text-success mb-0">Total Premium: </strong>
                            <span id="total_premium_sum_previousvalue" class="text-base">
                                {{!empty($totalPremiumSum)?"$".$totalPremiumSum:"N/A"}}
                            </span>
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
        const cleaned = dateStr.trim(); // remove all leading/trailing spaces
        const [m, d, y] = cleaned.split('/').map(v => v.padStart(2, '0'));

        return `${y}-${m}-${d}`;
    }

    $(document).on("click",".renewal_date_btn",function (event) {
        let e1 = $(this);
        event.preventDefault();
        var date = convertDateFormatYmd($(this).text());
        // Remove classes from all buttons
        $(".renewal_date_btn").removeClass("bg-primary text-white");

        // Show Loader (Same as DataTables loader)
        let loaderHtml = `<div class="loader-bg position-absolute loader-section">
            <figure class="loader-img"><img src="/images/logo.png" alt=""></figure>
            </div>`;
        $(".loaderarea").after(loaderHtml); // Append loader to a container (Ensure .loader-container exists in your layout)

        $.ajax({
            type: 'POST',
            url: '/leads/fetchDateWiseOlderData',
            data: {
                lead_id : "{{!empty($previousLead->id) ? $previousLead->id: 0}}",
                renewal_date: date
            },
            success: function(response) {
                $(".loader-section").remove();
                $(".area_appended_area").remove();
                if(response.lead_found == 1){
                    e1.addClass("bg-primary text-white");
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

);

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
