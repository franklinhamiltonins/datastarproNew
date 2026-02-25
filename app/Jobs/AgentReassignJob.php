<?php

namespace App\Jobs;

use App\Model\Dialing;
use App\Traits\CommonFunctionsTrait;
use App\Traits\DialRelatedTrait;
use App\Traits\MailingRelatedTrait;
use App\Traits\SMTPRelatedTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class AgentReassignJob implements ShouldQueue
{
    use CommonFunctionsTrait, DialRelatedTrait, Dispatchable, InteractsWithQueue,
        MailingRelatedTrait, Queueable, SerializesModels, SMTPRelatedTrait;

    public $dialingListIds;
    public $selectedAgentId;
    public $redirectProjectUrl;
    public $mailAgentId;

    public function __construct($dialingListIds, $selectedAgentId, $redirectProjectUrl, $mailAgentId)
    {
        $this->dialingListIds = $dialingListIds;
        $this->selectedAgentId = $selectedAgentId;
        $this->redirectProjectUrl = $redirectProjectUrl;
        $this->mailAgentId = $mailAgentId;
    }

    public function handle()
    {
        foreach ($this->dialingListIds as $dialingId) {
            $this->processDialingReassignment($dialingId);
        }

        DB::disconnect(); // Close DB connection after processing
    }

    // Process reassignment for a single dialing
    private function processDialingReassignment($dialingId)
    {
        $this->reassignAgents($dialingId);   // Reassign leads to agents
        $this->syncDialingUsers($dialingId); // Update dialing_user table
        $this->notifyReassignedAgent($dialingId); // Send notification mail
    }

    // Reassign leads in batches to selected agents
    private function reassignAgents($dialingId)
    {
        $agents = $this->selectedAgentId;
        $agentCount = count($agents);

        DB::table('dialings_leads')
            ->where('dialing_id', $dialingId)
            ->where('status', 'free')
            ->orderBy('lead_id')
            ->chunk(2000, function ($freeLeads) use ($agents, $agentCount, $dialingId) {
                $assignments = [];

                foreach ($freeLeads as $index => $lead) {
                    $agentId = $agents[$index % $agentCount];
                    $assignments[$agentId][] = $lead->lead_id;
                }

                foreach ($assignments as $agentId => $leadIds) {
                    DB::table('dialings_leads')
                        ->where('dialing_id', $dialingId)
                        ->whereIn('lead_id', $leadIds)
                        ->update([
                            'owned_by_agent_id' => 0,
                            'assigned_to_agent_id' => $agentId,
                        ]);
                }
            });
    }

    // Sync agents in dialing_user table
    private function syncDialingUsers($dialingId)
    {
        foreach ($this->selectedAgentId as $agentId) {
            DB::table('dialing_user')->updateOrInsert(
                ['user_id' => $agentId, 'dialing_id' => $dialingId],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }

        // Remove any agents not assigned
        DB::table('dialing_user')
            ->where('dialing_id', $dialingId)
            ->whereNotIn('user_id', $this->selectedAgentId)
            ->delete();
    }

    // Send email notification for reassignment
    private function notifyReassignedAgent($dialingId)
    {
        $dialing = Dialing::find($dialingId);
        if (!$dialing){
            return;
        }

        $subject = 'Dialing Agent Reassignment';
        $mailBody = "Your dialing ('{$dialing->name}') agent reassignment has been confirmed. "
            ."Please check this URL: {$this->redirectProjectUrl}";

        $this->sendSimpleNotificationMail($this->mailAgentId, $subject, $mailBody);
    }
}
