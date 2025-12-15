<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Model\LeadsModel\Lead;

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
        $leads = Lead::all();
        foreach($leads as $lead){
            if($lead && empty($lead->state)){
                $lead->update(array('state'=>'FL'));
            }
           
        }
    }
}
