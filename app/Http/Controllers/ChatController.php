<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\Message;
use App\Model\Smsnotification;
use App\Model\LeadsModel\Contact;
use App\Model\Setting;
use App\Model\FhinsureLog;
use App\Model\User;

use Vonage\Client;
use Vonage\SMS\Message\SMS;

use Illuminate\Support\Facades\Log;
use App\Events\NewSmsReceived;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


use App\Traits\CommonFunctionsTrait;

class ChatController extends Controller
{
	use CommonFunctionsTrait;
	public function index($contactId, $newsletter_type = '')
	{
	    $messages = Message::select('users.name','messages.chat_type','messages.chat_sms_sent_status','messages.content','messages.created_at');

	    $is_admin = auth()->user()->can('agent-create');
		if(!$is_admin){
			$messages->where(function($query) {
		        $query->where('messages.user_id', auth()->user()->id)
		              ->orWhere('messages.user_id', 0)
		              ->orWhereNull('messages.user_id');
		    });
		}

	    if (!empty($newsletter_type) && strtolower($newsletter_type) == 'yes') {
	        $messages->where('messages.newsletter_id', $contactId);
	    } else {
	        $messages->where('messages.contact_id', $contactId);
	    }
	    $messages->leftjoin('users','messages.user_id','=','users.id');

	    // Execute the query and get the collection
	    $messages = $messages->orderBy('messages.created_at')->get();

	    // Return JSON response with the collection (no conversion to array)
	    return response()->json([
	        'status' => '200',
	        'response' => $messages, // Return the collection directly
	        'contact_id' => $contactId,
	        'is_admin' => $is_admin,
	        'logged_in_user_id' => auth()->user()->id,
	        'unread_count' => 0
	    ]);
	}

	public function store(Request $request)
	{
		// if the user has  send stop message then don't allow him to send an outbound message from system start
		$chatContactId = $request->input('chatContactId');
		$is_admin = auth()->user()->can('agent-create');
		$isNewsletter = !empty($request->input('isNewsletter'))? $request->input('isNewsletter'): "";
		if(strtolower($isNewsletter) != "yes") {
			$checkChatFlag = Contact::where('id', $chatContactId)
			->first();
			if ($checkChatFlag->has_initiated_stop_chat == 1) {

				return response()->json([
					'success' => 'false',
					'response' => "Can not send the msg, chat has ended.",
					'is_admin' => $is_admin,
					'logged_in_user_name' => auth()->user()->name,
				], 500);
			}
		}
		

		// if the user has  send stop message then don't allow him to send an outbound message from system end.

		// if the agent send a msg to contact THEN again send msg will be appear apear after 5 hr - START
		$exceed_times = Setting::Find(1);
		$proceed_time_in_minute = $exceed_times->proceed_time_in_minute;
		// $exceed_time_minute = $exceed_times->exceed_time_minute;
		// $totalExceedMinutes = ($exceed_time_hour * 60) + $exceed_time_minute;

		$check_max_execution_time = $this->check_max_execution_time($chatContactId, $isNewsletter);
		// dd($check_max_execution_time);
		$dataOfExecute = json_decode($check_max_execution_time->getContent(), true);

    	$responseValue = !empty($dataOfExecute['response'])?intval($dataOfExecute['response']):0;

    	// echo "<pre>";print_r($responseValue);exit;
		if ($responseValue > 0) { 
			return response()->json([
				'success' => false,
				'left minute' => $responseValue,
				'response' => "You can't send the message before 5 hours.",
				'is_admin' => $is_admin,
				'logged_in_user_name' => auth()->user()->name,
			], 500);
		} 
		// if the agent send a msg to contact THEN again send msg will be appear apear after 5 hr - END

		// if this one is first msg add the STOP msg
		$chatContent = $request->input('content');
		$checkFirstMsg = Message::where('chat_type', 'outbound');
		if(strtolower($isNewsletter) != "yes")
			$checkFirstMsg= $checkFirstMsg->where('contact_id', $chatContactId);
		else
			$checkFirstMsg= $checkFirstMsg->where('newsletter_id', $chatContactId);
			
		$checkFirstMsg = $checkFirstMsg->first();

		if (is_null($checkFirstMsg)) {
			$chatContent = $chatContent . '</br> Please text "STOP" to stop the conversation.';
		}
		// if this one is first msg add the STOP msg end

		// update the message in the message table with outbound type which  will send sms to the number via command start
		try {
			$message = new Message();
			$message->user_id = auth()->id();
			if(strtolower($isNewsletter) != "yes"){
				$message->contact_id = $request->input('chatContactId');
				if(!empty($isNewsletter)){
					$message->through_sms_provider_flag = 1;
				}
			}
			else{
				$message->newsletter_id = $request->input('chatContactId');
				if(!empty($isNewsletter)){
					$message->through_sms_provider_flag = 2;
				}
			}
				
			// $message->max_time_to_send = Carbon::now()->addHours(5);
			$message->max_time_to_send = Carbon::now()->addMinutes($proceed_time_in_minute);
			$message->content = $chatContent;
			$message->chat_type = 'outbound'; // Change as needed
			$message->save();
			return response()->json([
				'status' => '200',
				'response' => $message,
				'last_insert_id' => $message->id,
				'contact_id' => $request->input('chatContactId'),
				'unread_count' => 0,
				'is_admin' => $is_admin,
				'logged_in_user_name' => auth()->user()->name,
			],200);
		} catch (\Exception $e) {
			return response()->json([
				'success' => 'false',
				'response' => "Failed to send the message. Please contact the administrator.",
				'is_admin' => $is_admin,
				'logged_in_user_name' => auth()->user()->name,
			], 500);
		}
		// update the message in the message table with outbound type which  will send sms to the number via command end
	}

	public function receivechat_test(Request $request)
	{
		$message = new Message();

		// testing data start

		// $from = 9546109418;
		$from = 1134567890;
		$contact_detail = Contact::select('id', 'c_full_name', 'lead_id')->where('c_phone', $from)->first();
		// dd($contact_detail);
		$contact_id = $contact_detail->id;
		$c_full_name = $contact_detail->c_full_name;
		$lead_id = $contact_detail->lead_id;
		$user_detail = Message::where('contact_id', $contact_id)->where('chat_type', 'outbound')->orderBy('id', 'desc')->first();
		$user_id = $user_detail->user_id;
		$content = $request->content;

		// getting from msg - START
		$msgContent = $request->content;
		$decoded_msg = json_decode($msgContent, true);
		$to_msg_type = $decoded_msg['to']['type'];
		$from_msg_type = $decoded_msg['from']['type'];
		$img_url = $decoded_msg['message']['content']['image']['url'];

		if ($to_msg_type === 'mms' && $from_msg_type === 'mms') {
			$message->msg_type = $from_msg_type;
		}
		if ($decoded_msg['message']['content']['type'] === 'image') {
			$message->image_url = $img_url;
		}
		if (isset($decoded_msg['message']['content']['caption'])) {
			$message->content = $decoded_msg['message']['content']['caption'];
		} else {
			$message->content = '';
		}
		// getting from msg - END

		$message->chat_sms_sent_status = 0;
		if (strtolower($msgContent) === 'stop') {
			$msgContent = $c_full_name . " has send STOP and doesn't want to receive anymore messages";
			$message->chat_sms_sent_status = 5;
		}

		if (strtolower($msgContent) === 'start') {
			$msgContent = $c_full_name . " has send START and wants to receive messages again.";
			$message->chat_sms_sent_status = 5;
		}

		// testing data end

		$message->user_id = $user_id;
		$message->contact_id = $contact_id;
		$message->content = $msgContent;
		$message->vonageResponse = '';
		$message->chat_type = 'inbound'; // Change as needed
		// dd($message);
		$message->save();
		$this->receiveSms($request->content, $message->user_id, $message->contact_id, $c_full_name, $lead_id, $msgContent);

		// updateing the execution time of message
		$messageRecord = Message::where('contact_id', $contact_id)
								->where('user_id', $user_id)
								->where('chat_type', 'outbound')
								->where('chat_sms_sent_status', '1')
								->orderBy('created_at', 'desc')
								->first();
		if ($messageRecord) {
			$messageRecord->max_time_to_send = null;
			$messageRecord->save();
		}

		// if the content is stop then  update the db for not allowing future messages to prospect start.



		if (strtolower($request->content) === 'stop') {
			$updateResult = DB::table('contacts')
				->where('id', $message->contact_id)
				->where('has_initiated_stop_chat', 0)
				->update(['has_initiated_stop_chat' => 1]);

			if ($updateResult) {
				Log::info('Status updated and stop activated for contact ' . $contact_id);
			} else {
				Log::error('Unable to enable stop and update status for contact ' . $contact_id);
			}
		}

		if (strtolower($request->content) === 'start') {
			$updateResult = DB::table('contacts')
				->where('id', $message->contact_id)
				->where('has_initiated_stop_chat', 1)
				->update(['has_initiated_stop_chat' => 0]);

			if ($updateResult) {
				Log::info('Status updated and restart activated for contact ' . $contact_id);
			} else {
				Log::error('Unable to enable restart and update status for contact ' . $contact_id);
			}
		}
	}

	public function receivechat(Request $request)
	{
		Log::info('Request ' . $request);
		// echo "<pre>";print_r($request->all());exit;

		// $decoded_msg = json_decode($request, true);


		$to_msg_type = $img_url = $caption = $from = '';

		$message = new Message();
		$responseData = $request;
		$content = $responseData->text;
		$msgContent = $responseData->text;
		$from = $responseData->msisdn;
		$stop_found = false;

		$lowercase_msgcontent = strtolower($msgContent);

		// Split the string into words
		$words = preg_split('/\s+/', $lowercase_msgcontent);

		// Check if "stop" is found within the first 4 or 5 words
		$max_words_to_check = 5;
		$words_to_check = array_slice($words, 0, $max_words_to_check);

		foreach ($words_to_check as $word) {
		    // Strip punctuation from each word and check if it's "stop"
		    if (preg_replace('/[^\w]/', '', $word) === 'stop') {
		        $stop_found = true;
		        break;
		    }
		}
		// echo $stop_found; exit;


		$jsonData = $request->json()->all();
		if ($jsonData) :
			$to_msg_type = (isset($jsonData['to']['type'])) ? $jsonData['to']['type'] : '';
			$from = (isset($jsonData['from']['number'])) ? $jsonData['from']['number'] : '';
			$to_msg_type = (isset($jsonData['to']['type'])) ? $jsonData['to']['type'] : '';
			$img_url = (isset($jsonData['message']['content']['image']['url'])) ? $jsonData['message']['content']['image']['url'] : '';
			$caption = (isset($jsonData['message']['content']['image']['caption'])) ? $jsonData['message']['content']['image']['caption'] : '';
			if ($to_msg_type == 'mms') :
				$msgContent = $img_url;
			endif;

		endif;


		$from = substr($responseData->msisdn, (strlen($responseData->msisdn) - 10));

		

		$user_id = null;
		$through_sms_provider_flag = null;
		$newsletter_id = null;
		$contact_id = null;
		$c_full_name = null;
		$lead_id = null;

		$contact_detail_news = FhinsureLog::select('id', 'first_name', 'last_name')->where('phone', $from)->first();
		if($contact_detail_news){
			$newsletter_id = $contact_detail_news->id;
		}

		$contact_detail = Contact::select('id', 'c_full_name', 'lead_id')->where('c_phone', $from)->first();
		if($contact_detail){
			$contact_detail->archive_sms = null;
			$contact_detail->save();
			$contact_id = $contact_detail->id;
		}

		$user_detail = Message::where('chat_type', 'outbound')
		->where(function($query) use($newsletter_id,$contact_id){
			$iloop = 0;
			if(!empty($contact_id)){
				$query->where('contact_id', $contact_id);
				$iloop++;
			}
			if(!empty($newsletter_id)){
				if(!empty($iloop)){
					$query->orWhere('newsletter_id', $newsletter_id);
				}
				else{
					$query->where('newsletter_id', $newsletter_id);
				}
			}
		})
		->orderBy('id', 'desc')->first();
		if($user_detail){
			$user_id = $user_detail->user_id;
			$through_sms_provider_flag = $user_detail->through_sms_provider_flag;
			$newsletter_id = $user_detail->newsletter_id;
		}
		if(!empty($user_detail->through_sms_provider_flag) && $user_detail->through_sms_provider_flag == 2){
			$contact_id = null;
			$contact_detail = FhinsureLog::select('id', 'first_name', 'last_name')->where('phone', $from)->first();
			if($contact_detail){
				$newsletter_id = $contact_detail->id;
				$c_full_name = $contact_detail->first_name.' '.$contact_detail->last_name;
			}
		}
		else{
			$newsletter_id = null;
			$contact_detail = Contact::select('id', 'c_full_name', 'lead_id')->where('c_phone', $from)->first();
			if($contact_detail){
				$contact_id = $contact_detail->id;
				$c_full_name = $contact_detail->c_full_name;
				$lead_id = $contact_detail->lead_id;
			}
		}

		$message->chat_sms_sent_status = 0;

		if ($stop_found) {
			$msgContent = $c_full_name . " has send STOP and doesn't want to receive anymore messages";
			$message->chat_sms_sent_status = 5;
		}
		else{
			if (strtolower($msgContent) === 'start') {
				$msgContent = $c_full_name . " has send START and wants to receive messages again.";
				$message->chat_sms_sent_status = 5;
			}
		}
		// echo $stop_found."found";exit;

		$message->user_id = $user_id;
		$message->through_sms_provider_flag = $through_sms_provider_flag;
		$message->newsletter_id = $newsletter_id;
		$message->contact_id = $contact_id;
		$message->content = $msgContent;
		$message->vonageResponse = '';
		$message->chat_type = 'inbound'; // Change as needed
		$message->save();

		// if the content is stop then  update the db for not allowing future messages to prospect start.
		if ($stop_found) {
			$updateResult = Contact::where('id', $message->contact_id)
				->where('has_initiated_stop_chat', 0)
				->update(['has_initiated_stop_chat' => 1]);

			if ($updateResult) {
				Log::info('Status updated and stop activated for contact ' . $contact_id);
			} else {
				Log::error('Unable to enable stop and update status for contact ' . $contact_id);
			}
		}
		else{
			if (strtolower($responseData->text) === 'start') {
				$updateResult = Contact::where('id', $message->contact_id)
					->where('has_initiated_stop_chat', 1)
					->update(['has_initiated_stop_chat' => 0]);

				if ($updateResult) {
					Log::info('Status updated and restart activated for contact ' . $contact_id);
				} else {
					Log::error('Unable to enable restart and update status for contact ' . $contact_id);
				}
			}
		}
		$this->receiveSms($responseData->text, $message->user_id, $message->contact_id, $c_full_name, $lead_id, $msgContent);
	}


	public function receiveSms($message, $user_id, $contact_id, $c_full_name, $lead_id, $manipulated_message_content)
	{
		try {
			// storing the detail in notification table
			$smsnotification = new Smsnotification();
			$smsnotification->user_id = $user_id;
			$smsnotification->contact_id = $contact_id;
			$smsnotification->smscontent = $message;
			$smsnotification->status = 0;
			$smsnotification->save();

			// event(
			// 	new \App\Events\leadclickedWebsocket(
			// 		json_encode([
			// 			'message' => $message,
			// 			'user_id' => $user_id,
			// 			'full_name' => $c_full_name,
			// 			'lead_id' => $lead_id,
			// 			'contact_id' => $contact_id,
			// 			'manipulated_message_content' => $manipulated_message_content
			// 		])
			// 	)
			// );

			// return response()->json(['success' => true, 'msg' => 'Notification received']);
		} catch (\Exception $e) {
			// return response()->json(['success' => false, 'msg' => $e->getMessage()]);
		}
	}

	public function getAllUnreadMsg()
	{

		$smsnotifications = Smsnotification::join('contacts', 'smsnotifications.contact_id', '=', 'contacts.id')
			->select('smsnotifications.*', 'contacts.c_full_name', 'contacts.lead_id')
			->where('smsnotifications.status', 0)
			->where('smsnotifications.user_id', auth()->user()->id)
			->whereNull('smsnotifications.deleted_at')
			->limit(5)
			->orderBy('smsnotifications.id', 'DESC')
			->get();
		// dd($smsnotifications);

		return json_encode([
			'status' => '200',
			'response' => $smsnotifications
		]);
	}
	public function chatsms(Request $request)
	{
		// $vonage_key = env('VONAGE_KEY');
		// $vonage_secret = env('VONAGE_SECRET');
		// $vonage_from = env('VONAGE_FROM');

		// $vonage_new_client = new Client(new \Vonage\Client\Credentials\Basic($vonage_key, $vonage_secret));

		// $vonage_new_client->message()->send([
		// 	// 'to' => $request->input('receiver_cotact_no'),
		// 	'to' => '+1 9546109418',
		// 	'from' => $vonage_from,
		// 	'text' => 'test msg',
		// ]);

		// saving data in 
	}
}