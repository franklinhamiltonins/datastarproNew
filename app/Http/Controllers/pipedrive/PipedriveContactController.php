<?php

namespace App\Http\Controllers\pipedrive;

use App\Model\AgentLog;
use App\Model\ContactStatus;
use App\Model\InsuranceType;
use App\Model\LeadsModel\Contact;
use App\Model\LeadsModel\Lead;
use App\Model\LeadSource;
use App\Model\User;
use App\Model\EventLogs;
use App\Traits\CommonFunctionsTrait;
use App\Traits\LoginFunctionTrait;
use App\Traits\PipeDriveTrait;
use App\Traits\SMTPRelatedTrait;
use Illuminate\Http\Request;
use Validator;

class PipedriveContactController extends PipedriveLoginController
{
    use CommonFunctionsTrait,LoginFunctionTrait,PipeDriveTrait,SMTPRelatedTrait;

    public function updateContact(Request $request)
    {
        $rules = Contact::rules();
        $niceNames = Contact::niceNames();

        // validate fields using nice name in error messages
        $validator = Validator::make($request->all(), $rules, [], $niceNames);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
            ], 200);
        }

        $cAgentId = $request->c_agent_id;
        if (! empty($request->c_status)) {
            $agentTypeStatus = ContactStatus::where('id', $request->c_status)->first();
            if ($agentTypeStatus && $agentTypeStatus->status_type == 2 && empty($cAgentId)) {
                return response()->json([
                    'status' => false,
                    'message' => $this->getAgentSelectionValidation($agentTypeStatus->name),
                ], 200);

            }
        }

        $contact = Contact::find($request->contact_id);
        if (! $contact) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update',
            ], 200);
        }

        $contact->c_first_name = $request->c_first_name;
        $contact->c_last_name = $request->c_last_name;
        $contact->c_full_name = $request->c_first_name.' '.$request->c_last_name;
        $contact->c_title = $request->c_title;
        $contact->c_address1 = $request->c_address1;
        $contact->c_address2 = $request->c_address2;
        $contact->c_city = $request->c_city;
        $contact->c_state = $request->c_state;
        $contact->c_county = $request->c_county;
        $contact->c_zip = $request->c_zip;
        $contact->c_phone = $request->c_phone;
        $contact->c_email = $request->c_email;
        $contact->c_status = $request->c_status;
        $contact->c_agent_id = $cAgentId;
        $contact->save();

        $this->contactbasedleadstatusupdate($request->lead_id, $contact->c_agent_id, $request->c_status);

        $agentTypeStatus = ContactStatus::where('id', $request->c_status)->first();

        $lead = Lead::find($request->lead_id);

        if (! empty($request->c_status) && $agentTypeStatus && empty($agentTypeStatus->false_status)) {
            $ownStatus = $agentTypeStatus->display_in_pipedrive;
            $this->updateDialingLists($request->c_status, $contact->id, $request->lead_id, $contact->c_agent_id, $ownStatus);
            $this->setContactToQueue($lead);

            $message = auth()->user()->name.' has updated status of contact : '.$contact->id.' to '.$request->c_status.' present in lead: '.$request->lead_id;
            AgentLog::updateOrCreate(
                ['user_id' => auth()->user()->id, 'contact_id' => $contact->id],
                ['message' => $message, 'user_id' => auth()->user()->id, 'lead_id' => $request->lead_id, 'contact_id' => $contact->id, 'status' => 'call_status_updated']
            );
        }

        $res = $this->leadIdBasedData($request->lead_id);
        create_log($lead, 'Edit Contact : '.$contact->c_first_name.' '.$contact->c_last_name, '');

        return response()->json([
            'status' => true,
            'message' => 'Updated',
            'lead' => $res['lead'],
            'status_list' => $res['status_list'],
            'additonalPolicy' => $res['additonalPolicy'],
        ], 200);
    }

    public function addContact(Request $request)
    {
        $rules = Contact::rules();
        $niceNames = Contact::niceNames();
        // validate fields using nice name in error messages
        $validator = Validator::make($request->all(), $rules, [], $niceNames);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
            ], 200);
        }

        $cAgentId = $request->c_agent_id;

        if (! empty($request->c_status)) {
            $agentTypeStatus = ContactStatus::where('id', $request->c_status)->first();
            if ($agentTypeStatus && $agentTypeStatus->status_type == 2 && empty($cAgentId)) {
                return response()->json([
                    'status' => false,
                    'message' => $this->getAgentSelectionValidation($agentTypeStatus->name),
                ], 200);
            }
        }

        $lead = Lead::find($request->lead_id);

        $contact = new Contact;
        $contact->c_first_name = $request->c_first_name;
        $contact->c_last_name = $request->c_last_name;
        $contact->c_full_name = $request->c_first_name.' '.$request->c_last_name;
        $contact->c_title = $request->c_title;
        $contact->c_address1 = $request->c_address1;
        $contact->c_address2 = $request->c_address2;
        $contact->c_city = $request->c_city;
        $contact->c_state = $request->c_state;
        $contact->c_county = $request->c_county;
        $contact->c_zip = $request->c_zip;
        $contact->c_phone = $request->c_phone;
        $contact->c_email = $request->c_email;
        $contact->lead_id = $request->lead_id;
        $contact->c_status = $request->c_status;
        $contact->c_agent_id = $cAgentId;
        $contact->save();

        $contact->leads()->associate($lead);

        $this->contactbasedleadstatusupdate($request->lead_id, $contact->c_agent_id, $request->c_status);

        $agentTypeStatus = ContactStatus::where('id', $request->c_status)->first();

        if (! empty($request->c_status) && $agentTypeStatus && empty($agentTypeStatus->false_status)) {
            $ownStatus = $agentTypeStatus->display_in_pipedrive;
            $this->updateDialingLists($request->c_status, $contact->id, $request->lead_id, $contact->c_agent_id, $ownStatus);
            $this->setContactToQueue($lead);

            $message = User::where('id', $contact->c_agent_id)->value('name').' has updated status of contact : '.$contact->id.' to '.$request->c_status.' present in lead: '.$request->lead_id;
            AgentLog::updateOrCreate(
                ['user_id' => $contact->c_agent_id, 'contact_id' => $contact->id],
                ['message' => $message, 'user_id' => $contact->c_agent_id, 'lead_id' => $request->lead_id, 'contact_id' => $contact->id, 'status' => 'call_status_updated']
            );
        }

        create_log($lead, 'Create Contact : '.$contact->c_first_name.' '.$contact->c_last_name, '');

        $res = $this->leadIdBasedData($request->lead_id);

        return response()->json([
            'status' => true,
            'message' => 'Updated',
            'lead' => $res['lead'],
            'status_list' => $res['status_list'],
            'additonalPolicy' => $res['additonalPolicy'],
        ], 200);
    }

    public function removeContact(Request $request)
    {
        $contact = Contact::find($request->id);
        if (! $contact) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update',
            ], 200);
        }

        $leadId = $contact->leads->id;

        $contact->delete();

        $this->contactbasedleadstatusupdate($leadId, 0, 0);

        $lead = Lead::find($leadId);
        create_log($lead, 'Delete Contact : '.$contact->c_first_name.' '.$contact->c_last_name, '');

        $this->updateDialingLists($lead->pipeline_status_id, 0, $leadId, $lead->pipeline_agent_id);

        $res = $this->leadIdBasedData($leadId);

        return response()->json([
            'status' => true,
            'message' => 'Updated',
            'lead' => $res['lead'],
            'status_list' => $res['status_list'],
            'additonalPolicy' => $res['additonalPolicy'],
        ], 200);
    }

    public function fetchRequiredContactInfo()
    {
        $states = Lead::leadStates();
        $counties = Lead::leadCounties();
        $statusOptions = parent::getContactStatusOptions();
        $leadSource = LeadSource::select('id as key', 'name as value')->where('status', 1)->get();

        $typesWithCarriersAndRatings = [
            'Property' => ['carrierVar' => 'carriersWithProperty', 'ratingVar' => 'ratingsWithProperty'],
            'General Liability' => ['carrierVar' => 'carriersWithGeneralLiability', 'ratingVar' => 'ratingsWithGeneralLiability'],
            'Crime Insurance' => ['carrierVar' => 'carriersWithCrimeInsurance', 'ratingVar' => 'ratingsWithCrimeInsurance'],
            'Directors & Officers' => ['carrierVar' => 'carriersWithDirectorOfficor', 'ratingVar' => 'ratingsWithDirectorOfficor'],
            'Umbrella' => ['carrierVar' => 'carriersWithUnbrella', 'ratingVar' => 'ratingsWithUnbrella'],
            'Workers Compensation' => ['carrierVar' => 'carriersWithWorkCompensation', 'ratingVar' => 'ratingsWithWorkCompensation'],
            'Flood' => ['carrierVar' => 'carriersWithFlood', 'ratingVar' => 'ratingsWithFlood'],
        ];

        foreach ($typesWithCarriersAndRatings as $name => $vars) {
            $type = InsuranceType::where('name', $name)->first();
            if ($type) {
                ${$vars['carrierVar']} = $type->carriers()->where('status', 1)->pluck('carriers.name', 'carriers.id')->toArray();
                ${$vars['ratingVar']} = $type->ratings()->where('status', 1)->pluck('ratings.name', 'ratings.id')->toArray();
            } else {
                ${$vars['carrierVar']} = collect();
                ${$vars['ratingVar']} = collect();
            }
        }

        $typesWithOnlyCarriers = [
            'Difference In Conditions' => 'carriersWithDifference',
            'X-Wind' => 'carriersWithXwind',
            'Equipment Breakdown' => 'carriersWithEquipment',
            'Commercial AutoMobile' => 'carriersWithCommercial',
            'Marina' => 'carriersWithMarina',
        ];

        foreach ($typesWithOnlyCarriers as $name => $varName) {
            $type = InsuranceType::where('name', $name)->first();
            ${$varName} = $type ? $type->carriers()->where('status', 1)->pluck('carriers.name', 'carriers.id')->toArray() : collect();
        }

        $additionalCarrier = [];
        foreach ($this->additionalPoliciesCarrier as $key => $policy) {
            $type = InsuranceType::where('name', $key)->first();
            $additionalCarrier[$key] = $type ? $type->carriers()->where('status', 1)->pluck('carriers.name', 'carriers.id')->toArray() : collect();
        }

        return response()->json([
            'status' => true,
            'message' => 'Sucessfully',
            'states' => $states,
            'counties' => $counties,
            'statusOptions' => $statusOptions,
            'leadSource' => $leadSource,

            // Carriers and Ratings
            'carriersWithProperty' => $carriersWithProperty,
            'ratingsWithProperty' => $ratingsWithProperty,
            'carriersWithGeneralLiability' => $carriersWithGeneralLiability,
            'ratingsWithGeneralLiability' => $ratingsWithGeneralLiability,
            'carriersWithCrimeInsurance' => $carriersWithCrimeInsurance,
            'ratingsWithCrimeInsurance' => $ratingsWithCrimeInsurance,
            'carriersWithDirectorOfficor' => $carriersWithDirectorOfficor,
            'ratingsWithDirectorOfficor' => $ratingsWithDirectorOfficor,
            'carriersWithUnbrella' => $carriersWithUnbrella,
            'ratingsWithUnbrella' => $ratingsWithUnbrella,
            'carriersWithWorkCompensation' => $carriersWithWorkCompensation,
            'ratingsWithWorkCompensation' => $ratingsWithWorkCompensation,
            'carriersWithFlood' => $carriersWithFlood,
            'ratingsWithFlood' => $ratingsWithFlood,

            // Only Carriers
            'carriersWithDifference' => $carriersWithDifference,
            'carriersWithXwind' => $carriersWithXwind,
            'carriersWithEquipment' => $carriersWithEquipment,
            'carriersWithCommercial' => $carriersWithCommercial,
            'carriersWithMarina' => $carriersWithMarina,

            'additionalPolicies' => $this->additionalPoliciesCarrier,
            'additionalCarrier' => $additionalCarrier,
        ], 200);
    }

    public function keepEventLog(Request $request)
    {
        $eventId = ! empty($request->event_id) ? $request->event_id : '';
        if (! empty($eventId)) {
            $event = EventLogs::where('event_id', $eventId)->first();
            if (! $event) {
                $event = new EventLogs;
                $event->event_id = $eventId;
                $event->status = 1;
            }
            $event->event_name = $request->event_id;
            $event->event_desc = $request->event_desc;
            $event->event_name = $request->event_title;
            $event->event_date = $request->event_date;
            $event->lead_id = $request->leadID;
            $event->agent_id = $request->agentId;
            $event->save();
        }

        return response()->json([
            'status' => false,
            'message' => 'Updated',
        ], 200);
    }

    public function deleteEventLog(Request $request)
    {
        $eventId = ! empty($request->event_id) ? $request->event_id : '';
        if (! empty($eventId)) {
            $event = EventLogs::where('event_id', $eventId)->first();
            if ($event) {
                $event->delete();
            }
        }

        return response()->json([
            'status' => false,
            'message' => 'Deleted',
        ], 200);

    }
}
