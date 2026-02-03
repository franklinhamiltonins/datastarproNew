<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Model\User;
use App\Model\Role;
use DB;

class ImpersonationController extends Controller
{
	// public function impersonate($id)
	// {
	// 	$user = User::find($id);

	// 	if ($user) {
	// 		// Store the original user's ID in the session
	// 		$authUser = Auth::user();

	// 		if ($authUser->hasRole('Super Admin')) {
	// 			session(['impersonate_original' => auth()->id()]);
	// 		}
	// 		else if ($authUser->hasRole('Manager')) {
	// 			session(['impersonate_manager' => auth()->id()]);
	// 		}
	// 		session(['impersonate' => $user->id]);
	// 		auth()->login($user); // Log in as the impersonated user
	// 		return redirect('/')->with('message', 'You are now impersonating ' . $user->name);
	// 	}

	// 	return redirect('/')->with('error', 'User not found');
	// }
	public function impersonate($id)
	{
	    $user = User::find($id);

	    if ($user) {
		    $currentId = auth()->id();

		    $stack = session('impersonate_stack', []);

		    $lastId = end($stack);

		    if ($lastId !== $id) {
		        $stack[] = $currentId;
		        session(['impersonate_stack' => $stack]);
		    }

		    // Actually impersonate this user
		    auth()->login($user);
		    session(['impersonate' => $id]);

		    return redirect('/')->with('message', 'You are now impersonating ' . $user->name);
		}
		return redirect('/')->with('error', 'User not found');
	}



	// public function leaveImpersonation()
	// {
	// 	$originalUserId = session('impersonate_original');
	// 	$originalManagerId = session('impersonate_manager');

	// 	if ($originalManagerId) {
	// 		$originalManager = User::find($originalManagerId);

	// 		if ($originalManager) {
	// 			auth()->login($originalManager); // Log back in as the Manager user
	// 			if($originalUserId){
	// 				session()->forget('impersonate_manager');
	// 			}
	// 			else{
	// 				session()->forget('impersonate');
	// 				session()->forget('impersonate_manager');
	// 			}
	// 			return redirect('/')->with('message', 'You have left impersonation mode');
	// 		}
	// 	}
	// 	else if ($originalUserId) {
	// 		$originalUser = User::find($originalUserId);

	// 		if ($originalUser) {
	// 			auth()->login($originalUser); // Log back in as the original user
	// 			session()->forget('impersonate');
	// 			session()->forget('impersonate_original');
	// 			return redirect('/')->with('message', 'You have left impersonation mode');
	// 		}
	// 	}

	// 	return redirect('/login')->with('error', 'Unable to leave impersonation');
	// }
	public function leaveImpersonation()
	{
	    $stack = session('impersonate_stack', []);

	    if (empty($stack)) {
	        return redirect('/')->with('error', 'Not impersonating anyone');
	    }

	    // Pop last user
	    $originalUserId = array_pop($stack);

	    // Update the stack
	    session(['impersonate_stack' => $stack]);

	    // Login back
	    $originalUser = User::find($originalUserId);

	    if ($originalUser) {
	        auth()->login($originalUser);

	        // If stack becomes empty, remove it entirely
	        if (empty($stack)) {
	            session()->forget('impersonate_stack');
	            session()->forget('impersonate');
	        }
	        else{
	        	session(['impersonate' => $originalUserId]);
	        }

	        return redirect('/')->with('message', 'You have left impersonation mode');
	    }

	    return redirect('/login')->with('error', 'Unable to restore user');
	}


	public function search(Request $request)
	{
	    $keyword = $request->get('keyword');

	    $authUser = Auth::user();

	    if (!$keyword || !$authUser) {
	        return response()->json([]);
	    }

	    if ($authUser->hasRole('Super Admin')) {
	        $users = User::with('roles')
	            ->where('name', 'like', "%{$keyword}%")
	            ->select('id', 'name', 'email')
	            ->limit(10)
	            ->get();

	    }
	    else if ($authUser->hasRole('Manager')) {

	        // Use DB query, NOT collection filtering
	        $users = $authUser->managerTeamList()
	            ->with('roles')
	            ->where('name', 'like', "%{$keyword}%")
	            ->select('users.id', 'users.name', 'users.email')
	            ->limit(10)
	            ->get();
	    }
	    else {
	        return response()->json([]);
	    }

	    $modified_user = $users->map(function ($user) {
	        return [
	            'id'    => $user->id,
	            'name'  => $user->name,
	            'email' => $user->email,
	            'role'  => $user->roles->pluck('name')->implode(', ')
	        ];
	    });

	    return response()->json($modified_user);
	}

}
