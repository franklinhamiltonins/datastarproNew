<?php

namespace App\Http\Controllers\pipedrive;

use App\Http\Controllers\Leads\ContactController;
use App\Model\User;
use App\Traits\CommonFunctionsTrait;
use App\Traits\LoginFunctionTrait;
use App\Traits\PipeDriveTrait;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PipedriveLoginController extends ContactController
{
    use CommonFunctionsTrait,LoginFunctionTrait,PipeDriveTrait;

    public function logout()
    {
        if (auth()->check()) {
            Auth::logout();
        }

        return response()->json([
            'status' => true,
            'message' => 'Logout Successfully',
        ], 200);
    }

    public function checkAlreadyLogin()
    {
        // Check if the user is authenticated
        if (! auth()->check()) {
            return response()->json([
                'status' => false,
                'message' => 'auth not found',
                'isLoggedIn' => false,
                'credentials' => null,
            ], 400);
        }

        // Get the authenticated user
        $user = auth()->user();
        $roles = $user->getRoleNames()->toArray();
        // return $roles;
        $credentials = $user;
        $agentWisePermission = $this->getAgentWisePermission($user);

        $isAdminUser = $agentWisePermission['isAdminUser'];

        // Initialize agent-related variables
        $agentId = $isAdminUser ? 0 : $user->id;
        $agentUsers = $this->getAgentListing($isAdminUser, $agentId, true, $roles);

        // Return a successful response
        return response()->json([
            'status' => true,
            'message' => 'auth found',
            'isLoggedIn' => true,
            'credentials' => $credentials,
            'agentId' => $agentId,
            'agentUsers' => $agentUsers,
            'agentWisePermission' => $agentWisePermission,
        ], 200);
    }

    public function request_login(Request $request)
    {
        // Attempt to find the user by email
        $user = User::where('email', $request->email)->first();

        // Return an error if the user does not exist
        if (! $user) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid Email',
                'isLoggedIn' => false,
                'credentials' => null,
            ], 200);
        }

        // Check if the provided password matches the user's stored password
        if (! Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid Password',
                'isLoggedIn' => false,
                'credentials' => null,
            ], 200);
        }

        if ($this->loginNotification($user)) {
            return response()->json([
                'status' => true,
                'message' => '',
                'userId' => $user->id,
                'showOtpBox' => true,
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Something Went Wrong',
                'userId' => 0,
                'showOtpBox' => false,
            ], 200);
        }
    }

    public function request_verify(Request $request)
    {
        $latestOtp = $this->getUserLatetOtp($request->user_id);

        if ($latestOtp == $request->otp) {
            $user = User::where('id', $request->user_id)->first();
            $this->verifyMarkUserOtp($request->user_id, $latestOtp);
            Auth::login($user);
            $credentials = auth()->user();
            $agentWisePermission = $this->getAgentWisePermission($user);

            $isAdminUser = $agentWisePermission['isAdminUser'];

            // Initialize agent-related variables
            $agentId = $isAdminUser ? 0 : $user->id;

            $agentUsers = $this->getAgentListing($isAdminUser, $agentId);

            return response()->json([
                'status' => true,
                'redirectTo' => '',
                'message' => '',
                'totalAttempt' => 5,
                'triedAttempt' => 0,
                'isLoggedIn' => true,
                'credentials' => $credentials,
                'agent_id' => $agentId,
                'agentUsers' => $agentUsers,
                'agentWisePermission' => $agentWisePermission,
            ], 200);
        } else {
            $this->invalidUserAttempt($request->user_id, $latestOtp);

            return response()->json([
                'status' => false,
                'redirectTo' => 'DONE 2',
                'message' => 'Incorrect Otp',
                'totalAttempt' => 5,
                'triedAttempt' => $this->getUserTriedAttempt($request->user_id, $latestOtp),
                'isLoggedIn' => false,
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
