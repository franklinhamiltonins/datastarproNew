<?php

namespace App\Traits;

use Illuminate\Support\Str;
use App\Model\LeadsModel\Lead;
use App\Model\LeadsModel\Contact;
use App\Model\Dialing;
use App\Model\SmtpConfiguration;
use Illuminate\Support\Facades\Crypt;
use App\Model\Email;
use Config;
use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Controllers\Controller;
use DB;
use App\Model\ContactStatus;
use App\Model\LeadAsanaDetail;
use App\Model\InsuranceType;
use App\Model\Carrier;
use App\Model\Rating;
use App\Model\User;
use App\Model\EventLogs;
use App\Model\AsanaQuestion;
use App\Model\LeadAssignmentLog;
use App\Model\Message;
use Carbon\Carbon;
use App\Model\UserTemplate;

use App\Jobs\CollabMailJob;


trait CommonFunctionsTrait
{
	public function calculateDistance($lat1, $lon1, $lat2, $lon2)
	{
		$earthRadius = 6371; // km
		$dLat = deg2rad($lat2 - $lat1);
		$dLon = deg2rad($lon2 - $lon1);

		$a = sin($dLat / 2) * sin($dLat / 2) +
			cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
			sin($dLon / 2) * sin($dLon / 2);
		$c = 2 * atan2(sqrt($a), sqrt(1 - $a));

		return round(($earthRadius * $c), 2); // distance in km
	}

	function removeAfterKeywords($string)
	{
		$keywords = ['apartment', 'unit', 'apt', '#'];
		$pattern = '/\b(?:' . implode('|', array_map('preg_quote', $keywords)) . ')\b.*$/i';
		return preg_replace($pattern, '', $string);
	}

	function verifyProspectStatus($current_priority, $max_priority, $old_status,  $c_address1, $c_city, $c_zip, $c_email)
	{
		$currrentStatus = $finalSatus = '';
		if (!empty($c_address1) && !empty($c_city) && !empty($c_zip) && !empty($c_email)) {
			$currrentStatus = 'success';
		} elseif (!empty($c_address1) || !empty($c_city) || !empty($c_zip) || !empty($c_email)) {
			$currrentStatus = 'partial';
		} else {
			$currrentStatus = 'unavailable';
		}

		if ($currrentStatus == 'success') {
			$finalSatus = 'success';
		} else { // either pending or unavailable
			if ($current_priority == $max_priority) { // last ai running
				if ($old_status == 'unavailable' && $currrentStatus == 'unavailable') {
					$finalSatus =  'not_found';
				} elseif ($old_status == 'partial' || $currrentStatus == 'partial') {
					$finalSatus = 'partial_all_platform_performed';
				}
			} elseif ($current_priority < $max_priority) {
				if ($old_status == 'unavailable' && $currrentStatus == 'unavailable') {
					$finalSatus =  'unavailable';
				} elseif ($old_status == 'partial' || $currrentStatus == 'partial') {
					$finalSatus =  'partial';
				} elseif ($old_status == 'pending' || $currrentStatus == 'unavailable') {
					$finalSatus =  'unavailable';
				}
			}
		}

		$data = array(
			'current_priority' => $current_priority,
			'max_priority' => $max_priority,
			'old_status' => $old_status,
			'c_address1' => $c_address1,
			'c_city' => $c_city,
			'c_zip' => $c_zip,
			'c_email' => $c_email,
			'currrentStatus' => $currrentStatus,
			'new_status' => $finalSatus
		);
		print_r($data);
		// Print the array
		return $finalSatus;
	}

	public function generateSlug($data)
	{
		$slug = implode('_ ', $data);
		$escapedStr = $this->removeSpecialCharacters($slug);
		return Str::slug(strtolower($escapedStr));
	}
	public function removeSpecialCharacters($str)
	{
		return preg_replace('/[^A-Za-z0-9\s\-]/u', '', $str);
	}

	public function checkLeadSlugExistance($lead_slug)
	{
		
		$leadsQuery = Lead::where('lead_slug', $lead_slug);
		// $leadsQuery->where("id", "!=", $id);
		if ($leadsQuery->exists()) {

			return false;
		}
		return true;
	}


	public function checkLeadSlugExistanceWithDistance($lead_slug, $newLeadLatitude, $newLeadLongitude, $id = "")
	{
		$existanceArr = [];
		$distanceRange = 0.1;
		$existingLeads = [];
		$leadsQuery = Lead::where('lead_slug', $lead_slug);
		if ($id) :
			$leadsQuery->where("id", "!=", $id);
		endif;
		if ($leadsQuery->exists()) {

			$existingLeads = $leadsQuery->get();
			array_push($existanceArr, 'Existing slug detected.');
			foreach ($existingLeads as  $existingLead) {

				$existingLatitude = $existingLead->latitude;
				$existingLongitude = $existingLead->longitude;

				$distance = $this->calculateDistance($newLeadLatitude, $newLeadLongitude, $existingLatitude, $existingLongitude);

				if ($distance <= $distanceRange) {
					array_push($existanceArr, 'Lead ' . $existingLead->name . ' already exists within ' . $distanceRange . ' km range.');
				}
			}
		}
		return ['status' => 200, 'message' => $existanceArr, 'existanceCount' => count($existanceArr), 'existingLeads' => $existingLeads];
	}

	public function checkContactSlugExistance($contact_slug, $id = '')
	{
		$existanceArr = [];
		$existingContacts = [];
		$contactQuery = Contact::where('contact_slug', $contact_slug);
		if ($id) :
			$contactQuery->where("id", "!=", $id);
		endif;
		if ($contactQuery->exists()) {
			$existingContacts = $contactQuery->get();
			foreach ($existingContacts as  $existingContact) {
				array_push($existanceArr, 'Contact ' . $existingContact->c_full_name . ' already exists.');
			}
		}
		return ['status' => 200, 'message' => $existanceArr,  'existanceCount' => count($existingContacts), 'existingContacts' => $existingContacts];
	}

	public function checkMailConfiguration()
	{

		$whereCond = [
			['username', '!=', ''],
			['password', '!=', ''],
			['host', '!=', ''],
			['port', '!=', ''],
			['encryption', '!=', ''],
			['from_name', '!=', ''],

		];
		$smtp_count = SmtpConfiguration::where('user_id', auth()->user()->id)
			->where($whereCond)
			->count();
		return $smtp_count;
	}

	public function setDynamicSMTP()
	{
		$smtp_data = $this->checkMailConfiguration();

		if (($smtp_data > 0)) {

			$configuration = SmtpConfiguration::where("user_id", auth()->user()->id)->first();
			$password = Crypt::decryptString("$configuration->password");

			$config = array(
				'driver'     => 'smtp',
				'transport' => 'smtp',
				'host'       => $configuration->host,
				'port'       => $configuration->port,
				'username'   => $configuration->username,
				'password'   => "$password",
				'encryption' => $configuration->encryption,
				'from'       => ['address' => $configuration->username, 'name' => $configuration->from_name],
				'sendmail'   => '/usr/sbin/sendmail -bs',
				'pretend'    => false,
			);

			Config::set('mail', $config);
		}
	}

	public function saveEmailData($data)
	{

		$data['user_id'] = auth()->user()->id;
		Email::create($data);
	}

	/**
	 * Scope a query to search for a term in the attributes
	 *
	 * @param \Illuminate\Database\Eloquent\Builder $query
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function search($query, $searchTerm, $attributes, $dateAttributes = [])
	{

		if (!$searchTerm || !$attributes) {
			return $query;
		}
		// echo "<pre>";print_r($attributes);exit;

		return $query->where(function (Builder $query) use ($attributes, $searchTerm, $dateAttributes) {
		    foreach (Arr::wrap($attributes) as $attribute) {
		        $query->when(
		            str_contains($attribute, '.') && method_exists($query->getModel(), explode('.', $attribute)[0]),
		            function (Builder $query) use ($attribute, $searchTerm) {
		                [$relationName, $relationAttribute] = explode('.', $attribute);

		                $query->orWhereHas($relationName, function (Builder $query) use ($relationAttribute, $searchTerm) {
		                    $query->where($relationAttribute, 'LIKE', "%{$searchTerm}%");
		                });
		            },
		            function (Builder $query) use ($attribute, $searchTerm) {
		                $query->orWhere($attribute, 'LIKE', "%{$searchTerm}%");
		            }
		        );
		    }
		});


		// return $query->where(function (Builder $query) use ($attributes, $searchTerm, $dateAttributes) {
		// 	foreach (Arr::wrap($attributes) as $attribute) {
		// 		$query->when(
		// 			str_contains($attribute, '.'),
		// 			function (Builder $query) use ($attribute, $searchTerm) {
		// 				[$relationName, $relationAttribute] = explode('.', $attribute);

		// 				$query->orWhereHas($relationName, function (Builder $query) use ($relationAttribute, $searchTerm) {
		// 					$query->where($relationAttribute, 'LIKE', "%{$searchTerm}%");
		// 				});
		// 			},
		// 			function (Builder $query) use ($attribute, $searchTerm) {
		// 				$query->orWhere($attribute, 'LIKE', "%{$searchTerm}%");
		// 			}
		// 		);
		// 	}
		// 	// foreach (Arr::wrap($dateAttributes) as $attribute) {
		// 	// 	$date = date_parse($searchTerm);
		// 	// 	if($date['day'])
		// 	// 		$query->orWhereDay($attribute, 'LIKE', "%{$date['day']}%");
		// 	// 	if($date['month'])
		// 	// 		$query->orWhereMonth($attribute, '=', $date['month']);
		// 	// 	if($date['year'])
		// 	// 		$query->orWhereYear($attribute, 'LIKE', "%{$date['year']}%");
		// 	// }


        // });
    }

    public function fetcharbitarycount($count_of_enteries,$max_enteries_loop)
    {
    	$random = rand(2,95);

    	$new_number = floor((($count_of_enteries*$random)/100));

    	if($new_number > $max_enteries_loop){
    		$new_number = $this->fetcharbitarycount($new_number,$max_enteries_loop);
    	}
    	if($new_number == 0){
    		$new_number = 1;
    	}
    	return $new_number;
    }

    public function delaytimecalculation($keyentry)
    {
    	return (($keyentry * rand(30,150)) + rand(1,120));
    }

    public function delaytimecalculationklaviyo($keyentry)
    {
    	return (($keyentry * rand(60,180)) + rand(1,120) + rand(1,120));
    }

    public function removeContactFromOwnLead($lead_id)
    {
    	DB::table('dialings_leads')
		->where('lead_id', $lead_id)
		// ->where('assigned_to_agent_id', $c_agent_id)
		->update(
			['owned_by_agent_id' => 0, 'status' => 'free','ownmarked_at'=>null]
		);
    }

    public function leadstatuslogmakeentry($lead_id,$c_agent_id,$status_id)
	{
		$make_log = true;
		$c_agent_id = !empty($c_agent_id)?$c_agent_id:0;
		$log_status = DB::table('lead_status_wise_log')->where('lead_id',$lead_id)->where('status_id',$status_id)->where('agent_id',$c_agent_id)->first();
		if($log_status){
			if(empty($log_status->end_timestamp)){
				$make_log = false;
			}
		}

		if($make_log){
			$estTime = new \DateTime('now', new \DateTimeZone('America/New_York'));
			
			DB::table('lead_status_wise_log')->where('lead_id',$lead_id)->where('agent_id',$c_agent_id)
			->whereNull('end_timestamp')
			->update([
				"end_timestamp" => $estTime->format('Y-m-d H:i:s')
			]);

			DB::table('lead_status_wise_log')->insert([
				'lead_id' => $lead_id,
				'status_id' => $status_id,
				'agent_id' => $c_agent_id,
				'start_timestamp' => $estTime->format('Y-m-d H:i:s'),
			]);
		}
	}

	private function prepareLeadCollaborators($lead)
    {
        if (!$lead) {
            return collect();
        }

        if (!$lead->relationLoaded('collaborators')) {
            $lead->load(['collaborators' => function ($query) {
                $query->select('users.id', 'users.name', 'users.email');
            }]);
        }

        return $lead->collaborators ?? collect();
    }

    private function prepareToAndCc($collabList)
    {
        $to = optional($collabList->shift())->email;
        $cc = $collabList->pluck('email')->toArray();
        return [$to, $cc];
    }

    private function prepareToAndCcArray(array $emails)
	{
	    $to = array_shift($emails);
	    return [$to, $emails];
	}

    private function getAgentName($id=null)
    {
    	$id = !empty($id)?$id:auth()->user()->id;
        return User::where('id',$id )->value('name') ?? 'Unknown Agent';
    }
    private function getAgentEmail($id=null)
    {
    	$id = !empty($id)?$id:auth()->user()->id;
        return User::where('id',$id )->value('email') ?? 'Unknown Agent';
    }

    public function newContactAdditionWithLeadShoot($lead, $c_agent_id, $c_first_name, $c_last_name, $new_status_id)
    {
        $collabList = $this->prepareLeadCollaborators($lead);
        if ($collabList->isEmpty()) {
            return;
        }

        [$to, $cc] = $this->prepareToAndCc($collabList);

        $new_status_name = ContactStatus::where('id', $new_status_id)->value('name') ?? 'N/A';
        $agent_name = $this->getAgentName($c_agent_id);

        $subject = "New Contact Addition Notification";
        $bodyMsg = "{$agent_name} has added a new Contact - ({$c_first_name} {$c_last_name}) of status - {$new_status_name} with Lead - {$lead->name} (Lead ID - {$lead->id}).";

        CollabMailJob::dispatch($subject, $bodyMsg, $to, $cc);
    }

    public function updateContactShoot($lead, $c_agent_id, $c_first_name, $c_last_name, $status_id)
    {
        $collabList = $this->prepareLeadCollaborators($lead);
        if ($collabList->isEmpty()) {
            return;
        }

        [$to, $cc] = $this->prepareToAndCc($collabList);

        $status_name = ContactStatus::where('id', $status_id)->value('name') ?? 'N/A';
        $agent_name = $this->getAgentName($c_agent_id);

        $subject = "Existing Contact Update Notification";
        $bodyMsg = "{$agent_name} has Updated Existing Contact - ({$c_first_name} {$c_last_name}) status - {$status_name} with Lead - {$lead->name} (Lead ID - {$lead->id}).";

        CollabMailJob::dispatch($subject, $bodyMsg, $to, $cc);
    }

    public function prepareassignedList($assigned_user_id, $lead)
	{
	    $emails = collect();

	    if ($assigned_user_id == -1) {
	        $emails = User::role('Service Team','Service & Agent')->pluck('email');
	    } else {
	        $emails->push($this->getAgentEmail($assigned_user_id));
	    }

	    if ($assigned_user_id != $lead->pipeline_agent_id) {
	        $emails->push($this->getAgentEmail($lead->pipeline_agent_id));
	    }

	    foreach ($lead->collaborators as $collab) {
	        $emails->push($collab->email);
	    }

	    return $emails->filter()->unique()->values()->toArray();
	}


    public function assigneeAddedMessageShoot($assigned_user_name, $assigned_user_id, $lead, $screenType)
	{
	    $emails = $this->prepareassignedList($assigned_user_id, $lead);
	    if (empty($emails)) {
	        return;
	    }

	    [$to, $cc] = $this->prepareToAndCcArray($emails);

	    $statusName = ContactStatus::where('id', $lead->pipeline_status_id)->value('name') ?? 'N/A';
	    $agentName = $this->getAgentName();

	    $stageName = null;
	    if ($screenType == 2) {
	        $stageId = $lead->leadAsanaDetail->asana_stage ?? 1;
	        $stageName = AsanaQuestion::where('id', $stageId)->value('name');
	    }

	    $subject = "Representative Assignment Notification";
	    $data = [
	        "leadId" => $lead->id,
	        "leadName" => $lead->name,
	        "agent_name" => $agentName,
	        "assigned_user_name" => $assigned_user_name,
	        "statusname" => $statusName,
	        "stagename" => $stageName,
	        "type" => 3,
	    ];

	    CollabMailJob::dispatch($subject, "", $to, $cc, $data);
	}

    public function statusChangeMsgShoot($lead, $old_status_id, $new_status_id)
    {
        $collabList = $this->prepareLeadCollaborators($lead);
        if ($collabList->isEmpty()) {
            return;
        }

        [$to, $cc] = $this->prepareToAndCc($collabList);

        $old_status_name = ContactStatus::where('id', $old_status_id)->value('name');
        $new_status_name = ContactStatus::where('id', $new_status_id)->value('name');
        $agent_name = $this->getAgentName();

        $subject = "Status Changes Notification";
        $bodyMsg = "{$agent_name} has changed the status of Lead - {$lead->name} (Lead ID - {$lead->id}) from {$old_status_name} to {$new_status_name}.";
        $data =[
        	"leadId" => $lead->id,
        	"leadName" => $lead->name,
        	"agent_name" => $agent_name,
        	// "old_status_id" => $old_status_id,
        	// "new_status_id" => $new_status_id,
        	"old_status_name" => $old_status_name,
        	"new_status_name" => $new_status_name,
        	"type" => 1,
        ];

        // return $data;

        // echo "<pre>";print_r($old_status_id);print_r($new_status_id);print_r($data);exit;

        CollabMailJob::dispatch($subject, $bodyMsg, $to, $cc, $data);
    }

    public function asanaStageChangeMsgShoot($leadId, $new_stage_id)
    {
    	$lead = Lead::find($leadId);
        $collabList = $this->prepareLeadCollaborators($lead);
        if ($collabList->isEmpty()) {
            return;
        }

        [$to, $cc] = $this->prepareToAndCc($collabList);

        $old_stage_name = null;
        if($new_stage_id > 1){
        	$old_stage_name = AsanaQuestion::where('id',  ($new_stage_id - 1))->value('name');
        }

        $new_stage_name = AsanaQuestion::where('id',  $new_stage_id)->value('name');

        $agent_name = $this->getAgentName();

        $subject = "Bind Management Stage Changes Notification";
        $bodyMsg = "{$agent_name} has changed the Bind Management stage of Lead - {$lead->name} (Lead ID - {$lead->id}) to {$new_stage_name}.";
        $data =[
        	"leadId" => $lead->id,
        	"leadName" => $lead->name,
        	"old_stage_name" => $old_stage_name,
        	"new_stage_name" => $new_stage_name,
        	"agent_name" => $agent_name,
        	"type" => 2,
        ];

        CollabMailJob::dispatch($subject, $bodyMsg, $to, $cc, $data);
    }

	public function fetchedReferalDialingId()
	{
		$id = 0;
		$dialing_query = Dialing::select('dialings.id')->where('dialings.referral_marker',1)->first();
		if($dialing_query){
			$id = $dialing_query->id;
		}
		unset($dialing_query);
		return $id;
	}

	public function assigAgentToDialing($c_agent_id,$dialing_id,$type)
	{
		if(!empty($c_agent_id)){
			DB::table('dialing_user')
			->updateOrInsert(
				['user_id' => $c_agent_id, 'dialing_id' => $dialing_id],
				['created_at' => now(), 'updated_at' => now()]
			);
		}
	}

	public function addLeadWithDialing($c_agent_id,$dialing_id,$lead_id)
	{
		if(!empty($c_agent_id)){
			$dialing_lead = DB::table('dialings_leads')->where('dialing_id',$dialing_id)->where('lead_id',$lead_id)->where('assigned_to_agent_id',$c_agent_id)->first();
			if(!$dialing_lead){
				DB::table('dialings_leads')
				->insert(
					['assigned_to_agent_id' => $c_agent_id, 'dialing_id' => $dialing_id,'lead_id'=> $lead_id,'ownmarked_at'=>null],
				);
			}
		}
	}

	public function contactbasedleadstatusupdate($lead_id,$agent_id,$status_id)
	{
		$contact = Contact::join('contact_status', 'contacts.c_status', '=', 'contact_status.id')
	    ->where('contacts.lead_id', $lead_id)
	    ->orderBy('contact_status.priority', 'desc')
	    ->select('contacts.c_status', 'contacts.c_agent_id')
	    ->first();
	    if($contact){
	    	$lead = Lead::where('id',$lead_id)->first();
	    	if($lead){
	    		$old_status_id = $lead->pipeline_status_id;
	    		$lead->pipeline_status_id = $contact->c_status;
	    		$lead->pipeline_agent_id = $contact->c_agent_id;
	    		$lead->save();

	    		$new_status_id = $lead->pipeline_status_id;
	    		if($old_status_id != $new_status_id){
		            $this->statusChangeMsgShoot($lead, $old_status_id, $new_status_id);
		            $this->leadstatuslogmakeentry($lead_id,$contact->c_agent_id,$status_id);
		        }
	    	}
	    	$contact_status_id = ContactStatus::whereIn('special_marker', [1, 2])->where('id',$contact->c_status)->first();

	    	if($contact_status_id){
	    		$contact_not = Contact::join('contact_status', 'contacts.c_status', '=', 'contact_status.id')
			    ->where('contacts.lead_id', $lead_id)
			    ->where(function ($query) {
			        $query->whereNotIn('contact_status.special_marker', [1, 2])
			              ->orWhereNull('contact_status.special_marker');
			    })
			    ->orderBy('contact_status.priority', 'desc') // Ensure highest priority is on top
			    ->select('contacts.c_status', 'contacts.c_agent_id')
			    ->first();

			    // echo "<pre>";print_r($contact_not);exit;
			    if($contact_not){
			    	Lead::where('id',$lead_id)->update([
			    		'pipeline_status_id' => $contact_not->c_status,
			    		'pipeline_agent_id' => $contact_not->c_agent_id,
			    	]);
			    }

	    	}
	    }
	    else{
	    	Lead::where('id',$lead_id)->update([
	    		'pipeline_status_id' => Null,
	    		'pipeline_agent_id' => null,
	    	]);
	    }
	    return true;

	}
	
	public function makelogInCarrierTable($value,$type)
	{
		$return_id = null;
		if(!empty($value)){
			$InsuranceType = InsuranceType::where('status',1)->where('name',$type)->first();
			if($InsuranceType){
				$alreadyEntry = Carrier::where('name',trim($value))->whereIn('status',[1,2])->first();
				if(!$alreadyEntry){
					$carrier = Carrier::Create(
		                ['name' => trim($value),'status'=>2]
		            );
		            $carrier->insuranceTypes()->attach([$InsuranceType->id]);
		            $return_id = $carrier->id;
				}
				else{
					$return_id = $alreadyEntry->id;
					// Fetch existing linked insurance types
	                $existingLinkedTypes = $alreadyEntry->insuranceTypes()->pluck('insurance_type_id')->toArray();

	                if (!in_array($InsuranceType->id, $existingLinkedTypes)) {
	                    $alreadyEntry->insuranceTypes()->attach([$InsuranceType->id]);
	                }
				}
			}
		}
		return $return_id;
	}

	public function makelogInRatingTable($value,$type)
	{
		$return_id = null;
		if(!empty($value)){
			$InsuranceType = InsuranceType::where('status',1)->where('name',$type)->first();
			if($InsuranceType){
				$alreadyEntry = Rating::where('name',trim($value))->whereIn('status',[1,2])->first();
				if(!$alreadyEntry){
					$rating = Rating::Create(
		                ['name' => trim($value),'status'=>2]
		            );
		            $rating->insuranceTypes()->sync([$InsuranceType->id]);
		            $return_id = $rating->id;
				}
				else{
					$return_id = $alreadyEntry->id;

					// Fetch existing linked insurance types
	                $existingLinkedTypes = $alreadyEntry->insuranceTypes()->pluck('insurance_type_id')->toArray();

	                if (!in_array($InsuranceType->id, $existingLinkedTypes)) {
						$alreadyEntry->insuranceTypes()->attach([$InsuranceType->id]);
	                }
				}
			}
			unset($InsuranceType);
		}
		return $return_id;
	}

	public function clearLeadAsanaDetailTable($table_id)
	{
		$leadAsanaDetail = LeadAsanaDetail::find($table_id);

		if($leadAsanaDetail){
			$leadAsanaDetail->appraisal = '';
			$leadAsanaDetail->wind_mitigation = '';
			$leadAsanaDetail->loss_run_authorization = '';
			$leadAsanaDetail->inspection_contact_form = '';
			$leadAsanaDetail->sov_form = '';
			$leadAsanaDetail->accord_form = '';
			$leadAsanaDetail->property_service_send_market = '';
			$leadAsanaDetail->general_liability_service_send_market = '';
			$leadAsanaDetail->do_service_send_market = '';
			$leadAsanaDetail->legal_defense_service_send_market = '';
			$leadAsanaDetail->umbrella_service_send_market = '';
			$leadAsanaDetail->crime_service_send_market = '';
			$leadAsanaDetail->workers_comp_service_send_market = '';
			$leadAsanaDetail->flood_service_send_market = '';
			$leadAsanaDetail->sent_to_client = '';
			$leadAsanaDetail->meeting_with_client = '';
			$leadAsanaDetail->signed_docusign_received = '';
			$leadAsanaDetail->property_bind_coverage = '';
			$leadAsanaDetail->general_liability_bind_coverage = '';
			$leadAsanaDetail->do_bind_coverage = '';
			$leadAsanaDetail->legal_defense_bind_coverage = '';
			$leadAsanaDetail->umbrella_bind_coverage = '';
			$leadAsanaDetail->crime_bind_coverage = '';
			$leadAsanaDetail->workers_comp_bind_coverage = '';
			$leadAsanaDetail->flood_bind_coverage = '';
			$leadAsanaDetail->add_policies_to_epic = '';
			$leadAsanaDetail->send_invoices_to_accounting = '';
			$leadAsanaDetail->add_policies_to_bind_document = '';
			$leadAsanaDetail->add_policies_to_eoi_direct = '';
			$leadAsanaDetail->send_policies_to_insured = '';
			$leadAsanaDetail->down_payment = '';
			$leadAsanaDetail->financing = '';
			$leadAsanaDetail->property_payment = '';
			$leadAsanaDetail->general_liability_payment = '';
			$leadAsanaDetail->do_payment = '';
			$leadAsanaDetail->legal_defense_payment = '';
			$leadAsanaDetail->umbrella_payment = '';
			$leadAsanaDetail->crime_payment = '';
			$leadAsanaDetail->workers_comp_payment = '';
			$leadAsanaDetail->flood_payment = '';

			$leadAsanaDetail->property_service_send_market_list = '';
			$leadAsanaDetail->general_liability_service_send_market_list = '';
			$leadAsanaDetail->do_service_send_market_list = '';
			$leadAsanaDetail->legal_defense_service_send_market_list = '';
			$leadAsanaDetail->umbrella_service_send_market_list = '';
			$leadAsanaDetail->crime_service_send_market_list = '';
			$leadAsanaDetail->workers_comp_service_send_market_list = '';
			$leadAsanaDetail->flood_service_send_market_list = '';

			$leadAsanaDetail->property_bind_coverage_list = '';
			$leadAsanaDetail->general_liability_bind_coverage_list = '';
			$leadAsanaDetail->do_bind_coverage_list = '';
			$leadAsanaDetail->legal_defense_bind_coverage_list = '';
			$leadAsanaDetail->umbrella_bind_coverage_list = '';
			$leadAsanaDetail->crime_bind_coverage_list = '';
			$leadAsanaDetail->workers_comp_bind_coverage_list = '';
			$leadAsanaDetail->flood_bind_coverage_list = '';
			
			$leadAsanaDetail->property_payment_list = '';
			$leadAsanaDetail->general_liability_payment_list = '';
			$leadAsanaDetail->do_payment_list = '';
			$leadAsanaDetail->legal_defense_payment_list = '';
			$leadAsanaDetail->umbrella_payment_list = '';
			$leadAsanaDetail->crime_payment_list = '';
			$leadAsanaDetail->workers_comp_payment_list = '';
			$leadAsanaDetail->flood_payment_list = '';

			$leadAsanaDetail->asana_stage = 1;
			$leadAsanaDetail->stage_completed = 0;
			// $leadAsanaDetail->renewal_date = NULL;
			$leadAsanaDetail->renewed_lead = 1;
			$leadAsanaDetail->save();
		}
	}

	public function clearLeadDetailTable($table_id)
	{
		$lead = Lead::find($table_id);

		if($lead){
			$lead->renewal_date = NULL;
			$lead->renewal_month = '';

			$lead->premium = NULL;
			$lead->premium_year = NULL;
			$lead->insured_amount = NULL;
			$lead->insured_year = NULL;
			// $lead->manag_company = NULL;
			// $lead->prop_manager = NULL;
			$lead->current_agency = NULL;
			$lead->current_agent = NULL;
			$lead->ins_prop_carrier = NULL;
			$lead->renewal_carrier_month = NULL;
			$lead->ins_flood = "NO";
			$lead->prop_floor = NULL;
			$lead->roof_geom = NULL;
			$lead->roof_covering = NULL;
			$lead->general_liability = NULL;
			$lead->GL_ren_month = NULL;
			$lead->crime_insurance = NULL;
			$lead->CI_ren_month = NULL;
			$lead->directors_officers = NULL;
			$lead->DO_ren_month = NULL;
			$lead->umbrella = NULL;
			$lead->U_ren_month = NULL;
			$lead->workers_compensation = NULL;
			$lead->WC_ren_month = NULL;
			$lead->flood = NULL;
			$lead->F_ren_month = NULL;

			// $lead->total_square_footage = '';
			$lead->roof_connection = NULL;
			$lead->roof_year = NULL;
			// $lead->pool = '';
			// $lead->lakes = '';
			// $lead->clubhouse = '';
			// $lead->tennis_basketball = '';
			$lead->other_community_info = NULL;
			$lead->iso = NULL;
			// $lead->appraisal_name = '';
			// $lead->appraisal_company = '';
			// $lead->appraisal_date = '';
			// $lead->incumbent_agency = '';
			// $lead->incumbent_agent = '';
			$lead->policy_renewal_date = NULL;
			$lead->wind_mitigation_date = NULL;
			// $lead->rating = NULL;
			// $lead->hurricane_deductible = '';
			// $lead->hurricane_deductible_occurrence = '';
			// $lead->skin_hole = '';
			// $lead->all_other_perils = '';
			// $lead->ordinance_of_law = '';
			// $lead->tiv_matches_appraisal = '';
			// $lead->secondary_water_insurance = '';
			// $lead->opening_protection = '';
			$lead->gl_expiry_premium = '';
			$lead->gl_policy_renewal_date = NULL;
			// $lead->gl_rating = '';
			// $lead->gl_exclusions = '';
			// $lead->gl_other_exclusions = '';

			$lead->ci_expiry_premium = NULL;
			$lead->ci_policy_renewal_date = NULL;
			$lead->ci_rating = '';
			$lead->employee_theft = '';
			$lead->operating_reserves = '';
			$lead->pending_litigation = '';
			$lead->litigation_date = NULL;
			$lead->do_expiry_premium = NULL;
			$lead->do_policy_renewal_date = NULL;
			$lead->do_rating = '';
			$lead->claims_made = '';
			$lead->umbrella_expiry_premium = NULL;
			$lead->umbrella_policy_renewal_date = NULL;
			$lead->umbrella_rating = '';
			$lead->umbrella_exclusions = '';
			$lead->umbrella_other_exclusions = '';
			$lead->wc_expiry_premium = NULL;
			$lead->wc_policy_renewal_date = NULL;
			$lead->wc_rating = '';
			$lead->employee_count = '';
			$lead->employee_payroll = '';
			$lead->flood_expiry_premium = NULL;
			$lead->flood_policy_renewal_date = NULL;
			$lead->flood_rating = '';
			$lead->elevation_certificate = '';
			$lead->loma_letter = '';
			$lead->gl_insurance_coverage = NULL;
			$lead->ci_insurance_coverage = NULL;
			$lead->do_insurance_coverage = NULL;
			$lead->u_insurance_coverage = NULL;
			$lead->wc_insurance_coverage = NULL;

			$lead->difference_in_condition = NULL;
			$lead->dic_ren_month = NULL;
			$lead->dic_expiry_premium = NULL;
			$lead->dic_policy_renewal_date = NULL;
			$lead->dic_hurricane_deductible = NULL;
			$lead->dic_all_other_perils = NULL;
			$lead->dic_insurance_coverage = NULL;

			$lead->x_wind = NULL;
			$lead->xw_ren_month = NULL;
			$lead->xw_expiry_premium = NULL;
			$lead->xw_policy_renewal_date = NULL;
			$lead->xw_hurricane_deductible = NULL;
			$lead->xw_all_other_perils = NULL;
			$lead->xw_insurance_coverage = NULL;

			$lead->equipment_breakdown = NULL;
			$lead->eb_ren_month = NULL;
			$lead->eb_expiry_premium = NULL;
			$lead->eb_policy_renewal_date = NULL;
			$lead->eb_hurricane_deductible = NULL;
			$lead->eb_all_other_perils = NULL;
			$lead->eb_insurance_coverage = NULL;

			$lead->commercial_automobiles = NULL;
			$lead->ca_ren_month = NULL;
			$lead->ca_expiry_premium = NULL;
			$lead->ca_policy_renewal_date = NULL;
			$lead->ca_hurricane_deductible = NULL;
			$lead->ca_all_other_perils = NULL;
			$lead->ca_insurance_coverage = NULL;

			$lead->marina = NULL;
			$lead->m_ren_month = NULL;
			$lead->m_expiry_premium = NULL;
			$lead->m_policy_renewal_date = NULL;
			$lead->m_hurricane_deductible = NULL;
			$lead->m_all_other_perils = NULL;
			$lead->m_insurance_coverage = NULL;

			$lead->save();
		}
	}

	public $mainInsuranceCarrier = [
        "Property" => "ins_prop_carrier",
        "General Liability" => "general_liability",
        "Crime Insurance" => "crime_insurance",
        "Directors & Officers" => "directors_officers",
        "Umbrella" => "umbrella",
        "Workers Compensation" => "workers_compensation",
        "Flood" => "flood",
        "Difference In Conditions" => "difference_in_condition",
        "X-Wind" => "x_wind",
        "Equipment Breakdown" => "equipment_breakdown",
        "Commercial AutoMobile" => "commercial_automobiles",
        "Marina" => "marina",
    ];

    public $additionalPoliciesCarrier = [
        "Glass" => "glass",
        "Excess Directors & Officers" => "excess_directors_officers",
        "Excess Liability" => "excess_liability",
        "Pollution" => "pollution",
        "Commercial" => "commercial",
        "Legal Defense" => "legal_defence",
        "Cyber" => "cyber",
    ];

    public $mainInsuranceRating = [
        "Property" => "rating",
        "General Liability" => "gl_rating",
        "Crime Insurance" => "ci_rating",
        "Directors & Officers" => "do_rating",
        "Umbrella" => "umbrella_rating",
        "Workers Compensation" => "wc_rating",
        "Flood" => "flood_rating",
    ];

    public function getAgentListing($isAdminUser,$agentId,$everyone=true,$roles=[])
    {
    	$agentUsers = [];
    	if(in_array("Manager",$roles,true)){
    		$user = User::where("id",$agentId)->first();
            if($user){
	            $agentUsers[$agentId] = [
	                "displayname" => "{$user->name} ({$user->email})",
	                "name" => $user->name,
	                "email" => $user->email,
	                "id" => $user->id,
	            ];
	            $agents = $user->managerTeamList;
	            foreach ($agents as $agent) {
	                $agentUsers[$agent->id] = [
	                    "displayname" => "{$agent->name} ({$agent->email})",
	                    "name" => $agent->name,
	                    "email" => $agent->email,
	                    "id" => $agent->id,
	                ];
	            }
            }
    	}
    	else{
	    	if ($isAdminUser) {
	            // If the user is an admin, list all agents
	            $agents = User::role(['Agent','Service & Agent'])->get();
	            if($everyone){
	            	$agentUsers[0] = [
		                "displayname" => "Everyone",
		                "name" => "Everyone",
		                "email" => "Everyone",
		                "id" => 0,
		            ];
	            }

	            foreach ($agents as $agent) {
	                $agentUsers[$agent->id] = [
	                    "displayname" => "{$agent->name} ({$agent->email})",
	                    "name" => $agent->name,
	                    "email" => $agent->email,
	                    "id" => $agent->id,
	                ];
	            }
	            unset($agents);
	        } else {
	            // If the user is not an admin, add the current user only
	            $user = User::where("id",$agentId)->first();
	            if($user){
		            $agentUsers[$agentId] = [
		                "displayname" => "{$user->name} ({$user->email})",
		                "name" => $user->name,
		                "email" => $user->email,
		                "id" => $user->id,
		            ];
		            $agents = $user->accessibleUsers;
		            foreach ($agents as $agent) {
		                $agentUsers[$agent->id] = [
		                    "displayname" => "{$agent->name} ({$agent->email})",
		                    "name" => $agent->name,
		                    "email" => $agent->email,
		                    "id" => $agent->id,
		                ];
		            }
	            }

	        }
	    }

        return $agentUsers;
    }

    public function updateDialingLists($status_id, $contact_id, $lead_id,$c_agent_id=0,$own_status=0)
	{
		$lead_dialing_id = DB::table('dialings_leads')
		    ->where('lead_id', $lead_id)
		    ->where('assigned_to_agent_id', $c_agent_id)
		    ->orderByDesc('dialing_id')
		    ->value('dialing_id');
		if(empty($lead_dialing_id)){
			$fetch_referal_dailing_id = $this->fetchedReferalDialingId();
			$this->assigAgentToDialing($c_agent_id,$fetch_referal_dailing_id,1);
			$this->addLeadWithDialing($c_agent_id,$fetch_referal_dailing_id,$lead_id);

			$lead_dialing_id = $fetch_referal_dailing_id;
		}

		if(empty($c_agent_id)){
			$c_agent_id = auth()->user()->id;
		}

		$ownStatusArray = ContactStatus::where('status_type', 2)->pluck('id')->toArray();

		$ownContactStatus = Contact::where('lead_id', $lead_id)
		    ->whereNotNull('c_phone')
		    ->whereIn('c_status', $ownStatusArray)
		    ->exists();


		$status = 'free';
		$updateArr = ['owned_by_agent_id' => 0, 'status' => 'free','ownmarked_at'=>null];
		// dd($ownContactStatus);
		if ($ownContactStatus) {
			DB::table('dialings_leads')
		    ->where('lead_id', $lead_id)
		    ->update($updateArr);

			$estTime = new \DateTime('now', new \DateTimeZone('America/New_York'));
			$est_timenow = $estTime->format('Y-m-d H:i:s');
			$updateArr = ['owned_by_agent_id' => $c_agent_id, 'status' => 'own','ownmarked_at'=> $est_timenow ];
		}

		DB::table('dialings_leads')->where('dialing_id', $lead_dialing_id)
				->where('lead_id', $lead_id)
				->where('assigned_to_agent_id', $c_agent_id)
				->update($updateArr);
	}

	public function setContactToQueue($lead)
	{
		$contacts_count = Contact::where('lead_id', $lead->id)->whereNotNull('c_phone')->where('c_status', 'Select Status')->count();

		$lead->no_of_times_contacts_called = $lead->no_of_times_contacts_called + 1;
		if ($contacts_count == 0) {
			$lead->queued_at = now();
		}
		$lead->save();
	}

	public function pipeDriveDisplayStatusList()
	{
		return ContactStatus::where('false_status', 0)
            ->whereNotNull('display_in_pipedrive')
            ->pluck('id');
	}

	public function decideColorTile($agentId,$notifyDays,$estTime,$nowTime,$statusId,$leadID)
    {
        $displayTileColor = 1; // Default color

        $itemBacklog = DB::table('lead_status_wise_log')
                     ->where('lead_id', $leadID)
                     ->where('status_id', $statusId)
                     ->when($agentId, function ($query) use ($agentId) {
                         return $query->where('agent_id', $agentId);
                     })
                     ->where('start_timestamp', '<', $estTime)
                     ->exists();

        if ($itemBacklog) {
            $displayTileColor = 2; // Red color

            $hasRecentEvent = EventLogs::where('lead_id', $leadID)
                               // ->when($agentId, function ($query) use ($agentId) {
                               //     return $query->where('agent_id', $agentId);
                               // })
                               ->where('event_date', '>=', $nowTime->toDateString())
                               ->exists();

            // echo "<pre>";print_r($nowTime->toDateString());exit;

            if ($hasRecentEvent) {
                $displayTileColor = 3; // Yellow color
            }
        }

        return $displayTileColor;
    }

    public function getSignedAorStatusId()
    {
    	$contact = ContactStatus::select('id', 'name')
                ->where('special_marker', 3)
                ->first();

        return $contact->id ?? 0;
    }

    public function getAgentWisePermission($user)
    {
    	$isAdminUser = $user->can('agent-create');
    	$is_admin_user = $user->can('agent-create');
        $accountListPermission = $user->can('all-accounts-list-pipedrive');
        $pipemng_acess = $user->can('pipe-management');
        $pipemng_acess_download = $user->can('pipe-management-download');
        $bindmng_acess = $user->can('bind-management');
        $bindmng_acess_download = $user->can('bind-management-download');
        $contact_delete = $user->can('contact-delete');
        $contact_create = $user->can('contact-create');
        $contact_edit = $user->can('contact-edit');
        $lead_file_list = $user->can('lead-file-list');
        $lead_file_upload = $user->can('lead-file-upload');
        $lead_file_download = $user->can('lead-file-download');
        $lead_file_delete = $user->can('lead-file-delete');
        $lead_edit = $user->can('lead-edit');
        $lead_create = $user->can('lead-create');

    	if(!$isAdminUser){
            $isAdminUser = $user->can('all-accounts-list-pipedrive');  
        }

        return [
        	"isAdminUser" => $isAdminUser,
        	"is_admin_user" => $is_admin_user,
        	"accountListPermission" => $accountListPermission,
        	"pipemng_acess" => $pipemng_acess,
        	"pipemng_acess_download" => $pipemng_acess_download,
        	"bindmng_acess" => $bindmng_acess,
        	"bindmng_acess_download" => $bindmng_acess_download,
        	"contact_delete" => $contact_delete,
        	"contact_create" => $contact_create,
        	"contact_edit" => $contact_edit,
        	"lead_file_list" => $lead_file_list,
        	"lead_file_upload" => $lead_file_upload,
        	"lead_file_download" => $lead_file_download,
        	"lead_file_delete" => $lead_file_delete,
        	"lead_edit" => $lead_edit,
        	"lead_create" => $lead_create,
        ];
    }

    public function leadTotalPremiumCalculaion($leadId)
    {
    	$lead = Lead::find($leadId);
    	$additonalPolicy = $lead->leadAdditionalpolicy()->get();

    	$total_premium = totalPremiumCalculationLeadWise($lead, $additonalPolicy);

    	return $total_premium;
    }

    public function leadTotalPremiumUpdate($leadId)
    {
    	$total_premium = $this->leadTotalPremiumCalculaion($leadId);

    	Lead::where("id",$leadId)->update(["total_premium"=>$total_premium]);
    }

    public function makeLogForAssigneeAgent($assigned_user_id, $lead)
	{
	    LeadAssignmentLog::create([
	        'status_id' => $lead->pipeline_status_id,
	        'agent_id' => $lead->pipeline_agent_id,
	        'assigned_user_id' => $assigned_user_id,
	        'lead_id' => $lead->id,
	        'changed_by_user_id' => auth()->id(),
	    ]);
	}

	public function check_max_execution_time($chatContactId, $isNewsletter=""){
		$checkLastMsg = Message::where('chat_type', 'outbound')
				->where('chat_sms_sent_status', '1');
		if(strtolower($isNewsletter) != "yes")
			$checkLastMsg = $checkLastMsg->where('contact_id', $chatContactId);
		else
			$checkLastMsg = $checkLastMsg->where('newsletter_id', $chatContactId);
			 // but what happen when this one is  0
		$checkLastMsg = $checkLastMsg->orderBy('created_at', 'desc')->first();

			if (isset($checkLastMsg->max_time_to_send)) {
				$maxTimeToSend = Carbon::parse($checkLastMsg->max_time_to_send);
				$nowAt = Carbon::now();
				$timeDifferenceInMinutes = $nowAt->diffInMinutes($maxTimeToSend, false);			
				return response()->json([
					'success' => true,
					'status' => 200, // Use numeric value for status code
					'response' => $timeDifferenceInMinutes,
					'timeleft' => $timeDifferenceInMinutes
				]);
			}else {
					return response()->json([
						'success' => false,
						'status' => 404,
						'response' => 'No message found',
					]);
				}
	}

	public function createTemplateSlug($data) {
		return Str::slug($data['template_name'], '-').'-'.auth()->user()->id.'-'.$data['template_type'];
	}

	public function checkingIsAdminUser($user)
	{
		$isAdminUser = $user->can('agent-create') || $user->can('all-accounts-list-pipedrive');

		return $isAdminUser;
	}

	public function checkingIsAllAccountDisplay($user,$isAdminUser)
	{
		$allDisplay = $isAdminUser || $user->hasRole('Manager');

		return $allDisplay;
	}

	public function getManagerId($user)
	{
		$managerId = 0;
		if($user->hasRole('Manager')){
			$managerId = $user->id;
		}

		return $managerId;
	}
}
