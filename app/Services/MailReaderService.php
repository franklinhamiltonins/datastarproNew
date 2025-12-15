<?php

namespace App\Services;

use Webklex\IMAP\Facades\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;

use App\Model\MailFetchedLog;
use App\Model\DailyCallReportLog;
use App\Model\Setting;
use DB;

class MailReaderService
{
    /**
     * Fetch mails with attachments, validate and store data from CSV
     */
    public function fetchMails(): void
    {
        $client = Client::account('default');

        try {
            $client->connect();
        } catch (\Exception $e) {
            logger()->error('IMAP connection error: ' . $e->getMessage());
            return;
        }

        $folder = $client->getFolder('INBOX');

        $mailSubjectText = Setting::where('id', 1)->value('mail_fetching_subject');

        $mailSubjectText = !empty($mailSubjectText)?$mailSubjectText:'Google sheet link';

        $messages = $folder->query()
            // ->unseen() // unread emails only
            ->subject($mailSubjectText)
            ->since(now()->subDays(1))
            ->limit(10)
            // ->subject($mailSubjectText)
            ->get();

        $messages = $messages->sortByDesc(function ($msg) {
            return optional($msg->getDate()->first())->getTimestamp();
        });

        // echo "<pre>";print_r($messages);exit;


        foreach ($messages as $message) {
            foreach ($message->getAttachments() as $attachment) {
                $filename = $attachment->getName();
                $filePath = 'attachments/' . $filename;

                Storage::disk('local')->put($filePath, $attachment->getContent());

                // Log file processing start
                $logId = $this->makeLogForProcess($filePath, pathinfo($filename, PATHINFO_EXTENSION));

                $csvContent = Storage::get($filePath);
                $csvData = array_map('str_getcsv', explode("\n", $csvContent));

                $this->processCSV($csvData, $logId);
            }

            // Optional: Mark message as read
            $message->setFlag('Seen');
        }

        $client->disconnect();
    }

    /**
     * Create a processing log for the attachment
     * 
     * @param string $filepath
     * @param string $filetype
     * @return int
     */
    protected function makeLogForProcess(string $filepath, string $filetype): int
    {
        $log = new MailFetchedLog();
        $log->date = Carbon::now()->toDateString();
        $log->time = Carbon::now()->toTimeString();
        $log->type = $filetype;
        $log->file_path = $filepath;
        $log->status = 0;
        $log->save();

        return $log->id;
    }

    /**
     * Process the CSV: validate and store or log issues
     * 
     * @param array $csvData
     * @param int $logId
     */
    protected function processCSV(array $csvData, int $logId): void
    {
        if (count($csvData) <= 1) {
            logger()->warning("Blank or insufficient CSV content. Log ID: $logId");
            return;
        }

        if (!$this->checkFormatValidation($csvData[0])) {
            logger()->error("CSV format mismatch. Log ID: $logId");
            // optionally notify via mail
            return;
        }

        $this->saveDataIntoTheTable(array_slice($csvData, 1),$logId);
    }

    /**
     * Validate header format against expected structure
     * 
     * @param array $inputHeader
     * @return bool
     */
    protected function checkFormatValidation(array $inputHeader): bool
    {
        $expectedJson = Setting::where('id', 1)->value('expected_json_format');

        if (!$expectedJson) {
            logger()->error('Expected format JSON not found in settings.');
            return false;
        }

        $expectedHeader = json_decode(trim($expectedJson), true);

        return $inputHeader === $expectedHeader;
    }

    /**
     * Save CSV data into database
     * 
     * @param array $dataRows
     */
    protected function saveDataIntoTheTable(array $dataRows,int $logId): void
    {
        if (empty($dataRows)){
            MailFetchedLog::where('id',$logId)->update(['status'=> 3]);
            return;
        }

        try {
            DB::beginTransaction();

            $insertData = array_map(function ($row) use ($logId){
                return [
                    "call_type"           => $row[0] ?? null,
                    "domain"              => $row[1] ?? null,
                    "user_franklin_id"    => $row[2] ?? null,
                    "btn"                 => $row[3] ?? null,
                    "call_begin"          => $row[4] ?? null,
                    "time_answer"         => $row[5] ?? null,
                    "duration"            => $row[6] ?? null,
                    "remote_number"       => $row[7] ?? null,
                    "dialed_number"       => $row[8] ?? null,
                    "call_id"             => $row[9] ?? null,
                    "origin_ip"           => $row[10] ?? null,
                    "term_ip"             => $row[11] ?? null,
                    "release_cause"       => $row[12] ?? null,
                    "mail_fetched_log_id" => $logId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }, $dataRows);

            foreach (array_chunk($insertData, 25) as $chunk) {
                DailyCallReportLog::insert($chunk);
            }

            MailFetchedLog::where('id',$logId)->update(['status'=> 1]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollback();
            MailFetchedLog::where('id',$logId)->update(['status'=> 2]);
            logger()->error('Error inserting call report data: ' . $e->getMessage());
        }
    }
}