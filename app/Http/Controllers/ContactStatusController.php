<?php

namespace App\Http\Controllers;

use App\Model\ContactStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Controller for managing Contact Status CRUD operations.
 * Handles create, read, update, delete, and bulk delete operations.
 */
class ContactStatusController extends Controller
{
    /**
     * Display a listing of contact statuses.
     */
    public function index()
    {
        $contactStatus = [];

        return view('contactstatus.index', compact('contactStatus'));
    }

    /**
     * Show the form for creating a new contact status.
     */
    public function create()
    {
        $page_type = 1;
        $ContactStatus = [];
        $id = 0;

        return view('contactstatus.create', compact('ContactStatus', 'page_type', 'id'));
    }

    /**
     * Show the form for editing an existing contact status.
     */
    public function edit($id)
    {
        $id = base64_decode($id);
        $ContactStatus = $this->findContactStatusOrRedirect($id, '/smsprovider');

        if ($ContactStatus instanceof \Illuminate\Http\RedirectResponse) {
            return $ContactStatus;
        }

        $page_type = 2;

        return view('contactstatus.create', compact('ContactStatus', 'page_type', 'id'));
    }

    /**
     * Display the specified contact status (read-only view).
     */
    public function show($id)
    {
        $id = base64_decode($id);
        $ContactStatus = $this->findContactStatusOrRedirect($id, '/smsprovider');

        if ($ContactStatus instanceof \Illuminate\Http\RedirectResponse) {
            return $ContactStatus;
        }

        $page_type = 3;

        return view('contactstatus.create', compact('ContactStatus', 'page_type', 'id'));
    }

    /**
     * Retrieve datatable data for contact statuses.
     * Handles pagination, sorting, and filtering.
     */
    public function data(Request $request)
    {
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $draw = $request->input('draw', 1);

        // Get column sorting information
        $filterOnColumnNumber = $request->input('order')[0]['column'] ?? 0;
        $filterOnColumnName = $request->input('columns')[$filterOnColumnNumber]['data'] ?? 'id';
        $orderBy = $request->input('order')[0]['dir'] ?? 'desc';

        // Build query with ordering and pagination
        $contactStatus = ContactStatus::orderBy($filterOnColumnName, $orderBy)
            ->offset($start)
            ->limit($length);

        return datatables()->of($contactStatus)
            ->addIndexColumn()
            ->rawColumns(['action'])
            ->make(true);
    }

    /**
     * Remove the specified contact status from storage.
     */
    public function destroy($id)
    {
        $contactStatus = ContactStatus::find($id);

        if (! $contactStatus) {
            toastr()->error('The Contact Status was removed previously');

            return back();
        }

        $contactStatus->delete();
        toastr()->success('Contact Status Deleted!');

        return redirect()->route('contactstatus.index');
    }

    /**
     * Remove multiple contact statuses from storage (bulk delete).
     */
    public function deleteBulk(Request $request)
    {
        $selectedIds = $request->selectedValues;

        if (count($selectedIds) <= 0) {
            return response()->json([
                'leadsCount' => 0,
                'message' => 'Please check at least one checkbox to continue.'
            ]);
        }

        ContactStatus::whereIn('id', $selectedIds)->delete();

        return response()->json([
            'leadsCount' => 1,
            'message' => 'Records deleted successfully'
        ]);
    }

    /**
     * Store a newly created contact status in storage.
     */
    public function store(Request $request)
    {
        // Validate request data
        $validator = $this->validateRequest($request);

        if ($validator->fails()) {
            $errorMessages = $validator->errors()->all();
            toastr()->error(implode('<br>', $errorMessages));

            return back()->withErrors($validator)->withInput();
        }

        // Build input array and create record
        $input = $this->buildContactStatusInput($request);
        ContactStatus::create($input);

        toastr()->success('Contact Status created successfully');

        return redirect()->route('contactstatus.index');
    }

    /**
     * Update the specified contact status in storage.
     */
    public function update(Request $request)
    {
        // Validate request data
        $validator = $this->validateRequest($request);

        if ($validator->fails()) {
            $errorMessages = $validator->errors()->all();
            toastr()->error(implode('<br>', $errorMessages));

            return back()->withErrors($validator)->withInput();
        }

        // Build input array and update record
        $input = $this->buildContactStatusInput($request);
        ContactStatus::where('id', $request->id)->update($input);

        toastr()->success('Contact Status Updated successfully');

        return redirect()->route('contactstatus.index');
    }

    // ============================================================================
    // Private Helper Methods
    // ============================================================================

    /**
     * Find contact status by ID or redirect with error.
     *
     * @param int $id Contact status ID
     * @param string $redirectRoute Route to redirect on failure
     * @return ContactStatus|\Illuminate\Http\RedirectResponse
     */
    private function findContactStatusOrRedirect($id, string $redirectRoute)
    {
        $contactStatusModel = ContactStatus::find($id);

        if (! $contactStatusModel) {
            toastr()->error('This Contact Status doesn\'t exist');

            return redirect($redirectRoute);
        }

        return $contactStatusModel;
    }

    /**
     * Build input array for contact status from request.
     *
     * @param Request $request
     * @return array Input array for ContactStatus
     */
    private function buildContactStatusInput(Request $request): array
    {
        return [
            'name' => $request->status_name,
            'priority' => $request->priority,
            'false_status' => ! empty($request->false_status) ? 1 : 0,
            'display_in_pipedrive' => ! empty($request->display_in_pipedrive) ? 1 : 0,
            'status_type' => ! empty($request->status_type) ? $request->status_type : null,
        ];
    }

    /**
     * Get validation rules for contact status.
     *
     * @return array Validation rules
     */
    private function getValidationRules(): array
    {
        return [
            'status_name' => 'required|max:100',
            'priority' => 'required|max:100',
        ];
    }

    /**
     * Validate the incoming request.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Validation\Validator
     */
    private function validateRequest(Request $request)
    {
        $rules = $this->getValidationRules();

        return Validator::make($request->all(), $rules);
    }
}
