<?php

namespace App\Console\Commands;

use App\Model\LeadsModel\Contact;
use Illuminate\Console\Command;

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
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        self::formatPhone();
    }

    private function formatPhone()
    {
        $contacts = Contact::all();
        foreach ($contacts as $contact) {
            $phone = $contact->c_phone;
            if ($contact && ! empty($phone)) {
                if (preg_match('/[-!$%^&*()_+|~=`{}\[\]:";<>?,. \/]/', $phone, $matches)) {
                    $phone = preg_replace('/[-!$%^&*()_+|~=`{}\[\]:";<>?,. \/]/', '', $phone);

                }

                $contact->update(['c_phone' => $phone]);

            }

        }
    }
}
