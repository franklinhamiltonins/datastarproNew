<?php

namespace App\Http\Controllers;

use App\Model\Permission;
use Illuminate\Http\Request;
use App\Model\User;
use App\Model\Setting;
use Illuminate\Support\Facades\Validator;


class SettingController extends Controller
{

	public function doc(Request $request)
	{
		return view('documentation');
	}
	public function generate(Request $request)
	{
		$modules = ['campaign', 'contact', 'dashboard', 'dialing', 'lead', 'lead-file', 'owned', 'role', 'setting', 'template', 'user'];

		$permissions = ['action', 'create', 'delete', 'download', 'edit', 'export', 'import', 'list', 'navigation', 'update', 'upload', 'filters', 'mapsearch'];

		foreach ($modules as $module) :
			foreach ($permissions as $permission) :
				echo $module_permission = strtolower($module . '-' . $permission);
				$permissionColl = Permission::where('name', $module_permission)->get();
				// dd($permissionColl);
				if ($permissionColl->count() > 0) :
					foreach ($permissionColl as $singlepermissioncoll) :
						echo $singlepermissioncoll->id;

						Permission::where('id', $singlepermissioncoll->id)->update(['name' => $module_permission, 'guard_name' => 'web', 'page' => $module . ' Page']);
					endforeach;

				else :
					$permissionModel = new Permission();
					$permissionModel->name = $module_permission;
					$permissionModel->guard_name = 'web';
					$permissionModel->page = $module . ' Page';
					$permissionModel->save();
				endif;
			endforeach;
		endforeach;
	}

	// for time execeed
	public function systemsetting(Request $request)
	{
		$setting_time_data = Setting::select('proceed_time_in_minute','notify_email','process_time_in_day_pipeline','notify_email_pipeline','renewal_days_in_pipeline')->first();
		// echo "<pre>";print_r($setting_time_data);exit;
		return view('systemsetting_view', compact('setting_time_data'));
	}
	public function storesystemsetting(Request $request){
		$validator = Validator::make($request->all(), [
			'proceed_time_in_minute' => 'required|max:100',
			'notify_email' => 'required',
			'process_time_in_day_pipeline' => 'required|max:100',
			'notify_email_pipeline' => 'required',
			'renewal_days_in_pipeline' => 'required|max:365',
		]);

		if ($validator->fails()) {
			$errors = $validator->errors()->all();
			foreach ($errors as $error) {
				toastr()->error($error);
			}
			return redirect()->back();
		}
		if(empty($request->renewal_days_in_pipeline)){
			toastr()->error("Renewal Notification In Day Pipeline cannot be blank. Please Enter a proper value.");
			return redirect()->back();
		}
		else if($request->renewal_days_in_pipeline > 365){
			toastr()->error("Renewal Notification In Day Pipeline cannot be greater than 365 days, Please Enter a value within range.");
			return redirect()->back();
		}

		$settingData = Setting::updateOrCreate(
			['id' => 1], // Find record by id = 1
			[
				'proceed_time_in_minute' => $request->proceed_time_in_minute,
				'notify_email' => $request->notify_email,
				'process_time_in_day_pipeline' => $request->process_time_in_day_pipeline,
				'notify_email_pipeline' => $request->notify_email_pipeline,
				'renewal_days_in_pipeline' => $request->renewal_days_in_pipeline,
			]
		);

		if($settingData){
			toastr()->success("Data submitted successfully");
			return redirect()->back();
		}else{
			toastr()->success("Something went wrong!!!");
			return redirect()->back();
		}
	}
}
