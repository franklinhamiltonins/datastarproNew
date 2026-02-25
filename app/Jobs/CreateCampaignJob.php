<?php

namespace App\Jobs;

use App\Model\Campaign;
use App\Model\LeadsModel\Lead;
use App\Traits\MailingRelatedTrait;
use App\Traits\SMTPRelatedTrait;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class CreateCampaignJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, MailingRelatedTrait, Queueable, SerializesModels, SMTPRelatedTrait;

    public $timeout = 1800;

    public $filters;
    public $campaignName;
    public $campaignId;
    public $locationId;
    public $locationSId;
    public $mailAgentId;

    public function __construct($filters, $campaignName, $campaignId, $locationId, $locationSId, $mailAgentId)
    {
        $this->filters = $filters;
        $this->campaignName = $campaignName;
        $this->campaignId = $campaignId;
        $this->locationId = $locationId;
        $this->locationSId = $locationSId;
        $this->mailAgentId = $mailAgentId;
    }

    public function handle()
    {
        $leadColumns = $this->getleadColumns();      // Lead columns for CSV
        $contactColumns = $this->getcontactColumns(); // Contact columns for CSV
        $columnsType = Lead::Get_column_type();      // Get column types
        $leadsQuery = $this->getFilteredLeads($columnsType);

        $fileName = $this->generateFileName();
        $filePath = $this->createCsvDirectory($fileName);
        $csvContent = fopen('php://memory', 'w');

        $columns = $this->columninsidefile();
        fputcsv($csvContent, $columns); // CSV headers

        $campaign = $this->createCampaign($leadsQuery); // Create campaign entry

        $this->processLeadsInChunks($leadsQuery, $leadColumns, $contactColumns, $campaign, $csvContent);

        $this->storeCsv($csvContent, $fileName); // Save CSV file

        $this->sendCampaignMail($filePath, $fileName); // Send CSV via email

        $this->deleteCsv($filePath); // Clean up CSV file
    }

    // Get filtered leads query
    private function getFilteredLeads($columnsType)
    {
        $table = Lead::query();
        $leadsQuery = filter_leads($table, $this->filters, $columnsType, $this->campaignId);

        return $this->locationSId
            ? $leadsQuery->select('*')->whereIn('id', $this->locationId)->orderBy('id', 'DESC')
            : $leadsQuery->select('*')->orderBy('id', 'DESC');
    }

    // Generate CSV file name
    private function generateFileName(): string
    {
        return 'Mailing_list'.date('Y_m_d_H_i_s').'.csv';
    }

    // Create CSV directory and return full path
    private function createCsvDirectory($fileName): string
    {
        $path = storage_path('app/public/csv');
        Storage::makeDirectory('public/csv');

        return $path.'/'.$fileName;
    }

    // Create campaign entry in DB
    private function createCampaign($leadsQuery)
    {
        return Campaign::create([
            'name' => $this->campaignName,
            'status' => 'PENDING',
            'lead_number' => $leadsQuery->count(),
        ]);
    }

    // Process leads in chunks and write CSV rows
    private function processLeadsInChunks($leadsQuery, $leadColumns, $contactColumns, $campaign, $csvContent)
    {
        $chunkSize = 1000;
        $leadsQuery->chunk($chunkSize, function ($leads) use ($leadColumns, $contactColumns, $campaign, $csvContent) {
            foreach ($leads as $lead) {
                $this->processSingleLead($lead, $leadColumns, $contactColumns, $campaign, $csvContent);
            }
        });
    }

    // Process a single lead and its contacts
    private function processSingleLead($lead, $leadColumns, $contactColumns, $campaign, $csvContent)
    {
        if (count($lead->contacts) == 0) {
            $csvRow = $this->generateLeadRow($lead, $leadColumns);
            fputcsv($csvContent, $csvRow);
        } else {
            foreach ($lead->contacts as $contact) {
                $csvRow = $this->generateLeadContactRow($lead, $contact, $leadColumns, $contactColumns);
                fputcsv($csvContent, $csvRow);
            }
        }

        $campaign->leads()->attach($lead->id); // Attach lead to campaign
    }

    // Generate CSV row for lead without contacts
    private function generateLeadRow($lead, $leadColumns): array
    {
        $row = [];
        foreach ($leadColumns as $col) {
            $row[] = ($col == 'creation_date' || $col == 'renewal_date')
                ? ($lead->$col ? Carbon::parse($lead->$col)->format('Y/m/d') : '')
                : $lead->$col;
        }
        return $row;
    }

    // Generate CSV row for lead with contact
    private function generateLeadContactRow($lead, $contact, $leadColumns, $contactColumns): array
    {
        $row = [];
        foreach ($leadColumns as $col) {
            if ($col != 'response_date') {
                $row[] = ($col == 'creation_date' || $col == 'renewal_date')
                    ? ($lead->$col ? Carbon::parse($lead->$col)->format('Y/m/d') : '')
                    : $lead->$col;
            }
        }

        foreach ($contactColumns as $col) {
            $row[] = $contact->$col;
        }

        // Add latest action date for this contact
        $ctName = $contact->c_first_name.' '.$contact->c_last_name;
        $action = $lead->actions()->where('contact_name', $ctName)->latest('contact_date')->first();
        $row[] = $action ? Carbon::parse($action->contact_date)->format('Y/m/d') : '';

        return $row;
    }

    // Store CSV to storage
    private function storeCsv($csvContent, $fileName)
    {
        rewind($csvContent);
        $csvData = stream_get_contents($csvContent);
        fclose($csvContent);
        Storage::put('public/csv/'.$fileName, $csvData);
    }

    // Send campaign CSV via email
    private function sendCampaignMail($filePath, $fileName)
    {
        $file = [
            "filePath" => $filePath,
            "fileName" => $fileName,
            "mime" => 'text/csv',
        ];
        $subject = "Campaign Leads CSV";
        $body = "Please find the attached CSV file containing the campaign leads.";
        $this->sendSimpleNotificationMail($this->mailAgentId, $subject, $body, $file);
    }

}
