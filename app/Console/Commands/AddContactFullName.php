<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Model\LeadsModel\Contact;

class AddContactFullName extends Command
{
    /** 
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:AddContactFullName';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Combines c_first_name with c_last_name and adds it to c_full_name';

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
        self::addFullName();
    }
    private function addFullName(){
        $contacts = Contact::all();
        foreach($contacts as $contact){
            $fullname = $contact->c_first_name.' '.$contact->c_last_name;
           
            if($contact && !empty( $fullname) && empty($contact->c_full_name)){
               
                $contact->update([
                    'c_full_name' => $fullname
                ]);
               
                  
              }
             
           
        }
    }
}
