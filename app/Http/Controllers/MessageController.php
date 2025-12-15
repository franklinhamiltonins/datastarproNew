<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;


class MessageController extends Controller
{




	// public function module(Request $request)
	// {
	// 	$is_admin = auth()->user()->can('agent-create');
	// 	$modules = $tableData = $this->moduleApi($request); // Paginate the results


	// 	// Define table headers
	// 	$tableHeaders = ['ID', 'Name', 'City', 'County', 'Contacts']; // Adjust as per your requirement 


	// 	$tableName = 'Modules'; // You can change this to whatever you want

	// 	$breadcrumbs = [
	// 		['title' => 'Home', 'url' => route('home')],
	// 		['title' => 'Modules', 'url' => route('dialings.index')]
	// 	];

	// 	// Determine whether action link should be displayed
	// 	$showActionLink = true; // Set this based on your condition

	// 	return view('modules.list', compact('modules', 'tableName', 'breadcrumbs', 'showActionLink', 'tableHeaders', 'tableData'));
	// }

	// public function moduleApi(Request $request)
	// {
	// 	$is_admin = auth()->user()->can('agent-create');
	// 	$dialing_query = Lead::query();



	// 	// Search functionality
	// 	if ($request->has('search')) {
	// 		$dialing_query->where('name', 'like', '%' . $request->input('search') . '%');
	// 	}

	// 	// Sorting functionality
	// 	$sortColumn = $request->input('sort', 'name');
	// 	$sortOrder = $request->input('order', 'asc');
	// 	$dialing_query->orderBy($sortColumn, $sortOrder);

	// 	$modules = Lead::join('dialings_leads', 'leads.id', '=', 'dialings_leads.lead_id')
	// 		->join('dialings', 'dialings.id', '=', 'dialings_leads.dialing_id')
	// 		->join('contacts', 'leads.id', '=', 'contacts.lead_id')
	// 		->select('leads.*') // Select the columns you want from the leads table
	// 		->where('dialings_leads.dialing_id', 1)
	// 		->where('dialings_leads.status', '!=', 'uncallable')
	// 		->where('contacts.c_phone', '!=', '')
	// 		->whereNUll('contacts.deleted_at')
	// 		->where(function ($query) {
	// 			$query->where('contacts.c_status', 'Select Status')
	// 				->orWhere('contacts.c_status', 'Call Back');
	// 		})
	// 		->orderByRaw('RAND()')
	// 		->paginate(25);

	// 	return $modules;
	// }






	// dialing index list page end




}
