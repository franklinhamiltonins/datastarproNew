<?php

namespace App\Http\Controllers;

use App\Model\FhinsureLog;
use App\Model\LeadsModel\Contact;
use App\Model\Message;
use App\Model\Setting;
use App\Model\Smsnotification;
use App\Traits\CommonFunctionsTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Vonage\SMS\Message\SMS;

class ChatController extends Controller
{
    use CommonFunctionsTrait;

    public function index($contactId, $newsletter_type = '')
    {
        $userId = auth()->id();
        $isAdmin = auth()->user()->can('agent-create');

        $messages = Message::query()
            ->leftJoin('users', 'messages.user_id', '=', 'users.id')
            ->select(
                'users.name',
                'messages.chat_type',
                'messages.chat_sms_sent_status',
                'messages.content',
                'messages.created_at'
            );

        // Restrict messages for non-admins
        if (! $isAdmin) {
            $messages->where(function ($query) use ($userId) {
                $query->where('messages.user_id', $userId)
                    ->orWhere('messages.user_id', 0)
                    ->orWhereNull('messages.user_id');
            });
        }

        // Filter based on newsletter or contact
        if (! empty($newsletter_type) && strtolower($newsletter_type) === 'yes') {
            $messages->where('messages.newsletter_id', $contactId);
        } else {
            $messages->where('messages.contact_id', $contactId);
        }

        // Fetch results
        $messages = $messages->orderBy('messages.created_at')->get();

        // Response
        return response()->json([
            'status' => 200,
            'response' => $messages,
            'contact_id' => $contactId,
            'is_admin' => $isAdmin,
            'logged_in_user_id' => $userId,
            'unread_count' => 0,
        ]);
    }

    public function store(Request $request)
    {
        $chatContactId = $request->chatContactId;
        $isNewsletter = strtolower($request->input('isNewsletter', ''));
        $isAdmin = auth()->user()->can('agent-create');
        $loggedUser = auth()->user()->name;
        $userId = auth()->id();

        $error = null;   // collect errors
        $success = null; // collect success data

        // STOP chat validation
        if ($isNewsletter !== 'yes') {
            $contact = Contact::select('has_initiated_stop_chat')->find($chatContactId);

            if ($contact && $contact->has_initiated_stop_chat == 1) {
                $error = 'Can not send the msg, chat has ended.';
            }
        }

        // Enforce 5-hour resend rule
        if (! $error) {
            $proceedMinutes = Setting::find(1)->proceed_time_in_minute;

            $execData = json_decode(
                $this->checkMaxExecutionTime($chatContactId, $isNewsletter)->getContent(),
                true
            );

            $leftMinutes = intval($execData['response'] ?? 0);

            if ($leftMinutes > 0) {
                $error = "You can't send the message before 5 hours.";
            }
        }

        // Add STOP message on first outbound
        $chatContent = $request->content;

        if (! $error) {
            $query = Message::where('chat_type', 'outbound');

            $isNewsletter !== 'yes'
                ? $query->where('contact_id', $chatContactId)
                : $query->where('newsletter_id', $chatContactId);

            if (! $query->exists()) {
                $chatContent .= '</br> Please text "STOP" to stop the conversation.';
            }
        }

        // Save message
        if (! $error) {
            try {
                $message = new Message;
                $message->user_id = $userId;

                if ($isNewsletter !== 'yes') {
                    $message->contact_id = $chatContactId;
                    $message->through_sms_provider_flag = $isNewsletter ? 1 : 0;
                } else {
                    $message->newsletter_id = $chatContactId;
                    $message->through_sms_provider_flag = $isNewsletter ? 2 : 0;
                }

                $message->max_time_to_send = now()->addMinutes($proceedMinutes);
                $message->content = $chatContent;
                $message->chat_type = 'outbound';
                $message->save();

                $success = [
                    'status' => 200,
                    'response' => $message,
                    'last_insert_id' => $message->id,
                    'contact_id' => $chatContactId,
                    'unread_count' => 0,
                    'is_admin' => $isAdmin,
                    'logged_in_user_name' => $loggedUser,
                ];

            } catch (\Exception $e) {
                $error = 'Failed to send the message. Please contact the administrator.';
            }
        }

        if ($error) {
            /** RETURN — Final Error */
            return response()->json([
                'success' => false,
                'response' => $error,
                'is_admin' => $isAdmin,
                'logged_in_user_name' => $loggedUser,
            ], 500);
        }

        /** RETURN #3 — Final Success */
        return response()->json($success);
    }

    public function receivechat(Request $request)
    {
        Log::info('Incoming Chat Request', $request->all());

        $responseData = $request;
        $rawMsg = $responseData->text ?? '';
        $from = substr($responseData->msisdn ?? '', -10);
        $lowerMsg = strtolower($rawMsg);

        // Detect STOP / START inside first 5 words
        $words = preg_split('/\s+/', $lowerMsg);
        $stop_found = collect(array_slice($words, 0, 5))
            ->contains(fn ($word) => preg_replace('/[^\w]/', '', $word) === 'stop');

        $isStartCommand = ($lowerMsg === 'start');

        // MMS / JSON payload handling
        $jsonData = $request->json()->all();

        $img_url = data_get($jsonData, 'message.content.image.url');
        $toType = data_get($jsonData, 'to.type');
        $from = data_get($jsonData, 'from.number', $from);

        $msgContent = ($toType === 'mms') ? $img_url : $rawMsg;

        // Identify Contact or Newsletter
        $newsletter = FhinsureLog::select('id', 'first_name', 'last_name')->where('phone', $from)->first();
        $contact = Contact::select('id', 'c_full_name', 'lead_id')->where('c_phone', $from)->first();

        $newsletter_id = $newsletter->id ?? null;
        $contact_id = $contact->id ?? null;
        $c_full_name = $contact->c_full_name ?? ($newsletter ? $newsletter->first_name.' '.$newsletter->last_name : null);
        $lead_id = $contact->lead_id ?? null;

        // If contact exists, unarchive SMS
        if ($contact) {
            $contact->update(['archive_sms' => null]);
        }

        // Fetch last outbound user (find who sent last message)
        $user_detail = Message::where('chat_type', 'outbound')
            ->where(function ($query) use ($newsletter_id, $contact_id) {
                if ($contact_id) {
                    $query->where('contact_id', $contact_id);
                }
                if ($newsletter_id) {
                    $query->orWhere('newsletter_id', $newsletter_id);
                }
            })
            ->orderByDesc('id')
            ->first();

        $user_id = $user_detail->user_id ?? null;
        $through_sms_provider_flag = $user_detail->through_sms_provider_flag ?? null;

        // Adjust contact/newsletter based on provider flag
        if ($through_sms_provider_flag == 2) {
            // Use newsletter
            $contact_id = null;
            if ($newsletter) {
                $newsletter_id = $newsletter->id;
                $c_full_name = $newsletter->first_name.' '.$newsletter->last_name;
            }
        } else {
            // Use contact
            $newsletter_id = null;
            if ($contact) {
                $contact_id = $contact->id;
                $c_full_name = $contact->c_full_name;
                $lead_id = $contact->lead_id;
            }
        }

        // Prepare STOP / START message override
        $smsStatus = 0;

        if ($stop_found) {
            $msgContent = "{$c_full_name} has send STOP and doesn't want to receive anymore messages";
            $smsStatus = 5;
        } elseif ($isStartCommand) {
            $msgContent = "{$c_full_name} has send START and wants to receive messages again.";
            $smsStatus = 5;
        }

        // Save Inbound Message
        $message = Message::create([
            'user_id' => $user_id,
            'through_sms_provider_flag' => $through_sms_provider_flag,
            'newsletter_id' => $newsletter_id,
            'contact_id' => $contact_id,
            'content' => $msgContent,
            'vonageResponse' => '',
            'chat_type' => 'inbound',
            'chat_sms_sent_status' => $smsStatus,
        ]);

        // STOP / START DB Update
        if ($stop_found) {
            $updated = Contact::where('id', $contact_id)
                ->where('has_initiated_stop_chat', 0)
                ->update(['has_initiated_stop_chat' => 1]);

            Log::info($updated
                ? "STOP activated for contact {$contact_id}"
                : "STOP update failed for contact {$contact_id}");
        }

        if ($isStartCommand) {
            $updated = Contact::where('id', $contact_id)
                ->where('has_initiated_stop_chat', 1)
                ->update(['has_initiated_stop_chat' => 0]);

            Log::info($updated
                ? "START activated for contact {$contact_id}"
                : "START update failed for contact {$contact_id}");
        }
        // Trigger internal handler
        $this->receiveSms($rawMsg, $user_id, $contact_id, $c_full_name, $lead_id, $msgContent);
    }

    public function receiveSms($message, $user_id, $contact_id, $c_full_name, $lead_id, $manipulated_message_content)
    {
        try {
            // storing the detail in notification table
            $smsnotification = new Smsnotification;
            $smsnotification->user_id = $user_id;
            $smsnotification->contact_id = $contact_id;
            $smsnotification->smscontent = $message;
            $smsnotification->status = 0;
            $smsnotification->save();
        } catch (\Exception $e) {
        }
    }

    public function getAllUnreadMsg()
    {
        $smsnotifications = Smsnotification::join('contacts', 'smsnotifications.contact_id', '=', 'contacts.id')
            ->select('smsnotifications.*', 'contacts.c_full_name', 'contacts.lead_id')
            ->where('smsnotifications.status', 0)
            ->where('smsnotifications.user_id', auth()->user()->id)
            ->limit(5)
            ->orderBy('smsnotifications.id', 'DESC')
            ->get();

        return json_encode([
            'status' => '200',
            'response' => $smsnotifications,
        ]);
    }
}
