<?php

namespace App\Http\Controllers\Leads;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\LeadsModel\Lead;
use App\Model\LeadsModel\ScrapApiPlatform;
use App\Model\LeadsModel\Log;
use Yajra\Datatables\Datatables;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Model\Dialing;
use App\Model\Agentlog;
use Validator;
use App\Traits\CommonFunctionsTrait;

use View;

class ScrapApiPlatformController extends Controller
{
	// use CommonFunctionsTrait;

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */

	//Listing view
	public function index()
	{
		return view('scrap_api_platform.index');
	}

	/* Display datatables
	 */
	public function getApiPlatforms()
	{
		try {
			$is_admin_user = auth()->user()->can('agent-create');
			$curlApis = DB::table('scrap_api_platforms')->where('deleted_at');

			return datatables()->of($curlApis)
				->addIndexColumn()
				// ->setTotalRecords($curlApis)
				->addColumn('action', function ($row) use ($is_admin_user) {
					$editLead      = 'lead-edit';
					$deleteLead    = 'lead-delete';
					$crudRoutePart = 'lead';
					return view('scrap_api_platform.partials.lead-buttons-actions', compact('editLead', 'deleteLead', 'crudRoutePart', 'row', 'is_admin_user'));
				})
				->make(true);
		} catch (\Throwable $err) {
			toastr()->error($err);
			throw ($err);
		}
	}

	// Create view
	public function create()
	{
		return view('scrap_api_platform.create');
	}


	// Store Api
	public function store(Request $request)
	{
		try {
			$input = $request->all();

			$new_priority_order = $input['priority_order'];
			$priority_validation = ScrapApiPlatform::priorityValidation($input['priority_order']);

			if (!$priority_validation['status']) {
				toastr()->error('Priority should be within ' . $priority_validation['data'] . '.');
				return back()->withErrors($priority_validation)->withInput();
			}

			$validator = ScrapApiPlatform::formValidation($request->all());
			if ($validator->fails()) {
				$errorMessages = $validator->errors()->all();
				toastr()->error(implode('<br>', $errorMessages));
				return back()->withErrors($validator)->withInput();
			}

			$this->reorderPriorities($new_priority_order);
			$input['priority_order'] = $new_priority_order;
			$api_setting = ScrapApiPlatform::withoutTrashed()->create($input);


			toastr()->success('Api Platform Setting <b>' . $api_setting->platform_name . '</b> created successfully.');

			return redirect()->route('platform_setting.index');
		} catch (\Throwable $err) {
			// toastr()->error('Something went wrong.');
			// return back()->withErrors('Something went wrong.')->withInput();
			toastr()->error($err);
			throw ($err);
		}
	}

	// Edit view
	public function edit(Request $request)
	{

		$api_id = $request->id;
		$scrapApi = ScrapApiPlatform::find($api_id);

		return view('scrap_api_platform.edit', compact('scrapApi'));
	}

	// Update Api
	public function update(Request $request)
	{
		try {
			$input = $request->all();

			$scrapApi = ScrapApiPlatform::find($request->id);

			$new_priority_order = $input['priority_order'];
			$old_priority_order = $scrapApi['priority_order'];

			$priority_validation = ScrapApiPlatform::priorityValidation($input['priority_order']);
			if (!$priority_validation['status']) {
				toastr()->error('Priority should be within ' . $priority_validation['data']);
				return back()->withErrors($priority_validation)->withInput();
			}

			$validator = ScrapApiPlatform::formValidation($request->all());
			if ($validator->fails()) {
				$errorMessages = $validator->errors()->all();
				toastr()->error(implode('<br>', $errorMessages));
				return back()->withErrors($validator)->withInput();
			}



			if ($new_priority_order != $old_priority_order)
				$this->reorderPrioritiesForUpdate($old_priority_order, $new_priority_order);

			$input['priority_order'] = $new_priority_order;
			$api_setting = $scrapApi->update($input);

			toastr()->success('Api Platform Setting updated successfully.');
			return redirect()->route('platform_setting.index');
		} catch (\Throwable $err) {
			toastr()->error($err);
			throw ($err);
		}
	}


	private function reorderPriorities($newPriority)
	{
		$platforms = ScrapApiPlatform::where('priority_order', '>=', $newPriority)
			->orderBy('priority_order', 'desc')
			->get();

		foreach ($platforms as $platform) {
			$incrementedPriority = $platform->priority_order + 1;

			// Check for conflicts with soft-deleted records
			while (ScrapApiPlatform::withTrashed()->where('priority_order', $incrementedPriority)->exists()) {
				$incrementedPriority++;
			}

			$platform->priority_order = $incrementedPriority;
			$platform->save();
		}
	}

	private function reorderPrioritiesForUpdate($currentPriority, $newPriority)
	{
		// echo $newPriority . '====' . $currentPriority;




		if ($newPriority > $currentPriority) {
			// Shift priorities down
			ScrapApiPlatform::whereBetween('priority_order', [$currentPriority + 1, $newPriority])
				->whereNull('deleted_at')
				->decrement('priority_order', 1);
		} elseif ($newPriority < $currentPriority) {
			// Shift priorities up
			ScrapApiPlatform::whereBetween('priority_order', [$newPriority, $currentPriority - 1])
				->whereNull('deleted_at')
				->increment('priority_order', 1);
		}
	}



	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function delete($id)
	{
		//find the lead to delete
		// echo $id;
		$scrapApi = ScrapApiPlatform::find($id);
		// print_r($scrapApi);
		// die;
		if (!$scrapApi) {

			toastr()->error('The Api settings was removed previously');
			return back();
		}
		ScrapApiPlatform::where('id', $id)
			->update([
				'priority_order' =>  NULL,
			]);

		$scrapApi->delete();

		toastr()->success('Api platform setting <b>' . $scrapApi->platform_name . '</b> Deleted!');
		return  redirect()->route('platform_setting.index');
	}
}
