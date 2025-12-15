<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Model\LeadsModel\Action;

class PopulateContactActionDate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:PopulateContactActionDate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This copies the "created_at" value to "contact_date"';

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
        self::addContactDate();
    }
    private function addContactDate(){
        $actions = Action::all();
        foreach($actions as $action){

            if( is_null($action->contact_date)){

                $action->update([
                    'contact_date' => $action->created_at
                ]);
                print_r( $action->id);
                print_r( ', ');

              }


        }
    }
}
