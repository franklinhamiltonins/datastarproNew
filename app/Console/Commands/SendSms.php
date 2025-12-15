<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Model\Message;
use App\Model\FhinsureLog;
use App\Model\LeadsModel\Contact;

use Vonage\Client;
use Illuminate\Support\Facades\Log;

class SendSms extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'command:smssend';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Command description';

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
		try {
			$vonage_key = env('VONAGE_KEY');
			$vonage_secret = env('VONAGE_SECRET');
			$vonage_from = env('VONAGE_FROM');
			$vonage_from = '+18882024249';

			$vonage_new_client = new Client(new \Vonage\Client\Credentials\Basic($vonage_key, $vonage_secret));
			$sendsmsData = Message::where('chat_type', 'outbound')->where('chat_sms_sent_status', '0')->orderBy('id', 'desc')->limit(1)->get();

			foreach ($sendsmsData as $smsdata) {
				$tomobile = '';
				if(!empty($smsdata->through_sms_provider_flag) && $smsdata->through_sms_provider_flag == 2){
					$contact = FhinsureLog::select('phone')->where('id', $smsdata->newsletter_id)->first();
					if($contact){
						$tomobile = $contact->phone;
					}
					unset($contact);
				}
				else{
					$contact_number = Contact::select('c_phone')->where('id', $smsdata->contact_id)->first();
					if($contact_number){
						$tomobile = $contact_number->c_phone;
					}
					unset($contact_number);
				}
				

				$vonageResponse = $vonage_new_client->message()->send([
					'to' => '+1 ' . $tomobile,
					'from' => $vonage_from,
					'text' => $smsdata->content,
				]);

				$message = $vonageResponse->current();

				if (isset($message['status']) &&    $message['status'] == 0) {
					Message::where('id', $smsdata->id)->update(['chat_sms_sent_status' => 1, 'vonageResponse' => json_encode($message)]);
				} else {
					Message::where('id', $smsdata->id)->update(['chat_sms_sent_status' => 2, 'vonageResponse' =>
					json_encode($message)]);
				}
			}
		} catch (\Exception $e) {
			Log::error('Exception occurred at line ' . $e->getLine() . ' : ' . $e->getMessage());
		}
	}
}
