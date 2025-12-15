<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Validator;
use App\Model\User;
use DB;
use Hash;

use App\Traits\SMTPRelatedTrait;
use App\Traits\LoginFunctionTrait;

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

    use AuthenticatesUsers,SMTPRelatedTrait,LoginFunctionTrait;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    public $totalAttempt = 5;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    // public function __construct()
    // {
    //     $this->middleware('guest')->except('logout');
    // }

    public function login(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        // Return an error if the user does not exist
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid Email',
                'userId' => 0,
                'showOtpBox' => false,
            ], 200);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid Password',
                'userId' => 0,
                'showOtpBox' => false,
            ], 200);
        }

        if($this->loginNotification($user)){
            return response()->json([
                'status' => true,
                'message' => '',
                'userId' => $user->id,
                'showOtpBox' => true,
            ], 200);
        }
        else{
            return response()->json([
                'status' => false,
                'message' => 'Something Went Wrong',
                'userId' => 0,
                'showOtpBox' => false,
            ], 200);
        } 
    }

    public function verifyOtp(Request $request)
    {
        $latestOtp = $this->getUserLatetOtp($request->user_id);

        if($latestOtp == $request->otp){
            $user = User::where('id', $request->user_id)->first();
            $this->verifyMarkUserOtp($request->user_id,$latestOtp);
            Auth::login($user);
            return response()->json([
                'status' => true,
                'redirectTo' => route('dashboard'),
                'message' => '',
                'totalAttempt' => $this->totalAttempt,
                'triedAttempt' => 0,
            ], 200);
        }
        else{
            $this->invalidUserAttempt($request->user_id,$latestOtp);
            return response()->json([
                'status' => false,
                'redirectTo' => 'DONE 2',
                "message" => "Incorrect 2FA Code",
                'totalAttempt' => $this->totalAttempt,
                'triedAttempt' => $this->getUserTriedAttempt($request->user_id,$latestOtp),
            ], 200);
        }
        
    }

    public function resendOtp(Request $request)
    {
        $latestOtp = $this->getUserLatetOtp($request->user_id);

        $recipientEmail = User::where('id', $request->user_id)->value('email') ?? '';
        $recipientName = User::where('id', $request->user_id)->value('name') ?? '';

        $this->send2FAMail($latestOtp,$recipientEmail,$recipientName);

        return response()->json([
                'status' => true,
                'message' => 'DONE 3',
                'userId' => 0,
                'showOtpBox' => true,
            ], 200);
    }
}
