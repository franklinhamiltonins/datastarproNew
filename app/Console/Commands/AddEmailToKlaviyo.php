<?php

namespace App\Console\Commands;

use App\Jobs\AddEmailToKlaviyo as AddEmailToKlaviyoJob;
use App\Model\LeadsModel\Contact;
use App\Traits\CommonFunctionsTrait;
use Illuminate\Console\Command;

class AddEmailToKlaviyo extends Command
{
    use CommonFunctionsTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:add-email-to-klaviyo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will fetch email and add into Klaviyo list';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Random number of contacts to process (1 to 3)
        $limit = $this->generateSecureRandomNumber(1, 3);

        $contacts = Contact::select('id', 'c_first_name', 'c_last_name', 'c_email', 'c_zip', 'klaviyo_call_initiated')
            ->where('c_email', 'like', '%mailinator.com%')
            ->whereNotNull('c_email')
            ->where('klaviyo_call_initiated', 0)
            ->limit($limit)
            ->get();

        if ($contacts->isEmpty()) {
            return; // nothing to process
        }

        // Dispatch jobs with random delay 1s–60s
        $contacts->each(function ($contact) {
            $delaySeconds = $this->generateSecureRandomNumber(1, 60);

            AddEmailToKlaviyoJob::dispatch($contact)
                ->delay(now()->addSeconds($delaySeconds));
        });

        // Mark contacts as "initiated"
        Contact::whereIn('id', $contacts->pluck('id'))->update([
            'klaviyo_call_initiated' => 1,
        ]);
    }
}
