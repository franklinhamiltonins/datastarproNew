<?php

namespace App\Http\Controllers;

use App\Jobs\ActivityReportDownload;
use App\Jobs\ProcessMailDailyCallReportJob;
use App\Jobs\ProcessMailerLeadTrackerReportJob;
use App\Model\ActivityReport;
use App\Model\ActivityReportFile;
use App\Model\LeadsModel\Lead;
use App\Model\LeadSource;
use App\Model\MailerLeadTracker;
use App\Traits\ActivityReportTrait;
use App\Traits\CommonFunctionsTrait;
use App\Traits\MessageConstantsTrait;
use App\Traits\SMTPRelatedTrait;
use Illuminate\Http\Request;

class ActivityReportController extends Controller
{
    use ActivityReportTrait, CommonFunctionsTrait, MessageConstantsTrait, SMTPRelatedTrait;

    protected $mailAgentId;

    /**
     * Constructor with middleware permissions
     */
    public function __construct()
    {
        $this->middleware('permission:report-activity-form', [
            'only' => ['activity', 'saveAgentActivity'],
        ]);

        $this->middleware('permission:report-mailer-lead-form', [
            'only' => ['mailerleadtracker', 'saveMailLeadTracker'],
        ]);

        $this->middleware('permission:report-activity-result', [
            'only' => ['activityReport', 'activityList'],
        ]);

        $this->middleware('permission:report-mailer-lead-result', [
            'only' => ['mailerLeadReport', 'mailLeadTrackerList'],
        ]);

        $this->middleware('permission:report-daily-call-result', [
            'only' => ['daillyCallReport', 'dailycallReportList'],
        ]);

        $this->middleware('permission:report-activity-download', [
            'only' => ['activityListDownload'],
        ]);

        $this->middleware('permission:report-mailer-lead-download', [
            'only' => ['mailLeadTrackerListDownload'],
        ]);

        $this->middleware('permission:report-daily-call-download', [
            'only' => ['dailycallReportListDownload'],
        ]);

        $this->middleware('permission:report-mailer-lead-delete', [
            'only' => ['deleteMailTracker'],
        ]);

        $this->mailAgentId = config('custom.mail_sent_user_id');
    }

    /**
     * Show activity report form
     */
    public function activity()
    {
        $user = auth()->user();
        $isAdminUser = $this->checkingIsAdminUser($user);
        $allDisplay = $this->checkingIsAllAccountDisplay($user, $isAdminUser);
        $agentId = $user->id;
        $agentUsers = $this->getAgentListing($isAdminUser, $agentId, false, $user->getRoleNames()->toArray());

        return view(
            'activityreport/tracker.activity',
            compact('agentUsers', 'agentId', 'isAdminUser', 'allDisplay')
        );
    }

    /**
     * Show mailer lead tracker form
     */
    public function mailerleadtracker()
    {
        $user = auth()->user();
        $isAdminUser = $this->checkingIsAdminUser($user);
        $allDisplay = $this->checkingIsAllAccountDisplay($user, $isAdminUser);
        $agentId = $user->id;
        $agentUsers = $this->getAgentListing($isAdminUser, $agentId, false, $user->getRoleNames()->toArray());
        $leadSource = LeadSource::select('id', 'name')->where('status', 1)->get();
        $contactsTitle = Lead::contactTitle();
        $statusOptions = self::getContactStatusOptions();
        $edit = false;

        return view(
            'activityreport/tracker.mailerleadtracker',
            compact(
                'leadSource',
                'agentUsers',
                'contactsTitle',
                'agentId',
                'edit',
                'statusOptions',
                'isAdminUser',
                'allDisplay'
            )
        );
    }

    /**
     * Edit mail tracker record
     */
    public function editMailTracker($encodedId)
    {
        $id = base64_decode($encodedId);
        $mailLead = MailerLeadTracker::where('id', $id)->first();

        if (! $mailLead) {
            return redirect()->route('agentreport.mailerLeadIndex');
        }

        $user = auth()->user();
        $isAdminUser = $this->checkingIsAdminUser($user);
        $allDisplay = $this->checkingIsAllAccountDisplay($user, $isAdminUser);
        $agentId = $user->id;
        $agentUsers = $this->getAgentListing($isAdminUser, $agentId, false, $user->getRoleNames()->toArray());
        $leadSource = LeadSource::select('id', 'name')->where('status', 1)->get();
        $contactsTitle = Lead::contactTitle();
        $statusOptions = self::getContactStatusOptions();
        $edit = true;

        return view(
            'activityreport/tracker.mailerleadtracker',
            compact(
                'leadSource',
                'agentUsers',
                'contactsTitle',
                'agentId',
                'statusOptions',
                'edit',
                'mailLead',
                'isAdminUser',
                'allDisplay'
            )
        );
    }

    /**
     * Delete mail tracker record
     */
    public function deleteMailTracker($encodedId)
    {
        $id = base64_decode($encodedId);
        $mailLead = MailerLeadTracker::where('id', $id)->first();

        if ($mailLead) {
            $mailLead->delete();
        }

        return response()->json([
            'status' => true,
            'message' => 'Record deleted successfully',
        ]);
    }

    /**
     * Save agent activity report
     */
    public function saveAgentActivity(Request $request)
    {
        $data = $request->except('signed_aor_doc', 'aor');
        $user = auth()->user();

        // Check for duplicate entry
        if ($this->isDuplicateActivityReport($data['date'], $data['user_id'])) {
            return response()->json([
                'status' => false,
                'redirection' => false,
                'message' => 'You cannot fill out the Agent Activity Form twice in a single day.',
            ]);
        }

        // Create activity report
        $report = ActivityReport::create($data);

        // Save AOR data if present
        $this->saveAorData($report, $request->aor);

        // Save signed documents if present
        $this->saveSignedAorDocuments($report, $request->file('signed_aor_doc'));

        // Determine redirection based on permission
        $redirection = $this->getRedirectionBasedOnPermission(
            $user,
            'report-activity-result'
        );

        return response()->json([
            'status' => true,
            'redirection' => $redirection,
            'message' => 'Agent activity report saved successfully.',
        ]);
    }

    /**
     * Save mail lead tracker
     */
    public function saveMailLeadTracker(Request $request)
    {
        // Validate request
        $validationResult = $this->validateMailLeadTrackerRequest($request);

        if (! $validationResult['valid']) {
            return response()->json([
                'status' => false,
                'message' => $validationResult['message'],
            ]);
        }

        $data = $request->input();
        $user = auth()->user();

        // Save or update tracker record
        $result = $this->saveMailLeadTrackerRecord($data);

        if ($result['error']) {
            MailerLeadTracker::where('id', $result['report']->id)->update([
                'status' => 2,
            ]);
        } else {
            // Check if lead can be created from mailer data
            $trackerId = $result['report']->id;

            if (empty($result['report']->lead_id)) {
                $checkEntry = $this->checkCanMakeEntryLead($data);

                if ($checkEntry) {
                    $leadResult = $this->createLeadWithMailerData($data, $trackerId);

                    if ($leadResult['error']) {
                        MailerLeadTracker::where('id', $trackerId)->update([
                            'status' => 2,
                        ]);
                    }
                }
            }
        }

        // Determine redirection based on permission
        $redirection = $this->getRedirectionBasedOnPermission(
            $user,
            'report-mailer-lead-result'
        );

        return response()->json([
            'status' => ! $result['error'],
            'redirection' => $redirection,
            'message' => $result['msg'],
        ]);
    }

    /**
     * Show activity report view
     */
    public function activityReport()
    {
        $user = auth()->user();
        $isAdminUser = $this->checkingIsAdminUser($user);
        $allDisplay = $this->checkingIsAllAccountDisplay($user, $isAdminUser);
        $agentId = auth()->user()->id;
        $agentUsers = $this->getAgentListing($isAdminUser, $agentId, false, $user->getRoleNames()->toArray());

        return view(
            'activityreport/report.activity-report',
            compact('agentUsers', 'agentId', 'isAdminUser', 'allDisplay')
        );
    }

    /**
     * Get activity list data
     */
    public function activityList(Request $request)
    {
        $user = auth()->user();
        $managerId = $this->getManagerId($user);
        $query = $this->activityListQuery($request->input(), false, $managerId);

        if ($request->view_type == 1) {
            return datatables()->of($query)
                ->addIndexColumn()
                ->addColumn('agent_name', function ($row) {
                    return optional($row->agent)->name ?? '';
                })
                ->addColumn('details', function ($row) {
                    $id = base64_encode($row->id);
                    $url = url("/agentreport/activity_details/{$id}");

                    return '<a href="'.$url.'" class="btn btn-sm btn-info">View</a>';
                })
                ->rawColumns(['details'])
                ->make(true);
        }

        // Consolidated view
        return datatables()->of($query->get())
            ->addIndexColumn()
            ->make(true);
    }

    /**
     * Download activity list report
     */
    public function activityListDownload(Request $request)
    {
        $requestData = $request->all();

        // Check SMTP configuration
        $smtpSetupData = $this->checkMailConfigurationUserWise($this->mailAgentId);

        if ($smtpSetupData == 0) {
            return response()->json([
                'status' => false,
                'message' => self::SMTP_NOT_CONFIGURED,
            ]);
        }

        $user = auth()->user();
        $requestData['manager_id'] = $this->getManagerId($user);

        ActivityReportDownload::dispatch($requestData, $this->mailAgentId);

        return response()->json([
            'status' => true,
            'message' => self::REPORT_GENERATING,
        ]);
    }

    /**
     * Show activity details
     */
    public function activityDetails($encodedId)
    {
        $id = base64_decode($encodedId);
        $report = ActivityReport::find($id);

        if (! $report) {
            return redirect()->route('agentreport.activityReport');
        }

        $aor = $report->aor()
            ->select('aor', 'aor_community_name', 'aor_effective_date', 'expiring_aor_premium')
            ->get();

        $files = $report->files()
            ->select('id', 'file_path', 'original_name', 'mime_type')
            ->get();

        return view(
            'activityreport/report.activity-details-report',
            compact('report', 'aor', 'files')
        );
    }

    /**
     * Show mailer lead report
     */
    public function mailerLeadReport()
    {
        $user = auth()->user();
        $isAdminUser = $this->checkingIsAdminUser($user);
        $allDisplay = $this->checkingIsAllAccountDisplay($user, $isAdminUser);
        $agentId = auth()->user()->id;
        $agentUsers = $this->getAgentListing($isAdminUser, $agentId, false, $user->getRoleNames()->toArray());
        $leadSource = LeadSource::select('id', 'name')->where('status', 1)->get();

        return view(
            'activityreport/report.mail-tracker-report',
            compact('agentUsers', 'agentId', 'leadSource', 'isAdminUser', 'allDisplay')
        );
    }

    /**
     * Get mail lead tracker list data
     */
    public function mailLeadTrackerList(Request $request)
    {
        $user = auth()->user();
        $managerId = $this->getManagerId($user);
        $query = $this->generateMailLeadTrackerData($request->all(), $managerId);

        if ($request->view_type == 1) {
            return datatables()->of($query)
                ->addIndexColumn()
                ->addColumn('agent_name', function ($lead) {
                    return optional($lead->agent)->name ?? '-';
                })
                ->addColumn('lead_source_name', function ($lead) {
                    return optional($lead->leadSource)->name ?? '-';
                })
                ->addColumn('business', function ($lead) {
                    return $lead->business ?? '-';
                })
                ->addColumn('contact_firstname', function ($lead) {
                    return $lead->contact_firstname ?? '-';
                })
                ->addColumn('contact_lastname', function ($lead) {
                    return $lead->contact_lastname ?? '-';
                })
                ->addColumn('phone', function ($lead) {
                    return $lead->phone ?? '-';
                })
                ->addColumn('email_address', function ($lead) {
                    return $lead->email_address ?? '-';
                })
                ->addColumn('status_note', function ($lead) {
                    return $lead->status_note ?? '-';
                })
                ->addColumn('action', function ($lead) {
                    return $this->buildMailTrackerActionColumn($lead);
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        // Consolidated View
        return datatables()->of($query->get())
            ->addIndexColumn()
            ->make(true);
    }

    /**
     * Build action column HTML for mail tracker
     */
    protected function buildMailTrackerActionColumn($lead): string
    {
        $id = base64_encode($lead->id);
        $view = '';

        if ($lead->status == 0) {
            $view = '<a href="/agentreport/mailerleadtracker/edit/'.$id.
                '" class="btn btn-sm btn-success"><i class="fa fa-edit"></i></a>';
        } elseif ($lead->status == 1) {
            $view = '<a href="/leads/edit/'.base64_encode($lead->lead_id).
                '" class="btn btn-sm btn-info"><i class="fa fa-eye"></i></a>';
        } elseif ($lead->status == 2) {
            $view = '<button class="btn btn-sm btn-danger deleteMailer" data-id="'.
                $id.'"><i class="fa fa-trash"></i></button>';
        }

        return $view;
    }

    /**
     * Download mail lead tracker report
     */
    public function mailLeadTrackerListDownload(Request $request)
    {
        $requestData = $request->all();

        // Check SMTP configuration
        $smtpSetupData = $this->checkMailConfigurationUserWise($this->mailAgentId);

        if ($smtpSetupData == 0) {
            return response()->json([
                'status' => false,
                'message' => self::SMTP_NOT_CONFIGURED,
            ]);
        }

        $user = auth()->user();
        $requestData['manager_id'] = $this->getManagerId($user);

        ProcessMailerLeadTrackerReportJob::dispatch($requestData, $this->mailAgentId);

        return response()->json([
            'status' => true,
            'message' => self::REPORT_GENERATING,
        ]);
    }

    /**
     * Download file
     */
    public function fileDownload($id)
    {
        $file = ActivityReportFile::find($id);

        if (! $file || ! $file->file_path) {
            abort(404, 'File path missing.');
        }

        // Clean the DB path
        $cleanPath = preg_replace('#^/?(storage|app/public)/#', '', $file->file_path);

        // Build full path
        $absolutePath = storage_path('app/public/'.$cleanPath);

        if (! file_exists($absolutePath)) {
            abort(404, 'File does not exist at '.$absolutePath);
        }

        return response()->download($absolutePath);
    }

    /**
     * Show daily call report
     */
    public function daillyCallReport()
    {
        $user = auth()->user();
        $isAdminUser = $this->checkingIsAdminUser($user);
        $allDisplay = $this->checkingIsAllAccountDisplay($user, $isAdminUser);
        $agentId = auth()->user()->id;
        $agentUsers = $this->getAgentListing($isAdminUser, $agentId, false, $user->getRoleNames()->toArray());

        return view(
            'activityreport/report.daillyCallReport',
            compact('agentUsers', 'agentId', 'isAdminUser', 'allDisplay')
        );
    }

    /**
     * Get daily call report list data
     */
    public function dailycallReportList(Request $request)
    {
        $formattedDate = $this->getFormatedDate($request->input());
        $agentId = $request->agent;
        $user = auth()->user();
        $managerId = $this->getManagerId($user);

        $results = $this->generateDailyReportData(
            $agentId,
            $formattedDate['from'],
            $formattedDate['to'],
            $managerId
        );

        return datatables()->of($results)
            ->addIndexColumn()
            ->make(true);
    }

    /**
     * Download daily call report
     */
    public function dailycallReportListDownload(Request $request)
    {
        $requestData = $request->all();

        // Check SMTP configuration
        $smtpSetupData = $this->checkMailConfigurationUserWise($this->mailAgentId);

        if ($smtpSetupData == 0) {
            return response()->json([
                'status' => false,
                'message' => self::SMTP_NOT_CONFIGURED,
            ]);
        }

        $user = auth()->user();
        $requestData['manager_id'] = $this->getManagerId($user);

        ProcessMailDailyCallReportJob::dispatch($requestData, $this->mailAgentId);

        return response()->json([
            'status' => true,
            'message' => self::REPORT_GENERATING,
        ]);
    }

    /**
     * Search communities
     */
    public function searchComm(Request $request)
    {
        $keyword = $request->get('keyword');
        $leads = Lead::select('name', 'address1', 'id', 'city', 'state', 'zip')
            ->where('name', 'like', "%{$keyword}%")
            ->limit(10)
            ->get();

        return response()->json($leads);
    }
}
