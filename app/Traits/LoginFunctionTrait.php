<?php

namespace App\Traits;

use App\Mail\Login2FAMailSending;
use App\Model\LoginOtpTracker;
use Illuminate\Support\Facades\Mail;

trait LoginFunctionTrait
{
    public function loginNotification($user)
    {
        $otp = $this->generateOtp();

        $this->makeOtpLog($otp, $user);

        $email = $user->email;
        $name = $user->name;
        // $email = "rohit.kumar@codeclouds.com";

        $this->send2FAMail($otp, $email, $name);

        return true;
    }

    public function generateOtp()
    {
        return random_int(100000, 999999);
    }

    public function makeOtpLog($otp, $user)
    {
        $log = new LoginOtpTracker;
        $log->otp = $otp;
        $log->user_id = $user->id;
        $log->save();
    }

    public function send2FAMail($otp, $recipientEmail, $name)
    {
        $mailAgentId = config('custom.mail_sent_user_id');

        $this->setDynamicSMTPUserWise($mailAgentId);

        $mail = Mail::to($recipientEmail);

        $data = [
            'otp' => $otp,
            'name' => $name,
        ];

        $mail->send(new Login2FAMailSending($data));
    }

    public function getUserLatetOtp($userId)
    {
        return LoginOtpTracker::where('user_id', $userId)
            ->where('status', 0)
            ->latest()
            ->value('otp') ?? '';
    }

    public function verifyMarkUserOtp($userId, $latestOtp)
    {
        LoginOtpTracker::where('user_id', $userId)
            ->where('status', 0)
            ->where('otp', $latestOtp)
            ->update(['status' => 1]);
    }

    public function invalidUserAttempt($userId, $latestOtp)
    {
        $log = LoginOtpTracker::where('user_id', $userId)
            ->where('status', 0)
            ->where('otp', $latestOtp)->first();
        if ($log) {
            $log->attempt += 1;
            $log->save();
        }
    }

    public function getUserTriedAttempt($userId, $latestOtp)
    {
        return LoginOtpTracker::where('user_id', $userId)
            ->where('status', 0)
            ->where('otp', $latestOtp)
            ->latest()
            ->value('attempt') ?? '';
    }
}
