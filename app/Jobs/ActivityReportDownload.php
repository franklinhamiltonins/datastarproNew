<?php

namespace App\Jobs;

use App\Traits\ActivityReportTrait;
use App\Traits\MessageConstantsTrait;
use App\Traits\SMTPRelatedTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ActivityReportDownload implements ShouldQueue
{
    use ActivityReportTrait, Dispatchable, InteractsWithQueue,
        MessageConstantsTrait, Queueable, SerializesModels, SMTPRelatedTrait;

    // Maximum execution time for this job in seconds
    public $timeout = 1800;

    protected $requestData;
    protected $mailAgentId;
    public function __construct($requestData, $mailAgentId)
    {
        $this->requestData = $requestData;
        $this->mailAgentId = $mailAgentId;
    }

    // The report preparation, CSV generation, storage, email sending, and cleanup
    public function handle()
    {
        try {
            // Prepare report data and metadata
            $report = $this->prepareReport();

            // Generate CSV data in memory
            $csvData = $this->generateCsv($report['results'], $report['viewType']);

            // Store CSV file to storage and get file path
            $filePath = $this->storeCsv($csvData, $report['fileName']);

            // Send email with CSV attachment
            $this->sendReportEmail($report['subject'], $report['body'], $filePath,$report['fileName']);

            // Delete CSV file after sending
            $this->deleteCsv($filePath);

        } catch (\Throwable $th) {
            Log::error(
                'ActivityReportDownload failed: (Agent Activity Report) '.$th->getMessage(),
                [
                    'line' => $th->getLine(),
                    'file' => $th->getFile(),
                    'trace' => $th->getTraceAsString(),
                ]
            );
        }
    }

    // Prepare report data based on request type (logwise or consolidated)
    private function prepareReport(): array
    {
        $viewType = $this->requestData['view_type'] ?? 1;
        $managerId = $this->requestData['manager_id'] ?? null;

        if ($viewType == 1) {
            // Logwise report
            $query = $this->activityListQuery($this->requestData, true, $managerId);
            $results = $this->formatAorDetails($query->get());
            $fileName = 'agent_activity_logwise_report_'.date('Y_m_d_H_i_s').'.csv';
            $subject = 'Agent Activity Report (Log Wise) CSV';
            $body = 'Please find the attached Agent Activity Report (Log Wise) CSV file.';
        } else {
            // Consolidated report
            $query = $this->activityListQuery($this->requestData, false, $managerId);
            $results = $query->get();
            $fileName = 'agent_activity_consolidated_report_'.date('Y_m_d_H_i_s').'.csv';
            $subject = 'Agent Activity Report (Consolidated) CSV';
            $body = 'Please find the attached Agent Activity Report (Consolidated) CSV file.';
        }

        return compact('results', 'fileName', 'subject', 'body', 'viewType');
    }

    // Generate CSV content for given results
    // $viewType 1 for logwise, 2 for consolidated
    private function generateCsv($results, int $viewType): string
    {
        $csv = fopen('php://memory', 'w');

        if ($viewType == 1) {
            $this->writeLogwiseCsvHeaders($csv);
            $this->writeLogwiseCsvRows($csv, $results);
        } else {
            $this->writeConsolidatedCsvHeaders($csv);
            $this->writeConsolidatedCsvRows($csv, $results);
        }

        rewind($csv);
        $csvData = stream_get_contents($csv);
        fclose($csv);

        return $csvData;
    }

    // Store CSV data to storage - $csvData CSV content
    private function storeCsv(string $csvData, string $fileName): string
    {
        $path = self::publicPathCsv();
        Storage::makeDirectory(self::PUBLIC_CSV);
        $filePath = $path.'/'.$fileName;

        Storage::put(self::PUBLIC_CSV.$fileName, $csvData);

        return $filePath;
    }

    // Write CSV headers for Logwise report
    private function writeLogwiseCsvHeaders($csv): void
    {
        fputcsv($csv, [
            'Date', 'Agent', 'Appointments', 'Policies', 'Expiring Policy Premium', 'Community Name',
            'AOR Break Down', 'AOR 1', 'Community Name 1', 'Effective Date 1', 'Expiring AOR Premium 1',
            'AOR 2', 'Community Name 2', 'Effective Date 2', 'Expiring AOR Premium 2',
            'AOR 3', 'Community Name 3', 'Effective Date 3', 'Expiring AOR Premium 3',
            'AOR 4', 'Community Name 4', 'Effective Date 4', 'Expiring AOR Premium 4',
            'AOR 5', 'Community Name 5', 'Effective Date 5', 'Expiring AOR Premium 5',
        ]);
    }

    // Write CSV rows for Logwise report
    private function writeLogwiseCsvRows($csv, $results): void
    {
        foreach ($results as $item) {
            fputcsv($csv, [
                $item['date'] ?? '', $item['agent_name'] ?? '', $item['appointments'] ?? '',
                $item['policies'] ?? '', $item['expiry_policies_premium'] ?? '',
                $item['community_name'] ?? '', $item['aor_breakdown'] ?? '',
                $item['aor1'] ?? '', $item['aor_community_name1'] ?? '', $item['aor_effective_date1'] ?? '',
                $item['expiring_aor_premium1'] ?? '', $item['aor2'] ?? '', $item['aor_community_name2'] ?? '',
                $item['aor_effective_date2'] ?? '', $item['expiring_aor_premium2'] ?? '', $item['aor3'] ?? '',
                $item['aor_community_name3'] ?? '', $item['aor_effective_date3'] ?? '',
                $item['expiring_aor_premium3'] ?? '', $item['aor4'] ?? '', $item['aor_community_name4'] ?? '',
                $item['aor_effective_date4'] ?? '', $item['expiring_aor_premium4'] ?? '', $item['aor5'] ?? '',
                $item['aor_community_name5'] ?? '', $item['aor_effective_date5'] ?? '', $item['expiring_aor_premium5'] ?? '',
            ]);
        }
    }

    // Write CSV headers for Consolidated report
    private function writeConsolidatedCsvHeaders($csv): void
    {
        fputcsv($csv, [
            'Agent', 'Total Agent Activity Submissions', 'Total Appointments',
            'Total Policies', 'Total Expiring Policy Premium',
        ]);
    }

    // Write CSV rows for Consolidated report
    private function writeConsolidatedCsvRows($csv, $results): void
    {
        foreach ($results as $item) {
            fputcsv($csv, [
                $item['agent_name'] ?? '', $item['total_lead'] ?? '',
                $item['total_appointments'] ?? '', $item['total_policies'] ?? '',
                $item['total_expiry_policies_premium'] ?? '',
            ]);
        }
    }
}
