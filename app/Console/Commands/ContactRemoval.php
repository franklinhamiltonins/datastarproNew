<?php

namespace App\Console\Commands;

use App\Model\LeadsModel\Contact;
use DB;
use Illuminate\Console\Command;

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

    public $verified_status = 'Verified';

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
            ->where('status', 0)->count() > 0) {

            DB::table($tablename)
                ->where('status', 0)
                ->orderBy('id')
                ->chunk(500, function ($nonmatcheddata) use ($tablename, $actiontype) {
                    foreach ($nonmatcheddata as $key => $nonmatched) {
                        $contact = Contact::withTrashed()->find($nonmatched->Contact_id);
                        if ($contact) {
                            if ($actiontype == 1) {
                                $this->blankcontactcolumndata($contact);
                            } elseif ($actiontype == 2) {
                                $this->updatecontactcolumndata($contact, $nonmatched);
                            }

                            $this->updatenonmatcheddatastatus($tablename, $nonmatched->id, 1);
                        } else {
                            $this->updatenonmatcheddatastatus($tablename, $nonmatched->id, 2);
                        }
                    }
                });
        }

        $this->info('Success');

        return 0;

    }

    public function blankcontactcolumndata($contact)
    {
        $contact = Contact::withTrashed()->find($contact->id);
        if ($contact) {
            $contact->c_address1 = null;
            $contact->c_address2 = null;
            $contact->c_city = null;
            $contact->c_state = null;
            $contact->c_zip = null;
            $contact->c_county = null;

            $contact->verified_status = $this->verified_status;
            $contact->save();
        }
    }

    public function updatecontactcolumndata($contact, $nonmatched)
    {
        $contact = Contact::withTrashed()->find($contact->id);
        if ($contact) {

            $contact->verified_status = $this->verified_status;
            $contact->save();
        }
    }

    public function beautifyphonenumberformat($phone_number)
    {
        return preg_replace('/\D/', '', $phone_number);
    }

    public function updatenonmatcheddatastatus($tablename, $nonmatchedid, $status)
    {
        DB::table($tablename)
            ->where('status', 0)
            ->where('id', $nonmatchedid)
            ->update(['status' => $status]);

        return 0;
    }
}
