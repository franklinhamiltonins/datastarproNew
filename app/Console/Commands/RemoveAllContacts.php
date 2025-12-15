<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Model\LeadsModel\Contact;
use App\Model\LeadsModel\Lead;


class RemoveAllContacts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:removeAllContacts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove all contacts from database';

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
        self::removeAllContacts();
    }

    private function removeAllContacts(){
        // $contacts = Contact::all();
        // $i = 0;

        // foreach($contacts as $contact){

        //     $lead= Lead::find($contact->leads->id);
        //     $name = $contact->c_first_name.' '.$contact->c_last_name;
        //     create_log($lead, 'Delete Contact : '. $name,'');
        //     print_r('Contact ID '. $contact->id .' - '.$name. ' deleted.');
        //     print_r(PHP_EOL);
        //     $contact->delete();
        //     $i++;


        // }

        // print_r($i.' contacts Deleted');

        print_r('Command Disabled . Uncomment in order to use it ');
    }
}
