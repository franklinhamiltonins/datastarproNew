<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Model\LeadsModel\Contact;
use Carbon\Carbon;
use DB;

class MarkArchiveContact extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mark:archivecontact';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Contact::
        whereNull('contacts.archive_sms')
        ->join('sms_provider_queue as spq', 'contacts.id', '=', 'spq.contact_id')
        ->leftJoin('messages', 'contacts.id', '=', 'messages.contact_id')
        ->where('spq.sms_sent_flag', 1)
        ->where(function($query) {
            $query->whereNull('messages.id')
                  ->orWhere('messages.created_at', '<', Carbon::now()->subDays(3));
        })
        ->update(['contacts.archive_sms' => 1]);

        $this->info("Done");

    }
}
