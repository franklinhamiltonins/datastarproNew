<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MailReaderService;

class ProcessMailAttachments extends Command
{   

    protected $signature = 'mail:process-attachments';
    protected $description = 'Read specific emails, download attachments and process them';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(MailReaderService $readerService)
    {
        try {
            $readerService->fetchMails();
            $this->info('Mail processing completed.');
        } catch (Exception $e) {
            $this->error('Error: ' . $e->getMessage());
        }
    }
}
