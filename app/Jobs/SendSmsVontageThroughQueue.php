<?php

namespace App\Jobs;

use App\Traits\VontageunctionsTrait;
use DB;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendSmsVontageThroughQueue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels,VontageunctionsTrait;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $timeout = 40; // Timeout in seconds

    public $tries = 1; // Maximum number of attempts

    protected $requestData;

    public function __construct($requestData)
    {
        $this->requestData = $requestData;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            // sending content message
            Log::info('SendSmsVontageThroughQueue Job started at: '.date('Y-m-d H:i:s'));
            $response = $this->sendVontagesms($this->requestData['c_phone'], $this->requestData['sms_content']);

            $this->outboundsavemessage($this->requestData, $response);

            Log::info('SendSmsVontageThroughQueue Job completed at: '.date('Y-m-d H:i:s'));
        } catch (\Exception $e) {
            Log::error('SendSmsVontageThroughQueue Job failed: '.$e->getMessage());
        } finally {
            // Ensure the connection is closed after job execution
            DB::disconnect();
        }
    }
}
