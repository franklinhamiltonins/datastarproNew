<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\LeadsModel\Log;
use Yajra\Datatables\Datatables;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Model\Carrier;
use App\Model\InsuranceType;
use App\Model\LeadsModel\Lead;
use Illuminate\Support\Facades\Validator;
use App\Model\LeadAdditionalPolicy;
use App\Traits\CommonFunctionsTrait;

class CarrierController extends Controller
{
    use CommonFunctionsTrait;

    public function index($pending=1)
    {
        $carrier = [];
        if(auth()->user()->can('agent-create')){
            $is_admin = 1;
        }
        else{
            $is_admin = 0;
        }

        return view('carrier.index', compact('carrier','pending','is_admin'));
    }

    public function convertToSnakeCase($key,$id) {

        $name =  !empty($this->mainInsuranceCarrier[$key])?$this->mainInsuranceCarrier[$key]:'';
        if(empty($name)){
            $count = LeadAdditionalPolicy::where('policy_type',$key)->where('carrier',$id)->count();
            $res = [
                'exist' => 1,
                'count' => $count,
                'name' => $this->additionalPoliciesCarrier[$key]
            ];
        }
        else{
            $count = Lead::where($name,$id)
                        ->count();
            $res = [
                'exist' => 1,
                'count' => $count,
                'name' => $name
            ];
        }

        return $res;

    }

    public function countLeadAssociation(Request $request)
    {
        $status = false;
        $totalcount = 0;
        $list = [];

        $carrier = Carrier::with('insuranceTypes:id,name')
            ->where('carriers.id', $request->data_id)
            ->first();

        if ($carrier) {
            $status = true;
            
            // Get active insurance types linked to a carrier
            $insuranceTypes = InsuranceType::where('status', 1)
                ->where('carrier', 1)
                ->pluck('name', 'id')
                ->toArray();

            foreach ($insuranceTypes as $key => $insuranceTypeName) {
                $value = $this->convertToSnakeCase($insuranceTypeName,$carrier->id);
                // echo "<pre>";print_r($value);echo $insuranceTypeName; exit;
                $snakeCaseName = $value['name'];
                $count = $value['count'];

                // Check if carrier has this insurance type
                $insurance = $carrier->insuranceTypes->firstWhere('name', $insuranceTypeName);

                if ($insurance) {
                    $list[$snakeCaseName] = [
                        'carrier' => $insurance->carriers()->where('carriers.status', 1)->where('carriers.id', '!=',$carrier->id)->select('carriers.id','carriers.name')->get(),
                        'name' => $insuranceTypeName,
                        'found' => 1,
                        'count' => $count,
                    ];
                    $totalcount += $count; // Add to total count
                } else {
                    $list[$snakeCaseName] = [
                        'carrier' => [],
                        'name' => $insuranceTypeName,
                        'found' => 0,
                        'count' => 0,
                    ];
                }
            }
        }

        return response()->json([
            'status' => $status,
            'totalcount' => $totalcount,
            'list' => $list,
        ]);
    }

    public function carrierFormSubmission(Request $request)
    {
        $updates = $request->except('previous_id'); 
        $previousId = $request->input('previous_id');

        foreach ($updates as $column => $newValue) {
            if (in_array($column, $this->mainInsuranceCarrier)) {
                Lead::where($column, $previousId) // Check where column has previous_id
                    ->update([$column => $newValue]); // Assign new value
            }
            else{
                $key = array_search($column, $this->additionalPoliciesCarrier);

                if(!empty($key)){
                    LeadAdditionalPolicy::where('policy_type',$key)->where('carrier',$previousId)
                    ->update(['carrier' => $newValue]);
                }
            }
        }

        $carrier = Carrier::find($previousId);
        if($carrier){
            $carrier->insuranceTypes()->detach();

            // Delete the carrier
            $carrier->delete();
        }

        if(empty($updates)){
            $message = "Carrier deleted successfully!";
        }
        else{
            $message = "Carrier reassigned and deleted successfully!";
        }

        return response()->json(['status' => true, 'message' => $message]);
    }

    public function forceDelete(Request $request)
    {
        $carrier = Carrier::find($request->data_id);

        if (!$carrier) {
            // toastr()->error("This Carrier doesn't exist");
            return response()->json(['status' => true, 'message' => "Carrier doesn't exist"]);
        }

        // Detach associated insurance types
        $carrier->insuranceTypes()->detach();

        // Delete the carrier
        $carrier->delete();

        return response()->json(['status' => true, 'message' => 'Carrier deleted successfully']);
    }

    public function data(Request $request)
    {
        $start = $request->input('start', 0); // Pagination start
        $length = $request->input('length', 10); // Pagination length
        $draw = $request->input('draw', 1); // For DataTable's draw counter

        $filter_on_column_number = $request->input('order')[0]['column'];
        $filter_on_column_name = $request->input('columns')[$filter_on_column_number]['data'] ?? 'id';

        if($filter_on_column_name == "id"){
            $order_by = 'desc';
        }
        else{
            $order_by = $request->input('order')[0]['dir'] ?? 'desc';
        }

        // Get carriers with related insurance types
        $carrier = Carrier::with('insuranceTypes');

        if (!empty($request->pending)) {
            $carrier = $carrier->where('status', $request->pending);
        }

        $totalRecords = $carrier->count(); // Get total records for pagination

        // Apply ordering and pagination
        $carrier = $carrier->orderBy($filter_on_column_name, $order_by)
                           ->offset($start)
                           ->limit($length);

        return datatables()->of($carrier)
            ->addIndexColumn()
            ->addColumn('insurance_types', function ($carrier) {
                // Get all related insurance type names as a comma-separated string
                return $carrier->insuranceTypes->pluck('name')->implode(', ');
            })
            ->rawColumns(['action', 'insurance_types']) // Include insurance_types as a raw column
            ->setTotalRecords($totalRecords) // Set the total records count for pagination
            ->make(true);
    }


    public function create($pending=1)
    {   
        $page_type = 1;  
        $insurance_type = InsuranceType::where('status',1)->where('carrier',1)->pluck('name', 'id')->toArray();

        // echo "<pre>";
        // print_r($insurance_type);exit;
        $selected_insurance_types = [];
        return view('carrier.create',compact('insurance_type','page_type','selected_insurance_types','pending'));
    }

    public function edit($id,$pending=1)
    {   
        // $is_admin = auth()->user()->can('agent-create');
        $id = base64_decode($id);
        $carrier = Carrier::find($id);
        if (!$carrier) {

            toastr()->error('This Carrier doesn\'t exist');
            return redirect('/carrier');
        }
        $page_type = 2;
        $insurance_type = InsuranceType::where('status',1)->where('carrier',1)->pluck('name', 'id')->toArray();
        $selected_insurance_types = $carrier->insuranceTypes->pluck('id')->toArray();
        return view('carrier.create', compact('carrier','page_type','insurance_type','selected_insurance_types','pending'));
    }

    public function show($id,$pending=1)
    {   
        // $is_admin = auth()->user()->can('agent-create');
        $id = base64_decode($id);
        $carrier = Carrier::find($id);
        if (!$carrier) {

            toastr()->error('This Carrier doesn\'t exist');
            return redirect('/carrier');
        }
        $page_type = 3;
        $insurance_type = InsuranceType::where('status',1)->where('carrier',1)->pluck('name', 'id')->toArray();
        $selected_insurance_types = $carrier->insuranceTypes->pluck('id')->toArray();
        return view('carrier.create', compact('carrier','page_type','insurance_type','selected_insurance_types','pending'));
    }

    public function store(Request $request)
    {
        $rules =[
            'carrier_name' => 'required|string|max:255',
            'insurance_type' => 'required|array',
        ];

        //validate fields using nice name in error messages
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            // $errorMessages = $validator->errors()->all();
            toastr()->error($validator->errors()->first());
            return back()->withErrors($validator)->withInput();
        }

        $alreadyEntry = Carrier::where('name',$request->carrier_name)->first();

        if(!$alreadyEntry){
            // Check if the carrier already exists by name
            $carrier = Carrier::Create(
                ['name' => $request->carrier_name]
            );

            // Attach the carrier to the provided insurance types
            $carrier->insuranceTypes()->sync($request->insurance_type);
            toastr()->success('Carrier created and attached to insurance types successfully.');
            return redirect()->route('carrier.index');
 
        }
        else{
            toastr()->error( "Carrier Already Exists");
            return back()->withInput();
        }

    }

    public function update(Request $request)
    {

        if(!empty($request->pending) && $request->pending == 2 && !empty($request->acceptance) && $request->acceptance == 3){
            $rules = [
                'id' => 'required',
                'carrier_name' => 'required|string|max:255',
            ];
        }
        else{
            $rules = [
                'id' => 'required',
                'carrier_name' => 'required|string|max:255',
                'insurance_type' => 'required|array',
            ];
        }
        

        // Validate fields using nice names in error messages
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            toastr()->error($validator->errors()->first());
            return back()->withErrors($validator)->withInput();
        }

        // Find the carrier by ID or return error if it doesn't exist
        $id = $request->id;
        $carrier = Carrier::find($id);
        if (!$carrier) {
            toastr()->error("This Carrier doesn't exist");
            return redirect()->route('carrier.index');
        }

        if(!empty($request->pending) && $request->pending == 1){
            // Check if another carrier with the same name exists
            $alreadyEntry = Carrier::where('name', $request->carrier_name)
                                ->where('id', '!=', $id)
                                ->first();
            if ($alreadyEntry) {
                toastr()->error("Carrier with this name already exists");
                return back()->withInput();
            }
        }
        $res_msg = 'Carrier updated and attached to insurance types successfully.';
        $res_success = 1;
        $carrier->name = $request->carrier_name;
        if(!empty($request->pending) && $request->pending == 2){
            $carrier->status = $request->acceptance;
            if($carrier->status == 3){
                $res_msg = 'Carrier Request Rejected';
                $res_success = 2;
            }
        }
        $carrier->save();

        // echo "<pre>";
        // print_r($carrier);
        // exit;
        

        if($res_success == 1){
            // Sync the carrier with the provided insurance types
            $carrier->insuranceTypes()->sync($request->insurance_type);
            toastr()->success($res_msg);
        }
        else{
            toastr()->success($res_msg);
        }
        return redirect()->route('carrier.index');
    }

    public function destroy($id)
    {
        // Find the carrier by ID
        $carrier = Carrier::find($id);

        if (!$carrier) {
            toastr()->error("This Carrier doesn't exist");
            return redirect()->route('carrier.index');
        }

        // Detach associated insurance types
        $carrier->insuranceTypes()->detach();

        // Delete the carrier
        $carrier->delete();

        toastr()->success('Carrier deleted successfully.');
        return redirect()->route('carrier.index');
    }

    public function deletebulk(Request $request)
    {
        $ids = $request->input('selectedValues', []);

        // Validate that we have an array of IDs
        if (empty($ids) || !is_array($ids)) {
            toastr()->error('No carriers selected for deletion.');
            return redirect()->route('carrier.index');
        }

        // Retrieve carriers to be deleted
        $carriers = Carrier::whereIn('id', $ids)->get();

        if ($carriers->isEmpty()) {
            toastr()->error('No valid carriers found for deletion.');
            return redirect()->route('carrier.index');
        }

        // Detach associated insurance types
        foreach ($carriers as $carrier) {
            $carrier->insuranceTypes()->detach();
        }

        // Bulk delete carriers
        Carrier::whereIn('id', $ids)->delete();

        toastr()->success('Selected carriers deleted successfully.');
        return redirect()->route('carrier.index');
    }

}
