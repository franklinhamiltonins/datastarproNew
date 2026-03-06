<?php

namespace App\Http\Controllers\Leads;

use App\Http\Controllers\Controller;
use App\Model\Campaign;
use App\Model\LeadsModel\Action;
use App\Model\LeadsModel\Contact;
use App\Model\LeadsModel\Lead;
use Illuminate\Http\Request;

class ActionController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:lead-action')
        ->only(['addAction', 'index', 'getContactReport']);
    }

    /**
     * Show Lead Contact Report page.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('actions.index');
    }

    /**
     * display data table
     */
    public function getContactReport(Request $request)
    {
        $actionsQuery = $this->baseContactQuery();

        $this->applyDateFilter($actionsQuery, $request->filters ?? []);

        $table = $actionsQuery
            ->select('actions.*', 'contacts.c_phone', 'contacts.c_email', 'contacts.c_is_client')
            ->orderBy('contact_date', 'desc');

        return datatables()->of($table)
            ->addIndexColumn()
            ->editColumn('leads', fn(Action $a) => $a->leads()->pluck('name')->implode('<br>'))
            ->editColumn('contacts', fn($a) => $a->c_phone)
            ->editColumn('email', fn($a) => $a->c_email)
            ->editColumn('campaigns', fn(Action $a) => $a->campaigns()->pluck('name')->implode('<br>'))
            ->addColumn('c_is_client', fn(Action $a) => $a->c_is_client ? 'yes' : 'no')
            ->editColumn('contact_date', fn($a) => $this->formatContactDate($a->contact_date))
            ->filterColumn('contact_date', fn($q, $k) =>
                $q->whereRaw("DATE_FORMAT(contact_date,'%m/%d/%Y') like ?", ["%$k%"])
            )
            ->make(true);
    }
    private function baseContactQuery()
    {
        return Action::with('campaigns', 'leads')
            ->crossJoin('contacts', function ($join) {
                $join->on('actions.contact_id', '=', 'contacts.id');
            });
    }
    private function applyDateFilter($query, $filters)
    {
        $start = $filters['startDate'] ?? null;
        $end   = $filters['endDate'] ?? null;

        if ($start && $end) {
            $start = date('Y-m-d', strtotime($start));
            $end   = date('Y-m-d', strtotime($end));

            $query->whereRaw("DATE(actions.contact_date) BETWEEN '$start' AND '$end'");
        }
    }
    private function formatContactDate($date)
    {
        return $date ? date('m/d/Y', strtotime($date)) : '';
    }

    /**
     * Create actions.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function addAction(Request $request, $id)
    {
        $this->validate($request, [
            'action' => 'required',
            'contact_date' => 'required',
        ], [], ['action' => 'Action']);

        $contactName = $this->resolveContactName($request);
        if ($contactName === null) {
            toastr()->error('Please select or add Contact');
            return back();
        }

        $action = Action::create($request->all());
        $lead   = Lead::find($id);

        if ($lead) {
            $this->attachLeadData($action, $lead, $request);
            $this->updateCampaignActions($id, $action);
            $this->createLeadLog($lead, $action, $contactName);

            toastr()->success(
                'Action <b>'.$action->action.'</b>, initiated by Contact: <b>'.$contactName.'</b> created successfully'
            );
        } else {
            toastr()->error('Failed to find the Business Information');
        }

        return back();
    }

    private function resolveContactName($request)
    {
        if (!$request->contact_id && $request->contact_name) {
            return $request->contact_name;
        }

        if (!$request->contact_id && !$request->contact_name) {
            return null;
        }

        if ($request->contact_id) {
            $contact = Contact::find($request->contact_id);
            return $contact ? ($contact->c_first_name.' '.$contact->c_last_name) : '';
        }

        return '';
    }

    private function attachLeadData($action, $lead, $request)
    {
        $action->leads()->associate($lead);
        $action->campaigns()->associate($request->campaign_id);
        $action->save();
    }

    private function updateCampaignActions($leadId, $action)
    {
        $campaigns = Campaign::whereHas('leads', function ($q) use ($leadId) {
            $q->where('lead_id', $leadId);
        })
        ->where('campaign_date', '>=', date('Y-m-d', strtotime($action->contact_date.' -11 days')))
        ->get();

        foreach ($campaigns as $campaign) {
            updateLeadActions($campaign);
        }
    }

    private function createLeadLog($lead, $action, $contactName)
    {
        create_log(
            $lead,
            'Add Action : '.$action->action.', initiated by Contact - '.$contactName,
            ''
        );
    }

}
