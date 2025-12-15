<?php


namespace App\Traits;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Config;
use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Builder;
use Vonage\Client;
use Illuminate\Support\Facades\Log;
use App\Model\LeadsModel\Lead;
use App\Model\LeadsModel\Contact;
use App\Model\LoginOtpTracker;
use Illuminate\Support\Facades\Auth;
use Validator;
use DB;
use Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

use App\Mail\Login2FAMailSending;

trait LoginFunctionTrait
{
	public function loginNotification($user)
    {
        $otp = $this->generateOtp();

        $this->makeOtpLog($otp,$user);

        $email = $user->email;
        $name = $user->name;
        // $email = "rohit.kumar@codeclouds.com";

        $this->send2FAMail($otp,$email,$name);

        return true;
    }

    public function generateOtp()
    {
        $otp = random_int(100000, 999999);

        return $otp;
    }

    public function makeOtpLog($otp,$user)
    {
        $log = new LoginOtpTracker();
        $log->otp = $otp;
        $log->user_id = $user->id;
        $log->save();
    }

    public function send2FAMail($otp,$recipientEmail,$name)
    {
        $mail_agent_id = env('MY_MAIL_SENT_USER_ID');

        $this->setDynamicSMTPUserWise($mail_agent_id);

        $mail = Mail::to($recipientEmail);

        $data = [
            "otp" => $otp,
            "name" => $name,
        ];


        $mail->send(new Login2FAMailSending($data));
                
        // Mail::send([], [], function ($message) use ($recipientEmail,$otp) {
        //     $message->to($recipientEmail);

        //     $message->subject('Login 2FA Code')
        //     ->setBody($otp.' is Your Otp to login into the dashboard', 'text/html');
        // });
    }

    public function getUserLatetOtp($userId)
    {
        return LoginOtpTracker::where('user_id', $userId)
            ->where("status",0)
            ->latest()
            ->value('otp') ?? '';
    }

    public function verifyMarkUserOtp($userId,$latestOtp)
    {
        LoginOtpTracker::where('user_id', $userId)
        ->where("status",0)
        ->where("otp",$latestOtp)
        ->update(["status"=>1]);
    }

    public function invalidUserAttempt($userId,$latestOtp)
    {
        $log = LoginOtpTracker::where('user_id', $userId)
        ->where("status",0)
        ->where("otp",$latestOtp)->first();
        if($log){
            $log->attempt += 1;
            $log->save();
        }
    }

    public function getUserTriedAttempt($userId,$latestOtp)
    {
        return LoginOtpTracker::where('user_id', $userId)
            ->where("status",0)
            ->where("otp",$latestOtp)
            ->latest()
            ->value('attempt') ?? '';
    }

}