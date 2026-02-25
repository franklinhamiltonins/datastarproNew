<?php

namespace App\Services;

use App\Model\AsanaQuestion;
use App\Model\LeadsModel\Lead;
use App\Model\Setting;
use App\Traits\CommonFunctionsTrait;
use App\Traits\SMTPRelatedTrait;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class ProcessAgentWiseLeadWiseReport
{
    use CommonFunctionsTrait, SMTPRelatedTrait;

    public function processDataAgentWise($agent)
    {
        $agentId = $agent['id'];
        $notifyDays = Setting::value('process_time_in_day_pipeline') ?? 4;

        $estTime = now()->subDays($notifyDays)
            ->timezone('America/New_York');
        $nowTime = now()->timezone('America/New_York');

        $sentMailId = config('custom.mail_sent_user_id');

        $pipeDrive = $this->getBackDatedPipeDriveLeads(
            $agentId, $notifyDays, $estTime, $nowTime
        );

        $bindMgmt = $this->getBackDatedBindMgmtLeads(
            $agentId, $notifyDays, $estTime, $nowTime
        );

        $fileNamePipe = $this->newCsvName('pipedrive');
        $fileNameBind = $this->newCsvName('bindmgmt');

        $filePathPipe = $this->generateCSV(
            $pipeDrive,
            ['Lead ID', 'Lead Name', 'Status', 'Agent'],
            $fileNamePipe
        );

        $filePathBind = $this->generateCSV(
            $bindMgmt,
            ['Lead ID', 'Lead Name', 'Agent', 'Stage'],
            $fileNameBind
        );

        $this->sendCriticalLeadEmailWithAttachments(
            $sentMailId, $agent,
            $filePathPipe, $fileNamePipe, $pipeDrive,
            $filePathBind, $fileNameBind, $bindMgmt
        );

        $this->deleteFiles([$filePathPipe, $filePathBind]);
    }

    protected function newCsvName($prefix)
    {
        return "critical_{$prefix}_lead_list_" . now()->format('Y_m_d_H_i_s') . '.csv';
    }

    protected function getBackDatedPipeDriveLeads(
        $agentId, $notifyDays, $estTime, $nowTime
    ) {
        $statusList = $this->pipeDriveDisplayStatusList();
        $results = [];

        Lead::whereIn('pipeline_status_id', $statusList)
            ->where(fn($q) => $q->whereNull('lead_asana_details.lead_id')
                ->orWhere('lead_asana_details.stage_completed', '!=', 1))
            ->when($agentId, fn($q) => $q->where('pipeline_agent_id', $agentId))
            ->join('users', 'leads.pipeline_agent_id', '=', 'users.id')
            ->join('contact_status', 'leads.pipeline_status_id', '=', 'contact_status.id')
            ->leftJoin('lead_asana_details', 'leads.id', '=', 'lead_asana_details.lead_id')
            ->select(
                'leads.id',
                'leads.name',
                'users.name as agent_name',
                'contact_status.name as status_name'
            )
            ->chunk(
                50,
                fn($chunk) =>
                $this->collectPipeDriveChunk(
                    $chunk,
                    $results,
                    $agentId,
                    $notifyDays,
                    $estTime,
                    $nowTime
                )
            );

        return $results;
    }

    private function collectPipeDriveChunk(
        $chunk, &$results, $agentId, $notifyDays, $estTime, $nowTime
    ) {
        foreach ($chunk as $lead) {
            $isCritical = $this->decideColorTile(
                $agentId,
                $notifyDays,
                $estTime,
                $nowTime,
                $lead->pipeline_status_id,
                $lead->id
            );

            if ($isCritical == 2) {
                $results[] = [
                    'leadId' => $lead->id,
                    'leadName' => $lead->name,
                    'statusName' => $lead->status_name,
                    'agentName' => $lead->agent_name,
                ];
            }
        }
    }

    protected function getBackDatedBindMgmtLeads(
        $agentId, $notifyDays, $estTime, $nowTime
    ) {
        $signedAorId = $this->getSignedAorStatusId();
        $beginAsanaName = AsanaQuestion::where('id', 1)->value('name');
        $results = [];

        Lead::where('pipeline_status_id', $signedAorId)
            ->where(fn($q) => $q->whereNull('lead_asana_details.last_updated_date')
                ->orWhere(fn($sub) =>
                    $sub->where('lead_asana_details.last_updated_date', '<', $estTime->toDateString())
                        ->where('lead_asana_details.stage_completed', '!=', 1)
                )
            )
            ->when($agentId, fn($q) => $q->where('pipeline_agent_id', $agentId))
            ->join('users', 'leads.pipeline_agent_id', '=', 'users.id')
            ->leftJoin('lead_asana_details', 'leads.id', '=', 'lead_asana_details.lead_id')
            ->leftJoin('asana_questions', 'lead_asana_details.asana_stage', '=', 'asana_questions.id')
            ->select(
                'leads.id',
                'leads.name',
                'users.name as agent_name',
                'asana_questions.name as stage_name'
            )
            ->chunk(
                50,
                fn($chunk) =>
                $this->collectBindMgmtChunk(
                    $chunk,
                    $results,
                    $beginAsanaName
                )
            );

        return $results;
    }

    private function collectBindMgmtChunk($chunk, &$results, $defaultStage)
    {
        foreach ($chunk as $lead) {
            $results[] = [
                'leadId' => $lead->id,
                'leadName' => $lead->name,
                'agentName' => $lead->agent_name,
                'asanaStageName' => $lead->stage_name ?? $defaultStage,
            ];
        }
    }

    protected function generateCSV(array $data, array $headers, string $fileName)
    {
        Storage::makeDirectory('public/csv');
        $path = storage_path('app/public/csv/' . $fileName);

        $csv = fopen('php://memory', 'w');
        fputcsv($csv, $headers);

        foreach ($data as $row) {
            fputcsv($csv, array_values($row));
        }

        rewind($csv);
        Storage::put('public/csv/' . $fileName, stream_get_contents($csv));
        fclose($csv);

        return $path;
    }

    protected function sendCriticalLeadEmailWithAttachments(
        $sentMailId, $agent,
        $filePathPipe, $fileNamePipe, $pipeData,
        $filePathBind, $fileNameBind, $bindData
    ) {
        [$to, $cc, $toName] = $this->prepareRecipientInfo($agent);

        if (!$to) {
            return;
        }

        $body = $this->buildEmailBody($toName, $pipeData, $bindData);

        $this->setDynamicSMTPUserWise($sentMailId);

        Mail::send(
            [],
            [],
            function ($m) use (
                $to,
                $cc,
                $filePathPipe,
                $fileNamePipe,
                $pipeData,
                $filePathBind,
                $fileNameBind,
                $bindData,
                $agent,
                $body
            ) {
                $m->to($to);

                if ($cc) {
                    $m->cc($cc);
                }

                $m->subject("Critical Lead Data ({$agent['name']})");

                if (count($pipeData)) {
                    $m->attach($filePathPipe, [
                        'as' => $fileNamePipe,
                        'mime' => 'text/csv',
                    ]);
                }

                if (count($bindData)) {
                    $m->attach($filePathBind, [
                        'as' => $fileNameBind,
                        'mime' => 'text/csv',
                    ]);
                }

                $m->html($body);
            }
        );
    }

    private function prepareRecipientInfo($agent)
    {
        if (!empty($agent['id'])) {
            $to = $agent['email'] ?? null;
            return [$to, [], $agent['name'] ?? 'Agent'];
        }

        $notify = Setting::value('notify_email');

        if (!$notify) {
            return [null, [], 'Team'];
        }

        $emails = array_filter(explode(',', $notify));
        $to = array_shift($emails);

        return [$to, $emails, 'Team'];
    }

    private function buildEmailBody($toName, $pipeData, $bindData)
    {
        $pipeCount = count($pipeData);
        $bindCount = count($bindData);

        return
            "<p>Dear {$toName},</p>" .
            '<p>Please find the attached critical lead data reports.</p>' .
            '<ul>' .
            "<li><strong>PipeDrive critical leads:</strong> {$pipeCount}</li>" .
            "<li><strong>Bind Management critical leads:</strong> {$bindCount}</li>" .
            '</ul>' .
            '<p>Regards,<br/>Your System</p>';
    }

    protected function deleteFiles(array $paths)
    {
        foreach ($paths as $full) {
            $relative = str_replace(storage_path('app/public/'), '', $full);
            $storagePath = 'public/' . $relative;

            if (Storage::exists($storagePath)) {
                Storage::delete($storagePath);
            }
        }
    }
}
