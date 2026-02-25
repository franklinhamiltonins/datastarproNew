<?php

namespace App\Jobs;

use App\Mail\DialcreationConfirmation;
use App\Model\Dialing;
use App\Model\User;
use App\Traits\CommonFunctionsTrait;
use App\Traits\DialRelatedTrait;
use App\Traits\SMTPRelatedTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class DialCreateJob implements ShouldQueue
{
    use CommonFunctionsTrait, DialRelatedTrait, SMTPRelatedTrait;
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 1800;

    public $agentListName;
    public $agentId;
    public $locationLeadsIdSearch;
    public $locationLeadsId;
    public $searchFields;
    public $campaignId;
    public $redirectProjectUrl;
    public $mailAgentId;

    public function __construct(
        $agentListName,
        $agentId,
        $locationLeadsIdSearch,
        $locationLeadsId,
        $searchFields,
        $campaignId,
        $redirectProjectUrl,
        $mailAgentId
    ) {
        $this->agentListName = $agentListName;
        $this->agentId = $agentId;
        $this->locationLeadsIdSearch = $locationLeadsIdSearch;
        $this->locationLeadsId = $locationLeadsId;
        $this->searchFields = $searchFields;
        $this->campaignId = $campaignId;
        $this->redirectProjectUrl = $redirectProjectUrl;
        $this->mailAgentId = $mailAgentId;
    }

    public function handle()
    {
        try {
            DB::beginTransaction();

            if ($this->dialingExists()) {
                return;
            }

            $dialing = $this->createDialing();
            $agentArr = $this->attachAgents($dialing);
            $this->assignLeadsToDialing($dialing, $agentArr);
            $this->sendConfirmationMail($dialing);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            // optional: Log::error($e->getMessage());
        } finally {
            DB::disconnect();
        }
    }

    private function dialingExists(): bool
    {
        return Dialing::where('name', $this->agentListName)->exists();
    }

    private function createDialing(): Dialing
    {
        $dialing = new Dialing();
        $dialing->name = $this->agentListName;
        $dialing->lead_number = 0;
        $dialing->save();

        return $dialing;
    }

    private function attachAgents(Dialing $dialing): array
    {
        $agentArr = [];

        foreach ($this->agentId as $index => $agentId) {
            $agentArr[$index] = $agentId;

            $user = User::find($agentId);
            if ($user) {
                $user->dialings()->attach($dialing->id);
            }
        }

        return $agentArr;
    }

    private function assignLeadsToDialing(Dialing $dialing, array $agentArr): void
    {
        $leadsQuery = $this->leadOutputGetFordialing(
            $this->locationLeadsIdSearch,
            $this->locationLeadsId,
            $this->searchFields,
            $this->campaignId
        );

        $leadsQuery->groupBy('leads.id')
        ->chunk(1000, function ($leads) use ($dialing, $agentArr) {
            $insertArray = [];
            $key = 0;

            foreach ($leads as $lead) {
                $agentValue = $agentArr[$key % count($agentArr)];

                $insertArray[] = [
                    'dialing_id' => $dialing->id,
                    'lead_id' => $lead->id,
                    'owned_by_agent_id' => 0,
                    'status' => 'free',
                    'assigned_to_agent_id' => $agentValue,
                ];

                $key++;
            }

            DB::table('dialings_leads')->insert($insertArray);
        });
    }

    private function sendConfirmationMail(Dialing $dialing): void
    {
        $recipientData = $this->getRecipientData('notify_email');
        if (! $recipientData) {
            return;
        }
        [$recipientEmail, $ccEmails] = $recipientData;

        $data = [
            'message' => sprintf(
                'Your dialing creation (%s) has been confirmed. Please check this URL: %s',
                $dialing->name,
                $this->redirectProjectUrl
            ),
        ];

        $this->setDynamicSMTPUserWise($this->mailAgentId);

        $mail = Mail::to($recipientEmail);
        if (count($ccEmails) > 0) {
            $mail->cc($ccEmails);
        }

        $mail->send(new DialcreationConfirmation($data));
    }
}
