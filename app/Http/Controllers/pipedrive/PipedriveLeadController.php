<?php

namespace App\Http\Controllers\pipedrive;

use App\Model\AgentLog;
use App\Model\AsanaQuestion;
use App\Model\Carrier;
use App\Model\ContactStatus;
use App\Model\File;
use App\Model\LeadAdditionalPolicy;
use App\Model\LeadAsanaDetail;
use App\Model\LeadsModel\Contact;
use App\Model\LeadsModel\Lead;
use App\Model\LeadsModel\Note;
use App\Model\LeadSource;
use App\Model\Rating;
use App\Model\User;
use App\Traits\CommonFunctionsTrait;
use App\Traits\PipeDriveTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PipedriveLeadController extends PipedriveLoginController
{
    use CommonFunctionsTrait,PipeDriveTrait;

    public function leadfiledownload($agentId = 0, $name = '')
    {
        $filename = 'leads_export.csv';
        $statusList = ContactStatus::select('id', 'name')
            ->where('false_status', 0)
            ->where('display_in_pipedrive', 1)
            ->orderBy('priority', 'ASC')
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($statusList, $agentId, $name) {
            $handle = fopen('php://output', 'w');

            // Add CSV headers
            fputcsv($handle, [
                'Lead ID', 'Lead Name', 'Address', 'Total Premium', 'Policy Renewal Date',
                'Pipeline Agent ID', 'Agent Name', 'Agent Email', 'Pipeline Status ID',  'Pipeline Status',
            ]);

            foreach ($statusList as $status) {
                $list = Lead::where('pipeline_status_id', $status->id)
                    ->join('users', 'leads.pipeline_agent_id', '=', 'users.id');

                // Correct filtering by agent ID
                if (! empty($agentId)) {
                    $list = $list->where('pipeline_agent_id', $agentId);
                }

                if (! empty($name)) {
                    $list = $list->where('leads.name', 'like', '%'.$name.'%');
                }

                // Correctly chain select and chunk
                $list->select(
                    'leads.id',
                    'leads.name',
                    'leads.address1',
                    'leads.total_premium',
                    'leads.policy_renewal_date',
                    'leads.pipeline_agent_id',
                    'users.name as agent_name',
                    'users.email as agent_email',
                    'leads.pipeline_status_id',
                )
                    ->chunk(1000, function ($leads) use ($handle, $status) {
                        foreach ($leads as $lead) {
                            fputcsv($handle, [
                                $lead->id,
                                $lead->name,
                                $lead->address1,
                                $lead->total_premium,
                                $lead->policy_renewal_date,
                                $lead->pipeline_agent_id,
                                $lead->agent_name,
                                $lead->agent_email,
                                $lead->pipeline_status_id,
                                $status->name,
                            ]);
                        }
                    });
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function leadasanafiledownload($agentId = 0, $name = '')
    {
        $filename = 'leads_bindmgmt_export.csv';
        $questions = AsanaQuestion::select('id', 'name')->where('status', 1)->orderBy('priority', 'ASC')->get();

        $status = ContactStatus::select('id', 'name')
            ->where('special_marker', 3)
            ->first();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($questions, $status, $agentId, $name) {
            $handle = fopen('php://output', 'w');

            // CSV headers
            fputcsv($handle, [
                'Lead ID', 'Lead Name', 'Address', 'Total Premium', 'Policy Renewal Date',
                'Pipeline Agent ID', 'Agent Name', 'Agent Email', 'Pipeline Status ID', 'Pipeline Status', 'Asana Stage',
            ]);

            foreach ($questions as $key => $question) {
                if ($key === 0) {
                    $leadList = Lead::leftJoin('lead_asana_details', 'leads.id', '=', 'lead_asana_details.lead_id')
                        ->where(function ($query) {
                            $query->where('lead_asana_details.asana_stage', 1)
                                ->orWhereNull('lead_asana_details.lead_id');
                        })
                        ->where('leads.pipeline_status_id', $status->id ?? 0)
                        ->join('users', 'leads.pipeline_agent_id', '=', 'users.id');

                } else {
                    $leadList = LeadAsanaDetail::join('leads', 'lead_asana_details.lead_id', '=', 'leads.id')
                        ->join('users', 'leads.pipeline_agent_id', '=', 'users.id')
                        ->where('lead_asana_details.asana_stage', $question->id)
                        ->where('lead_asana_details.stage_completed', 0)
                        ->whereNull('leads.deleted_at');
                }
                if (! empty($agentId)) {
                    $leadList = $leadList->where('leads.pipeline_agent_id', $agentId);
                }

                if (! empty($name)) {
                    $leadList = $leadList->where('leads.name', 'like', '%'.$name.'%');
                }

                $leadList->select(
                    'leads.id',
                    'leads.name',
                    'leads.address1',
                    'leads.total_premium',
                    'leads.policy_renewal_date',
                    'leads.pipeline_agent_id',
                    'users.name as agent_name',
                    'users.email as agent_email',
                    'leads.pipeline_status_id',
                )
                    ->chunk(1000, function ($leads) use ($handle, $status, $question) {
                        foreach ($leads as $lead) {
                            fputcsv($handle, [
                                $lead->id,
                                $lead->name,
                                $lead->address1,
                                $lead->total_premium,
                                $lead->policy_renewal_date,
                                $lead->pipeline_agent_id,
                                $lead->agent_name,
                                $lead->agent_email,
                                $lead->pipeline_status_id,
                                $status->name ?? '',
                                $question->name,
                            ]);
                        }
                    });
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function individualLeadData(Request $request)
    {
        $lead = Lead::find($request->leadId);
        if (! $lead) {
            return response()->json([
                'status' => false,
                'message' => 'status',
                'lead' => [],
                'status_list' => [],
                'additonalPolicy' => [],
            ], 200);
        }

        $res = $this->leadIdBasedData($request->leadId);

        return response()->json([
            'status' => true,
            'message' => 'status',
            'lead' => $res['lead'],
            'status_list' => $res['status_list'],
            'additonalPolicy' => $res['additonalPolicy'],
        ], 200);
    }

    public function shiftLeadStatus(Request $request)
    {
        try {
            // Get the request data
            $leadId = $request->input('leadId');
            $oldStatusId = $request->input('oldStatusId');
            $newStatusId = $request->input('newStatusId');
            $agentId = $request->input('agentId');

            $newStatusName = '';

            if (! empty($request->input('specialType'))) {
                $status = ContactStatus::select('id', 'name')->where('special_marker', $request->input('specialType'))->first();
                if ($status) {
                    $newStatusId = $status->id;
                    $newStatusName = $status->name;
                }
            }

            $lead = Lead::where('id', $leadId)
                ->where('pipeline_status_id', $oldStatusId)
                ->first();

            if ($lead) {
                $lead->pipeline_status_id = $newStatusId;
                $lead->save();

                Contact::where('lead_id', $leadId)->where('c_status', $oldStatusId)
                    ->update(['c_status' => $newStatusId]);

                $this->leadstatuslogmakeentry($leadId, $agentId, $newStatusId);
                $checking = $this->statusChangeMsgShoot($lead, $oldStatusId, $newStatusId);

                return response()->json([
                    'status' => true,
                    'message' => 'Contact status updated successfully',
                    'newStatusId' => $newStatusId,
                    'new_status_name' => $newStatusName,
                    'checking' => $checking,
                ], 200);
            }

            // If the update failed, return a failure response
            return response()->json([
                'status' => false,
                'message' => 'Failed to update contact status',
            ], 500);

        } catch (\Exception $e) {
            // Catch any other exceptions and return a generic error response
            return response()->json([
                'status' => false,
                'message' => 'An unexpected error occurred',
            ], 500);
        }
    }

    public function updateIndividualLead(Request $request)
    {
        $leadId = $request->input('leadId');
        $specialType = $request->input('specialType');
        $agentId = $request->input('agentId');

        $agentTypeStatus = ContactStatus::select('id', 'name')->where('special_marker', $request->input('specialType'))->first();
        if ($agentTypeStatus) {
            $lead = Lead::where('id', $leadId)
                ->first();

            if ($lead) {
                $agent = User::find($agentId);

                if ($agent && $agent->hasRole('Agent')) {

                } else {
                    $agentId = $lead->pipeline_agent_id;
                }
                if ($specialType == 3) {
                    $contact = Contact::where('lead_id', $leadId)
                        ->where('c_status', '!=', 8)
                        ->orderBy('c_status', 'DESC')
                        ->first();

                    if (! $contact) {
                        return response()->json([
                            'status' => false,
                            'message' => 'Failed to update contact status',
                            'newStatusId' => $agentTypeStatus->id,
                            'new_status_name' => $agentTypeStatus->name,
                        ], 500);
                    }
                    $contact->c_status = $agentTypeStatus->id;
                    $contact->c_agent_id = $agentId;
                    $contact->save();

                    $contact_id = $contact->id;
                } else {
                    $contact = Contact::where('lead_id', $leadId)
                        ->update([
                            'c_status' => $agentTypeStatus->id,
                            'c_agent_id' => $agentId,
                        ]);

                    $lead->pipeline_status_id = $agentTypeStatus->id;
                    $lead->pipeline_agent_id = $agentId;
                    $lead->save();

                    $contact_id = 0;

                    $contact = Contact::where('lead_id', $leadId)->first();
                    if ($contact) {
                        $contact_id = $contact->id;
                    }
                }

                $this->contactbasedleadstatusupdate($leadId, $agentId, $agentTypeStatus->id);

                $agentTypeStatus = ContactStatus::where('id', $agentTypeStatus->id)->first();

                if ($agentTypeStatus) {
                    $ownStatus = $agentTypeStatus->display_in_pipedrive;
                    $this->updateDialingLists($agentTypeStatus->id, $contact_id, $leadId, $agentId, $ownStatus);
                    $this->setContactToQueue($lead);

                    $message = User::where('id', $agentId)->value('name').' has updated status of contact : '.$contact_id.' to '.$agentTypeStatus->id.' present in lead: '.$leadId;
                    AgentLog::updateOrCreate(
                        ['user_id' => $agentId, 'contact_id' => $contact_id],
                        ['message' => $message, 'user_id' => $agentId, 'lead_id' => $leadId, 'contact_id' => $contact_id, 'status' => 'call_status_updated']
                    );
                }
                create_log($lead, 'Edit Contact : '.$contact->c_first_name.' '.$contact->c_last_name, '');

                $res = $this->leadIdBasedData($leadId);

                return response()->json([
                    'status' => true,
                    'message' => 'Contact status updated successfully',
                    'newStatusId' => $agentTypeStatus->id,
                    'new_status_name' => $agentTypeStatus->name,
                    'lead' => $res['lead'],
                ], 200);
            }
        }

        return response()->json([
            'status' => false,
            'message' => 'An unexpected error occurred',
        ], 500);
    }

    public function fetchLeadDataEmail(Request $request)
    {
        $lead = Lead::select('pipeline_status_id')->find($request->leadID);

        if (! $lead || ! $lead->pipeline_status_id) {
            return response()->json([
                'status' => false,
                'email_list' => [],
            ], 200);
        }

        $emailList = Contact::where('lead_id', $request->leadID)
            ->where('c_status', $lead->pipeline_status_id)
            ->whereNotNull('c_email')
            ->pluck('c_email')
            ->filter()
            ->values()
            ->toArray();

        return response()->json([
            'status' => ! empty($emailList),
            'email_list' => $emailList,
        ], 200);
    }

    public function reassignLeadStatus(Request $request)
    {
        $lead = Lead::where('id', $request->leadId)
        // ->where('pipeline_status_id',$oldStatusId)
            ->first();

        if ($lead) {
            $agentId = $request->agent_id;
            $agent = User::find($agentId);

            if ($agent && $agent->hasRole('Agent')) {

            } else {
                $agentId = $lead->pipeline_agent_id;
            }

            $contact = Contact::where('lead_id', $request->leadId)
                ->where('id', $request->contact_id)
                ->first();

            if (! $contact) {
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to update contact status',
                ], 500);
            }
            $contact->c_status = $request->status;
            $contact->c_agent_id = $agentId;
            $contact->save();

            $this->contactbasedleadstatusupdate($request->leadId, $agentId, $request->status);

            $agentTypeStatus = ContactStatus::where('id', $request->status)->first();

            $lead = Lead::find($request->leadId);

            if (! empty($request->status) && $agentTypeStatus) {
                $ownStatus = $agentTypeStatus->display_in_pipedrive;
                $this->updateDialingLists($request->status, $contact->id, $request->leadId, $agentId, $ownStatus);
                $this->setContactToQueue($lead);

                $message = User::where('id', $agentId)->value('name').' has updated status of contact : '.$contact->id.' to '.$request->status.' present in lead: '.$request->leadId;
                AgentLog::updateOrCreate(
                    ['user_id' => $agentId, 'contact_id' => $contact->id],
                    ['message' => $message, 'user_id' => $agentId, 'lead_id' => $request->leadId, 'contact_id' => $contact->id, 'status' => 'call_status_updated']
                );
            }
            create_log($lead, 'Edit Contact : '.$contact->c_first_name.' '.$contact->c_last_name, '');

            return response()->json([
                'status' => true,
                'message' => 'Contact status updated successfully',
            ], 200);
        }

        // If the update failed, return a failure response
        return response()->json([
            'status' => false,
            'message' => 'Failed to update contact status',
        ], 500);
    }

    public function addLeadNote(Request $request)
    {
        if (! empty($request->noteId)) {
            $note = Note::find($request->noteId);
            if ($note) {
                $note->contact_id = $request->contact_id;
                $note->description = $request->description;
                $note->save();

                $contact = Contact::find($request->contact_id);

                $note->contacts()->associate($contact);
                $note->save();

                $lead = Lead::find($request->leadId);

                create_log($lead, 'Edit Note : '.$note->title, '');

                return response()->json([
                    'status' => true,
                    'message' => 'Notes Updated Sucessfully',
                    'note' => ! empty($contact->c_full_name) ? $contact->c_full_name : '',
                    'is_upadted' => 1,
                ], 200);
            }
        } else {
            $input = [
                'lead_id' => $request->leadId,
                'contact_id' => $request->contact_id,
                'user_id' => $request->agentId,
                'description' => $request->description,
            ];
            $note = Note::create($input);
            // getting lead and contact
            $lead = Lead::find($request->leadId);
            $contact = Contact::find($request->contact_id);

            // attach the note to lead & contact
            $note->leads()->associate($lead);
            $note->contacts()->associate($contact);
            $note->save();

            create_log($lead, 'Create Note : '.$note->title, '');

            $newlyaddednote = Note::leftjoin('contacts', 'notes.contact_id', '=', 'contacts.id')
                ->leftjoin('users', 'notes.user_id', '=', 'users.id')
                ->select('notes.id', 'notes.description', 'notes.created_at', 'notes.contact_id', 'contacts.c_full_name as contact_name', 'users.name as agent_name')
                ->where('notes.id', $note->id)->first();

            if ($newlyaddednote) {
                $newlyaddednote->created_date = date('m/d/Y', strtotime($newlyaddednote->created_at));
            }

            return response()->json([
                'status' => true,
                'message' => 'Notes added Sucessfully',
                'note' => $newlyaddednote,
                'is_upadted' => 0,
            ], 200);
        }
    }

    public function addLeadFile(Request $request)
    {
        // Validate the request payload
        $request->validate([
            'leadId' => 'required|exists:leads,id',
            'files' => 'required|array',
            'files.*' => 'file',
            'description' => 'nullable|string|max:255',
        ]);

        // Fetch the lead by ID
        $lead = Lead::find($request->leadId);
        if (! $lead) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid Lead ID',
            ], 404);
        }

        // Initialize an array to hold saved file data
        $filesArray = [];

        // Check if files are provided
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                // Generate a unique file name
                $fileName = time().'_'.$file->getClientOriginalName();

                // Store the file in the public directory
                $filePath = $file->storeAs('uploads', $fileName, 'public');

                // Associate the uploaded file with the lead
                $uploadedFile = $lead->files()->create([
                    'name' => $fileName,
                    'description' => $request->description,
                    'file_path' => '/storage/'.$filePath, // Public storage path
                ]);

                // Log the file upload action
                create_log($lead, 'Upload File: '.$fileName, '');

                // Add the file details to the array
                $filesArray[] = [
                    'id' => $uploadedFile->id,
                    'name' => $uploadedFile->name,
                    'description' => $uploadedFile->description,
                    'date' => date('m/d/Y', strtotime($uploadedFile->created_at)),
                    'download_link' => url('leads/edit/file-download/'.$uploadedFile->id),
                ];
            }

            // Return success response with saved data
            return response()->json([
                'status' => true,
                'message' => 'Files added successfully',
                'file' => $filesArray,
            ], 200);
        }

        // Return error response if no files are provided
        return response()->json([
            'status' => false,
            'message' => 'No files provided for upload',
        ], 400);
    }

    public function destroyLeadNote(Request $request)
    {
        // get the note to delete
        $note = Note::find($request->id);
        if (! $note) {
            return response()->json([
                'status' => true,
                'message' => 'Note destroyed Sucessfully',
            ], 200);
        }
        $lead = Lead::find($note->leads->id);
        create_log($lead, 'Delete Note : '.$note->title, '');

        $note->delete();

        return response()->json([
            'status' => true,
            'message' => 'Note destroyed Sucessfully',
        ], 200);
    }

    public function destroyLeadFile(Request $request)
    {
        $file = File::find($request->id);
        if (! $file) {

            return response()->json([
                'status' => true,
                'message' => 'File destroyed Sucessfully',
            ], 200);
        }
        $lead = $file->uploaded_files;

        create_log($lead, 'Delete File : '.$file->name, '');

        $file->delete();

        return response()->json([
            'status' => true,
            'message' => 'File destroyed Sucessfully',
        ], 200);
    }

    public function updateIndiLeadData(Request $request)
    {
        $request->validate([
            'leadId' => 'required|integer|exists:leads,id',
            'dbFieldName' => 'required|string',
            // 'value' => 'nullable|string',
        ]);

        $lead = Lead::find($request->leadId);

        if ($lead) {
            $fieldvalue = $request->value;
            if ($request->otherPermission && $fieldvalue == 'other') {
                $fieldvalue = $request->otherValueField;

                if (! empty($request->catName)) {
                    if (! empty($request->requestType) && $request->requestType == 1) {
                        $fieldvalue = $this->makelogInCarrierTable($request->otherValueField, $request->catName);
                        $resArray[$request->catDbField] = $request->otherValueField;
                    } elseif (! empty($request->requestType) && $request->requestType == 2) {
                        $fieldvalue = $this->makelogInRatingTable($request->otherValueField, $request->catName);
                        $resArray[$request->catDbField] = $request->otherValueField;
                    }
                }
            } else {
                if (! empty($request->catName)) {
                    if (! empty($request->requestType) && $request->requestType == 1) {
                        $resArray[$request->catDbField] = Carrier::where('id', $fieldvalue)->pluck('name')->first() ?? '';
                    } elseif (! empty($request->requestType) && $request->requestType == 2) {
                        $resArray[$request->catDbField] = Rating::where('id', $fieldvalue)->pluck('name')->first() ?? '';
                    } elseif (! empty($request->requestType) && $request->requestType == 3) {
                        $resArray[$request->catDbField] = LeadSource::where('id', $fieldvalue)->pluck('name')->first() ?? '';
                    }
                }
            }

            $lead->{$request->dbFieldName} = $fieldvalue;
            $resArray[$request->dbFieldName] = $fieldvalue;

            $lead->save();

            create_log($lead, 'updated  lead : column - '.$request->dbFieldName.'  value - '.$request->value, '');

            return response()->json([
                'status' => true,
                'message' => 'Lead updated successfully',
                'res_array' => $resArray,
            ]);
        } else {
            // Return an error response if the lead is not found
            return response()->json([
                'status' => false,
                'message' => 'Lead not found',
            ], 404);
        }
    }

    public function updateIndiLeadDataGroup(Request $request)
    {
        $request->validate([
            'leadId' => 'required|integer|exists:leads,id',
        ]);

        $lead = Lead::find($request->leadId);

        if ($lead) {
            $resArray = [];

            foreach ($request->input('inputdata', []) as $value) {
                $fieldvalue = $value['value'];
                $dbFieldName = $value['dbFieldName'];

                if ($value['otherPermission'] && $fieldvalue == 'other') {
                    $fieldvalue = $value['otherValueField'];
                    if (! empty($value['catName']) && ! empty($value['requestType']) && $value['requestType'] == 1) {
                        $fieldvalue = $this->makelogInCarrierTable($value['otherValueField'], $value['catName']);
                        $resArray[$value['catDbField']] = $value['otherValueField'];
                    }
                } elseif (! empty($value['catName']) && ! empty($value['requestType']) && $value['requestType'] == 1) {
                    $resArray[$value['catDbField']] = Carrier::where('id', $fieldvalue)->pluck('name')->first() ?? '';
                }

                $lead->{$dbFieldName} = $fieldvalue;
                $resArray[$dbFieldName] = $fieldvalue;

                create_log($lead, 'Updated lead: column - '.$dbFieldName.' value - '.$fieldvalue, '');
            }

            $lead->save();

            $this->leadTotalPremiumUpdate($lead->id);

            return response()->json([
                'status' => true,
                'message' => 'Lead updated successfully',
                'res_array' => $resArray,
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Lead not found',
            ], 404);
        }
    }

    public function updateAdditionalPolicyLead(Request $request)
    {
        $request->validate([
            'leadId' => 'required',
            'policy_type' => 'required',
            'carrier' => 'required',
        ]);

        if (! empty($request->item_id)) {
            $additional = LeadAdditionalPolicy::find($request->item_id);
            if (! $additional) {
                return response()->json([
                    'status' => false,
                    'message' => 'Additional',
                ], 200);
            }
        } else {
            $additional = new LeadAdditionalPolicy;
            $additional->lead_id = $request->leadId;
        }
        if ($request->carrier === 'other') {
            $carrier_id = $this->makelogInCarrierTable($request->carrierOther, $request->policy_type);
        } else {
            $carrier_id = $request->carrier;
        }
        $additional->carrier = $carrier_id;
        $additional->policy_type = $request->policy_type;
        $additional->expiry_premium = $request->expiry_premium;
        $additional->policy_renewal_date = $request->policy_renewal_date;
        $additional->hurricane_deductible = $request->hurricane_deductible;
        $additional->all_other_perils = $request->all_other_perils_deductible;
        $additional->insurance_coverage = $request->notes;

        $additional->save();

        $res = $this->leadIdBasedData($request->leadId);

        $this->leadTotalPremiumUpdate($request->leadId);

        return response()->json([
            'status' => true,
            'message' => 'Additional',
            'additonalPolicy' => $res['additonalPolicy'],
        ], 200);
    }

    public function deleteAdditionalPolicyLead(Request $request)
    {
        $request->validate([
            'leadId' => 'required',
        ]);

        if (! empty($request->item_id)) {
            $additional = LeadAdditionalPolicy::find($request->item_id);
            if ($additional) {
                $additional->delete();
            }
        }

        $res = $this->leadIdBasedData($request->leadId);

        return response()->json([
            'status' => true,
            'message' => 'Additional',
            'additonalPolicy' => $res['additonalPolicy'],
        ], 200);
    }

    public function leadAsanaDetails(Request $request)
    {
        $leadId = $request->input('lead_id');

        $details = $this->getQuestionAsana($leadId);
        $carrier = $this->carrierListDisplay();

        return response()->json([
            'status' => true,
            'message' => 'Lead Asana Details',
            'carrier' => $carrier,
            'details' => $details['questions'],
            'asana_stage' => $details['asana_stage'],
            'stage_completed' => $details['stage_completed'],
            'asana_priority' => $details['asana_priority'],
        ], 200);
    }

    public function updateleadAsanaDetails(Request $request)
    {
        $leadId = $request->input('leadId');
        $stagewiseQuestion = $request->input('stagewise_question');

        $closeModel = false;

        $leadAsana = LeadAsanaDetail::where('lead_id', $leadId)->first();
        if (! $leadAsana) {
            $leadAsana = new LeadAsanaDetail;
            $leadAsana->lead_id = $leadId;
            $leadAsana->asana_stage = 1;
            $leadAsana->last_updated_date = Carbon::now()->toDateString();
        }
        foreach ($stagewiseQuestion as $keyquestion) {
            // echo "<pre>";print_r($stagewiseQuestion);exit;
            $leadAsana->{$keyquestion['short_hand']} = $keyquestion['answer'];
            if ($keyquestion['show_selection'] && $keyquestion['answer'] == 'yes') {
                $leadAsana->{$keyquestion['selection_short_hand']} = $keyquestion['selection_list'];
            }
        }
        if ($leadAsana->asana_stage == $request->input('id')) {
            $leadAsana->last_updated_date = Carbon::now()->toDateString();
            if ($leadAsana->asana_stage < 11) {
                $leadAsana->asana_stage = $this->getAsanaStagePriority($leadAsana->asana_stage);
            } else {
                if (! empty($request->input('renewalDate'))) {
                    $leadAsana->stage_completed = 1;
                }
                $leadAsana->renewal_date = $request->input('renewalDate');
                $closeModel = true;
            }
            $this->asanaStageChangeMsgShoot($leadId, $leadAsana->asana_stage);
        }
        $leadAsana->save();

        $details = $this->getQuestionAsana($leadId);

        return response()->json([
            'status' => true,
            'message' => 'Lead Asana Updated Sucessfully',
            'leadId' => $leadId,
            'stagewise_question' => $stagewiseQuestion,
            'close_model' => $closeModel,
            'asana_stage' => $leadAsana->asana_stage,
            'details' => $details['questions'],
            'asana_stage' => $details['asana_stage'],
            'asana_priority' => $details['asana_priority'],
            'stage_completed' => $details['stage_completed'],
        ], 200);
    }

    public function getCollaboratorDetails(Request $request)
    {
        $leadId = $request->input('leadId');

        $lead = Lead::find($leadId);

        if (! $lead) {
            return response()->json([
                'status' => false,
                'message' => 'Lead not found',
            ], 400);
        }

        $userList = User::role(['Agent', 'Service & Agent', 'Admin', 'Super Admin', 'Service Team', 'Manager'])->select('id', 'name', 'email')->get();

        $collabList = $lead->collaborators()
            ->select('users.id', 'users.name', 'users.email')
            ->get();

        $collabArr = $collabList->pluck('id')->toArray();

        return response()->json([
            'status' => true,
            'message' => 'Collaborator Details',
            'collabList' => $collabList,
            'collabArr' => $collabArr,
            'userList' => $userList,
        ]);
    }

    public function updateCollaboratorDetails(Request $request)
    {
        $leadId = $request->input('leadId');
        $collabUserIds = $request->input('collabUserIds', []);

        $lead = Lead::find($leadId);
        if (! $lead) {
            return response()->json([
                'status' => false,
                'message' => 'Lead not found',
            ], 400);
        }

        $lead->collaborators()->sync($collabUserIds);

        $lead = $lead->fresh('collaborators:id,name,email');

        return response()->json([
            'status' => true,
            'message' => 'Collaborators updated successfully',
            'collaborators' => $lead->collaborators,
        ]);
    }

    public function updateAssignee(Request $request)
    {
        $leadId = $request->input('leadId');
        $assignee = $request->input('assignee');
        $screenType = $request->input('screenType');

        $lead = Lead::with([
            'assignedUser:id,name,email',
            'leadAsanaDetail:id,lead_id,asana_stage',
            'collaborators:id,name,email',
        ])->find($leadId);

        if (! $lead) {
            return response()->json([
                'status' => false,
                'message' => 'Lead not found',
            ], 404);
        }

        $lead->assigned_user_id = $assignee;
        $lead->save();

        $lead = $lead->fresh('assignedUser:id,name,email');

        $assignedUser = $lead->assignedUser;

        $assignedUserName = '';
        $assignedUserId = 0;

        if (! $assignedUser) {
            $assignedUser = $lead->customUserGetting();
        }

        if (! empty($assignedUser->name)) {
            $assignedUserName = $assignedUser->name;
            $assignedUserId = $assignedUser->id;

            $this->assigneeAddedMessageShoot($assignedUserName, $assignedUserId, $lead, $screenType);
        }

        $this->makeLogForAssigneeAgent($assignedUserId, $lead);

        return response()->json([
            'status' => true,
            'message' => "{$lead->name} assignee has been updated",
            'assignee_user' => $assignedUser,
        ]);
    }
}
