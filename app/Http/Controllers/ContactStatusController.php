<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\LeadsModel\Lead;
use App\Model\LeadsModel\Contact;
use App\Model\LeadsModel\Log;
use Yajra\Datatables\Datatables;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Model\ContactStatus;
use Illuminate\Support\Facades\Validator;

class ContactStatusController extends Controller
{
    public function index()
    {
        $contactstatus = [];

       

        return view('contactstatus.index', compact('contactstatus'));
    }

    public function edit($id)
    {   
        // $is_admin = auth()->user()->can('agent-create');
        $id = base64_decode($id);
        $ContactStatus = ContactStatus::find($id);
        if (!$ContactStatus) {

            toastr()->error('This Contact Status doesn\'t exist');
            return redirect('/smsprovider');
        }
        $page_type = 2;
        return view('contactstatus.create', compact('ContactStatus','page_type','id'));
    }

    public function show($id)
    {   
        // $is_admin = auth()->user()->can('agent-create');
        $id = base64_decode($id);
        $ContactStatus = ContactStatus::find($id);
        if (!$ContactStatus) {

            toastr()->error('This Contact Status doesn\'t exist');
            return redirect('/smsprovider');
        }
        $page_type = 3;
        return view('contactstatus.create', compact('ContactStatus','page_type','id'));
    }

    public function data(Request $request)
    {
        $start = $request->input('start', 0);
        $length = $request->input('length', 10); // Default length or adjust as needed
        $draw = $request->input('draw', 1);
        $filter_on_column_number = $request->input('order')[0]['column'];
        $filter_on_column_name = $request->input('columns')[$filter_on_column_number]['data'] ?? 'id';
        $order_by = $request->input('order')[0]['dir'] ?? 'desc';

        $contactstatus = ContactStatus::
        orderBy($filter_on_column_name, $order_by)
        ->offset($start)
        ->limit($length);

        return datatables()->of( $contactstatus)
        ->addIndexColumn()
        ->rawColumns(['action'])
        // ->setTotalRecords($totalRecords)
        ->make(true);
    }

    public function destroy($id)
    {
        //find the smsprovider to delete
        $contactstatus = ContactStatus::find($id);
        if (!$contactstatus) {

            toastr()->error('The Contact Status was removed previously');
            return back();
        }
        $contactstatus->delete();
        // toastr()->success('SmsProvider <b>' . $smsprovider->smsprovider_name . '</b> Deleted!');
        toastr()->success('Contact Status Deleted!');
        return  redirect()->route('contactstatus.index');
    }

    public function deletebulk(Request $request)
    {
        $smsproviderIds = $request->selectedValues;
        if (count($smsproviderIds) <= 0) :
            return response()->json(['smsproviderCount' => 0, 'message' => 'Please check at least one checkbox to continue.']);
        endif;

        ContactStatus::whereIn('id', $smsproviderIds)->delete();

        return response()->json(['smsproviderCount' => 1, 'message' => 'Records deleted successfully']);
    }

    public function create()
    {   
        $page_type = 1;  
        $ContactStatus = [];  
        $id = 0;
        // return view('smsprovider.create', compact('is_admin','agent_users'));
        return view('contactstatus.create',compact('ContactStatus','page_type','id'));
    }

    public function store(Request $request)
    {
        $rules =[
            'status_name' => 'required|max:100',
            'priority' => 'required|max:100'
        ];

        //validate fields using nice name in error messages
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $errorMessages = $validator->errors()->all();
            toastr()->error(implode('<br>', $errorMessages));
            return back()->withErrors($validator)->withInput();
        }

        $input = [
            "name" => $request->status_name,
            "priority" => $request->priority,
            "false_status" => !empty($request->false_status)?1:0,
            "display_in_pipedrive" => !empty($request->display_in_pipedrive)?1:0,
            "status_type" => !empty($request->status_type)?$request->status_type:null,
        ];


        // $input['text'] = strip_tags(trim($input['text']));
        $smsprovider = ContactStatus::create($input);

        toastr()->success('Contact Status created successfully');
        return redirect()->route('contactstatus.index');
    }

    public function update(Request $request)
    {
        $rules =[
            'status_name' => 'required|max:100',
            'priority' => 'required|max:100',
            'id' => 'required|max:100'
        ];

        // echo "<pre>";print_r($request->input());exit;

        //validate fields using nice name in error messages
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $errorMessages = $validator->errors()->all();
            toastr()->error(implode('<br>', $errorMessages));
            return back()->withErrors($validator)->withInput();
        }

        $input = [
            "name" => $request->status_name,
            "priority" => $request->priority,
            "false_status" => !empty($request->false_status)?1:0,
            "display_in_pipedrive" => !empty($request->display_in_pipedrive)?1:0,
            "status_type" => !empty($request->status_type)?$request->status_type:null,
        ];


        // $input['text'] = strip_tags(trim($input['text']));
        ContactStatus::where('id',$request->id)->update($input);

        toastr()->success('Contact Status Updated successfully');
        return redirect()->route('contactstatus.index');
    }

}
