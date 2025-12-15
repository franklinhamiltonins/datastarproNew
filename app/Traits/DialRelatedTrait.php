<?php

namespace App\Traits;

use Illuminate\Support\Str;
use App\Model\LeadsModel\Lead;
use App\Model\LeadsModel\Contact;
use App\Model\SmtpConfiguration;
use Illuminate\Support\Facades\Crypt;
use App\Model\Email;
use Config;
use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

trait DialRelatedTrait
{
    public function leadoutputget_fordialing($location_leads_id_search,$location_leads_id,$search_fields, $campaignId)
    {
    	$columnsType = Lead::Get_column_type();

    	if ($location_leads_id_search) {
			$table = Lead::with('contacts')->whereIn('id', $location_leads_id);
		} else {
			$table = Lead::with('contacts');
		}

		$leadsQuery = filter_leads($table, $search_fields, $columnsType, $campaignId);
		$leadsQuery->where('is_client', 0);

		$ownedLeads = DB::table('dialings_leads')->where('status', 'owned')->where('owned_by_agent_id', '>', 0)->get()->pluck('lead_id');
		if ($ownedLeads) :
			$leadsQuery->whereNotIn('id', $ownedLeads);
		endif;

		// ->leftJoin('dialings_leads', function ($join) {
        // 	$join->on('leads.id', '=', 'dialings_leads.lead_id')
        //     ->where('dialings_leads.status', '=', 'owned')
        //     ->where('dialings_leads.owned_by_agent_id', '>', 0);
    	// })
		// ->whereNull('dialings_leads.dialing_id');

		$dialingContactStatus = Controller::getDialingStatusOptions();

		$leadsQuery->whereHas('contacts', function ($query) use ($dialingContactStatus) {
			$query->where('c_phone', '<>', '');
			$query->whereIn('c_status', $dialingContactStatus);
		});

		return $leadsQuery;
    }
}
