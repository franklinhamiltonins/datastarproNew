<?php

namespace App\Console\Commands;

use App\Model\LeadsModel\Contact;
use Illuminate\Console\Command;

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
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        self::addFullName();
    }

    private function addFullName():void
    {
        // Fetch only 10 contacts that don't have full_name
        $contacts = Contact::whereNull('c_full_name')
            ->orWhere('c_full_name', '')
            ->limit(10)
            ->get();

        foreach ($contacts as $contact) {
            $fullName = trim($contact->c_first_name . ' ' . $contact->c_last_name);

            // Update only if fullname is not empty
            if (!empty($fullName)) {
                $contact->update([
                    'c_full_name' => $fullName,
                ]);
            }
        }
    }
}
