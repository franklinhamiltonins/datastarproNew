<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Model\LeadsModel\Contact;

class FormatContactPhone extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:FormatContactPhone';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update contacts phone from xxx-xxx-xxxx to 1xxxxxxxxxx ';

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
        self::formatPhone();
    }
    private function formatPhone(){
        $contacts = Contact::all();
        foreach($contacts as $contact){
            $phone =$contact->c_phone;
            if($contact && !empty( $phone)){
               
                  //format phone to xxxxxxxxxx
                
                  if(preg_match( '/[-!$%^&*()_+|~=`{}\[\]:";<>?,. \/]/',$phone, $matches)){
                    $phone =  preg_replace('/[-!$%^&*()_+|~=`{}\[\]:";<>?,. \/]/', '', $phone);
                   
                    }
                    
                       $contact->update(array('c_phone'=>$phone));
                 
                  
              }
             
           
        }
    }
}
