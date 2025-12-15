<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Model\LeadsModel\Lead;

class UpperCaseleadMonths extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:UpperCaseleadMonths';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to update renewal_month names from lowercase to uppercase';

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
            if($lead && !empty($lead->renewal_month)){
         
                switch($lead->renewal_month){
                 
                   case 'january' :
                     $lead->update(array('renewal_month'=> 'January'));
                   break;
                   case 'february' :
                     $lead->update(array('renewal_month'=> 'February'));
                   break;
                   case 'march' :
                     $lead->update(array('renewal_month'=> 'March'));
                   break;
                   case 'april' :
                     $lead->update(array('renewal_month'=> 'April'));
                   break;
                   case 'may' :
                     $lead->update(array('renewal_month'=> 'May'));
                   break;
                   case 'june' :
                     $lead->update(array('renewal_month'=> 'June'));
                   break;
                   case 'july' :
                     $lead->update(array('renewal_month'=> 'July'));
                   break;
                   case 'august' :
                     $lead->update(array('renewal_month'=> 'August'));
                   break;
                   case 'september' :
                     $lead->update(array('renewal_month'=> 'September'));
                   break;
                   case 'october' :
                     $lead->update(array('renewal_month'=> 'October'));
                   break;
                   case 'november' :
                     $lead->update(array('renewal_month'=> 'November'));
                   break;
                   case 'december' :
                     $lead->update(array('renewal_month'=> 'December'));
                     break;
                }
            }
           
        }
    }
}
