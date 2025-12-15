<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Model\LeadsModel\Lead;
use App\Model\Dialing;
use App\Model\Agentlistlead;
use App\Model\LeadsModel\Contact;
use App\Model\User;
use App\Model\Calllog;
use App\Model\Agentlog;
use Illuminate\Support\Facades\DB;

class AgentController extends Controller
{
	private $is_admin = false;

	// dialing index list page start
	public function reports()
	{
		$vars = array();
		return view('agents.report', compact($vars));
	}

	public function getReportsDataApi(Request $request)
	{
		$is_admin = auth()->user()->can('agent-create');
		$logsData = AgentLog::select('agentlogs.*', 'users.name as agent_name', 'leads.name as business_name', 'contacts.c_full_name as contact_name', 'contacts.c_phone as contact_phone')
			->join('users', 'users.id', '=', 'agentlogs.user_id')
			->join('leads', 'leads.id', '=', 'agentlogs.lead_id')
			->join('contacts', 'contacts.id', '=', 'agentlogs.contact_id')
			->get();

		return datatables()->of($logsData)
			->addIndexColumn()
			// ->addColumn('action', function ($row) use ($is_admin) {
			// 	$deleteLead    = 'lead-delete';
			// 	$crudRoutePart = 'lead';
			// 	return view('agents.partials.buttons-actions', compact('deleteLead', 'crudRoutePart', 'row', 'is_admin'));
			// })

			// ->rawColumns(['action'])
			->make(true);
	}
}
