<?php

namespace App\Services;

use App\Model\DailyCallReportLog;
use App\Model\MailFetchedLog;
use App\Model\Setting;
use DB;
use Illuminate\Support\Facades\Storage;
use Webklex\IMAP\Facades\Client;

class MailReaderService
{
    public function fetchMails(): void
    {
        $client = Client::account('default');

        if (! $this->connectImap($client)) {
            return;
        }

        $folder = $client->getFolder('INBOX');
        $subject = Setting::where('id', 1)->value('mail_fetching_subject') ?? 'Google sheet link';

        $messages = $folder->query()
            ->since(now()->subDays(1))
            ->subject($subject)
            ->limit(10)
            ->get()
            ->sortByDesc(fn ($msg) => optional($msg->getDate()->first())->getTimestamp());

        foreach ($messages as $message) {
            foreach ($message->getAttachments() as $attachment) {
                $filePath = $this->storeAttachment($attachment);
                $logId = $this->createLog($filePath, pathinfo($filePath, PATHINFO_EXTENSION));

                $csvData = $this->readCsv($filePath);
                $this->processCsvContent($csvData, $logId);
            }

            $message->setFlag('Seen'); // optional
        }

        $client->disconnect();
    }

    private function connectImap($client): bool
    {
        try {
            $client->connect();
            return true;
        } catch (\Exception $e) {
            logger()->error('IMAP connection error: '.$e->getMessage());
            return false;
        }
    }

    private function storeAttachment($attachment): string
    {
        $filename = $attachment->getName();
        $path = 'attachments/'.$filename;

        Storage::disk('local')->put($path, $attachment->getContent());

        return $path;
    }

    private function createLog(string $path, string $type): int
    {
        return MailFetchedLog::create([
            'date'      => now()->toDateString(),
            'time'      => now()->toTimeString(),
            'type'      => $type,
            'file_path' => $path,
            'status'    => 0,
        ])->id;
    }

    private function readCsv(string $path): array
    {
        $content = Storage::get($path);
        return array_map('str_getcsv', explode("\n", $content));
    }

    private function processCsvContent(array $csv, int $logId): void
    {
        if (count($csv) <= 1) {
            logger()->warning("Blank or insufficient CSV. Log ID: $logId");
            return;
        }

        if (! $this->isValidHeader($csv[0])) {
            logger()->error("CSV header mismatch. Log ID: $logId");
            return;
        }

        $this->storeCsvRows(array_slice($csv, 1), $logId);
    }

    private function isValidHeader(array $inputHeader): bool
    {
        $expected = Setting::where('id', 1)->value('expected_json_format');

        if (! $expected) {
            logger()->error('Expected header JSON missing in settings.');
            return false;
        }

        return $inputHeader === json_decode(trim($expected), true);
    }

    private function storeCsvRows(array $rows, int $logId): void
    {
        if (empty($rows)) {
            MailFetchedLog::whereId($logId)->update(['status' => 3]);
            return;
        }

        try {
            DB::beginTransaction();

            $mapped = array_map(fn ($r) => $this->mapRow($r, $logId), $rows);

            foreach (array_chunk($mapped, 25) as $chunk) {
                DailyCallReportLog::insert($chunk);
            }

            MailFetchedLog::whereId($logId)->update(['status' => 1]);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            MailFetchedLog::whereId($logId)->update(['status' => 2]);
            logger()->error('Error inserting report data: '.$e->getMessage());
        }
    }

    private function mapRow(array $row, int $logId): array
    {
        return [
            'call_type'           => $row[0] ?? null,
            'domain'              => $row[1] ?? null,
            'user_franklin_id'    => $row[2] ?? null,
            'btn'                 => $row[3] ?? null,
            'call_begin'          => $row[4] ?? null,
            'time_answer'         => $row[5] ?? null,
            'duration'            => $row[6] ?? null,
            'remote_number'       => $row[7] ?? null,
            'dialed_number'       => $row[8] ?? null,
            'call_id'             => $row[9] ?? null,
            'origin_ip'           => $row[10] ?? null,
            'term_ip'             => $row[11] ?? null,
            'release_cause'       => $row[12] ?? null,
            'mail_fetched_log_id' => $logId,
            'created_at'          => now(),
            'updated_at'          => now(),
        ];
    }
}
