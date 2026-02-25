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

class ProcessMailDailyCallReportJob implements ShouldQueue
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
            $formattedDate = $this->getFormatedDate($this->requestData);

            $results = $this->generateDailyReportData(
                $this->requestData['agent'],
                $formattedDate['from'],
                $formattedDate['to'],
                $this->requestData['manager_id']
            );

            $filePath = $this->generateCsv($results);
            $this->sendDailyReportEmail($filePath);
            $this->deleteCsv($filePath);

        } catch (\Throwable $th) {
            $this->logError($th);
        }
    }

    private function generateCsv(array $results): string
    {
        $fileName = 'daily_call_report_'.date('Y_m_d_H_i_s').'.csv';
        $path = storage_path('app/public/csv');
        Storage::makeDirectory('public/csv');
        $filePath = $path.'/'.$fileName;

        $csv = fopen('php://memory', 'w');
        $this->writeCsvHeader($csv);
        $this->writeCsvRows($csv, $results);

        rewind($csv);
        $csvData = stream_get_contents($csv);
        fclose($csv);

        Storage::put('public/csv/'.$fileName, $csvData);

        return $filePath;
    }

    private function writeCsvHeader($csv): void
    {
        fputcsv($csv, [
            'Producer Name', 'Outbound Calls', 'Facebook', 'Mailer', 'Sms', 'Email',
            '611 Transfer', '611 Referal Email', 'Appointment', 'Policies',
            'Expiry Premium', 'Aor', 'Aor Effective Month', 'Aor Premium',
        ]);
    }

    private function writeCsvRows($csv, array $results): void
    {
        foreach ($results as $item) {
            fputcsv($csv, [
                $item['producer_name'] ?? '',
                $item['outbound_calls'] ?? '0',
                $item['facebook'] ?? '0',
                $item['mailer'] ?? '0',
                $item['sms'] ?? '0',
                $item['email'] ?? '0',
                $item['transfer_611'] ?? '0',
                $item['referal_611'] ?? '0',
                $item['appointments'] ?? '',
                $item['policies'] ?? '',
                formatUSNumber($item['expiry_premium'] ?? 0),
                $item['aor'] ?? '',
                $item['aor_effective_month'] ?? '',
                formatUSNumber($item['aor_premium'] ?? 0),
            ]);
        }
    }

    private function sendDailyReportEmail(string $filePath): void
    {
        $fileName = basename($filePath);
        $subject = "Daily Call Report CSV";
        $mailBody = "Please find the attached Daily Call Report CSV file.";

        $this->setDynamicSMTPUserWise($mailAgentId);

        $this->sendReportEmail($subject, $mailBody, $filePath, $fileName);
    }

    private function logError(\Throwable $th): void
    {
        Log::error(
            'ProcessMailDailyCallReportJob failed (Daily Call Report): '.$th->getMessage(),
            [
                'line' => $th->getLine(),
                'file' => $th->getFile(),
                'trace' => $th->getTraceAsString(),
            ]
        );
    }
}
