<?php

namespace App\Http\Controllers;

use App\Model\LeadSource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\Datatables\Datatables;

/**
 * Controller for managing Lead Source CRUD operations.
 * Handles create, read, update, delete, and bulk delete operations.
 */
class LeadSourceController extends Controller
{
    /**
     * Display a listing of lead sources.
     */
    public function index()
    {
        $rating = [];

        return view('leadsource.index', compact('rating'));
    }

    /**
     * Retrieve datatable data for lead sources.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function data(Request $request)
    {
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $draw = $request->input('draw', 1);

        // Get sorting column number and direction
        $filterOnColumnNumber = $request->input('order')[0]['column'];
        $filterOnColumnName = $request->input('columns')[$filterOnColumnNumber]['data'] ?? 'id';
        $orderBy = $request->input('order')[0]['dir'] ?? 'desc';

        // Initialize the query for LeadSource model
        $leadSourceQuery = LeadSource::where('status', 1);

        // Get total records count before applying pagination
        $totalRecords = $leadSourceQuery->count();

        // Apply ordering and pagination
        $leadSourceQuery = $leadSourceQuery->orderBy($filterOnColumnName, $orderBy)
            ->offset($start)
            ->limit($length);

        // Use datatables() with the query
        return datatables($leadSourceQuery)
            ->addIndexColumn()
            ->rawColumns(['action'])
            ->with('draw', $draw)
            ->with('recordsTotal', $totalRecords)
            ->with('recordsFiltered', $totalRecords)
            ->make(true);
    }

    /**
     * Show the form for creating a new lead source.
     */
    public function create()
    {
        $page_type = 1;

        return view('leadsource.create', compact('page_type'));
    }

    /**
     * Show the form for editing an existing lead source.
     *
     * @param int $id Lead Source ID
     */
    public function edit($id)
    {
        $id = base64_decode($id);
        $leadsource = $this->findLeadSourceOrRedirect($id, '/leadsource');

        if ($leadsource instanceof \Illuminate\Http\RedirectResponse) {
            return $leadsource;
        }

        $page_type = 2;

        return view('leadsource.create', compact('leadsource', 'page_type'));
    }

    /**
     * Display the specified lead source (read-only view).
     *
     * @param int $id Lead Source ID
     */
    public function show($id)
    {
        $id = base64_decode($id);
        $leadsource = $this->findLeadSourceOrRedirect($id, '/leadsource');

        if ($leadsource instanceof \Illuminate\Http\RedirectResponse) {
            return $leadsource;
        }

        $page_type = 3;

        return view('leadsource.create', compact('leadsource', 'page_type'));
    }

    /**
     * Store a newly created lead source in storage.
     *
     * @param Request $request
     */
    public function store(Request $request)
    {
        $validator = $this->validateLeadSourceRequest($request);

        if ($validator->fails()) {
            toastr()->error($validator->errors()->first());

            return back()->withErrors($validator)->withInput();
        }

        $alreadyEntry = LeadSource::where('name', $request->leadsource_name)->first();

        if (! $alreadyEntry) {
            LeadSource::create(['name' => $request->leadsource_name]);
            toastr()->success('Lead Source created');

            return redirect()->route('leadsource.index');
        }

        toastr()->error('Lead Source Already Exists');

        return back()->withInput();
    }

    /**
     * Update the specified lead source in storage.
     *
     * @param Request $request
     */
    public function update(Request $request)
    {
        $rules = [
            'id' => 'required',
            'leadsource_name' => 'required|string|max:255',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            toastr()->error($validator->errors()->first());

            return back()->withErrors($validator)->withInput();
        }

        $leadsource = LeadSource::find($request->id);

        if (! $leadsource) {
            toastr()->error("This Lead Source doesn't exist");

            return redirect()->route('leadsource.index');
        }

        $leadsourceOther = LeadSource::where('id', '!=', $request->id)->where('name', $request->leadsource_name)->first();

        if ($leadsourceOther) {
            toastr()->error('This Lead Source with same name already exist');

            return redirect()->route('leadsource.index');
        }

        $leadsource->name = $request->leadsource_name;
        $leadsource->save();

        toastr()->success('Lead Source Updated');

        return redirect()->route('leadsource.index');
    }

    /**
     * Remove the specified lead source from storage.
     *
     * @param int $id Lead Source ID
     */
    public function destroy($id)
    {
        $leadsource = LeadSource::find($id);

        if (! $leadsource) {
            toastr()->error("This Lead Source doesn't exist");

            return redirect()->route('leadsource.index');
        }

        $leadsource->delete();

        toastr()->success('Lead Source deleted successfully.');

        return redirect()->route('leadsource.index');
    }

    /**
     * Remove multiple lead sources from storage (bulk delete).
     *
     * @param Request $request
     */
    public function deleteBulk(Request $request)
    {
        $ids = $request->input('selectedValues', []);

        if (empty($ids) || ! is_array($ids)) {
            toastr()->error('No Lead Source selected for deletion.');

            return redirect()->route('leadsource.index');
        }

        $leadsources = LeadSource::whereIn('id', $ids)->get();

        if ($leadsources->isEmpty()) {
            toastr()->error('No valid Lead Source found for deletion.');

            return redirect()->route('leadsource.index');
        }

        LeadSource::whereIn('id', $ids)->delete();

        toastr()->success('Selected Lead Source deleted successfully.');

        return redirect()->route('leadsource.index');
    }

    // ============================================================================
    // Private Helper Methods
    // ============================================================================

    /**
     * Find lead source by ID or redirect with error.
     *
     * @param int $id Lead Source ID
     * @param string $redirectRoute Route to redirect on failure
     * @return LeadSource|\Illuminate\Http\RedirectResponse
     */
    private function findLeadSourceOrRedirect($id, string $redirectRoute)
    {
        $leadsource = LeadSource::find($id);

        if (! $leadsource) {
            toastr()->error('This Lead Source doesn\'t exist');

            return redirect($redirectRoute);
        }

        return $leadsource;
    }

    /**
     * Validate lead source request.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Validation\Validator
     */
    private function validateLeadSourceRequest(Request $request)
    {
        $rules = [
            'leadsource_name' => 'required|string|max:255',
        ];

        return Validator::make($request->all(), $rules);
    }
}
