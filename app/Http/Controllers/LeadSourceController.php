<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;


use App\Model\LeadsModel\Log;
use Yajra\Datatables\Datatables;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Model\LeadSource;
use Illuminate\Support\Facades\Validator;

class LeadSourceController extends Controller
{
    public function index()
    {
        $rating = [];

        return view('leadsource.index', compact('rating'));
    }

    public function data(Request $request)
    {
        $start = $request->input('start', 0); // Pagination start
        $length = $request->input('length', 10); // Pagination length
        $draw = $request->input('draw', 1); // For DataTable's draw counter

        // Get sorting column number and direction
        $filter_on_column_number = $request->input('order')[0]['column'];
        $filter_on_column_name = $request->input('columns')[$filter_on_column_number]['data'] ?? 'id';
        $order_by = $request->input('order')[0]['dir'] ?? 'desc';

        // Initialize the query for LeadSource model
        $leadSourceQuery = LeadSource::where('status', 1);

        // Get total records count before applying pagination
        $totalRecords = $leadSourceQuery->count();

        // Apply ordering and pagination
        $leadSourceQuery = $leadSourceQuery->orderBy($filter_on_column_name, $order_by)
                                           ->offset($start)
                                           ->limit($length);

        // Use datatables() with the query
        return datatables($leadSourceQuery)
            ->addIndexColumn()
            ->rawColumns(['action']) // Specify any columns that should be treated as raw HTML
            ->with('draw', $draw) // Send draw counter back to DataTables
            ->with('recordsTotal', $totalRecords) // Total records before filtering
            ->with('recordsFiltered', $totalRecords) // Total records after filtering (if no filtering applied, same as totalRecords)
            ->make(true);
    }

    public function create()
    {   
        $page_type = 1;  
        return view('leadsource.create',compact('page_type'));
    }

    public function edit($id)
    {   
        // $is_admin = auth()->user()->can('agent-create');
        $id = base64_decode($id);
        $leadsource = LeadSource::find($id);
        if (!$leadsource) {

            toastr()->error('This Lead Source doesn\'t exist');
            return redirect('/leadsource');
        }
        $page_type = 2;
        return view('leadsource.create', compact('leadsource','page_type'));
    }

    public function show($id)
    {   
        $id = base64_decode($id);
        $leadsource = LeadSource::find($id);
        if (!$leadsource) {

            toastr()->error('This Lead Source doesn\'t exist');
            return redirect('/leadsource');
        }
        $page_type = 3;
        return view('leadsource.create', compact('leadsource','page_type'));
    }

    public function store(Request $request)
    {
        $rules =[
            'leadsource_name' => 'required|string|max:255',
        ];

        //validate fields using nice name in error messages
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            // $errorMessages = $validator->errors()->all();
            toastr()->error($validator->errors()->first());
            return back()->withErrors($validator)->withInput();
        }

        $alreadyEntry = LeadSource::where('name',$request->leadsource_name)->first();

        if(!$alreadyEntry){
            // Check if the rating already exists by name
            LeadSource::Create(
                ['name' => $request->leadsource_name]
            );

            toastr()->success('Lead Source created');
            return redirect()->route('leadsource.index');
 
        }
        else{
            toastr()->error( "Lead Source Already Exists");
            return back()->withInput();
        }

    }

    public function update(Request $request)
    {

        $rules = [
            'id' => 'required',
            'leadsource_name' => 'required|string|max:255',
        ];
        

        // Validate fields using nice names in error messages
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            toastr()->error($validator->errors()->first());
            return back()->withErrors($validator)->withInput();
        }

        $leadsource = LeadSource::find($request->id);
        if (!$leadsource) {
            toastr()->error("This Lead Source doesn't exist");
            return redirect()->route('leadsource.index');
        }

        $leadsourceOther = LeadSource::where('id','!=',$request->id)->where('name',$request->leadsource_name)->first();
        if($leadsourceOther){
            toastr()->error("This Lead Source with same name already exist");
            return redirect()->route('leadsource.index');
        }
        unset($leadsourceOther);


        $leadsource->name = $request->leadsource_name;
        $leadsource->save();

        toastr()->success("Lead Source Updated");
        
        return redirect()->route('leadsource.index');
    }

    public function destroy($id)
    {
        // Find the Rating by ID
        $leadsource = LeadSource::find($id);

        if (!$leadsource) {
            toastr()->error("This Lead Source doesn't exist");
            return redirect()->route('leadsource.index');
        }

        // Delete the rating
        $leadsource->delete();

        toastr()->success('Lead Source deleted successfully.');
        return redirect()->route('leadsource.index');
    }

    public function deletebulk(Request $request)
    {
        $ids = $request->input('selectedValues', []);

        // Validate that we have an array of IDs
        if (empty($ids) || !is_array($ids)) {
            toastr()->error('No Lead Source selected for deletion.');
            return redirect()->route('leadsource.index');
        }

        // Retrieve Rating to be deleted
        $leadsources = LeadSource::whereIn('id', $ids)->get();

        if ($leadsources->isEmpty()) {
            toastr()->error('No valid Lead Source found for deletion.');
            return redirect()->route('leadsource.index');
        }

        // Bulk delete ratings
        LeadSource::whereIn('id', $ids)->delete();

        toastr()->success('Selected Lead Source deleted successfully.');
        return redirect()->route('leadsource.index');
    }
}
