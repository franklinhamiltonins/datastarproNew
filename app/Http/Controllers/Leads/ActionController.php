<?php

namespace App\Http\Controllers\Leads;

use App\Model\LeadsModel\Contact;
use App\Model\LeadsModel\Action;
use App\Model\LeadsModel\Lead;
use App\Model\Campaign;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ActionController extends Controller
{

    function __construct()
    {

        $this->middleware('permission:lead-action', ['only' => ['add_action','index','get_contact_report']]);

    }

     /**
     * Show Lead Contact Report page.
     *
     * @return \Illuminate\Http\Response
     */

    public function index(){

        $actions = Action::all();

        //$actionsQuery =  Action::query()->with('campaigns','leads')->rightJoin('contacts', 'actions.contact_id', '=', 'contacts.id')->get();


        return view('actions.index',compact('actions'));
    }

    /**
     * display data table
     */
    public function get_contact_report(Request $request){
        $actionsQuery = Action::with('campaigns','leads')->crossJoin('contacts', function($join) {
            $join->on('actions.contact_id', '=', 'contacts.id');
        });


        $start_date = !empty($request->filters['startDate']) ? $request->filters['startDate'] : '';
        $end_date = !empty($request->filters['endDate']) ? $request->filters['endDate'] : '';

        if($start_date && $end_date){

            $start_date = date('Y-m-d', strtotime($start_date));
            $end_date = date('Y-m-d', strtotime($end_date));

            $actionsQuery->whereRaw("date(actions.contact_date) >= '" . $start_date . "' AND date(actions.contact_date) <= '" . $end_date . "'");
        }

    $table = $actionsQuery->select('actions.*','contacts.c_phone','contacts.c_email','contacts.c_is_client')->orderBy('contact_date','desc');

       return datatables()->of( $table)
       ->addIndexColumn()

        ->editColumn('leads', function (Action $action) {
            return $action->leads()->pluck('name')->implode('<br>');
        })
        ->editColumn('contacts', function ($action) {

        return $action->c_phone;

        })
        ->editColumn('email', function ($action) {

            return $action->c_email;

        })
        ->editColumn('campaigns', function (Action $action) {
            return $action->campaigns()->pluck('name')->implode('<br>');

        })

        ->addColumn('c_is_client', function (Action $action) {
            return $action->c_is_client? 'yes' : 'no';

        })
        ->editColumn('contact_date', function ($actionsQuery ){
            if(isset($actionsQuery ->contact_date)){//Fix for removing default date(01/01/1970) that Yajra adds to table when doesn't find any date in db
            return date('m/d/Y', strtotime($actionsQuery ->contact_date) );
            }
        })
        ->filterColumn('contact_date', function ($query, $keyword) {
            $query->whereRaw("DATE_FORMAT(contact_date,'%m/%d/%Y') like ?", ["%$keyword%"]);
        })


       ->make(true);


    }

    /**
       * Create actions.
       *
       * @param  int  $id
       * @return \Illuminate\Http\Response
       */

    public function add_action(Request $request,$id)
    {
        $rules = [

            'action'=> 'required',
            'contact_date'=> 'required',

        ];
        $niceNames = [
            'action'=> 'Action',

        ];


        //validate fields using nice name in error messages
        $this->validate($request, $rules, [], $niceNames);

        if(!$request->contact_id && $request->contact_name ){ //if there is not ID , but is contact name from other imput

        $contactName =$request->contact_name;

        } else if(!$request->contact_id && !$request->contact_name){ //if there is not contact_id and contact_name

            toastr()->error('Please select or add Contact');
            return redirect()->back();

        }else if($request->contact_id){// if there is contact_id

            $contact = Contact::find($request->contact_id);

            $contactName= $contact->c_first_name. ' ' .$contact->c_last_name;
        }
        $input = $request->all();


        //create new contact
        $action = Action::create($input);


        //get the lead where the contact was added
        $lead= Lead::find($id);

        //attach the contact to lead
        $action->leads()->associate($lead);
        $action->campaigns()->associate($request->campaign_id);
        $action->save();
        // $campaignsChanged = collect();

        //get the lead campaigns where the campaign date is bigger than (action contact_date date - 11 days)
        $campaings = Campaign::whereHas('leads',function ($query) use($id){
            $query->where('lead_id',$id);
        })->where('campaign_date','>=',date('Y-m-d',strtotime($action->contact_date.'-11 days')))->get();


        //foreach campaign update lead_actions, adding this action
        foreach($campaings as $campaign){

                update_leadActions($campaign);

                // $campaignsChanged->push($campaign->id. ' - ' .$campaign->name );

        }

        create_log( $lead, 'Add Action  : '. $action->action. ', initiated by Contact - '.$contactName.' ','');
        toastr()->success('Action <b>' . $action->action.'</b>, initiated by Contact: <b>'.$contactName.'</b> created successfully');
        return redirect()->back();
    }



}
