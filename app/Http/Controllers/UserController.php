<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\User;
use App\Model\Role;
use DB;
use Hash;
use Validator;
use Illuminate\Support\Arr;

class UserController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	function __construct()
	{

		$this->middleware('permission:user-list|user-create|user-edit|user-delete', ['only' => ['index', 'store']]);
		$this->middleware('permission:user-create', ['only' => ['create', 'store']]);
		$this->middleware('permission:user-edit', ['only' => ['edit', 'update']]);
		$this->middleware('permission:user-delete', ['only' => ['destroy']]);
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index(Request $request)
	{
		//paginate the users table and sort it asc
		$data = User::orderBy('id', 'ASC')->paginate(10);
		return view('users.index', compact('data'))
			->with('i', ($request->input('page', 1) - 1) * 10);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create()
	{
		//get all role names found in roles table
		$roles = Role::pluck('name', 'name')->all();
		return view('users.create', compact('roles'));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request)
	{
		//validate form
		// $this->validate($request, [
			$rules = [
			'name' => 'required|string|max:191',
			'email' => 'required|email|unique:users,email',
			'password' => 'required|same:confirm-password',
			'roles' => 'required'
		];

		$validator = Validator::make($request->all(), $rules, []);

		if ($validator->fails()) {
			$errorMessages = $validator->errors()->all();
			toastr()->error(implode('<br>', $errorMessages));
			return back()->withErrors($validator)->withInput();
		}

		$input = $request->all();
		$input['password'] = Hash::make($input['password']);
		//crete user
		$user = User::create($input);
		$user->assignRole($request->input('roles'));


		toastr()->success('User <b>' . $user->name . '</b> created successfully');
		return redirect()->route('users.index');
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function show($id)
	{
		//get user
		$id = base64_decode($id);
		$user = User::find($id);
		if (!$user) {

			toastr()->error('This User doesn\'t exist');
			return back();
		}
		return view('users.show', compact('user'));
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function edit($id)
	{
		//get the user
		$id = base64_decode($id);
		$user = User::find($id);
		if (!$user) {

			toastr()->error('This User doesn\'t exist');
			return back();
		}
		//get the role names found in role table
		$roles = Role::pluck('name', 'name')->all();
		//get all roles
		$userRole = $user->roles->pluck('name', 'name')->all();
		$agents = User::role('agent')->where("id","!=",$id)->pluck('name', 'id')->toArray();
		$assignedUser = $user->accessibleUsers->pluck('id')->toArray();

		// echo "<pre>";
		// echo $id."<br>";
		// print_r($agents);exit;

		return view('users.edit', compact('user', 'roles', 'userRole',"agents","assignedUser"));
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, $id)
	{
		//validate form
		// $this->validate($request, [
		$rules= [
			'name' => 'required|string|max:191',
			'email' => 'required|email|unique:users,email,' . $id,
			'password' => 'same:confirm-password',
			'roles' => 'required'
		];

		$validator = Validator::make($request->all(), $rules, []);

		if ($validator->fails()) {
			$errorMessages = $validator->errors()->all();
			toastr()->error(implode('<br>', $errorMessages));
			return back()->withErrors($validator)->withInput();
		}

		$input = $request->all();
		//if pass is not empty, update it
		if (!empty($input['password'])) {
			$input['password'] = Hash::make($input['password']);
		} else {
			$input = Arr::except($input, array('password'));  //leave it as it is   
		}
		// get the user and update it
		$user = User::find($id);
		if (!$user) {

			toastr()->error('Something went wrong');
			return back();
		}
		$user->update($input);

		$user->accessibleUsers()->sync($request->master_access);

		DB::table('model_has_roles')->where('model_id', $id)->delete();
		// assign role to user
		$user->assignRole($request->input('roles'));


		toastr()->success('User <b>' . $user->name . '</b> updated successfully');
		return redirect()->route('users.index');
	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id)
	{
		//get the user
		$user = User::findOrFail($id);
		if (!$user) {

			toastr()->error('The User was removed previously');
			return back();
		}
		//if the user is not the loggedin user
		if ($user->id != auth()->user()->id) {
			// rename lead if deleted - to fix the Unique issue
			$user->update([
				'email' => time() . '::' . $user->email
			]);
			$user->delete();

			toastr()->success('User <b>' . $user->name . '</b> Deleted!');
			return redirect()->back();
		}
		toastr()->error('You cannot delete yourself!');

		return redirect()->back();
	}

	public function getAgentDetails(Request $request) {
		$agent = User::find($request->id);
		return response()->json(['agent' => $agent, 'message' => '']);
	}

}
