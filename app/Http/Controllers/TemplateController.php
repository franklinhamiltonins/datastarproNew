<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Model\Template;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use App\Model\User;
use Spatie\Permission\Models\Permission;
use App\Model\Role;
use DataTables;
use Redirect, Response;
use App\Model\LeadsModel\Contact;
use App\Traits\CommonFunctionsTrait;
use App\Model\UserTemplate;


class TemplateController extends Controller
{
	use CommonFunctionsTrait;
	
	private $is_admin = false;
	public $hideColumns = ['created_at', 'updated_at', 'deleted_at'];
	// public $tableHeaders = ['created_at', 'updated_at', 'deleted_at'];
	public $tableHeaders = [
		[
			"columnName" => "id",
			"niceName" => "Id"
		],
		[
			"columnName" => "template_name",
			"niceName" => "Template Name"
		],
		[
			"columnName" => "template_content",
			"niceName" => "Template Content"
		],
		[
			"columnName" => "user_id",
			"niceName" => "User Name"
		]

	];
	public $showActionLink = true;

	public function list(Request $request)
	{

		$request->showActionLink = $this->showActionLink;
		$request->tableHeaders = $this->tableHeaders;
		$request->hideColumns = $this->hideColumns;
		// $request->notificationData = $data;
		return parent::list($request);
	}
	// public function create(Request $request)
	// {
	// 	dd($request);
	// }
	public function listByUserId(Request $request)
	{
		// $data = DB::table('templates')->where('user_id', auth()->user()->id)->get();
		$type = $request->input('type') ?? 'sms';
		$user_id = auth()->user()->id;

		$is_admin = auth()->user()->can('agent-create');

		if($is_admin){
			$templateData = Template::where('template_type', $type)->get();
		}
		else{
			$templateData = Template::where('template_type', $type)
			->where('set_for_all','yes')
			->orWhereHas('user', function ($query) use ($user_id, $type) {
				$query->where('template_type', $type)
				->Where('user_id', $user_id);
			})
			->get();

		}

		// dd($templateData);
		foreach($templateData as $template) {
			$template->delete_permission = false;
			$is_admin = auth()->user()->can('agent-create');
			if($is_admin || ($template->set_for_all == "no" && $template->created_by == $user_id)) 
				$template->delete_permission = true;
		}
		return json_encode([
			'status' => 200,
			'response' => $templateData,
		]);
	}

	public function addNewTemplate(Request $request)
	{
		if(isset($request->template_type) && $request->template_type == "mail") {
			$validator = Validator::make($request->all(), [
				'template_name' => 'required|max:100',
				'template_subject'  => 'required|max:200',
				'template_content' => 'required'
			]);
		} else {
			$validator = Validator::make($request->all(), [
				'template_name' => 'required|max:100',
				'template_content' => 'required|max:200'
			]);
		}

		if ($validator->fails()) {
			return response()->json([
				'status' => 422,
				'errors' => $validator->errors()->all()
			]);
		}

		try {
			$validatedData = $validator->validated();
			$input = $request->all();

			$input['template_type'] = isset($input['template_type']) && $input['template_type'] == 'mail' ? 'mail' : 'sms';

			$template_name_slug = $this->createTemplateSlug($input);
			// Check if template with the same name already exists
			$existingTemplate = Template::where('template_name_slug', $template_name_slug)->first();
			if ($existingTemplate) {
				return response()->json([
					'status' => 500,
					'response' => "Template already exists."
				]);
			}
			$input['template_name_slug'] = $template_name_slug;
			// $input['template_content'] = strip_tags(trim($input['template_content']));
			$input['template_subject'] = $input['template_type'] == 'mail' ? $input['template_subject'] : null;
			$input['created_by'] = auth()->user()->id;

			$templateData = Template::create($input);

			UserTemplate::create([
				'user_id' 	  => auth()->user()->id,
				'template_id' => $templateData->id
			]);

			return response()->json([
				'status' => 200,
				'response' => $templateData,
				'message' => 'New Template created successfully'
			]);
		} catch (\Exception $e) {
			return response()->json([
				'status' => 500,
				'response' => "Failed to create the new template. Please contact the administrator.",
				'error'=>$e->getMessage()
			]);
		}
	}


	public function deleteTemplate(Request $request)
	{
		$templateId = $request->templateId;
		try {
			$templateDateted = Template::where([
				'id' => $templateId
			])->delete();
			if ($templateDateted) {

				UserTemplate::where([
					'template_id' => $templateId
				])->delete();

				return json_encode([
					'status' => 200,
					'message' => 'Template Deleted Successfully'
				]);
			} else {
				return response()->json([
					'success' => 'false',
					'response' => "Failed to delete the template. Please contact the administrator."
				], 500);
			}
		} catch (\Exception $e) {
			return response()->json([
				'success' => 'false',
				'response' => "Failed to delete the template. Please contact the administrator."
			], 500);
		}
	}

	public function contactDetail(Request $request)
	{
		$contactId = $request->singleContactId;
		try {
			// $contactDetail = DB::table('contacts')->where('id', $contactId)->with('leads')->get();
			$contactDetail = Contact::where('id', $contactId)->with('leads:id,name')->get();
			if ($contactDetail) {
				return json_encode([
					'status' => 200,
					'message' => 'Contact details showed Successfully',
					'response' => $contactDetail
				]);
			} else {
				return response()->json([
					'success' => 'false',
					'response' => "Failed to show contact detail. Please contact the administrator."
				], 500);
			}
		} catch (\Exception $e) {
			return response()->json([
				'success' => 'false',
				'response' => "Failed to show contact detail. Please contact the administrator.",
				'error'=>$e->getMessage()
			], 500);
		}
	}
	public function templateDetail(Request $request)
	{
		$templateId = $request->singleTemplateId;
		try {
			$templateDetail = DB::table('templates')->where('id', $templateId)->get();
			if ($templateDetail) {
				return json_encode([
					'status' => 200,
					'message' => 'template details showed Successfully',
					'response' => $templateDetail
				]);
			} else {
				return response()->json([
					'success' => 'false',
					'response' => "Failed to show template detail. Please template the administrator."
				], 500);
			}
		} catch (\Exception $e) {
			return response()->json([
				'success' => 'false',
				'response' => "Failed to show contact detail. Please contact the administrator."
			], 500);
		}
	}

	public function index(Request $request)
	{
		$is_admin = auth()->user()->can('agent-create');
		// $is_admin = false;
		return view('templates.index', compact('is_admin'));
	}

	public function get_templates(Request $request){
        $start = $request->input('start', 0);
		$length = $request->input('length', 10); // Default length or adjust as needed
		$draw = $request->input('draw', 1);
		$filter_on_column_number = $request->input('order')[0]['column'];
		$filter_on_column_name = $request->input('columns')[$filter_on_column_number]['data'] ?? 'id';
		$order_by = $request->input('order')[0]['dir'] ?? 'desc';
		$search_value = $request->input('search')['value'] ?? null;

		$is_admin = auth()->user()->can('agent-create');
		
		if($is_admin)
			$searchQuery = Template::with('user');
		else if(!$is_admin && empty($search_value))
			$searchQuery = Template::where(function ($query) {
				$query->where('set_for_all','yes');
				})
			->orWhereHas('user', function ($query) {
				$query->where('user_id', auth()->user()->id);
			});
		else
			$searchQuery = Template::with('user');

		$totalRecords = $searchQuery->count();
		$filteredRecords = $searchQuery->count();

		// Apply search filter
		if (!empty($search_value)) {
			if($is_admin)
				$searchQuery = $this->search($searchQuery, $search_value, ['template_name', 'template_type', 'template_subject', 'set_for_all', 'user.name']);
			else {
				$searchQuery = $this->search($searchQuery, $search_value, ['template_name', 'template_type', 'template_subject']);
				$searchQuery = $searchQuery->whereHas('user', function ($query) {
					$query->where('user_id', auth()->user()->id);
				})->orWhere('set_for_all','yes');;
			}
				
			$filteredRecords = $searchQuery->count();
		}
		$templates = $searchQuery->orderBy($filter_on_column_name, $order_by)->offset($start)->limit($length);


		// echo $templates->toSql();exit;
	

        return datatables()->of( $templates)
        ->addIndexColumn()
        ->editColumn('template_type', function ($searchQuery) {
            return $searchQuery->template_type == 'sms' ? 'SMS' : 'Email';
        })
		->editColumn('set_for_all', function ($searchQuery) {
            return $searchQuery->set_for_all == 'yes' ? 'Yes' : 'No';
        })
		->editColumn('user_id', function ($searchQuery) {
			$agent_name = "";
			if(!is_null($searchQuery->user)) {
				foreach($searchQuery->user as $user) {
					$agent_name .= "<a href='/users/".base64_encode($user->id)."'>".$user->name."</a>, ";
				}
			}
            return rtrim($agent_name, ", ");
        })
        ->addColumn('action', function ($row) use ($is_admin) {
			return view('templates.partials.buttons-actions', compact('row','is_admin'));			
		})
		->rawColumns(['action'])
		->setTotalRecords($totalRecords)
		->setFilteredRecords($filteredRecords)
		->make(true);
    }

	public function delete_templates(Request $request)
	{
		$templateIds = $request->selectedValues;
		if (count($templateIds) <= 0) :
			return response()->json(['templateCount' => 0, 'message' => 'Please check at least one checkbox to continue.']);
		endif;

		UserTemplate::whereIn('template_id', $templateIds)->delete();

		Template::whereIn('id', $templateIds)->delete();

		return response()->json(['templateCount' => 1, 'message' => 'Records deleted successfully']);
	}

	/**
	 * Display the specified resource.
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function show(Request $request, $id)
	{
		$is_admin = auth()->user()->can('agent-create');
		$id = base64_decode($id);
		$template = Template::findOrFail($id);
		if (!$template) {

			toastr()->error('This Template doesn\'t exist');
			return redirect('/templates');
		}
		$template = Template::where('id', $id)->with('user')->first();
		$agent_name = "";
		if(!is_null($template->user)) {
			foreach($template->user as $user) {
				$agent_name .= $user->name." ( ".$user->email." )<br>";
			}
		}
		return view('templates.show', compact('template','is_admin', 'agent_name'));
	}

	/**
	 * Show the form for editing the specified resource.
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function edit($id)
	{	
		$id = base64_decode($id);
		$is_admin = auth()->user()->can('agent-create');
		if($is_admin) {
			$template = Template::with('userTemplates')->find($id);
		} else {
			$template = Template::whereHas('userTemplates', function($q)
			{
				$q->where('user_id','=', auth()->user()->id);
			
			})->find($id);
		}
		
		if (!$template) {

			toastr()->error('This Template doesn\'t exist or you don\'t have the permission');
			return redirect('/templates');
		}
		// if($is_admin || ($template->set_for_all == "no")) {
		if($template->created_by == auth()->user()->id) {
			$agents = User::role(['Agent','Service & Agent', 'Admin', 'Super Admin'])->orderBy('name', 'asc')->get();
			$agent_users = [];
			// $agent_users[0] = 'Select Agent';
			$selected_agents = [];
			if($is_admin) {
				foreach ($agents as $key => $agent) {
					$agent_users[$agent->id] = $agent->name . ' (' . $agent->email . ')';
				}
				$selected_agents_arr = $template->userTemplates->toArray();
				foreach($selected_agents_arr as $key => $selected_agent)
				{
					$selected_agents[] = $selected_agent['user_id'];
				}
			}
			return view('templates.edit', compact('template','is_admin','agent_users', 'selected_agents'));
		} else {
			toastr()->error("You don't have permission to edit the template.");
			return redirect('/templates');
		}
		
	}

	/**
	 * Show the form for creating a new resource.
	 * @return \Illuminate\Http\Response
	 */
	public function create()
	{		
		$is_admin = auth()->user()->can('agent-create');
		$agents = User::role(['Agent','Service & Agent', 'Admin', 'Super Admin'])->orderBy('name', 'asc')->get();
		$agent_users = [];
		// $agent_users[0] = 'Select Agent';
		
		foreach ($agents as $key => $agent) {
			$agent_users[$agent->id] = $agent->name . ' (' . $agent->email . ')';
		}
		return view('templates.create', compact('is_admin','agent_users'));
	}



	/**
	 * Store a newly created resource in storage.
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request)
	{
		if(isset($request->template_type) && $request->template_type == "mail") {
			$rules =[
				'template_name'    => 'required|max:100',
				'template_type'    => 'required',
				'template_subject' => 'required_if:template_type,mail',
				'template_content' => 'required',
				'user_id'          => 'required_without:set_for_all|array|min:1',
			];
		} else {
			$rules =[
				'template_name'    => 'required|max:100',
				'template_type'    => 'required',
				'template_content' => 'required|max:200',
				'user_id'          => 'required_without:set_for_all|array|min:1',
			];
		}
		$niceNames = [
			'template_name'    => 'Template Name',
			'template_type'    => 'Template Type',
			'template_subject' => 'Template Subject',
			'template_content' => 'Template content',
			'user_id'          => 'User Name'
		];
		$messages = [
			'user_id.required_without' =>'Select User Name',
		];

		//validate fields using nice name in error messages
		$validator = Validator::make($request->all(), $rules, $messages, $niceNames);

		if ($validator->fails()) {
			$errorMessages = $validator->errors()->all();
			toastr()->error(implode('<br>', $errorMessages));
			return back()->withErrors($validator)->withInput();
		}

		$input = $request->all();

		$input['set_for_all'] = isset($input['set_for_all']) && $input['set_for_all'] > 0 ? 'yes' : 'no';
		if($input['set_for_all'] == "yes") {
			$userIds = NULL;
		} elseif(isset($input['user_id']) && count($input['user_id']) > 0) {
			$userIds = $input['user_id'];
		} else 
			$userIds = [auth()->user()->id];
		
		$template_name_slug = $this->createTemplateSlug($input);
		// Check if template with the same name already exists
		$existingTemplate = Template::where('template_name_slug', $template_name_slug)->first();
		if ($existingTemplate) {
			toastr()->error('Template already exists.');
			return back()->withErrors($validator)->withInput();
		}
		$input['template_name_slug'] = $template_name_slug;
		$input['template_subject'] = $input['template_type'] == 'mail' ? $input['template_subject'] : null;
		$input['created_by'] = auth()->user()->id;
		$input['template_content'] = strip_tags(trim($input['template_content']));

		$template = Template::create($input);
		$id = $template->id;

		if($userIds) {
			$createRecord = [];
			foreach($userIds as $key=>$userId) {
				$createRecord[] = [
					'user_id' 	  => $userId,
					'template_id' => $id
				];
			}
			UserTemplate::insert($createRecord);
		}

		$id = base64_encode($id);

		toastr()->success('Template <b>' . $template->template_name . '</b> created successfully');
		return redirect()->route('templates.update', compact('id'));
	}
	/**
	 * Update the specified resource in storage.
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, $id)
	{
		
		$template = Template::find($id);
		$input = $request->all();
		// if(isset($input['set_for_all']))
		// 	$request->merge(['img' => $img]);

		if (!$template) {
			toastr()->error('Something went wrong');
			return back();
		}
		if($template->template_type == 'mail') {
			$rules =[
				'template_name' => 'required|max:100',
				'template_subject'  => 'required_if:template_type,mail',
				'template_content' => 'required',
				'user_id'          => 'required_without:set_for_all|array|min:1',
			];
		} else {
			$rules =[
				'template_name' => 'required|max:100',
				'template_content' => 'required|max:200',
				'user_id'          => 'required_without:set_for_all|array|min:1',
			];
		}

		$niceNames = [
			'template_name' => 'Template Name',
			'template_subject' => 'Template Subject',
			'template_content' => 'Template content',
			'user_id'          => 'User Name'
		];
		$messages = [
			'user_id.required_without' =>'Select User Name',
		];

		//validate fields using nice name in error messages
		$validator = Validator::make($request->all(), $rules, $messages, $niceNames);

		if ($validator->fails()) {
			$errorMessages = $validator->errors()->all();
			toastr()->error(implode('<br>', $errorMessages));
			return back()->withErrors($validator)->withInput();
		}
		
		$input['set_for_all'] = isset($input['set_for_all']) && $input['set_for_all'] > 0 ? 'yes' : 'no';
		if($input['set_for_all'] == "yes") {
			$userIds = NULL;
		} elseif(isset($input['user_id']) && count($input['user_id']) > 0) {
			$userIds = $input['user_id'];
		} else 
			$userIds = [auth()->user()->id];

		$slugData['template_name'] = $input['template_name'];
		$slugData['template_type'] = $template->template_type;
		$template_name_slug = $this->createTemplateSlug($slugData);
		// Check if template with the same name already exists
		$existingTemplate = Template::where('template_name_slug', $template_name_slug)->first();
		if ($existingTemplate && $existingTemplate->id != $id) {
			toastr()->error('Template already exists.');
			return back();
		}
		$input['template_name_slug'] = $template_name_slug;
		// $input['template_content'] = strip_tags(trim($input['template_content']));
		$template->update($input);

		// update pivot table
		
		$selected_agents = json_decode($input['selected_agents']);
		if($userIds) {
			// remove agents those are saved previously but removed now
			$agents_to_remove = array_diff($selected_agents, $userIds);
			if(!empty($agents_to_remove)) {
				$deletedRows = UserTemplate::where('template_id', $id)->whereIn('user_id', $agents_to_remove)->delete();
			}
			// update/create the newly selected records
			foreach($userIds as $key=>$userId) {
				UserTemplate::updateOrCreate(
					[
						'user_id' 	  => $userId,
						'template_id' => $id
					]
				);
			}
		} else {
			// remove all agents those are saved previously, if any
			if(!empty($selected_agents)) {
				$deletedRows = UserTemplate::where('template_id', $id)->whereIn('user_id', $selected_agents)->delete();
			}
		}

		toastr()->success('Template ' . $input['template_name'] . ' updated successfully');
		return redirect()->back();
		
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id)
	{
		//find the template to delete
		$template = Template::find($id);
		if (!$template) {

			toastr()->error('The Template was removed previously');
			return back();
		}
		$template->delete();
		toastr()->success('Template <b>' . $template->template_name . '</b> Deleted!');
		return  redirect()->route('templates.index');
	}
    
	
}
