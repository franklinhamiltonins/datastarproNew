<?php

namespace App\Services;

use App\Mail\AssignMentNotification;
use App\Mail\BindmgmtAsanaNotification;
use App\Mail\StatusChangeNotification;
use App\Traits\CommonFunctionsTrait;
use App\Traits\SMTPRelatedTrait;
use Illuminate\Support\Facades\Mail;

class ShootMailViaSystem
{
    use CommonFunctionsTrait,SMTPRelatedTrait;

    public function shootMail($subject, $bodyMsg, $to, $cc, $data)
    {
        $mailAgentId = config('custom.mail_sent_user_id');
        $this->setDynamicSMTPUserWise($mailAgentId);

        if (! empty($data['type'])) {
            $mail = Mail::to($to);

            if (count($cc) > 0) {
                $mail->cc($cc);
            }
            if ($data['type'] == 1) {
                $mail->send(new StatusChangeNotification($data, $subject));
            } elseif ($data['type'] == 2) {
                $mail->send(new BindmgmtAsanaNotification($data, $subject));
            } elseif ($data['type'] == 3) {
                $mail->send(new AssignMentNotification($data, $subject));
            }
        } else {
            $this->sendEmail($to, $cc,$subject, $bodyMsg);
        }
    }
}
