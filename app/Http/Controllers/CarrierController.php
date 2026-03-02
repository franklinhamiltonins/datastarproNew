<?php

namespace App\Http\Controllers;

use App\Model\Carrier;
use App\Model\InsuranceType;
use App\Model\LeadAdditionalPolicy;
use App\Model\LeadsModel\Lead;
use App\Traits\CommonFunctionsTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Controller for managing Carrier CRUD operations.
 * Handles create, read, update, delete, and bulk delete operations.
 */
class CarrierController extends Controller
{
    use CommonFunctionsTrait;

    /**
     * Display a listing of carriers.
     *
     * @param int $pending Pending status filter
     */
    public function index($pending = 1)
    {
        $carrier = [];
        $isAdmin = auth()->user()->can('agent-create') ? 1 : 0;

        return view('carrier.index', compact('carrier', 'pending', 'isAdmin'));
    }

    /**
     * Convert insurance type key to snake_case column name.
     *
     * @param string $key Insurance type key
     * @param int $id Carrier ID
     * @return array Result with existence info
     */
    public function convertToSnakeCase($key, $id)
    {
        $name = ! empty($this->mainInsuranceCarrier[$key]) ? $this->mainInsuranceCarrier[$key] : '';

        if (empty($name)) {
            $count = LeadAdditionalPolicy::where('policy_type', $key)->where('carrier', $id)->count();
            $res = [
                'exist' => 1,
                'count' => $count,
                'name' => $this->additionalPoliciesCarrier[$key],
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
     * Count lead associations for a carrier.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
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

            foreach ($insuranceTypes as $insuranceTypeName) {
                $value = $this->convertToSnakeCase($insuranceTypeName, $carrier->id);
                $snakeCaseName = $value['name'];
                $count = $value['count'];

                // Check if carrier has this insurance type
                $insurance = $carrier->insuranceTypes->firstWhere('name', $insuranceTypeName);

                if ($insurance) {
                    $list[$snakeCaseName] = [
                        'carrier' => $insurance->carriers()->where('carriers.status', 1)->where('carriers.id', '!=', $carrier->id)->select('carriers.id', 'carriers.name')->get(),
                        'name' => $insuranceTypeName,
                        'found' => 1,
                        'count' => $count,
                    ];
                    $totalcount += $count;
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

    /**
     * Handle carrier form submission with reassignment.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function carrierFormSubmission(Request $request)
    {
        $updates = $request->except('previous_id');
        $previousId = $request->input('previous_id');

        foreach ($updates as $column => $newValue) {
            if (in_array($column, $this->mainInsuranceCarrier)) {
                Lead::where($column, $previousId)
                    ->update([$column => $newValue]);
            } else {
                $key = array_search($column, $this->additionalPoliciesCarrier);

                if (! empty($key)) {
                    LeadAdditionalPolicy::where('policy_type', $key)->where('carrier', $previousId)
                        ->update(['carrier' => $newValue]);
                }
            }
        }

        $carrier = Carrier::find($previousId);
        if ($carrier) {
            $carrier->insuranceTypes()->detach();
            $carrier->delete();
        }

        $message = empty($updates)
            ? 'Carrier deleted successfully!'
            : 'Carrier reassigned and deleted successfully!';

        return response()->json(['status' => true, 'message' => $message]);
    }

    /**
     * Force delete a carrier.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function forceDelete(Request $request)
    {
        $carrier = Carrier::find($request->data_id);

        if (! $carrier) {
            return response()->json(['status' => true, 'message' => "Carrier doesn't exist"]);
        }

        $carrier->insuranceTypes()->detach();
        $carrier->delete();

        return response()->json(['status' => true, 'message' => 'Carrier deleted successfully']);
    }

    /**
     * Retrieve datatable data for carriers.
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

        // Get carriers with related insurance types
        $carrier = Carrier::with('insuranceTypes');

        if (! empty($request->pending)) {
            $carrier = $carrier->where('status', $request->pending);
        }

        $totalRecords = $carrier->count();

        // Apply ordering and pagination
        $carrier = $carrier->orderBy($filterOnColumnName, $orderBy)
            ->offset($start)
            ->limit($length);

        return datatables()->of($carrier)
            ->addIndexColumn()
            ->addColumn('insurance_types', function ($carrier) {
                return $carrier->insuranceTypes->pluck('name')->implode(', ');
            })
            ->rawColumns(['action', 'insurance_types'])
            ->setTotalRecords($totalRecords)
            ->make(true);
    }

    /**
     * Show the form for creating a new carrier.
     *
     * @param int $pending Pending status
     * @return \Illuminate\View\View
     */
    public function create($pending = 1)
    {
        $page_type = 1;
        $insurance_type = InsuranceType::where('status', 1)->where('carrier', 1)->pluck('name', 'id')->toArray();
        $selected_insurance_types = [];

        return view('carrier.create', compact('insurance_type', 'page_type', 'selected_insurance_types', 'pending'));
    }

    /**
     * Show the form for editing an existing carrier.
     *
     * @param int $id Carrier ID
     * @param int $pending Pending status
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function edit($id, $pending = 1)
    {
        $id = base64_decode($id);
        $carrier = $this->findCarrierOrRedirect($id, '/carrier');

        if ($carrier instanceof \Illuminate\Http\RedirectResponse) {
            return $carrier;
        }

        $page_type = 2;
        $insurance_type = InsuranceType::where('status', 1)->where('carrier', 1)->pluck('name', 'id')->toArray();
        $selected_insurance_types = $carrier->insuranceTypes->pluck('id')->toArray();

        return view('carrier.create', compact('carrier', 'page_type', 'insurance_type', 'selected_insurance_types', 'pending'));
    }

    /**
     * Display the specified carrier (read-only view).
     *
     * @param int $id Carrier ID
     * @param int $pending Pending status
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show($id, $pending = 1)
    {
        $id = base64_decode($id);
        $carrier = $this->findCarrierOrRedirect($id, '/carrier');

        if ($carrier instanceof \Illuminate\Http\RedirectResponse) {
            return $carrier;
        }

        $page_type = 3;
        $insurance_type = InsuranceType::where('status', 1)->where('carrier', 1)->pluck('name', 'id')->toArray();
        $selected_insurance_types = $carrier->insuranceTypes->pluck('id')->toArray();

        return view('carrier.create', compact('carrier', 'page_type', 'insurance_type', 'selected_insurance_types', 'pending'));
    }

    /**
     * Store a newly created carrier in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validator = $this->validateCarrierRequest($request);

        if ($validator->fails()) {
            toastr()->error($validator->errors()->first());

            return back()->withErrors($validator)->withInput();
        }

        $alreadyEntry = Carrier::where('name', $request->carrier_name)->first();

        if (! $alreadyEntry) {
            $carrier = Carrier::create(['name' => $request->carrier_name]);
            $carrier->insuranceTypes()->sync($request->insurance_type);
            toastr()->success('Carrier created and attached to insurance types successfully.');

            return redirect()->route('carrier.index');
        }

        toastr()->error('Carrier Already Exists');

        return back()->withInput();
    }

    /**
     * Update the specified carrier in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $rules = $this->getCarrierValidationRules($request);
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            toastr()->error($validator->errors()->first());

            return back()->withErrors($validator)->withInput();
        }

        $id = $request->id;
        $carrier = Carrier::find($id);

        if (! $carrier) {
            toastr()->error("This Carrier doesn't exist");

            return redirect()->route('carrier.index');
        }

        if (! empty($request->pending) && $request->pending == 1) {
            $alreadyEntry = Carrier::where('name', $request->carrier_name)
                ->where('id', '!=', $id)
                ->first();

            if ($alreadyEntry) {
                toastr()->error('Carrier with this name already exists');

                return back()->withInput();
            }
        }

        $res_msg = 'Carrier updated and attached to insurance types successfully.';
        $res_success = 1;

        $carrier->name = $request->carrier_name;

        if (! empty($request->pending) && $request->pending == 2) {
            $carrier->status = $request->acceptance;
            if ($carrier->status == 3) {
                $res_msg = 'Carrier Request Rejected';
                $res_success = 2;
            }
        }

        $carrier->save();

        if ($res_success == 1) {
            $carrier->insuranceTypes()->sync($request->insurance_type);
        }

        toastr()->success($res_msg);

        return redirect()->route('carrier.index');
    }

    /**
     * Remove the specified carrier from storage.
     *
     * @param int $id Carrier ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $carrier = Carrier::find($id);

        if (! $carrier) {
            toastr()->error("This Carrier doesn't exist");

            return redirect()->route('carrier.index');
        }

        $carrier->insuranceTypes()->detach();
        $carrier->delete();

        toastr()->success('Carrier deleted successfully.');

        return redirect()->route('carrier.index');
    }

    /**
     * Remove multiple carriers from storage (bulk delete).
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteBulk(Request $request)
    {
        $ids = $request->input('selectedValues', []);

        if (empty($ids) || ! is_array($ids)) {
            toastr()->error('No carriers selected for deletion.');

            return redirect()->route('carrier.index');
        }

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

    // ============================================================================
    // Private Helper Methods
    // ============================================================================

    /**
     * Find carrier by ID or redirect with error.
     *
     * @param int $id Carrier ID
     * @param string $redirectRoute Route to redirect on failure
     * @return Carrier|\Illuminate\Http\RedirectResponse
     */
    private function findCarrierOrRedirect($id, string $redirectRoute)
    {
        $carrier = Carrier::find($id);

        if (! $carrier) {
            toastr()->error('This Carrier doesn\'t exist');

            return redirect($redirectRoute);
        }

        return $carrier;
    }

    /**
     * Get validation rules for carrier request.
     *
     * @param Request $request
     * @return array Validation rules
     */
    private function getCarrierValidationRules(Request $request): array
    {
        $rules = [
            'id' => 'required',
            'carrier_name' => 'required|string|max:255',
        ];

        // Add insurance_type validation unless in approval mode with rejection
        if (empty($request->pending) || $request->pending != 2 || empty($request->acceptance) || $request->acceptance != 3) {
            $rules['insurance_type'] = 'required|array';
        }

        return $rules;
    }

    /**
     * Validate carrier request.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Validation\Validator
     */
    private function validateCarrierRequest(Request $request)
    {
        $rules = [
            'carrier_name' => 'required|string|max:255',
            'insurance_type' => 'required|array',
        ];

        return Validator::make($request->all(), $rules);
    }
}
