<?php

namespace App\Console\Commands;

use App\Model\LeadsModel\Lead;
use Illuminate\Console\Command;

class UpdateBusinessState extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:UpdateBusinessState';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates empty lead states to "FL"';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $leads = Lead::limit(10)->get();
        foreach ($leads as $lead) {
            if ($lead && empty($lead->state)) {
                $lead->update(['state' => 'FL']);
            }

        }
    }
}
