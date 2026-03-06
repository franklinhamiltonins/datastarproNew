<?php

namespace App\Console\Commands;

use App\Services\ProcessAgentWiseLeadWiseReport;
use App\Traits\CommonFunctionsTrait;
use Illuminate\Console\Command;

class ShootDailyAgentWiseLeadStatus extends Command
{
    use CommonFunctionsTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shoot:dailyagentwiseleadstatus';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'send a mail to notify daily regarding agent wise lead status';

    protected $processAgentWise;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(ProcessAgentWiseLeadWiseReport $processAgentWise)
    {
        parent::__construct();
        $this->processAgentWise = $processAgentWise;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $agentUsers = $this->getAgentListing(true, 0, true);

        foreach ($agentUsers as $agent) {
            try {
                $this->processAgentWise->processDataAgentWise($agent);
                $this->info($agent['displayname'].' Mail processing completed.');
            } catch (\Exception $e) {
                $this->error('Error: '.$agent['displayname'].'- '.$e->getMessage());
            }
        }

        $this->info('Mail Shooted to All Agent.');
    }
}
