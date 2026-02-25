<?php

namespace App\Jobs;

use App\Traits\KlaviyoFunctionsTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;


class AddEmailToKlaviyo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, KlaviyoFunctionsTrait, Queueable,SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $timeout = 40; // Timeout in seconds
    public $tries = 1; // Maximum number of attempts
    protected $contact;

    public function __construct(object $contact)
    {
        $this->contact = $contact;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->newklaviyoapi();
    }

    public function newklaviyoapi()
    {
        $header = $this->getKlaviyoHeader();
        $post_field = $this->getKlaviyoProfileCreationBody($this->contact->id);
        if ($post_field['success']) {
            $profile_id = $this->callKlaviyoProfileCreation($header, $post_field['payload']);
            if (! empty($profile_id)) {
                $post_field2 = $this->getKlaviyoProfileLinktolist($profile_id);
                $this->callKlaviyoProfileLinktoList($header, $post_field2);
                unset($post_field2);
            }
        }
        unset($header,$post_field);

    }
}
