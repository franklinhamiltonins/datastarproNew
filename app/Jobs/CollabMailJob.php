<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Services\ShootMailViaSystem;

class CollabMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $subject, $bodyMsg, $to, $cc,$data;

    public function __construct($subject, $bodyMsg, $to, $cc,$data=[])
    {
        $this->subject = $subject;
        $this->bodyMsg = $bodyMsg;
        $this->to = $to;
        $this->cc = $cc;
        $this->data = $data;
    }

    public function handle()
    {
        (new ShootMailViaSystem())->shootMail(
            $this->subject,
            $this->bodyMsg,
            $this->to,
            $this->cc,
            $this->data,
        );
    }
}
