<?php

namespace App\Http\Controllers;

use App\Model\AgentLog;
use Illuminate\Http\Request;

class AgentController extends Controller
{
    private $is_admin = false;

    // dialing index list page start
    public function reports()
    {
        return view('agents.report');
    }

    public function getReportsDataApi(Request $request)
    {
        $logsData = AgentLog::select('agentlogs.*', 'users.name as agent_name', 'leads.name as business_name', 'contacts.c_full_name as contact_name', 'contacts.c_phone as contact_phone')
            ->join('users', 'users.id', '=', 'agentlogs.user_id')
            ->join('leads', 'leads.id', '=', 'agentlogs.lead_id')
            ->join('contacts', 'contacts.id', '=', 'agentlogs.contact_id')
            ->get();

        return datatables()->of($logsData)
            ->addIndexColumn()
            ->make(true);
    }
}
