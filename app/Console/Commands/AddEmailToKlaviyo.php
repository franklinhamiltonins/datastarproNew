<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Model\LeadsModel\Contact;
use App\Jobs\AddEmailToKlaviyo as AddEmailToKlaviyoJob;

class AddEmailToKlaviyo extends Command
{
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
        $length = rand(1, 3);
		$contacts = Contact::select('id', 'c_first_name', 'c_last_name', 'c_email', 'c_zip', 'klaviyo_call_initiated')->where([
			['c_email', 'like', '%mailinator.com%'],
			['c_email', '!=', NULL ],
			['klaviyo_call_initiated', '=', 0],
		])->limit($length)->get();

		$ids = [];
		foreach($contacts as $contact){
			$ids[] = $contact -> id;
			$delay = rand(1, 60); // Random delay between 1 second and 5 minutes
			AddEmailToKlaviyoJob::dispatch($contact)
				->delay(now()->addSeconds($delay));
		}
		if(!empty($ids))
			Contact::whereIn('id', $ids)->update(['klaviyo_call_initiated' => 1]);
    }
}
