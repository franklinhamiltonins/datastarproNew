<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Model\LeadsModel\Log;
use Yajra\Datatables\Datatables;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Model\Rating;
use App\Model\InsuranceType;
use App\Model\LeadsModel\Lead;
use Illuminate\Support\Facades\Validator;
use App\Traits\CommonFunctionsTrait;

class RatingController extends Controller
{
    use CommonFunctionsTrait;
    public function index($pending=1)
    {
        $rating = [];
        if(auth()->user()->can('agent-create')){
            $is_admin = 1;
        }
        else{
            $is_admin = 0;
        }

        return view('rating.index', compact('rating','pending','is_admin'));
    }

    public function convertToSnakeCase($key,$id) {

        $name =  !empty($this->mainInsuranceRating[$key])?$this->mainInsuranceRating[$key]:'';
        if(empty($name)){
            $res = [
                'exist' => 0,
                'count' => 0,
                'name' => '',
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

        // echo "<pre>";print_r($res);print_r($id);exit;

        return $res;

    }

    public function countLeadAssociationRating(Request $request)
    {
        $status = false;
        $totalcount = 0;
        $list = [];
        // echo "<pre>";print_r($request->input());exit;

        $rating = Rating::with('insuranceTypes:id,name')
            ->where('ratings.id', $request->data_id)
            ->first();

        if ($rating) {
            $status = true;
            
            // Get active insurance types linked to a rating
            $insuranceTypes = InsuranceType::where('status', 1)
                ->where('rating', 1)
                ->pluck('name', 'id')
                ->toArray();

            foreach ($insuranceTypes as $key => $insuranceTypeName) {
                $value = $this->convertToSnakeCase($insuranceTypeName,$rating->id);
                // echo "<pre>";print_r($value);echo $insuranceTypeName; exit;
                $snakeCaseName = $value['name'];
                $count = $value['count'];

                // Check if rating has this insurance type
                $insurance = $rating->insuranceTypes->firstWhere('name', $insuranceTypeName);

                if ($insurance) {
                    $list[$snakeCaseName] = [
                        'rating' => $insurance->ratings()->where('ratings.status', 1)->where('ratings.id', '!=',$rating->id)->select('ratings.id','ratings.name')->get(),
                        'name' => $insuranceTypeName,
                        'found' => 1,
                        'count' => $count,
                    ];
                    $totalcount += $count; // Add to total count
                } else {
                    $list[$snakeCaseName] = [
                        'rating' => [],
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

    public function ratingFormSubmission(Request $request)
    {
        $updates = $request->except('previous_id'); 
        $previousId = $request->input('previous_id');

        foreach ($updates as $column => $newValue) {
            if (in_array($column, $this->mainInsuranceRating)) {
                Lead::where($column, $previousId) // Check where column has previous_id
                    ->update([$column => $newValue]); // Assign new value
            }
        }

        $rating = Rating::find($previousId);
        if($rating){
            $rating->insuranceTypes()->detach();

            // Delete the rating
            $rating->delete();
        }

        if(empty($updates)){
            $message = "Rating deleted successfully!";
        }
        else{
            $message = "Rating reassigned and deleted successfully!";
        }
        return response()->json(['status' => true, 'message' => $message ]);
    }

    public function forceDelete(Request $request)
    {
        $rating = Rating::find($request->data_id);

        if (!$rating) {
            // toastr()->error("This Rating doesn't exist");
            return response()->json(['status' => true, 'message' => "Rating doesn't exist"]);
        }

        // Detach associated insurance types
        $rating->insuranceTypes()->detach();

        // Delete the rating
        $rating->delete();

        return response()->json(['status' => true, 'message' => 'Rating deleted successfully']);
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

        // Get ratings with related insurance types
        $rating = Rating::with('insuranceTypes');

        if (!empty($request->pending)) {
            $rating = $rating->where('status', $request->pending);
        }

        $totalRecords = $rating->count(); // Get total records for pagination

        // Apply ordering and pagination
        $rating = $rating->orderBy($filter_on_column_name, $order_by)
                           ->offset($start)
                           ->limit($length);

        return datatables()->of($rating)
            ->addIndexColumn()
            ->addColumn('insurance_types', function ($rating) {
                // Get all related insurance type names as a comma-separated string
                return $rating->insuranceTypes->pluck('name')->implode(', ');
            })
            ->rawColumns(['action', 'insurance_types']) // Include insurance_types as a raw column
            ->setTotalRecords($totalRecords) // Set the total records count for pagination
            ->make(true);
    }


    public function create($pending=1)
    {   
        $page_type = 1;  
        $insurance_type = InsuranceType::where('status',1)->where('rating',1)->pluck('name', 'id')->toArray();

        // echo "<pre>";
        // print_r($insurance_type);exit;
        $selected_insurance_types = [];
        return view('rating.create',compact('insurance_type','page_type','selected_insurance_types','pending'));
    }

    public function edit($id,$pending=1)
    {   
        // $is_admin = auth()->user()->can('agent-create');
        $id = base64_decode($id);
        $rating = Rating::find($id);
        if (!$rating) {

            toastr()->error('This Rating doesn\'t exist');
            return redirect('/rating');
        }
        $page_type = 2;
        $insurance_type = InsuranceType::where('status',1)->where('rating',1)->pluck('name', 'id')->toArray();
        $selected_insurance_types = $rating->insuranceTypes->pluck('id')->toArray();
        return view('rating.create', compact('rating','page_type','insurance_type','selected_insurance_types','pending'));
    }

    public function show($id,$pending=1)
    {   
        // $is_admin = auth()->user()->can('agent-create');
        $id = base64_decode($id);
        $rating = Rating::find($id);
        if (!$rating) {

            toastr()->error('This Rating doesn\'t exist');
            return redirect('/rating');
        }
        $page_type = 3;
        $insurance_type = InsuranceType::where('status',1)->where('rating',1)->pluck('name', 'id')->toArray();
        $selected_insurance_types = $rating->insuranceTypes->pluck('id')->toArray();
        return view('rating.create', compact('rating','page_type','insurance_type','selected_insurance_types','pending'));
    }

    public function store(Request $request)
    {
        $rules =[
            'rating_name' => 'required|string|max:255',
            'insurance_type' => 'required|array',
        ];

        //validate fields using nice name in error messages
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            // $errorMessages = $validator->errors()->all();
            toastr()->error($validator->errors()->first());
            return back()->withErrors($validator)->withInput();
        }

        $alreadyEntry = Rating::where('name',$request->rating_name)->first();

        if(!$alreadyEntry){
            // Check if the rating already exists by name
            $rating = Rating::Create(
                ['name' => $request->rating_name]
            );

            // Attach the rating to the provided insurance types
            $rating->insuranceTypes()->sync($request->insurance_type);
            toastr()->success('Rating created and attached to insurance types successfully.');
            return redirect()->route('rating.index');
 
        }
        else{
            toastr()->error( "Rating Already Exists");
            return back()->withInput();
        }

    }

    public function update(Request $request)
    {

        if(!empty($request->pending) && $request->pending == 2 && !empty($request->acceptance) && $request->acceptance == 3){
            $rules = [
                'id' => 'required',
                'rating_name' => 'required|string|max:255',
            ];
        }
        else{
            $rules = [
                'id' => 'required',
                'rating_name' => 'required|string|max:255',
                'insurance_type' => 'required|array',
            ];
        }
        

        // Validate fields using nice names in error messages
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            toastr()->error($validator->errors()->first());
            return back()->withErrors($validator)->withInput();
        }

        // Find the rating by ID or return error if it doesn't exist
        $id = $request->id;
        $rating = Rating::find($id);
        if (!$rating) {
            toastr()->error("This Rating doesn't exist");
            return redirect()->route('rating.index');
        }

        if(!empty($request->pending) && $request->pending == 1){
            // Check if another Rating with the same name exists
            $alreadyEntry = Rating::where('name', $request->rating_name)
                                ->where('id', '!=', $id)
                                ->first();
            if ($alreadyEntry) {
                toastr()->error("Rating with this name already exists");
                return back()->withInput();
            }
        }
        $res_msg = 'Rating updated and attached to insurance types successfully.';
        $res_success = 1;
        $rating->name = $request->rating_name;
        if(!empty($request->pending) && $request->pending == 2){
            $rating->status = $request->acceptance;
            if($rating->status == 3){
                $res_msg = 'Rating Request Rejected';
                $res_success = 2;
            }
        }
        $rating->save();

        // echo "<pre>";
        // print_r($rating);
        // exit;
        

        if($res_success == 1){
            // Sync the rating with the provided insurance types
            $rating->insuranceTypes()->sync($request->insurance_type);
            toastr()->success($res_msg);
        }
        else{
            toastr()->success($res_msg);
        }
        return redirect()->route('rating.index');
    }

    public function destroy($id)
    {
        // Find the Rating by ID
        $rating = Rating::find($id);

        if (!$rating) {
            toastr()->error("This Rating doesn't exist");
            return redirect()->route('rating.index');
        }

        // Detach associated insurance types
        $rating->insuranceTypes()->detach();

        // Delete the rating
        $rating->delete();

        toastr()->success('Rating deleted successfully.');
        return redirect()->route('rating.index');
    }

    public function deletebulk(Request $request)
    {
        $ids = $request->input('selectedValues', []);

        // Validate that we have an array of IDs
        if (empty($ids) || !is_array($ids)) {
            toastr()->error('No Ratings selected for deletion.');
            return redirect()->route('rating.index');
        }

        // Retrieve Rating to be deleted
        $ratings = Rating::whereIn('id', $ids)->get();

        if ($ratings->isEmpty()) {
            toastr()->error('No valid Ratings found for deletion.');
            return redirect()->route('rating.index');
        }

        // Detach associated insurance types
        foreach ($ratings as $rating) {
            $rating->insuranceTypes()->detach();
        }

        // Bulk delete ratings
        Rating::whereIn('id', $ids)->delete();

        toastr()->success('Selected ratings deleted successfully.');
        return redirect()->route('rating.index');
    }

}
