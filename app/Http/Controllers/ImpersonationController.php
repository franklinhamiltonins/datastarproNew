<?php

namespace App\Http\Controllers;

use App\Model\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImpersonationController extends Controller
{
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

            return redirect('/')->with('message', 'You are now impersonating '.$user->name);
        }

        return redirect('/')->with('error', 'User not found');
    }

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
            } else {
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
        if (! $keyword || ! $authUser) {
            return response()->json([]);
        }
        if ($authUser->hasRole('Super Admin')) {
            $users = User::with('roles')
                ->where('name', 'like', "%{$keyword}%")
                ->select('id', 'name', 'email')
                ->limit(10)
                ->get();

        } elseif ($authUser->hasRole('Manager')) {
            // Use DB query, NOT collection filtering
            $users = $authUser->managerTeamList()
                ->with('roles')
                ->where('name', 'like', "%{$keyword}%")
                ->select('users.id', 'users.name', 'users.email')
                ->limit(10)
                ->get();
        } else {
            return response()->json([]);
        }
        $modified_user = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->roles->pluck('name')->implode(', '),
            ];
        });

        return response()->json($modified_user);
    }
}
