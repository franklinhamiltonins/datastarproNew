<?php
if (!function_exists('filter_leads')) {

	/**
	 * filter leads
	 *
	 * @param  string $person Name
	 * @return string
	 */
	function filter_leads($leadsQuery, $filters, $columnsType, $campaignId)
	{
		$contactColumn =  Schema::getColumnListing('contacts');
		$exculsion_array = ["name"];
		// dd($leadsQuery->toSql());
		//we can't send json data, because the table uses raw data . This is why we encode and then decode to get the right format data
		$filters = json_decode(json_encode($filters));
		// echo "<pre>";print_r($filters);exit;
		if ($filters) {

			//if there are filters
			//start leads querry
			$leadsQuery->where(function ($q) use ($filters, $columnsType, $contactColumn,$exculsion_array) {
				// loop trough sections
				foreach ($filters as $filter) {
					// open where querry
					$q->where(function ($s) use ($filters, $filter, $columnsType, $contactColumn,$exculsion_array) {
						$count = 0; // the count of the loop
						foreach ($filter as $fl) {
							if(!empty($fl->s_name)){
								if(!(in_array($fl->s_name,$exculsion_array) && empty($fl->s_val))){
									if ((!isset($fl->s_val)) || (($fl->s_val === "" || $fl->s_val === null) && $fl->s_val !== 0)) {
										$sValue = null;
									} else if ($fl->s_val == "0") {
										$sValue = $fl->s_val;
									} else {
										$sValue = $fl->s_val;
									}
									if (in_array($fl->s_name, $contactColumn)) { // if it's contact
										//make the querry
										$clauseWhereHas =  ($count == 1) ? 'whereHas' : 'orWhereHas';
										$s->$clauseWhereHas('contacts', function ($w) use ($clauseWhereHas, $fl, $sValue) {
											//when value is null search for null or empty values
											if ($sValue == null) {
												if ($fl->s_op == "like") {
													$w->where(function ($z) use ($fl, $sValue) {
														$z->where($fl->s_name, $fl->s_op == "like" ? "=" : "!=", $sValue)->orWhere($fl->s_name, $fl->s_op == "like" ? "=" : "!=", "");
													});
												} else {
													$w->where(function ($z) use ($fl, $sValue) {
														$z->where($fl->s_name, $fl->s_op == "like" ? "=" : "!=", $sValue)->where($fl->s_name, $fl->s_op == "like" ? "=" : "!=", "");
													});
												}
											} else {
												$w->where($fl->s_name, $fl->s_op, '%' . $sValue . '%');
											}
										});
									} else if ($fl->s_name == 'campaign_date') {  //if it is Last Campaign Date
										$clauseWhereHas =  ($count == 1) ? 'whereHas' : 'orWhereHas';
										$clauseWhereDoesntHave =  ($count == 1) ? 'whereDoesntHave' : 'orWhereDoesntHave';
										if ($sValue == null) {
											if ($fl->s_op == "like") {
												$s->$clauseWhereDoesntHave('campaigns', function ($w)  use ($filter, $fl, $sValue) {
													$w->where('status', 'COMPLETED')->whereNotNull('campaign_date');
												});
											} else {
												$s->$clauseWhereHas('campaigns', function ($w)  use ($filter, $fl, $sValue) {
													$w->where('status', 'COMPLETED')->whereNotNull('campaign_date');
												});
											}
										} else {
											switch ($fl->s_op) {
												case 'like':
													$s->$clauseWhereHas('campaigns', function ($w)  use ($filter, $fl, $sValue) {
														$w->where('status', 'COMPLETED')->where('campaign_date', 'like', $sValue);
													});
													break;

												case 'not like':
													$s->$clauseWhereDoesntHave('campaigns', function ($w)  use ($filter, $fl, $sValue) {
														$w->where('status', 'COMPLETED')->where('campaign_date', 'like', $sValue);
													});
													break;

												case '>':
													$s->$clauseWhereHas('campaigns', function ($w)  use ($filter, $fl, $sValue) {
														$w->where('status', 'COMPLETED')->where('campaign_date', '>', $sValue);
													});
													break;

												case '<':
													$s->$clauseWhereDoesntHave('campaigns', function ($w)  use ($filter, $fl, $sValue) {
														$w->where('status', 'COMPLETED')->where('campaign_date', '>=', $sValue);
													})->with('campaigns');
													break;

												case '>=':
													$s->$clauseWhereHas('campaigns', function ($w)  use ($filter, $fl, $sValue) {
														$w->where('status', 'COMPLETED')->where('campaign_date', '>=', $sValue);
													});
													break;

												case '<=':
													$s->$clauseWhereDoesntHave('campaigns', function ($w)  use ($filter, $fl, $sValue) {
														$w->where('status', 'COMPLETED')->where('campaign_date', '>', $sValue);
													});
													break;
											}
										}
									} else {
										//make the querry
										$clauseWhere =  ($count == 1) ? 'where' : 'orWhere';
										$clauseWhereNull = ($count == 1) ? 'whereNull' : 'orWhereNull';
										$clauseWhereNotNull = ($count == 1) ? 'whereNotNull' : 'orWhereNotNull';
										// echo $clauseWhere." ---- ".$clauseWhereNull." ---- ".$clauseWhereNotNull." ---- ".$sValue." ---- ";exit;
										if ($sValue == null) { //when value is null search for null or empty values
											if (in_array($fl->s_name, $columnsType['number'])) {
												if ($fl->s_op == "like") {
													$s->$clauseWhereNull($fl->s_name, $sValue);
												} else {
													$s->$clauseWhereNotNull($fl->s_name, $sValue);
												}
											} else {
												if ($fl->s_op == "like") {
													$s->$clauseWhere(function ($z) use ($fl, $sValue) {
														$z->where($fl->s_name, $fl->s_op == "like" ? "=" : "!=", $sValue)->orWhere($fl->s_name, $fl->s_op == "like" ? "=" : "!=", "");
													});
												} else {
													$s->$clauseWhere(function ($z) use ($fl, $sValue) {
														$z->where($fl->s_name, $fl->s_op == "like" ? "=" : "!=", $sValue)->where($fl->s_name, $fl->s_op == "like" ? "=" : "!=", "");
													});
												}
											}
										} else {
											if($fl->s_name == "lead_source"){
												$s->$clauseWhere($fl->s_name, $fl->s_op, $fl->s_val);
											}
											else{
												$s->$clauseWhere($fl->s_name, $fl->s_op, (in_array($fl->s_name, $columnsType['date']) || in_array($fl->s_name, $columnsType['number'])) ?  $fl->s_val : '%' . $fl->s_val . '%');
											}
											// echo (in_array($fl->s_name, $columnsType['date']) || in_array($fl->s_name, $columnsType['number'])) ?  $fl->s_val : '%' . $fl->s_val . '%';exit;
											
										}
									}
								}
							}
						}
					});
				}
			});
		} else if ($campaignId) { // if there is campaign filter the leads based on campaign - user gets here from the campaign page
			// get the campaign
			$campaign = App\Model\Campaign::find($campaignId);
			if ($campaign) {
				// get it's lead id's
				$leads = $campaign->leads;
				if (count($leads) > 0) {
					//make querry based on campaign lead_ids
					$leadsQuery->where(function ($q) use ($leads) {
						// $campaignIds = json_decode(json_encode($campaignId));
						$count = 0;
						foreach ($leads as $lead) {
							$count++;
							$clause =  ($count == 1) ? 'where' : 'orWhere'; // if it is the first contact filter use Where , if it is second > , use or
							$q->$clause('id', $lead->id);
						}
					});
				} else {
					$leadsQuery = App\Model\LeadsModel\Lead::where('id', '0'); //if there are no leads, search for something that will never exist in db, so that table shows it found nothing
				}
			}
		}
		return $leadsQuery;
	}
}

if (!function_exists('filter_agent_leads')) {

	function filter_agent_leads($agent_leads_query, $filters, $columnsType)
	{
		//we can't send json data, because the table uses raw data . This is why we encode and then decode to get the right format data
		$filters = json_decode(json_encode($filters));
		if ($filters) {
			$agent_leads_query->where(function ($q) use ($filters, $columnsType) {
				// loop trough sections
				foreach ($filters as $filter) {
					// open where querry
					$q->where(function ($s) use ($filters, $filter, $columnsType) {
						$count = 0; // the count of the loop
						foreach ($filter as $fl) {
							if (($fl->s_val == "" || $fl->s_val == "null") && $fl->s_val != "0") {
								$sValue = null;
							} else if ($fl->s_val == "0") {
								$sValue = $fl->s_val;
							} else {
								$sValue = $fl->s_val;
							}
							//make the querry
							$clauseWhere =  ($count == 1) ? 'where' : 'orWhere';
							$clauseWhereNull = ($count == 1) ? 'whereNull' : 'orWhereNull';
							$clauseWhereNotNull = ($count == 1) ? 'whereNotNull' : 'orWhereNotNull';
							if ($sValue == null) { //when value is null search for null or empty values
								if (in_array($fl->s_name, $columnsType['number'])) {
									if ($fl->s_op == "like") {
										$s->$clauseWhereNull($fl->s_name, $sValue);
									} else {
										$s->$clauseWhereNotNull($fl->s_name, $sValue);
									}
								} else {
									if ($fl->s_op == "like") {
										$s->$clauseWhere(function ($z) use ($fl, $sValue) {
											$z->where($fl->s_name, $fl->s_op == "like" ? "=" : "!=", $sValue)->orWhere($fl->s_name, $fl->s_op == "like" ? "=" : "!=", "");
										});
									} else {
										$s->$clauseWhere(function ($z) use ($fl, $sValue) {
											$z->where($fl->s_name, $fl->s_op == "like" ? "=" : "!=", $sValue)->where($fl->s_name, $fl->s_op == "like" ? "=" : "!=", "");
										});
									}
								}
							} else {
								$s->$clauseWhere($fl->s_name, $fl->s_op, (in_array($fl->s_name, $columnsType['number'])) ?  $fl->s_val : '%' . $fl->s_val . '%');
							}
						}
					});
				}
			})->get();
		}
		return $agent_leads_query;
	}
}

if (!function_exists('update_camp_leadActions')) {
	/**
	 * Update campaign lead_actions
	 *
	 * @param  string $person Name
	 * @return string
	 */
	function update_leadActions($campaign)
	{
		$actions = "0";
		$campaignDate = $campaign->campaign_date; //get campaign date
		if ($campaignDate &&  $campaign->status == "COMPLETED") { // if campaign has campaign date and it's status is completed
			$date = Carbon\Carbon::createFromFormat('Y-m-d',  $campaignDate); //format date Carbon date
			$tenDays = $date->addDays(10); // add ten days to campaign date
			// get campaign actions number
			$actions =  App\Model\LeadsModel\Action::whereHas('leads', function ($query) use ($campaign) {
				$query->whereHas('campaigns', function ($qw) use ($campaign) {
					$qw->where('id', $campaign->id);
				});
			})->where(function ($q) use ($campaign, $campaignDate, $tenDays) {
				$q->whereBetween('contact_date', [
					$campaignDate,
					$tenDays
				]); //get the actions where created at is situated between campaign date and campaign date+ 10
			})->count();
		}
		if ($campaign->lead_actions != $actions) {

			$campaign->update([
				'lead_actions' => $actions
			]);
		}
	}
}

if (!function_exists('formatUSNumber')) {
	function formatUSNumber($number, $decimals = 2) {
	    return number_format($number, $decimals, '.', ',');
	}
}

if (!function_exists('totalPremiumCalculationLeadWise')) {
	function totalPremiumCalculationLeadWise($lead, $additionalPolicy)
	{
	    // All possible premium fields
	    $premiumFields = [
	        'premium',
	        'gl_expiry_premium',
	        'ci_expiry_premium',
	        'do_expiry_premium',
	        'umbrella_expiry_premium',
	        'wc_expiry_premium',
	        'flood_expiry_premium',
	        'dic_expiry_premium',
	        'xw_expiry_premium',
	        'eb_expiry_premium',
	        'ca_expiry_premium',
	        'm_expiry_premium',
	    ];

	    $total_premium_sum = 0;

	    foreach ($premiumFields as $field) {
	        $value = isset($lead->$field) ? (float)$lead->$field : 0;
	        $total_premium_sum += $value > 0 ? $value : 0;
	    }

	    if (!empty($additionalPolicy) && is_iterable($additionalPolicy)) {
	        foreach ($additionalPolicy as $policy) {
	            $value = isset($policy->expiry_premium) ? (float)$policy->expiry_premium : 0;
	            $total_premium_sum += $value > 0 ? $value : 0;
	        }
	    }

	    return $total_premium_sum > 0 ? $total_premium_sum : 0;
	}

}
