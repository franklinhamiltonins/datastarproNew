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

class ActivityReportDownload implements ShouldQueue
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
            if($this->requestData['view_type'] == 1){
                $query = $this->activityListQuery($this->requestData,true,$this->requestData['manager_id']);
                $results = $this->formatAorDetails($query->get());
                $fileName = 'agent_activity_logwise_report_' . date('Y_m_d_H_i_s') . '.csv';
                $subject = "Agent Activity Report (Log Wise) CSV ";
                $body = "Please find the attached Agent Activity Report (Log Wise) CSV file.";
            }
            else{
                $query = $this->activityListQuery($this->requestData,false,$this->requestData['manager_id']);
                $results = $query->get();
                $fileName = 'agent_activity_consolidated_report_' . date('Y_m_d_H_i_s') . '.csv';
                $subject = "Agent Activity Report (Consolidated) CSV ";
                $body = "Please find the attached Agent Activity Report (Consolidated) CSV file.";
            }

            
            $path = storage_path('app/public/csv');
            Storage::makeDirectory('public/csv');
            $filePath = $path . '/' . $fileName;

            $csv = fopen('php://memory', 'w');

            if($this->requestData['view_type'] == 1){
                // CSV HEADERS
                fputcsv($csv, [
                    'Date', 'Agent', 'Appointments', 'Policies', 'Expiring Policy Premium', 'Community Name','AOR Break Down',
                    'AOR 1', 'Community Name 1','Effective Date 1','Expiring AOR Premium 1',
                    'AOR 2', 'Community Name 2','Effective Date 2','Expiring AOR Premium 2',
                    'AOR 3', 'Community Name 3','Effective Date 3','Expiring AOR Premium 3',
                    'AOR 4', 'Community Name 4','Effective Date 4','Expiring AOR Premium 4',
                    'AOR 5', 'Community Name 5','Effective Date 5','Expiring AOR Premium 5',
                ]);

                foreach ($results as $item) {
                    fputcsv($csv, [
                        $item['date'] ?? '',
                        $item['agent_name'] ?? '',
                        $item['appointments'] ?? '',
                        $item['policies'] ?? '',
                        $item['expiry_policies_premium'] ?? '',
                        $item['community_name'] ?? '',
                        $item['aor_breakdown'] ?? '',
                        $item['aor1'] ?? '',
                        $item['aor_community_name1'] ?? '',
                        $item['aor_effective_date1'] ?? '',
                        $item['expiring_aor_premium1'] ?? '',
                        $item['aor2'] ?? '',
                        $item['aor_community_name2'] ?? '',
                        $item['aor_effective_date2'] ?? '',
                        $item['expiring_aor_premium2'] ?? '',
                        $item['aor3'] ?? '',
                        $item['aor_community_name3'] ?? '',
                        $item['aor_effective_date3'] ?? '',
                        $item['expiring_aor_premium3'] ?? '',
                        $item['aor4'] ?? '',
                        $item['aor_community_name4'] ?? '',
                        $item['aor_effective_date4'] ?? '',
                        $item['expiring_aor_premium4'] ?? '',
                        $item['aor5'] ?? '',
                        $item['aor_community_name5'] ?? '',
                        $item['aor_effective_date5'] ?? '',
                        $item['expiring_aor_premium5'] ?? '',
                    ]);
                }
            }
            else{
                fputcsv($csv, [
                    'Agent', 'Total Agent Activity Submissions', 'Total Appointments', 'Total Policies', 'Total Expiring Policy Premium'
                ]);

                foreach ($results as $item) {
                    fputcsv($csv, [
                        $item['agent_name'] ?? '',
                        $item['total_lead'] ?? '',
                        $item['total_appointments'] ?? '',
                        $item['total_policies'] ?? '',
                        $item['total_expiry_policies_premium'] ?? '',
                    ]);
                }
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
                
                Mail::send([], [], function ($message) use ($fileName, $filePath,$recipientEmail,$ccEmails,$subject,$body) {
                    $message->to($recipientEmail);

                    if (count($ccEmails) > 0) {
                        $message->cc($ccEmails);
                    }
                    $message->subject($subject)
                    ->html($body)
                    ->attach($filePath, [
                        'as' => $fileName,
                        'mime' => 'text/csv',
                    ]);
                });
            }
            unset($setting_time_data);

            // Delete file after sending
            if (Storage::exists('public/csv/' . $fileName)) {
                Storage::delete('public/csv/' . $fileName);
            }

        } catch (\Throwable $th) {
            Log::error('ActivityReportDownload failed: (Agent Activity Report)' . $th->getMessage(), [
                'line' => $th->getLine(),
                'file' => $th->getFile(),
                'trace' => $th->getTraceAsString(),
            ]);
        }
    }
}
