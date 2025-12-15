<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

use App\Model\Setting;
use Carbon\Carbon;
use App\Traits\SMTPRelatedTrait;
use App\Traits\ActivityReportTrait;

class ProcessMailDailyCallReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels,SMTPRelatedTrait,ActivityReportTrait;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $timeout = 1800; 
    protected $requestData,$mail_agent_id;

    public function __construct($requestData,$mail_agent_id)
    {
        $this->requestData = $requestData;
        $this->mail_agent_id = $mail_agent_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $formattedDate = $this->getFormatedDate($this->requestData);
            $results = $this->generateDailyReportData($this->requestData['agent'], $formattedDate['from'], $formattedDate['to']);

            $fileName = 'daily_call_report_' . date('Y_m_d_H_i_s') . '.csv';
            $path = storage_path('app/public/csv');
            Storage::makeDirectory('public/csv');
            $filePath = $path . '/' . $fileName;

            $csv = fopen('php://memory', 'w');

            // CSV HEADERS
            fputcsv($csv, [
                'Producer Name', 'Outbound Calls', 'Facebook', 'Mailer', 'Sms', 'Email',
                '611 Transfer', '611 Referal Email', 'Appointment', 'Policies',
                'Expiry Premium', 'Aor', 'Aor Effective Month', 'Aor Premium'
            ]);

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
                    number_format($item['expiry_premium'] ?? 0, 2),
                    $item['aor'] ?? '',
                    $item['aor_effective_month'] ?? '',
                    number_format($item['aor_premium'] ?? 0, 2),
                ]);
            }

            rewind($csv);
            $csvData = stream_get_contents($csv);
            fclose($csv);

            Storage::put('public/csv/' . $fileName, $csvData);

            $setting_time_data = Setting::select('notify_email')->first();
            if($setting_time_data && !empty($setting_time_data->notify_email)){
                $recipientEmail_arr = explode(',', $setting_time_data->notify_email);

                $recipientEmail = $recipientEmail_arr[0];

                $ccEmails = array_slice($recipientEmail_arr, 1);

                $this->setDynamicSMTPUserWise($this->mail_agent_id);
                
                Mail::send([], [], function ($message) use ($fileName, $filePath,$recipientEmail,$ccEmails) {
                    $message->to($recipientEmail);

                    if (count($ccEmails) > 0) {
                        $message->cc($ccEmails);
                    }
                    $message->subject('Daily Call Report CSV')
                    ->attach($filePath, [
                        'as' => $fileName,
                        'mime' => 'text/csv',
                    ])
                    ->setBody('Please find the attached Daily Call Report CSV file.', 'text/html');
                });
            }
            unset($setting_time_data);

            // Delete file after sending
            if (Storage::exists('public/csv/' . $fileName)) {
                Storage::delete('public/csv/' . $fileName);
            }

        } catch (\Throwable $th) {
            Log::error('ProcessMailDailyCallReportJob failed: (Daily Call Report)' . $th->getMessage(), [
                'line' => $th->getLine(),
                'file' => $th->getFile(),
                'trace' => $th->getTraceAsString(),
            ]);
        }
    }
}
