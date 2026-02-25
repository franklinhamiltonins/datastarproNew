<?php

namespace App\Http\Middleware;

use App\Model\AgentLog;
use App\Model\Message;
use App\Model\Setting;
use App\Model\Smsnotification;
use Closure;
use DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class NotificationMiddleware
{
    protected $limit = 5;

    public function handle($request, Closure $next)
    {
        $user = Auth::user();

        // If user not logged in, return empty response
        if (!$user) {
            View::share('notifications', $this->emptyResponse());
            return $next($request);
        }

        $notifications = [
            'msg_count'                     => $this->getSmsNotificationCount($user->id),
            'call_initiated_count'          => $this->getCallInitiatedCount($user->id),
            'call_initiated_coll'           => $this->getCallInitiatedList($user->id),
            'inbound_count'                => $this->getInboundCount(),
            'inbound_messages'              => $this->getInboundMessages(),
            'inbound_notification_count'    => $this->getInboundNotificationCount(),
            'inbound_notification_messages' => $this->getInboundNotificationMessages(),
            'can_access_notification'       => $this->canAccessNotification($user),
            'pipeline_url'                  => $this->getPipelineUrl(),
        ];

        View::share('notifications', $notifications);
        return $next($request);
    }

    // SMS Notification Count
    private function getSmsNotificationCount($userId)
    {
        return Smsnotification::where('status', 0)
        ->where('user_id', $userId)
        ->whereNull('deleted_at')
        ->count();
    }

    // Call initiated (count + list)
    private function getCallInitiatedCount($userId)
    {
        return AgentLog::where('status', 'call_initiated')
        ->where('user_id', $userId)
        ->count();
    }

    private function getCallInitiatedList($userId)
    {
        return AgentLog::select([
            'agentlogs.created_at',
            'agentlogs.lead_id',
            'agentlogs.contact_id',
            'agentlogs.user_id',
            'leads.name',
            'contacts.c_phone',
            'contacts.c_full_name',
        ])
        ->join('leads', 'leads.id', '=', 'agentlogs.lead_id')
        ->join('contacts', 'contacts.id', '=', 'agentlogs.contact_id')
        ->where('agentlogs.status', 'call_initiated')
        ->where('agentlogs.user_id', $userId)
        ->orderBy('agentlogs.id', 'desc')
        ->limit($this->limit)
        ->get();
    }

    // Inbound messages (provider flag = 1)
    private function baseInboundQuery()
    {
        return Message::select([
            'messages.content',
            'contacts.c_phone',
            'contacts.lead_id',
            'messages.created_at as in_time',
            'messages.contact_id',
            DB::raw("CONCAT(contacts.c_first_name, ' ', contacts.c_last_name) AS full_name"),
        ])
        ->join('contacts', 'messages.contact_id', '=', 'contacts.id')
        ->where('messages.chat_type', 'inbound')
        ->where('messages.through_sms_provider_flag', 1)
        ->where('contacts.has_initiated_stop_chat', 0)
        ->whereNull('contacts.agent_marked_conversation_ended')
        ->whereNull('contacts.archive_sms');
    }

    private function getInboundCount()
    {
        return $this->baseInboundQuery()->count();
    }

    private function getInboundMessages()
    {
        return $this->baseInboundQuery()
        ->orderBy('messages.created_at', 'DESC')
        ->limit($this->limit)
        ->get();
    }

    // Newsletter inbound notifications (flag = 2)
    private function getInboundNotificationCount()
    {
        return Message::where('chat_type', 'inbound')
        ->where('through_sms_provider_flag', 2)
        ->count();
    }

    private function getInboundNotificationMessages()
    {
        return Message::select([
            'messages.content',
            'newsletters.phone as c_phone',
            'messages.created_at as in_time',
            'messages.newsletter_id',
            DB::raw("CONCAT(newsletters.first_name, ' ', newsletters.last_name) AS full_name"),
        ])
        ->join('newsletters', 'messages.newsletter_id', '=', 'newsletters.id')
        ->where('messages.chat_type', 'inbound')
        ->where('messages.through_sms_provider_flag', 2)
        ->orderBy('messages.created_at', 'DESC')
        ->limit($this->limit)
        ->get();
    }

    // Permission validation (same logic)
    private function canAccessNotification($user)
    {
        return $user->can('agent-create') || $user->id == 26;
    }

    // Pipeline URL
    private function getPipelineUrl()
    {
        return optional(Setting::select('pipeline_url')->first())->pipeline_url;
    }

    // Empty placeholder for unauthenticated users
    private function emptyResponse()
    {
        return [
            'msg_count'                     => 0,
            'call_initiated_count'          => 0,
            'call_initiated_coll'           => [],
            'inbound_count'                => 0,
            'inbound_messages'              => [],
            'inbound_notification_count'    => 0,
            'inbound_notification_messages' => [],
            'can_access_notification'       => false,
            'pipeline_url'                  => null,
        ];
    }
}
