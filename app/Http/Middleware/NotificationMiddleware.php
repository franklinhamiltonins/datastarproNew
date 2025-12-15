<?php

namespace App\Http\Middleware;

use Closure;
use App\Model\AgentLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use App\Model\Message;
use App\Model\Smsnotification;
use App\Model\Setting;
use DB;

class NotificationMiddleware
{
	public function handle($request, Closure $next)
	{

		$msg_count = Smsnotification::where('smsnotifications.status', 0)
			->where('smsnotifications.user_id', Auth::id())
			->whereNull('smsnotifications.deleted_at')
			->count();
		

		$call_initiated_count = AgentLog::where('agentlogs.status', 'call_initiated')
			->where('user_id', Auth::id())
			// ->orderBy('agentlogs.id', 'desc')
			->count();
		// Retrieve contact IDs on every request
		$limit = 5;
		$call_initiated_coll = AgentLog::select('agentlogs.created_at', 'agentlogs.lead_id', 'agentlogs.contact_id', 'agentlogs.user_id', 'agentlogs.created_at', 'leads.name', 'contacts.c_phone', 'contacts.c_full_name')
			->join('leads', 'leads.id', 'agentlogs.lead_id', 'inner')
			->join('contacts', 'contacts.id', 'agentlogs.contact_id', 'inner')
			->where('agentlogs.status', 'call_initiated')
			->where('agentlogs.user_id', Auth::id())
			->orderBy('agentlogs.id', 'desc')
			->limit($limit)
			->get();

		$inbound_count = Message::join('contacts','messages.contact_id', '=', 'contacts.id')->where('messages.chat_type','inbound')->where('messages.through_sms_provider_flag',1)->where('contacts.has_initiated_stop_chat', 0)->whereNull('contacts.agent_marked_conversation_ended')->whereNull('contacts.archive_sms')->count();

		$inbound_messages = Message::select('messages.content','contacts.c_phone',
			'contacts.lead_id',"messages.created_at as in_time",'messages.contact_id',
		DB::raw("CONCAT(contacts.c_first_name, ' ', contacts.c_last_name) as full_name"))
		->join('contacts','messages.contact_id', '=', 'contacts.id')
		->where('messages.through_sms_provider_flag', 1)
		->where('contacts.has_initiated_stop_chat', 0)
		->whereNull('contacts.agent_marked_conversation_ended')
		->whereNull('contacts.archive_sms')
		->where('messages.chat_type','inbound')
		->orderBy("messages.created_at","DESC")
		->limit($limit)
		->get();


		$inbound_notification_count = Message::where('messages.chat_type','inbound')->where('through_sms_provider_flag',2)->count();
		$inbound_notification_messages =Message::select('messages.content','newsletters.phone as c_phone',
		"messages.created_at as in_time",'messages.newsletter_id',
		DB::raw("CONCAT(newsletters.first_name, ' ', newsletters.last_name) as full_name"))
		->join('newsletters','messages.newsletter_id', '=', 'newsletters.id')
		->where('messages.through_sms_provider_flag', 2)
		->where('messages.chat_type','inbound')
		->orderBy("messages.created_at","DESC")
		->limit($limit)
		->get();

		$can_access_notification = false;
		$user = auth()->user(); // Get the authenticated user

		if ($user) { // Check if the user is authenticated
		    $is_admin = $user->can('agent-create'); // Check the permission
		    if ($is_admin) {
		        $can_access_notification = true;
		    } else {
		        if ($user->id == 26) {
		            $can_access_notification = true;
		        }
		    }
		}

		$setting_time_data = Setting::select('pipeline_url')->first();


		// Share the contact IDs with all views
		View::share('notifications', [
			'msg_count' => $msg_count,
			'call_initiated_count' => $call_initiated_count,
			'call_initiated_coll' => $call_initiated_coll,
			'inbound_count' => $inbound_count,
			'inbound_messages' => $inbound_messages,
			'inbound_notification_count' => $inbound_notification_count,
			'inbound_notification_messages' => $inbound_notification_messages,
			'can_access_notification' => $can_access_notification,
			'pipeline_url' => $setting_time_data->pipeline_url,
		]);

		return $next($request);
	}
}
