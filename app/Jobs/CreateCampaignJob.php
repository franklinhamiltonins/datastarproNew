<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Model\File;
use App\Model\Campaign;
use App\Model\LeadsModel\Filter;
use App\Model\Setting;
use Carbon\Carbon;
use App\Model\LeadsModel\Lead;
use App\Traits\SMTPRelatedTrait;
use App\Traits\MailingRelatedTrait;

class CreateCampaignJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels,SMTPRelatedTrait,MailingRelatedTrait;

    /**
     * Create a new job instance.
     *
     * @return void
     */ 
    public $timeout = 1800; 

    public $filters,$campaignName,$campaignId,$location_leads_id,$location_leads_id_search, $mail_agent_id;
    public function __construct($filters,$campaignName,$campaignId,$location_leads_id,$location_leads_id_search, $mail_agent_id)
    {
        $this->filters = $filters;
        $this->campaignName = $campaignName;
        $this->campaignId = $campaignId;
        $this->location_leads_id = $location_leads_id;
        $this->location_leads_id_search = $location_leads_id_search;
        $this->mail_agent_id = $mail_agent_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $leadColumns =  $this->getleadColumns(); 

        $contactColumns =  $this->getcontactColumns(); 

        $columnsType = Lead::Get_column_type(); //get columns type
        $table =  Lead::query();

        $leadsQuery = filter_leads($table, $this->filters, $columnsType, $this->campaignId); // filter leads by search filters. FN: Support/helper.php

        $queryareachunk = $this->location_leads_id_search ? $leadsQuery->select('*')->whereIn('id', $this->location_leads_id)->orderBy('id', 'DESC'):
            $leadsQuery->select('*')->orderBy('id', 'DESC');

        $fileName =  'Mailing_list'.date('Y_m_d_H_i_s'). '.csv';
        $path = storage_path('app/public/csv');
        Storage::makeDirectory('public/csv');
        $filePath = $path . '/' . $fileName;
        $csvContent = fopen('php://memory', 'w');

        $columns = $this->columninsidefile();

        fputcsv($csvContent, $columns);

        $campaign = Campaign::create([
            'name' => $this->campaignName,
            'status' => 'PENDING',
            'lead_number' => $queryareachunk->count()
        ]);


        // Process leads in chunks to avoid memory issues
        $chunkSize = 1000; // Adjust chunk size as needed
        $queryareachunk->chunk($chunkSize, function ($leads) use ($csvContent, $leadColumns, $contactColumns,$campaign) {
            foreach ($leads as $lead) {
                $csvRow = [];
                if (count($lead->contacts) == 0) {
                    foreach ($leadColumns as $leadColumn) {
                        $csvRow[] = $leadColumn == 'creation_date' ||  $leadColumn == 'renewal_date' ? 
                                ($lead->$leadColumn ? Carbon::parse($lead->$leadColumn)->format('Y/m/d') : "") : 
                                $lead->$leadColumn;
                    }
                    fputcsv($csvContent, $csvRow);
                } else {
                    foreach ($lead->contacts as $contact) {
                        $csvRow = [];
                        foreach ($leadColumns as $leadColumn) {
                            if($leadColumn != 'response_date'){
                                $csvRow[] = $leadColumn == 'creation_date' ||  $leadColumn == 'renewal_date' ? 
                                    ($lead->$leadColumn ? Carbon::parse($lead->$leadColumn)->format('Y/m/d') : "") : 
                                    $lead->$leadColumn;
                            }
                        }
                        foreach ($contactColumns as $contactColumn) {
                            $csvRow[] = $contact->$contactColumn;
                        }
                        $ctName = $contact->c_first_name . ' ' . $contact->c_last_name;
                        $action = $lead->actions()->where('contact_name', $ctName)->latest('contact_date')->first();
                        $actionDate = $action ? Carbon::parse($action->contact_date)->format('Y/m/d') : "";
                        $csvRow[] = $actionDate;
                        fputcsv($csvContent, $csvRow);
                    }
                }
                $campaign->leads()->attach($lead->id);
            }
        });

        rewind($csvContent);
        $csvData = stream_get_contents($csvContent);
        fclose($csvContent);

        Storage::put('public/csv/' . $fileName, $csvData);

        try {
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
                    $message->subject('Campaign Leads CSV')
                    ->attach($filePath, [
                        'as' => $fileName,
                        'mime' => 'text/csv',
                    ])
                    ->html('Please find the attached CSV file containing the campaign leads.');
                });
            }
            unset($setting_time_data);
            
        } catch (\Throwable $th) {
            Log::error('Error while sending campaign leads CSV email: ' . $th->getMessage(), [
                'file' => $th->getFile(),
                'line' => $th->getLine(),
                'trace' => $th->getTraceAsString(),
            ]);
        }

        if (Storage::exists('public/csv/' . $fileName)) {
            Storage::delete('public/csv/' . $fileName);
        }
        unset($campaign,$queryareachunk,$csvContent,$columns,$leadColumns,$contactColumns,$columnsType,$table,$leadsQuery);
    }
}
