<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\User;
use App\Model\Campaign;
use App\Model\LeadsModel\Lead;
use App\Model\Role;
use DB;
use Hash;
use Illuminate\Support\Arr;

use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Facades\Http;
use App\Model\ScrapCounty;
use App\Model\ScrapCity;

class HomeController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	function __construct()
	{

		$this->middleware('permission:dashboard-list|dashboard-create|dashboard-edit|dashboard-delete', ['only' => ['index', 'store']]);
		$this->middleware('permission:dashboard-create', ['only' => ['create', 'store']]);
		$this->middleware('permission:dashboard-edit', ['only' => ['edit', 'update']]);
		$this->middleware('permission:dashboard-delete', ['only' => ['destroy']]);
		$this->middleware('permission:dashboard-list', ['only' => ['edit_profile,update_profile']]);
	}
	public function index()
	{
		$leads = 0; //count(Lead::all());
		$campaigns = 0; //count(Campaign::all());
		$pendingCampaigns = 0; // Campaign::where('status', 'PENDING')->count();
		$completedCampaigns = 0; //Campaign::where('status', 'COMPLETED')->count();

		return view('index', compact('leads', 'campaigns', 'pendingCampaigns', 'completedCampaigns'));
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function edit_profile($id)
	{
		$user = User::find($id);
		if (!$user) {

			toastr()->error('Something went wrong');
			return back();
		}
		//get all role names from db
		$roles = Role::pluck('name', 'name')->all();
		//get the role name of the actual user
		$userRole = $user->roles->pluck('name', 'name')->all();

		return view('profile', compact('user', 'roles', 'userRole'));
	}
	public function update_profile(Request $request, $id)
	{
		//form validation
		$this->validate($request, [
			'name' => 'required',
			'email' => 'required|email|unique:users,email,' . $id,
			'password' => 'same:confirm-password',
			'roles' => 'required'
		]);

		$input = $request->all();
		//if the pasword is not empty, update id , if not let it as it is
		if (!empty($input['password'])) {
			$input['password'] = Hash::make($input['password']);
		} else {
			$input = Arr::except($input, array('password'));
		}
		//get the user that needs to be updated
		$user = User::find($id);
		if (!$user) {

			toastr()->error('Something went wrong');
			return back();
		}
		$user->update($input);
		DB::table('model_has_roles')->where('model_id', $id)->delete();
		//update user with the new role 
		$user->assignRole($request->input('roles'));

		toastr()->success('Profile updated successfully');
		return redirect()->back();
	}
}
