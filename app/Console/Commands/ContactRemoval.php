<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Model\LeadsModel\Contact;
use DB;

class ContactRemoval extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'contact:removal {tablename} {actiontype}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'update contact infomation - actiontype = 1 - removal of field, actiontype = 2 update of fields';

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
        $tablename = $this->argument('tablename');
        $actiontype = $this->argument('actiontype');

        while (DB::table($tablename)
        ->where('status',0)->count() > 0) {

            DB::table($tablename)
            ->where('status',0)
            // ->select('id','Contact_id')
            // ->where('id',2)
            ->orderBy('id')
            ->chunk(500,function($nonmatcheddata) use($tablename,$actiontype){
                foreach ($nonmatcheddata as $key => $nonmatched) {
                    $contact = Contact::withTrashed()->find($nonmatched->Contact_id);
                    if($contact){
                        if($actiontype == 1){
                            $this->blankcontactcolumndata($contact);
                        }
                        else if($actiontype == 2){
                            $this->updatecontactcolumndata($contact,$nonmatched);
                        }
                        
                        $this->updatenonmatcheddatastatus($tablename,$nonmatched->id,1);
                    }
                    else{
                        $this->updatenonmatcheddatastatus($tablename,$nonmatched->id,2);
                    }
                }
            });
        }

        $this->info("Success");
        return 0;
        
    }

    public function blankcontactcolumndata($contact)
    {
        $contact = Contact::withTrashed()->find($contact->id);
        if($contact){
            // $contact->c_first_name = '';
            // $contact->c_last_name = '';
            // $contact->c_full_name = null;
            $contact->c_address1 = null;
            $contact->c_address2 = null;
            $contact->c_city = null;
            $contact->c_state = null;
            $contact->c_zip = null;
            $contact->c_county = null;
            // $contact->c_phone = null;
            // $contact->c_email = null;

            $contact->verified_status = 'Unverified';
            $contact->save();
        }
    }

    public function updatecontactcolumndata($contact,$nonmatched)
    {
        $contact = Contact::withTrashed()->find($contact->id);
        // echo "<pre>";print_r($contact);exit;
        if($contact){
            $verified_status = 'Verified';
            if(!empty($nonmatched->Contact_First_Name)){
                $contact->c_first_name = $nonmatched->Contact_First_Name;
            }
            // else{
            //     if(!empty($contact->c_first_name)){
            //         $verified_status .= ' Contact_First_Name was blank';
            //     }
            // }

            if(!empty($nonmatched->Contact_Last_Name)){
                $contact->c_last_name = $nonmatched->Contact_Last_Name;
            }
            // else{
            //     if(!empty($contact->c_last_name)){
            //         $verified_status .= ' Contact_Last_Name was blank';
            //     }
            // }

            if(!empty($nonmatched->Contact_First_Name) || !empty($nonmatched->Contact_Last_Name)){
                $contact->c_full_name = $nonmatched->Contact_First_Name.' '.$nonmatched->Contact_Last_Name;
            }

            if(!empty($nonmatched->Contact_Address1)){
                $contact->c_address1 = $nonmatched->Contact_Address1;
            }
            // else{
            //     if(!empty($contact->c_address1)){
            //         $verified_status .= ' Contact_Address1 was blank';
            //     }
            // }

            // if(!empty($nonmatched->Contact_Address2)){
            //     $contact->c_address2 = $nonmatched->Contact_Address2;
            // }
            // else{
            //     if(!empty($contact->c_address2)){
            //         $verified_status .= ' Contact_Address2 was blank';
            //     }
            // }

            if(!empty($nonmatched->Contact_City)){
                $contact->c_city = $nonmatched->Contact_City;
            }
            // else{
            //     if(!empty($contact->c_city)){
            //         $verified_status .= ' Contact_City was blank';
            //     }
            // }

            if(!empty($nonmatched->Contact_State)){
                $contact->c_state = $nonmatched->Contact_State;
            }
            // else{
            //     if(!empty($contact->c_state)){
            //         $verified_status .= ' Contact_State was blank';
            //     }
            // }

            if(!empty($nonmatched->Contact_Zip)){
                $contact->c_zip = $nonmatched->Contact_Zip;
            }
            // else{
            //     if(!empty($contact->c_zip)){
            //         $verified_status .= ' Contact_Zip was blank';
            //     }
            // }

            if(!empty($nonmatched->Contact_Phone)){
                $contact->c_phone = $this->beautifyphonenumberformat($nonmatched->Contact_Phone);;
            }
            // else{
            //     if(!empty($contact->c_phone)){
            //         $verified_status .= ' Contact_Phone was blank';
            //     }
            // }

            if(!empty($nonmatched->c_secondary_phone)){
                $contact->c_secondary_phone = $this->beautifyphonenumberformat($nonmatched->c_secondary_phone);;
            }

            if(!empty($nonmatched->Contact_Email)){
                $contact->c_email = $nonmatched->Contact_Email;
            }
            // else{
            //     if(!empty($contact->c_email)){
            //         $verified_status .= ' Contact_Email was blank';
            //     }
            // }

            $contact->verified_status = $verified_status;
            $contact->save();
        }
    }

    public function beautifyphonenumberformat($phone_number)
    {
        return preg_replace('/\D/', '', $phone_number);
    }

    public function updatenonmatcheddatastatus($tablename,$nonmatchedid,$status)
    {
        DB::table($tablename)
        ->where('status',0)
        ->where('id',$nonmatchedid)
        ->update(['status' => $status]);

        return 0;
    }
}
