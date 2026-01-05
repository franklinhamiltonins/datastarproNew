<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;

use App\Traits\CommonFunctionsTrait;
use App\Traits\SMTPRelatedTrait;
use App\Model\Setting;
use DB;
use App\Model\LeadsModel\Lead;
use App\Model\AsanaQuestion;


use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ProcessAgentWiseLeadWiseReport
{
    use CommonFunctionsTrait,SMTPRelatedTrait;

    public function processDataAgentWise($agent)
    {
        $agentId = $agent["id"];
        $notifyDays = Setting::value('process_time_in_day_pipeline') ?? 4;
        $estTime = now()->subDays($notifyDays)->timezone('America/New_York');
        $nowTime = now()->timezone('America/New_York');
        $sentMailId = env('MY_MAIL_SENT_USER_ID');
        // echo "<pre>";print_r($sentMailId);exit;

        // Fetch data
        $backDatedLeadPipeDrive = $this->getBackDatedPipeDriveLeads($agentId, $notifyDays, $estTime, $nowTime);
        $backDatedLeadBindMgmt = $this->getBackDatedBindMgmtLeads($agentId, $notifyDays, $estTime, $nowTime);

        // Generate CSVs
        $fileNamePipedrive = 'critical_pipedrive_lead_list_' . now()->format('Y_m_d_H_i_s') . '.csv';
        $fileNameBindMgmt = 'critical_bindmgmt_lead_list_' . now()->format('Y_m_d_H_i_s') . '.csv';
        $filePathPipeDrive = $this->generateCSV($backDatedLeadPipeDrive, [
            'Lead ID', 'Lead Name' ,'Status', 'Agent'
        ], $fileNamePipedrive);

        $filePathBindMgmt = $this->generateCSV($backDatedLeadBindMgmt, [
            'Lead ID', 'Lead Name', 'Agent', 'Stage'
        ], $fileNameBindMgmt);

        // Send email with attachments
        $this->sendCriticalLeadEmailWithAttachments(
            $sentMailId,$agent,
            $filePathPipeDrive, $fileNamePipedrive, $backDatedLeadPipeDrive,
            $filePathBindMgmt, $fileNameBindMgmt, $backDatedLeadBindMgmt
        );


        // Cleanup
        $this->deleteFiles([$filePathPipeDrive, $filePathBindMgmt]);

        // echo $agentId;exit;

    }

    protected function getBackDatedPipeDriveLeads($agentId, $notifyDays, $estTime, $nowTime)
    {
        $statusList = $this->pipeDriveDisplayStatusList();
        $results = [];

        Lead::whereIn('pipeline_status_id', $statusList)
            ->where(function ($query) {
                $query->whereNull('lead_asana_details.lead_id')
                      ->orWhere('lead_asana_details.stage_completed', '!=', 1);
            })
            ->when($agentId, function ($query) use ($agentId) {
                return $query->where('pipeline_agent_id', $agentId);
            })
            ->join('users', 'leads.pipeline_agent_id', '=', 'users.id')
            ->join('contact_status', 'leads.pipeline_status_id', '=', 'contact_status.id')
            ->leftJoin('lead_asana_details', 'leads.id', '=', 'lead_asana_details.lead_id')
            ->select("leads.id","leads.name","leads.pipeline_agent_id","leads.pipeline_status_id",
                     "contact_status.name as pipeline_status_name","users.name as pipeline_agent_name")
            ->chunk(50, function ($leadData) use (&$results, $agentId, $notifyDays, $estTime, $nowTime) {
                foreach ($leadData as $lead) {
                    if ($this->decideColorTile($agentId, $notifyDays, $estTime, $nowTime, $lead->pipeline_status_id, $lead->id) == 2) {
                        $results[] = [
                            "leadId" => $lead->id,
                            "leadName" => $lead->name,
                            // "statusId" => $lead->pipeline_status_id,
                            "statusName" => $lead->pipeline_status_name,
                            // "agentId" => $lead->pipeline_agent_id,
                            "agentName" => $lead->pipeline_agent_name,
                        ];
                    }
                }
            });

        return $results;
    }

    protected function getBackDatedBindMgmtLeads($agentId, $notifyDays, $estTime, $nowTime)
    {
        $signedAorId = $this->getSignedAorStatusId();
        $beginAsanaName = AsanaQuestion::where("id",1)->value("name");
        $results = [];

        Lead::where('pipeline_status_id', $signedAorId)
            ->where(function ($query) use ($estTime) {
                $query->whereNull('lead_asana_details.last_updated_date')
                      ->orWhere(function ($subQuery) use ($estTime) {
                          $subQuery->where('lead_asana_details.last_updated_date', '<', $estTime->toDateString())
                                   ->where('lead_asana_details.stage_completed', '!=', 1);
                      });
            })
            ->when($agentId, function ($query) use ($agentId) {
                return $query->where('pipeline_agent_id', $agentId);
            })
            ->join('users', 'leads.pipeline_agent_id', '=', 'users.id')
            // ->join('contact_status', 'leads.pipeline_status_id', '=', 'contact_status.id')
            ->leftJoin('lead_asana_details', 'leads.id', '=', 'lead_asana_details.lead_id')
            ->leftJoin('asana_questions', 'lead_asana_details.asana_stage', '=', 'asana_questions.id')
            ->select("leads.id","leads.name","leads.pipeline_agent_id","leads.pipeline_status_id",
                     "users.name as pipeline_agent_name",
                     "asana_questions.name as asana_stage_name")
            ->chunk(50, function ($leadData) use (&$results, $beginAsanaName) {
                foreach ($leadData as $lead) {
                    $results[] = [
                        "leadId" => $lead->id,
                        "leadName" => $lead->name,
                        // "statusId" => $lead->pipeline_status_id,
                        // "statusName" => $lead->pipeline_status_name,
                        // "agentId" => $lead->pipeline_agent_id,
                        "agentName" => $lead->pipeline_agent_name,
                        "asanaStageName" => $lead->asana_stage_name ?? $beginAsanaName,
                    ];
                }
            });

        return $results;
    }

    protected function generateCSV(array $data, array $headers, string $fileName)
    {
        $path = storage_path('app/public/csv');
        Storage::makeDirectory('public/csv');
        $filePath = $path . '/' . $fileName;

        $csv = fopen('php://memory', 'w');
        fputcsv($csv, $headers);

        foreach ($data as $row) {
            fputcsv($csv, array_values($row));
        }

        rewind($csv);
        Storage::put('public/csv/' . $fileName, stream_get_contents($csv));
        fclose($csv);

        return $filePath;
    }

    protected function sendCriticalLeadEmailWithAttachments(
        $sentMailId, $agent,
        $filePathPipeDrive, $fileNamePipeDrive, $backDatedLeadPipeDrive, 
        $filePathBindMgmt, $fileNameBindMgmt, $backDatedLeadBindMgmt
    )
    {
        if (!empty($agent["id"])) {
            $to_name = $agent["name"] ?? 'Agent';
            $emails = !empty($agent["email"]) ? [$agent["email"]] : [];
        } else {
            $to_name = "Team";
            $setting = Setting::select('notify_email')->first();
            if (!$setting || empty($setting->notify_email)) return;

            $emails = array_filter(explode(',', $setting->notify_email));
        }

        if (empty($emails)) return;

        $to = array_shift($emails);

        $totalPipeDriveCount = count($backDatedLeadPipeDrive);
        $totalBindMgmtCount = count($backDatedLeadBindMgmt);

        $body = "<p>Dear {$to_name},</p>";
        $body .= "<p>Please find the attached critical lead data reports.</p>";
        $body .= "<ul>";
        $body .= "<li><strong>PipeDrive critical leads:</strong> {$totalPipeDriveCount}</li>";
        $body .= "<li><strong>Bind Management critical leads:</strong> {$totalBindMgmtCount}</li>";
        $body .= "</ul>";
        $body .= "<p>Regards,<br/>Your System</p>";

        $this->setDynamicSMTPUserWise($sentMailId);

        Mail::send([], [], function ($message) use ($to, $emails, $body, $agent,
                                                    $filePathPipeDrive, $fileNamePipeDrive, $backDatedLeadPipeDrive, 
                                                    $filePathBindMgmt, $fileNameBindMgmt, $backDatedLeadBindMgmt) 
        {
            $message->to($to);
            if (!empty($emails)) $message->cc($emails);
            $message->subject("Critical Lead Data ({$agent['name']})");

            if (count($backDatedLeadPipeDrive) > 0) {
                $message->attach($filePathPipeDrive, [
                    'as' => $fileNamePipeDrive,
                    'mime' => 'text/csv'
                ]);
            }

            if (count($backDatedLeadBindMgmt) > 0) {
                $message->attach($filePathBindMgmt, [
                    'as' => $fileNameBindMgmt,
                    'mime' => 'text/csv'
                ]);
            }

            $message->html($body);
        });
    }


    protected function deleteFiles(array $filePaths)
    {
        foreach ($filePaths as $path) {
            $relativePath = str_replace(storage_path('app/public/'), '', $path);
            if (Storage::exists('public/' . $relativePath)) {
                Storage::delete('public/' . $relativePath);
            }
        }
    }
}