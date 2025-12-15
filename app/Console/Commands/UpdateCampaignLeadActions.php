<?php

namespace App\Console\Commands;

use App\Model\Campaign;
use App\Model\LeadsModel\Action;
use Illuminate\Console\Command;
use Carbon\Carbon;

class UpdateCampaignLeadActions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:UpdateCampaignLeadActions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates campaign lead actions ';

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
        self::updateCampaignLeadsActions();
    }

    private function updateCampaignLeadsActions(){
        $campaigns = Campaign::whereNotNull('campaign_date')->get();//get the campaign
       
        $actions = "0"; // set default 
            foreach($campaigns as $campaign){
                $campaignDate = $campaign->campaign_date; //get campaign date
                if( $campaignDate  && $campaign->status == "COMPLETED" ){ // if campaign has campaign date and it's status is completed
                   $actions = "0"; 
                   $date = Carbon::createFromFormat('Y-m-d',  $campaignDate ); //format date Carbon date
                   $tenDays = $date->addDays(10); // add ten days to campaign date
                      // get campaign actions number
                      $actions = Action::whereHas('leads',function($query)use( $campaign) {
                
                         $query->whereHas('campaigns',function($qw)use( $campaign) {
                            $qw->where('id',$campaign->id);
                         });
                      })->where(function($q) use ( $campaign,$campaignDate,$tenDays) { 
                               $q->whereBetween('created_at', [ $campaignDate ,
                               $tenDays            
                               ]); //get the actions where created at is situated between campaign date and campaign date+ 10
                         })->count();
                }
                
                if($campaign->lead_actions != $actions && $actions > 0){
                   
                   $campaign->update([
                      'lead_actions' =>$actions
                   ]);
                }       
                    
            }
    }
            
}
