<?php

namespace App\Http\Controllers;

use App\Model\InsuranceType;
use App\Model\LeadsModel\Lead;
use App\Model\Rating;
use App\Traits\CommonFunctionsTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Controller for managing Rating CRUD operations.
 * Handles create, read, update, delete, and bulk delete operations.
 */
class RatingController extends Controller
{
    use CommonFunctionsTrait;

    /**
     * Display a listing of ratings.
     *
     * @param int $pending Pending status filter
     */
    public function index($pending = 1)
    {
        $rating = [];
        $isAdmin = auth()->user()->can('agent-create') ? 1 : 0;

        return view('rating.index', compact('rating', 'pending', 'isAdmin'));
    }

    /**
     * Convert insurance type key to snake_case column name.
     *
     * @param string $key Insurance type key
     * @param int $id Rating ID
     * @return array Result with existence info
     */
    public function convertToSnakeCase($key, $id)
    {
        $name = ! empty($this->mainInsuranceRating[$key]) ? $this->mainInsuranceRating[$key] : '';

        if (empty($name)) {
            $res = [
                'exist' => 0,
                'count' => 0,
                'name' => '',
            ];
        } else {
            $count = Lead::where($name, $id)->count();
            $res = [
                'exist' => 1,
                'count' => $count,
                'name' => $name,
            ];
        }

        return $res;
    }

    /**
     * Count lead associations for a rating.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function countLeadAssociationRating(Request $request)
    {
        $status = false;
        $totalcount = 0;
        $list = [];

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
                $value = $this->convertToSnakeCase($insuranceTypeName, $rating->id);
                $snakeCaseName = $value['name'];
                $count = $value['count'];

                // Check if rating has this insurance type
                $insurance = $rating->insuranceTypes->firstWhere('name', $insuranceTypeName);

                if ($insurance) {
                    $list[$snakeCaseName] = [
                        'rating' => $insurance->ratings()->where('ratings.status', 1)->where('ratings.id', '!=', $rating->id)->select('ratings.id', 'ratings.name')->get(),
                        'name' => $insuranceTypeName,
                        'found' => 1,
                        'count' => $count,
                    ];
                    $totalcount += $count;
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

    /**
     * Handle rating form submission with reassignment.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function ratingFormSubmission(Request $request)
    {
        $updates = $request->except('previous_id');
        $previousId = $request->input('previous_id');

        foreach ($updates as $column => $newValue) {
            if (in_array($column, $this->mainInsuranceRating)) {
                Lead::where($column, $previousId)
                    ->update([$column => $newValue]);
            }
        }

        $rating = Rating::find($previousId);
        if ($rating) {
            $rating->insuranceTypes()->detach();
            $rating->delete();
        }

        $message = empty($updates)
            ? 'Rating deleted successfully!'
            : 'Rating reassigned and deleted successfully!';

        return response()->json(['status' => true, 'message' => $message]);
    }

    /**
     * Force delete a rating.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function forceDelete(Request $request)
    {
        $rating = Rating::find($request->data_id);

        if (! $rating) {
            return response()->json(['status' => true, 'message' => "Rating doesn't exist"]);
        }

        $rating->insuranceTypes()->detach();
        $rating->delete();

        return response()->json(['status' => true, 'message' => 'Rating deleted successfully']);
    }

    /**
     * Retrieve datatable data for ratings.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function data(Request $request)
    {
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);

        $filterOnColumnNumber = $request->input('order')[0]['column'];
        $filterOnColumnName = $request->input('columns')[$filterOnColumnNumber]['data'] ?? 'id';
        $orderBy = ($filterOnColumnName == 'id') ? 'desc' : ($request->input('order')[0]['dir'] ?? 'desc');

        // Get ratings with related insurance types
        $rating = Rating::with('insuranceTypes');

        if (! empty($request->pending)) {
            $rating = $rating->where('status', $request->pending);
        }

        $totalRecords = $rating->count();

        // Apply ordering and pagination
        $rating = $rating->orderBy($filterOnColumnName, $orderBy)
            ->offset($start)
            ->limit($length);

        return datatables()->of($rating)
            ->addIndexColumn()
            ->addColumn('insurance_types', function ($rating) {
                return $rating->insuranceTypes->pluck('name')->implode(', ');
            })
            ->rawColumns(['action', 'insurance_types'])
            ->setTotalRecords($totalRecords)
            ->make(true);
    }

    /**
     * Show the form for creating a new rating.
     *
     * @param int $pending Pending status
     * @return \Illuminate\View\View
     */
    public function create($pending = 1)
    {
        $page_type = 1;
        $insurance_type = InsuranceType::where('status', 1)->where('rating', 1)->pluck('name', 'id')->toArray();
        $selected_insurance_types = [];

        return view('rating.create', compact('insurance_type', 'page_type', 'selected_insurance_types', 'pending'));
    }

    /**
     * Show the form for editing an existing rating.
     *
     * @param int $id Rating ID
     * @param int $pending Pending status
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function edit($id, $pending = 1)
    {
        $id = base64_decode($id);
        $rating = $this->findRatingOrRedirect($id, '/rating');

        if ($rating instanceof \Illuminate\Http\RedirectResponse) {
            return $rating;
        }

        $page_type = 2;
        $insurance_type = InsuranceType::where('status', 1)->where('rating', 1)->pluck('name', 'id')->toArray();
        $selected_insurance_types = $rating->insuranceTypes->pluck('id')->toArray();

        return view('rating.create', compact('rating', 'page_type', 'insurance_type', 'selected_insurance_types', 'pending'));
    }

    /**
     * Display the specified rating (read-only view).
     *
     * @param int $id Rating ID
     * @param int $pending Pending status
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show($id, $pending = 1)
    {
        $id = base64_decode($id);
        $rating = $this->findRatingOrRedirect($id, '/rating');

        if ($rating instanceof \Illuminate\Http\RedirectResponse) {
            return $rating;
        }

        $page_type = 3;
        $insurance_type = InsuranceType::where('status', 1)->where('rating', 1)->pluck('name', 'id')->toArray();
        $selected_insurance_types = $rating->insuranceTypes->pluck('id')->toArray();

        return view('rating.create', compact('rating', 'page_type', 'insurance_type', 'selected_insurance_types', 'pending'));
    }

    /**
     * Store a newly created rating in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validator = $this->validateRatingRequest($request);

        if ($validator->fails()) {
            toastr()->error($validator->errors()->first());

            return back()->withErrors($validator)->withInput();
        }

        $alreadyEntry = Rating::where('name', $request->rating_name)->first();

        if (! $alreadyEntry) {
            $rating = Rating::create(['name' => $request->rating_name]);
            $rating->insuranceTypes()->sync($request->insurance_type);
            toastr()->success('Rating created and attached to insurance types successfully.');

            return redirect()->route('rating.index');
        }

        toastr()->error('Rating Already Exists');

        return back()->withInput();
    }

    /**
     * Update the specified rating in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $rules = $this->getRatingValidationRules($request);
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            toastr()->error($validator->errors()->first());

            return back()->withErrors($validator)->withInput();
        }

        $id = $request->id;
        $rating = Rating::find($id);

        if (! $rating) {
            toastr()->error("This Rating doesn't exist");

            return redirect()->route('rating.index');
        }

        if (! empty($request->pending) && $request->pending == 1) {
            $alreadyEntry = Rating::where('name', $request->rating_name)
                ->where('id', '!=', $id)
                ->first();

            if ($alreadyEntry) {
                toastr()->error('Rating with this name already exists');

                return back()->withInput();
            }
        }

        $res_msg = 'Rating updated and attached to insurance types successfully.';
        $res_success = 1;

        $rating->name = $request->rating_name;

        if (! empty($request->pending) && $request->pending == 2) {
            $rating->status = $request->acceptance;
            if ($rating->status == 3) {
                $res_msg = 'Rating Request Rejected';
                $res_success = 2;
            }
        }

        $rating->save();

        if ($res_success == 1) {
            $rating->insuranceTypes()->sync($request->insurance_type);
        }

        toastr()->success($res_msg);

        return redirect()->route('rating.index');
    }

    /**
     * Remove the specified rating from storage.
     *
     * @param int $id Rating ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $rating = Rating::find($id);

        if (! $rating) {
            toastr()->error("This Rating doesn't exist");

            return redirect()->route('rating.index');
        }

        $rating->insuranceTypes()->detach();
        $rating->delete();

        toastr()->success('Rating deleted successfully.');

        return redirect()->route('rating.index');
    }

    /**
     * Remove multiple ratings from storage (bulk delete).
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteBulk(Request $request)
    {
        $ids = $request->input('selectedValues', []);

        if (empty($ids) || ! is_array($ids)) {
            toastr()->error('No Ratings selected for deletion.');

            return redirect()->route('rating.index');
        }

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

    // ============================================================================
    // Private Helper Methods
    // ============================================================================

    /**
     * Find rating by ID or redirect with error.
     *
     * @param int $id Rating ID
     * @param string $redirectRoute Route to redirect on failure
     * @return Rating|\Illuminate\Http\RedirectResponse
     */
    private function findRatingOrRedirect($id, string $redirectRoute)
    {
        $rating = Rating::find($id);

        if (! $rating) {
            toastr()->error('This Rating doesn\'t exist');

            return redirect($redirectRoute);
        }

        return $rating;
    }

    /**
     * Get validation rules for rating request.
     *
     * @param Request $request
     * @return array Validation rules
     */
    private function getRatingValidationRules(Request $request): array
    {
        $rules = [
            'id' => 'required',
            'rating_name' => 'required|string|max:255',
        ];

        // Add insurance_type validation unless in approval mode with rejection
        if (empty($request->pending) || $request->pending != 2 || empty($request->acceptance) || $request->acceptance != 3) {
            $rules['insurance_type'] = 'required|array';
        }

        return $rules;
    }

    /**
     * Validate rating request.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Validation\Validator
     */
    private function validateRatingRequest(Request $request)
    {
        $rules = [
            'rating_name' => 'required|string|max:255',
            'insurance_type' => 'required|array',
        ];

        return Validator::make($request->all(), $rules);
    }
}
