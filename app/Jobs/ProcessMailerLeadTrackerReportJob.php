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

class ProcessMailerLeadTrackerReportJob implements ShouldQueue
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
            $query = $this->generateMailLeadTrackerData($this->requestData);

            $results = $query->get();

            if($this->requestData['view_type'] == 1){
                $fileName = 'mail_lead_tracker_logwise_report_' . date('Y_m_d_H_i_s') . '.csv';
                $subject = "Mailer Lead Tracker Report (Log Wise) CSV ";
                $body = "Please find the attached Mailer Lead Tracker Report (Log Wise) CSV file.";
            }
            else{
                $fileName = 'mail_lead_tracker_consolidated_report_' . date('Y_m_d_H_i_s') . '.csv';
                $subject = "Mailer Lead Tracker Report (Consolidated) CSV ";
                $body = "Please find the attached Mailer Lead Tracker Report (Consolidated) CSV file.";
            }
            $path = storage_path('app/public/csv');
            Storage::makeDirectory('public/csv');
            $filePath = $path . '/' . $fileName;

            $csv = fopen('php://memory', 'w');

            if($this->requestData['view_type'] == 1){
                // CSV HEADERS
                fputcsv($csv, [
                    'Business Name', 'Lead Source', 'Agent', 'Contact FirstName', 'Contact LastName', 'Phone','Email', 'Status Notes', 'Date'
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
                        $item->date ?? '',
                    ]);
                }
            }
            else{
                fputcsv($csv, [
                     'Agent', 'Total Mailer Lead Submissions'
                ]);

                foreach ($results as $item) {
                    fputcsv($csv, [
                        $item->agent_name ?? '',
                        $item->total_lead ?? '',
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
                    ->attach($filePath, [
                        'as' => $fileName,
                        'mime' => 'text/csv',
                    ])
                    ->setBody($body, 'text/html');
                });
            }
            unset($setting_time_data);

            // Delete file after sending
            if (Storage::exists('public/csv/' . $fileName)) {
                Storage::delete('public/csv/' . $fileName);
            }

        } catch (\Throwable $th) {
            Log::error('ProcessMailerLeadTrackerReportJob failed: (Mailer Lead Tracker Report) ' . $th->getMessage(), [
                'line' => $th->getLine(),
                'file' => $th->getFile(),
                'trace' => $th->getTraceAsString(),
            ]);
        }
    }
}
