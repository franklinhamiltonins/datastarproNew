<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Model\LeadsModel\Contact;
use Illuminate\Support\Facades\Log;

class UpdateContactPhone extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:contact-phone-update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update contact phone to exclude +1';

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
        try{

            Contact::chunk(200, function ($contacts) {
                $update_arr = [];
                foreach($contacts as $contact) {
                    $id = $contact->id;
                    $phone = $contact->c_phone;
                    $c_phone_updated = 0;
                    $c_phone_update_status = "";
    
                    if(!$phone) {
                        $c_phone_update_status = "Phone no not added.";
                    } elseif (!str_contains($phone, '+1')) {
                        $c_phone_update_status = "Phone no does not contain +1";
                    } else {
                        $phone = str_replace("+1","",$contact->c_phone);
                        if(strlen($phone) < 10) { 
                            $c_phone_update_status = "Not Updated! Phone length is ". strlen($phone)." (excluding +1).";
                        } else {
                                $c_phone_updated = 1;
                                $c_phone_update_status = "Updated successfully.";
                        }
                    }
                    // get the array to update
                    $update_arr[] = [
                        'id' => $id, 
                        'c_phone' => $phone, 
                        'c_phone_updated' => $c_phone_updated, 
                        'c_phone_update_status' => $c_phone_update_status
                    ];
                }
        
                Contact::upsert($update_arr, ['id'], ['c_phone', 'c_phone_updated', 'c_phone_update_status']);
                // Log::info("Contact updated successfully");
            });

        } catch (\Exception $e) {
			Log::error('Failed to update contact!' . $e->getMessage());
		}        
    }
}
