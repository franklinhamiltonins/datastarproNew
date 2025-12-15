<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Model\User;
use App\Model\Role;
use DB;

class ImpersonationController extends Controller
{
	public function impersonate($id)
	{
		$user = User::find($id);

		if ($user) {
			// Store the original user's ID in the session
			session(['impersonate_original' => auth()->id()]);
			session(['impersonate' => $user->id]);
			auth()->login($user); // Log in as the impersonated user
			return redirect('/')->with('message', 'You are now impersonating ' . $user->name);
		}

		return redirect('/')->with('error', 'User not found');
	}


	public function leaveImpersonation()
	{
		$originalUserId = session('impersonate_original');

		if ($originalUserId) {
			$originalUser = User::find($originalUserId);

			if ($originalUser) {
				auth()->login($originalUser); // Log back in as the original user
				session()->forget('impersonate');
				session()->forget('impersonate_original');
				return redirect('/')->with('message', 'You have left impersonation mode');
			}
		}

		return redirect('/login')->with('error', 'Unable to leave impersonation');
	}
	public function search(Request $request)
	{
		$keyword = $request->get('keyword');
		$users = User::select('name', 'email', 'id')->where('name', 'like', "%{$keyword}%")->limit(10)->get();
		$modified_user = [];
		$i = 0;
		foreach ($users as $user) {

			$modified_user[$i]['name'] = $user->name;
			$modified_user[$i]['id'] = $user->id;
			$modified_user[$i]['email'] = $user->email;
			$usersWithRoles = DB::table('users')
				->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
				->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
				->select('users.id', 'users.name', 'users.email', DB::raw('GROUP_CONCAT(roles.name SEPARATOR ", ") as roles'))
				->where('users.id', $user->id)
				->get();

			$modified_user[$i]['role'] = $usersWithRoles->first()->roles;
			$i++;
		}

		return response()->json($modified_user); // Ensure this returns a JSON array 
	}
}
