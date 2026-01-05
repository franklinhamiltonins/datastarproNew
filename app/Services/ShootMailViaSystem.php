<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;

use App\Traits\CommonFunctionsTrait;
use App\Traits\SMTPRelatedTrait;
use App\Model\Setting;
use DB;
use App\Model\LeadsModel\Lead;
use App\Model\AsanaQuestion;


use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

use App\Mail\BindmgmtAsanaNotification;
use App\Mail\StatusChangeNotification;
use App\Mail\AssignMentNotification;

class ShootMailViaSystem
{
    use CommonFunctionsTrait,SMTPRelatedTrait;

    public function shootMail($subject,$bodyMsg,$to,$cc,$data)
    {
        $mail_agent_id = env('MY_MAIL_SENT_USER_ID');
        $this->setDynamicSMTPUserWise($mail_agent_id);

        if(!empty($data['type'])){
            $mail = Mail::to($to);

            if (count($cc) > 0) {
                $mail->cc($cc);
            }
            if($data['type'] == 1){
                $mail->send(new StatusChangeNotification($data,$subject));
            }
            else if($data['type'] == 2){
                $mail->send(new BindmgmtAsanaNotification($data,$subject));
            }
            else if($data['type'] == 3){
                $mail->send(new AssignMentNotification($data,$subject));
            }
        }
        else{
            Mail::send([], [], function ($message) use ($subject,$bodyMsg,$to,$cc) {
                $message->to($to);

                if (count($cc) > 0) {
                    $message->cc($cc);
                }
                $message->subject($subject)
                ->html($bodyMsg);
            });
        }
    }
}