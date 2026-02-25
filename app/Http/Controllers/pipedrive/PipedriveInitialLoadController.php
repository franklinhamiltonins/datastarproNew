<?php

namespace App\Http\Controllers\pipedrive;

use App\Model\AsanaQuestion;
use App\Model\ContactStatus;
use App\Model\File;
use App\Model\LeadAsanaDetail;
use App\Model\LeadInfoLog;
use App\Model\LeadsModel\Lead;
use App\Model\LeadsModel\Log;
use App\Model\LeadsModel\Note;
use App\Model\Setting;
use App\Traits\CommonFunctionsTrait;
use App\Traits\LoginFunctionTrait;
use App\Traits\PipeDriveTrait;
use App\Traits\SMTPRelatedTrait;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;

class PipedriveInitialLoadController extends PipedriveLoginController
{
    use CommonFunctionsTrait,LoginFunctionTrait,PipeDriveTrait,SMTPRelatedTrait;

    public function differentDealStatus()
    {
        $status = ContactStatus::select('id', 'name')->where('false_status', 0)->where('display_in_pipedrive', 1)->orderBy('priority', 'ASC')->get();

        return response()->json([
            'status' => true,
            'message' => 'status',
            'status_list' => $status,
        ], 200);
    }

    public function statusWiseLeadList(Request $request)
    {
        $statusId = $request->input('statusId');
        $agentId = $request->input('agentId');
        $pageNumber = $request->input('page_number', 1);
        $pageSize = $request->input('page_size', 10);

        $latestStatus = DB::table('lead_status_wise_log')
            ->select(DB::raw('MAX(id) as id'), 'lead_id')
            ->where('status_id', 8)
            ->groupBy('lead_id');

        $leadQuery = Lead::where('leads.pipeline_status_id', $statusId)
            ->join('users', 'leads.pipeline_agent_id', '=', 'users.id')
            ->leftJoin('lead_asana_details', 'leads.id', '=', 'lead_asana_details.lead_id')
            ->leftJoinSub($latestStatus, 'latest_log', function ($join) {
                $join->on('leads.id', '=', 'latest_log.lead_id');
            })
            ->leftJoin('lead_status_wise_log as lswl', 'lswl.id', '=', 'latest_log.id')
            ->where(function ($query) {
                $query->whereNull('lead_asana_details.lead_id')
                    ->orWhere('lead_asana_details.stage_completed', '!=', 1);
            });

        if (! empty($agentId)) {
            $leadQuery->where(function ($query) use ($agentId) {
                $query->where('leads.pipeline_agent_id', $agentId)
                    ->orWhere('leads.assigned_user_id', $agentId);
            });
        }

        if (! empty($request->input('searchName'))) {
            $leadQuery->where('leads.name', 'like', '%'.$request->input('searchName').'%');
        }

        $totalInsuredAmount = $leadQuery->sum('leads.total_premium');

        if ($request->filled('sortBy')) {
            $sortBy = $request->input('sortBy');
            $orderBy = $request->input('orderBy', 'asc');

            switch ($sortBy) {
                case 'name':
                    $leadQuery->orderBy('leads.name', $orderBy);
                    break;

                case 'date':
                    $leadQuery->orderBy('lswl.start_timestamp', $orderBy);
                    break;

                case 'updated':
                    $leadQuery->orderBy('leads.updated_at', $orderBy);
                    break;

                case 'premium':
                    $leadQuery->orderBy('leads.total_premium', $orderBy);
                    break;
            }
        }

        $selectFields = [
            'leads.id',
            'leads.name',
            'leads.address1',
            'leads.created_at',
            'leads.updated_at',
            'leads.total_premium as insured_amount',
            'leads.policy_renewal_date',
            'users.name as pipeline_agent_name',
            'users.email as pipeline_agent_email',
            'leads.pipeline_agent_id',
            'leads.assigned_user_id',

            'lswl.start_timestamp as status8_start_timestamp',
        ];

        $leadList = $leadQuery->select($selectFields)
            ->paginate($pageSize, ['*'], 'page', $pageNumber);

        $leadList->getCollection()->load([
            'collaborators:id,name,email',
            'assignedUser:id,name,email',
        ]);

        $status = ContactStatus::select('special_marker', 'name')->find($statusId);

        if ($status && $status->special_marker === 3) {
            foreach ($leadList->items() as $lead) {
                $lead->display_tile_color = 4; // Green color
                $lead->assigned_user_custom = $lead->customUserGetting();
            }
        } else {
            $notifyDays = Setting::value('process_time_in_day_pipeline') ?? 4;
            $estTime = now()->subDays($notifyDays)->timezone('America/New_York');
            $nowTime = now()->timezone('America/New_York');

            foreach ($leadList->items() as $lead) {
                $displayTileColor = $this->decideColorTile($agentId, $notifyDays, $estTime, $nowTime, $statusId, $lead->id);
                $lead->display_tile_color = $displayTileColor;
                $lead->assigned_user_custom = $lead->customUserGetting();
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Lead list fetched successfully',
            'lead_list' => $leadList->getCollection(),
            'pagination' => [
                'total' => $leadList->total(),
                'current_page' => $leadList->currentPage(),
                'last_page' => $leadList->lastPage(),
                'per_page' => $leadList->perPage(),
                'totalInsuredAmount' => formatUSNumber($totalInsuredAmount),
            ],
        ], 200);
    }

    public function fetchTotalDealData(Request $request)
    {
        $agentId = $request->input('agentId');

        // Fetch the relevant status list only when needed in the query
        $statusList = $this->pipeDriveDisplayStatusList();

        // Get the sum of premium and count of matched entries
        $result = Lead::whereIn('pipeline_status_id', $statusList);

        if (! empty($agentId)) {
            $result = $result->where('pipeline_agent_id', $agentId);
        }
        if (! empty($request->input('searchName'))) {
            $result = $result->where('leads.name', 'like', '%'.$request->input('searchName').'%');
        }

        $result = $result->selectRaw('SUM(total_premium) as total_insured_amount, COUNT(*) as total_entries')
            ->first();

        $totalInsuredAmount = '0.00';
        $totalEntries = 0;

        if ($result) {
            $totalInsuredAmount = ! empty($result->total_insured_amount) ? formatUSNumber($result->total_insured_amount) : '0.00';
            $totalEntries = ! empty($result->total_entries) ? $result->total_entries : 0;
        }

        // Prepare a safe response, even if no result is found
        return response()->json([
            'status' => true,
            'message' => 'Lead total',
            'result' => [
                'total_insured_amount' => $totalInsuredAmount,
                'total_entries' => $totalEntries,
            ],
        ], 200);
    }

    public function allStatusList()
    {
        $statusList = ContactStatus::select('id', 'name', 'false_status', 'display_in_pipedrive')
            ->where(function ($query) {
                $query->whereNull('special_marker')
                    ->orWhere('special_marker', 3);
            })
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'status',
            'status_list' => $statusList,
        ], 200);
    }

    public function asanaStatusList()
    {
        $question = AsanaQuestion::select('id', 'name')->where('status', 1)->orderBy('priority', 'ASC')->get();

        return response()->json([
            'status' => true,
            'message' => 'question',
            'question' => $question,
        ], 200);

    }

    // helper function
    public function checkLeadNeedToRenewal()
    {
        $settingTimeData = Setting::select('renewal_days_in_pipeline')->first();
        $subDays = ! empty($settingTimeData->renewal_days_in_pipeline) ? $settingTimeData->renewal_days_in_pipeline : 0;
        $leadList = LeadAsanaDetail::where('lead_asana_details.asana_stage', 11)
            ->where('lead_asana_details.stage_completed', 1)
            ->where('lead_asana_details.renewal_date', '<=', Carbon::today()->addDays($subDays))
            // ->select('lead_asana_details.lead_id')
            ->limit(10)
            ->get()->toArray();

        foreach ($leadList as $keylead) {
            $this->makelogLeadAsanaWise($keylead);
            $this->clearLeadAsanaDetailTable($keylead['id']);

            $this->clearLeadDetailTable($keylead['lead_id']);
        }
    }

    // helper function
    public function makelogLeadAsanaWise($LeadAsanaDetail)
    {
        $leadInfoLog = new LeadInfoLog;
        $leadInfoLog->lead_id = $LeadAsanaDetail['lead_id'];
        $leadInfoLog->table_name = 'LeadAsanaDetail';
        $leadInfoLog->renewal_date = $LeadAsanaDetail['renewal_date'];
        $leadInfoLog->data = json_encode($LeadAsanaDetail);
        $leadInfoLog->save();
        unset($leadInfoLog);

        $lead = Lead::where('id', $LeadAsanaDetail['lead_id'])->first();
        if ($lead) {
            $additonalPolicy = $lead->leadAdditionalpolicy()->get()->toArray();
            $lead = $lead->toArray();
            $leadLog = new LeadInfoLog;
            $leadLog->lead_id = $LeadAsanaDetail['lead_id'];
            $leadLog->table_name = 'Lead';
            $leadLog->renewal_date = $LeadAsanaDetail['renewal_date'];
            $leadLog->data = json_encode($lead);
            $leadLog->save();

            unset($leadLog);

            $leadLog = new LeadInfoLog;
            $leadLog->lead_id = $LeadAsanaDetail['lead_id'];
            $leadLog->table_name = 'LeadAdditionalPolicy';
            $leadLog->renewal_date = $LeadAsanaDetail['renewal_date'];
            $leadLog->data = json_encode($additonalPolicy);
            $leadLog->save();

            unset($leadLog);
        }
    }

    public function differentQuestionWiseLead(Request $request)
    {
        $asanaStage = $request->input('asana_stage');
        $agentId = $request->input('agentId');
        $filterData = ! empty($request->input('filterData')) ? json_decode(json_encode($request->input('filterData'))) : (object) [];
        $pageNumber = $request->input('page_number', 1);
        $pageSize = $request->input('page_size', 10);
        $sortBy = $request->input('sortBy');
        $orderBy = $request->input('orderBy', 'asc');

        $statusId = $this->getSignedAorStatusId();

        if ($asanaStage == 1) {
            $this->checkLeadNeedToRenewal();

            $leadList = Lead::join('users', 'leads.pipeline_agent_id', '=', 'users.id')
                ->leftJoin('lead_asana_details', 'leads.id', '=', 'lead_asana_details.lead_id')
                ->where(function ($query) {
                    $query->where('lead_asana_details.asana_stage', 1)
                        ->orWhereNull('lead_asana_details.lead_id');
                })
                ->where('leads.pipeline_status_id', $statusId);
        } else {
            $leadList = Lead::join('lead_asana_details', 'leads.id', '=', 'lead_asana_details.lead_id')
                ->join('users', 'leads.pipeline_agent_id', '=', 'users.id')
                ->where('lead_asana_details.asana_stage', $asanaStage)
                ->where('lead_asana_details.stage_completed', 0)
                ->where('leads.pipeline_status_id', $statusId);
        }

        if (! empty($sortBy)) {
            if ($sortBy === 'name') {
                $leadList->orderBy('leads.name', $orderBy);
            } elseif ($sortBy === 'date') {
                $leadList->orderBy('status14_start_timestamp', $orderBy);
            } elseif ($request->input('sortBy') == 'updated') {
                $leadList->orderBy('leads.updated_at', $request->input('orderBy', 'asc'));
            } elseif ($sortBy === 'premium') {
                $leadList->orderBy('leads.total_premium', $orderBy);
            }
        }

        $leadList->leftJoin('lead_status_wise_log as lswl', function ($join) {
            $join->on('leads.id', '=', 'lswl.lead_id')
                ->where('lswl.status_id', '=', 14);
        });

        if (! empty($agentId)) {
            $leadList->where(function ($query) use ($agentId) {
                $query->where('leads.pipeline_agent_id', $agentId)
                    ->orWhere('leads.assigned_user_id', $agentId);
            });
        }

        if (! empty($filterData) && ! empty($filterData->name)) {
            $leadList->where('leads.name', 'like', '%'.$filterData->name.'%');
        }

        $totalInsuredAmount = $leadList->sum('total_premium');

        $selectFields = [
            'leads.id',
            'leads.name',
            'leads.address1',
            'leads.updated_at',
            'lead_asana_details.stage_completed',
            'leads.total_premium as insured_amount',
            'lead_asana_details.renewed_lead',
            'lead_asana_details.renewal_date',
            'leads.assigned_user_id',
            'leads.pipeline_agent_id',
            'leads.total_premium',
            'users.name as pipeline_agent_name',
            'users.email as pipeline_agent_email',
            'lswl.start_timestamp as status14_start_timestamp',
        ];

        $leadList = $leadList
            ->select($selectFields)
            ->paginate($pageSize, ['*'], 'page', $pageNumber);

        $leadList->getCollection()->load([
            'collaborators:id,name,email',
            'assignedUser:id,name,email',
        ]);

        foreach ($leadList->items() as $lead) {
            $lead->assigned_user_custom = $lead->customUserGetting();
        }

        return response()->json([
            'status' => true,
            'message' => 'Leads retrieved successfully',
            'lead_list' => $leadList->getCollection(),
            'pagination' => [
                'total' => $leadList->total(),
                'current_page' => $leadList->currentPage(),
                'last_page' => $leadList->lastPage(),
                'per_page' => $leadList->perPage(),
                'totalInsuredAmount' => $totalInsuredAmount,
            ],
        ], 200);

    }

    public function listSpecialStatus(Request $request)
    {
        $specialType = $request->input('specialType');
        $agentId = $request->input('agentId');
        $filterData = ! empty($request->input('filterData')) ? json_decode(json_encode($request->input('filterData'))) : (object) [];
        $pageNumber = $request->input('page_number', 1); // Default to page 1 if not provided
        $pageSize = $request->input('page_size', 20);    // Default page size to 20 if not provided

        if (isset($filterData->agent_id)) {
            $agentId = $filterData->agent_id;
        }

        if ($specialType == 4) {
            $status = ContactStatus::select('id', 'name')->where('special_marker', 3)->first();
            if ($status) {
                $leadQuery = Lead::join('lead_asana_details', 'leads.id', '=', 'lead_asana_details.lead_id')
                    ->where(function ($query) {
                        $query->where('lead_asana_details.stage_completed', 1);
                        // ->orWhereNull('lead_asana_details.lead_id'); // Ensure there is no entry
                    })
                    ->where('leads.pipeline_status_id', $status->id);
            }
        } else {
            $status = ContactStatus::select('id', 'name')->where('special_marker', $request->input('specialType'))->first();
            if ($status) {
                // Build the query
                $leadQuery = Lead::where('leads.pipeline_status_id', $status->id);
            }
        }
        if (! $status) {
            return response()->json([
                'status' => false,
                'message' => 'Something Went Wrong',
            ], 500);
        }

        if (! empty($agentId)) {
            $leadQuery = $leadQuery->where('leads.pipeline_agent_id', $agentId);
        }

        if (! empty($filterData) && ! empty($filterData->name)) {
            $leadQuery = $leadQuery->where('leads.name', 'like', '%'.$filterData->name.'%');
        }

        // Calculate the total insured amount
        $totalInsuredAmount = $leadQuery->sum('leads.total_premium');

        if (! empty($request->input('columnKey'))) {
            $leadQuery = $leadQuery->orderBy($request->input('columnKey'), $request->input('direction'));
        }

        // Select relevant lead fields and paginate the results
        if ($specialType == 4) {
            $leadList = $leadQuery->select('leads.id', 'leads.name', 'leads.address1', 'leads.city', 'leads.zip', 'leads.total_premium as insured_amount', 'lead_asana_details.renewal_date');
        } else {
            $leadList = $leadQuery->select('leads.id', 'leads.name', 'leads.address1', 'leads.city', 'leads.zip', 'leads.total_premium as insured_amount');
        }

        $leadList = $leadQuery->paginate($pageSize, ['*'], 'page', $pageNumber);

        // Return the response
        return response()->json([
            'status' => true,
            'message' => 'Lead list fetched successfully',
            'lead_list' => $leadList->items(), // The actual list of leads
            'pagination' => [
                'total' => $leadList->total(),   // Total number of records
                'current_page' => $leadList->currentPage(), // Current page
                'last_page' => $leadList->lastPage(), // Total number of pages
                'per_page' => $leadList->perPage(), // Number of items per page
                'totalInsuredAmount' => ! empty($totalInsuredAmount) ? formatUSNumber($totalInsuredAmount) : '0.00',
            ],
        ], 200);
    }

    public function leadsNotesList(Request $request)
    {
        $notes = Note::leftjoin('contacts', 'notes.contact_id', '=', 'contacts.id')
            ->leftjoin('users', 'notes.user_id', '=', 'users.id')
            ->select('notes.id', 'notes.description', 'notes.created_at', 'notes.contact_id', 'contacts.c_full_name as contact_name', 'users.name as agent_name')
            ->where('notes.lead_id', $request->leadId)
            ->where('notes.deleted_at', null)
            ->orderBy('notes.id', 'desc')->get();

        foreach ($notes as $key => $value) {
            $notes[$key]->created_date = date('m/d/Y', strtotime($value->created_at));
        }

        return response()->json([
            'status' => true,
            'message' => 'notes',
            'notes' => $notes,
        ], 200);
    }

    public function leadsLogsList(Request $request)
    {
        $agentId = $request->input('agentId');
        $leadId = $request->input('leadId');

        $logsQuery = Log::with('users')
            ->where('lead_id', $leadId)
            ->orderByDesc('id');

        if (! empty($agentId)) {
            $logsQuery->where('user_id', $agentId);
        }

        $logs = $logsQuery->limit(100)->get();

        $logsarray = $logs->map(function ($log) {
            return [
                'username' => $log->users && $log->users->name ? $log->users->name : '',
                'action' => $log->action,
                'id' => $log->id,
                'date' => $log->created_at->format('m/d/Y'),
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'logs',
            'logs' => $logsarray,
        ], 200);
    }

    public function leadsFilesList(Request $request)
    {
        $fileQuery = File::query(); // start query
        $leadId = (! empty($request->leadId)) ? $request->leadId : (''); // get the lead id

        // $fileQuery->where('lead_id', $leadId);// get the files for the specific lead id
        $fileQuery->whereHasMorph('uploaded_files', [Lead::class], function ($query) use ($leadId) {
            $query->where('uploaded_files_id', $leadId);
        });

        $fileQuery = $fileQuery->select('id', 'name', 'description', 'created_at')->orderBy('created_at', 'DESC')->get();

        $filesarray = [];

        foreach ($fileQuery as $key => $valuefiles) {
            $filesarray[] = [
                'id' => $valuefiles->id,
                'name' => $valuefiles->name,
                'description' => $valuefiles->description,
                'date' => date('m/d/Y', strtotime($valuefiles->created_at)),
                'download_link' => url('leads/edit/file-download/'.$valuefiles->id),
            ];
        }
        unset($fileQuery);

        return response()->json([
            'status' => true,
            'message' => 'files',
            'files' => $filesarray,
        ],200);
    }
}
