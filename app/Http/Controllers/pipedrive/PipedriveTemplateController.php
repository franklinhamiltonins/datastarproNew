<?php

namespace App\Http\Controllers\pipedrive;

use App\Mail\ContactMail;
use App\Model\LeadsModel\Contact;
use App\Model\Message;
use App\Model\Setting;
use App\Model\SmtpConfiguration;
use App\Model\Template;
use App\Model\User;
use App\Model\UserTemplate;
use App\Traits\CommonFunctionsTrait;
use App\Traits\PipeDriveTrait;
use App\Traits\SMTPRelatedTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Validator;

class PipedriveTemplateController extends PipedriveLoginController
{
    use CommonFunctionsTrait,PipeDriveTrait,SMTPRelatedTrait;

    public function contactChat($contactId)
    {
        $user = auth()->user();
        $agentWisePermission = $this->getAgentWisePermission($user);

        $isAdminUser = $agentWisePermission['isAdminUser'];
        $agentId = $isAdminUser ? 0 : $user->id;

        $messages = Message::select('users.name', 'messages.chat_type', 'messages.chat_sms_sent_status', 'messages.content', 'messages.created_at', 'messages.id');

        if ($agentId) {
            $messages->where(function ($query) use ($agentId) {
                $query->where('messages.user_id', $agentId)
                    ->orWhere('messages.user_id', 0)
                    ->orWhereNull('messages.user_id');
            });
        }
        $messages->where('messages.contact_id', $contactId);

        $messages->leftjoin('users', 'messages.user_id', '=', 'users.id');

        // Execute the query and get the collection
        $messages = $messages->orderBy('messages.created_at')->get();

        // Return JSON response with the collection (no conversion to array)
        return response()->json([
            'status' => '200',
            'response' => $messages, // Return the collection directly
            'contact_id' => $contactId,
            'is_admin' => $isAdminUser,
            'unread_count' => 0,
        ], 200);
    }

    public function sendChat(Request $request)
    {
        $chatContactId = $request->input('chatContactId');
        $chatAgentId = $request->input('chatAgentId');
        $chatContent = $request->input('content');

        $user = User::findOrFail($chatAgentId);
        $agentWisePermission = $this->getAgentWisePermission($user);
        $isAdminUser = $agentWisePermission['isAdminUser'];

        // Check if chat is stopped
        $contact = Contact::find($chatContactId);
        if ($contact && $contact->has_initiated_stop_chat) {
            return response()->json([
                'success' => false,
                'response' => 'Cannot send message, chat has ended.',
                'is_admin' => $isAdminUser,
                'logged_in_user_name' => $user->name,
            ], 403);
        }

        // Check max execution time
        $checkMaxTimeResponse = $this->checkMaxExecutionTime($chatContactId);
        $responseData = json_decode($checkMaxTimeResponse->getContent(), true);

        if (! empty($responseData['response']) && $responseData['response'] > 0) {
            return response()->json([
                'success' => false,
                'left_minute' => $responseData['response'],
                'response' => "You can't send the message before {$responseData['response']} mins.",
                'is_admin' => $isAdminUser,
                'logged_in_user_name' => $user->name,
            ], 429);
        }

        // Append STOP message if first outbound message
        $firstOutbound = Message::where('chat_type', 'outbound')
            ->where('contact_id', $chatContactId)
            ->first();

        if (is_null($firstOutbound)) {
            $chatContent .= '</br> Please text "STOP" to stop the conversation.';
        }

        // Save the message
        try {
            $settings = Setting::find(1);
            $proceedTime = $settings->proceed_time_in_minute ?? 0;

            $message = new Message;
            $message->user_id = auth()->id();
            $message->contact_id = $chatContactId;
            $message->through_sms_provider_flag = 1;
            $message->max_time_to_send = Carbon::now()->addMinutes($proceedTime);
            $message->content = $chatContent;
            $message->chat_type = 'outbound';
            $message->save();

            $lastEntry = [
                'name' => $user->name,
                'chat_type' => $message->chat_type,
                'id' => $message->id,
                'chat_sms_sent_status' => $message->chat_sms_sent_status ?? null,
                'content' => $message->content,
                'created_at' => $message->created_at,
            ];

            return response()->json([
                'success' => true,
                'response' => $message,
                'last_insert_id' => $message->id,
                'contact_id' => $chatContactId,
                'unread_count' => 0,
                'is_admin' => $isAdminUser,
                'logged_in_user_name' => $user->name,
                'last_entry' => $lastEntry,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'response' => 'Failed to send the message. Please contact the administrator.',
                'is_admin' => $isAdminUser,
                'logged_in_user_name' => $user->name,
                'error' => $e->getMessage(), // Optional for debugging
            ], 500);
        }
    }

    public function sendMail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'chatContactId' => 'required|integer|exists:contacts,id',
            'chatAgentId' => 'required|integer|exists:users,id',
            'content' => 'required|string',
            'subject' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'response' => $validator->errors()->first(), // return first validation error
                'is_admin' => false, // you may not know is_admin yet at this point
            ], 422);
        }

        $chatContactId = $request->input('chatContactId');
        $chatAgentId = $request->input('chatAgentId');
        $mailContent = $request->input('content');
        $mailSubject = $request->input('subject');

        $user = User::findOrFail($chatAgentId);
        $agentWisePermission = $this->getAgentWisePermission($user);
        $isAdminUser = $agentWisePermission['isAdminUser'];

        $contact = Contact::find($chatContactId);

        if (! $contact || empty($contact->c_email)) {
            return response()->json([
                'success' => false,
                'response' => 'Invalid contact or email not found.',
                'is_admin' => $isAdminUser,
            ], 422);
        }

        try {
            // Check SMTP configuration
            if (! $this->checkMailConfigurationUserWise($chatAgentId)) {
                return response()->json([
                    'success' => false,
                    'response' => 'You do not have SMTP configuration. Please set it up before attempting to send emails.',
                    'is_admin' => $isAdminUser,
                ], 400);
            }

            // Apply SMTP settings
            $this->setDynamicSMTPUserWise($chatAgentId);

            $data['subject'] = $mailSubject;
            $data['content'] = $mailContent;
            $smtpData = SmtpConfiguration::where('user_id', $chatAgentId)->first();
            if ($smtpData) {
                $data['signature_image'] = $smtpData->signature_image;
                $data['signature_text'] = $smtpData->signature_text;
            } else {
                $data['signature_image'] = '';
                $data['signature_text'] = '';
            }
            $data['module_name'] = 'contact';
            $data['contact_id'] = $chatContactId;

            Mail::to($contact->c_email)->send(new ContactMail($data));

            $this->saveEmailData($data);

            return response()->json([
                'success' => true,
                'response' => 'Email sent successfully.',
                'is_admin' => $isAdminUser,
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'response' => 'Failed to send the message. Please contact the administrator.',
                'is_admin' => $isAdminUser,
                'error' => config('app.debug') ? $e->getMessage() : null, // only show error in debug
            ], 500);
        }
    }

    public function getTemplateData(Request $request)
    {
        $chatType = $request->input('chatType') ?? 'sms';
        $chatAgentId = $request->input('chatAgentId');

        $isAdmin = auth()->user()->can('agent-create');

        if ($isAdmin) {
            $templateData = Template::where('template_type', $chatType)->get();
        } else {
            $templateData = Template::where('template_type', $chatType)
                ->where('set_for_all', 'yes')
                ->orWhereHas('user', function ($query) use ($chatAgentId, $chatType) {
                    $query->where('template_type', $chatType)
                        ->Where('user_id', $chatAgentId);
                })
                ->get();

        }

        foreach ($templateData as $template) {
            $template->delete_permission = false;
            $isAdmin = auth()->user()->can('agent-create');
            if ($isAdmin || ($template->set_for_all == 'no' && $template->created_by == $chatAgentId)) {
                $template->delete_permission = true;
            }
        }

        return json_encode([
            'success' => true,
            'response' => $templateData,
        ], 200);
    }

    public function saveTemplate(Request $request)
    {
        $rules = [
            'template_name' => 'required|max:100',
            'template_content' => 'required'.($request->template_type !== 'mail' ? '|max:200' : ''),
        ];

        if ($request->template_type === 'mail') {
            $rules['template_subject'] = 'required|max:200';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()->all(),
            ], 400);
        }

        try {
            $validated = $validator->validated();
            $chatAgentId = $request->input('chatAgentId');
            $data = $request->input();

            $templateType = $request->input('template_type') === 'mail' ? 'mail' : 'sms';
            $templateNameSlug = $this->createTemplateSlug($data);

            // Prevent duplicate template
            if (Template::where('template_name_slug', $templateNameSlug)->exists()) {
                return response()->json([
                    'success' => false,
                    'response' => 'Template already exists.',
                ]);
            }

            $templateData = Template::create([
                'template_name' => $validated['template_name'],
                'template_name_slug' => $templateNameSlug,
                'template_content' => $validated['template_content'],
                'template_subject' => $templateType === 'mail' ? $validated['template_subject'] : null,
                'template_type' => $templateType,
                'created_by' => $chatAgentId,
            ]);

            UserTemplate::create([
                'user_id' => $chatAgentId,
                'template_id' => $templateData->id,
            ]);

            return response()->json([
                'success' => true,
                'response' => $templateData,
                'message' => 'New Template created successfully',
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'response' => 'Failed to create the new template. Please contact the administrator.',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    public function deleteTemplate(Request $request)
    {
        $templateId = $request->templateId;
        try {
            $templateDateted = Template::where([
                'id' => $templateId,
            ])->delete();
            if ($templateDateted) {

                UserTemplate::where([
                    'template_id' => $templateId,
                ])->delete();

                return json_encode([
                    'success' => true,
                    'message' => 'Template Deleted Successfully',
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'response' => 'Failed to delete the template. Please contact the administrator.',
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'response' => 'Failed to delete the template. Please contact the administrator.',
            ], 500);
        }
    }
}
