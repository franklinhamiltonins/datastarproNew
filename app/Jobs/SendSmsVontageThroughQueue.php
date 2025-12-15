<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Config;
use DB;

use App\Traits\VontageunctionsTrait;

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
    protected $request_data;

    public function __construct($request_data)
    {
        $this->request_data = $request_data;
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
            Log::info("SendSmsVontageThroughQueue Job started at: " . date("Y-m-d H:i:s"));
            $response = $this->sendVontagesms($this->request_data['c_phone'],$this->request_data['sms_content']);

            $this->outboundsavemessage($this->request_data,$response);

            Log::info("SendSmsVontageThroughQueue Job completed at: " . date("Y-m-d H:i:s"));
        }
        catch (\Exception $e) {
            Log::error("SendSmsVontageThroughQueue Job failed: " . $e->getMessage());
            
            // Re-throw the exception to allow Laravel to handle retries
            // throw $e;
        } finally {
            // Ensure the connection is closed after job execution
            DB::disconnect();
        }
    }
}
