<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Model\User;
use App\Providers\RouteServiceProvider;
use App\Traits\LoginFunctionTrait;
use App\Traits\SMTPRelatedTrait;
use Hash;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers,LoginFunctionTrait,SMTPRelatedTrait;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    public $totalAttempt = 5;

    public function login(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        $status = false;
        $message = "";
        $showOtpBox = false;
        $redirectTo = "";

        if (! $user) {
            $message = "Invalid Email";
        }
        elseif (! Hash::check($request->password, $user->password)) {
            $message = "Invalid Password";
        }
        elseif($user->twofactor_authentication) {
            if ($this->loginNotification($user)) {
                $status = true;
                $showOtpBox = true;
            }
            else{
                $message = "Something Went Wrong";
            }
        }
        else{
            Auth::login($user);
            $status = true;
            $redirectTo = route('dashboard');
        }

        return response()->json([
            'status' => $status,
            'message' => $message,
            'userId' => $user->id ?? 0,
            'showOtpBox' => $showOtpBox,
            'redirectTo' => $redirectTo,
        ], 200);
    }

    public function verifyOtp(Request $request)
    {
        $latestOtp = $this->getUserLatetOtp($request->user_id);

        if ($latestOtp == $request->otp) {
            $user = User::where('id', $request->user_id)->first();
            $this->verifyMarkUserOtp($request->user_id, $latestOtp);
            Auth::login($user);

            return response()->json([
                'status' => true,
                'redirectTo' => route('dashboard'),
                'message' => '',
                'totalAttempt' => $this->totalAttempt,
                'triedAttempt' => 0,
            ], 200);
        } else {
            $this->invalidUserAttempt($request->user_id, $latestOtp);

            return response()->json([
                'status' => false,
                'redirectTo' => 'DONE 2',
                'message' => 'Incorrect 2FA Code',
                'totalAttempt' => $this->totalAttempt,
                'triedAttempt' => $this->getUserTriedAttempt($request->user_id, $latestOtp),
            ], 200);
        }

    }

    public function resendOtp(Request $request)
    {
        $latestOtp = $this->getUserLatetOtp($request->user_id);

        $recipientEmail = User::where('id', $request->user_id)->value('email') ?? '';
        $recipientName = User::where('id', $request->user_id)->value('name') ?? '';

        $this->send2FAMail($latestOtp, $recipientEmail, $recipientName);

        return response()->json([
            'status' => true,
            'message' => 'DONE 3',
            'userId' => 0,
            'showOtpBox' => true,
        ], 200);
    }
}
