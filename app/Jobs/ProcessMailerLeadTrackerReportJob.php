<?php

namespace App\Jobs;

use App\Traits\ActivityReportTrait;
use App\Traits\SMTPRelatedTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessMailerLeadTrackerReportJob implements ShouldQueue
{
    use ActivityReportTrait, SMTPRelatedTrait, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 1800;

    protected $requestData;
    protected $mailAgentId;

    public function __construct(array $requestData, int $mailAgentId)
    {
        $this->requestData = $requestData;
        $this->mailAgentId = $mailAgentId;
    }

    public function handle(): void
    {
        try {
            $results = $this->generateMailLeadTrackerData(
                $this->requestData,
                $this->requestData['manager_id']
            )->get();

            [$fileName, $subject, $body] = $this->getFileDetails($this->requestData['view_type']);
            $filePath = $this->generateCsv($results, $this->requestData['view_type'], $fileName);

            $this->setDynamicSMTPUserWise($this->mailAgentId);
            $this->sendReportEmail($subject, $body, $filePath, $fileName);
            $this->deleteCsv($filePath);

        } catch (\Throwable $th) {
            $this->logError($th);
        }
    }

    private function getFileDetails(int $viewType): array
    {
        $timestamp = date('Y_m_d_H_i_s');
        if ($viewType === 1) {
            return [
                "mail_lead_tracker_logwise_report_{$timestamp}.csv",
                'Mailer Lead Tracker Report (Log Wise) CSV',
                'Please find the attached Mailer Lead Tracker Report (Log Wise) CSV file.'
            ];
        }

        return [
            "mail_lead_tracker_consolidated_report_{$timestamp}.csv",
            'Mailer Lead Tracker Report (Consolidated) CSV',
            'Please find the attached Mailer Lead Tracker Report (Consolidated) CSV file.'
        ];
    }

    private function generateCsv($results, int $viewType, string $fileName): string
    {
        $path = storage_path('app/public/csv');
        Storage::makeDirectory('public/csv');
        $filePath = $path.'/'.$fileName;

        $csv = fopen('php://memory', 'w');

        if ($viewType === 1) {
            $this->writeLogwiseCsv($csv, $results);
        } else {
            $this->writeConsolidatedCsv($csv, $results);
        }

        rewind($csv);
        Storage::put('public/csv/'.$fileName, stream_get_contents($csv));
        fclose($csv);

        return $filePath;
    }

    private function writeLogwiseCsv($csv, $results): void
    {
        fputcsv($csv, [
            'Business Name', 'Lead Source', 'Agent', 'Contact FirstName', 'Contact LastName', 'Phone',
            'Email', 'Status Notes', 'Date'
        ]);

        foreach ($results as $item) {
            fputcsv($csv, [
                $item->business ?? '',
                optional($item->leadSource)->name ?? '',
                optional($item->agent)->name ?? '',
                $item->contact_firstname ?? '',
                $item->contact_lastname ?? '',
                $item->phone ?? '',
                $item->email_address ?? '',
                strip_tags($item->status_note ?? ''),
                $item->date ?? ''
            ]);
        }
    }

    private function writeConsolidatedCsv($csv, $results): void
    {
        fputcsv($csv, ['Agent', 'Total Mailer Lead Submissions']);

        foreach ($results as $item) {
            fputcsv($csv, [
                $item->agent_name ?? '',
                $item->total_lead ?? ''
            ]);
        }
    }

    private function logError(\Throwable $th): void
    {
        Log::error(
            'ProcessMailerLeadTrackerReportJob failed (Mailer Lead Tracker Report): '.$th->getMessage(),
            [
                'line' => $th->getLine(),
                'file' => $th->getFile(),
                'trace' => $th->getTraceAsString(),
            ]
        );
    }
}
