<?php

namespace App\Http\Controllers\Leads;

use App\Exports\LeadsExport;
use App\Http\Controllers\Controller;
use App\Model\Campaign;
use App\Model\LeadsModel\Contact;
use App\Model\LeadsModel\Lead;
use App\Model\LeadsModel\Log;
use App\Traits\CommonFunctionsTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Session;
use Validator;

/**
 * Controller for importing leads and contacts from CSV/Excel files
 */
class ImportController extends Controller
{
    use CommonFunctionsTrait;

    /** @var array Allowed file extensions for import */
    private const ALLOWED_EXTENSIONS = ['csv', 'xlsx', 'xls'];

    /**
     * Constructor - Set middleware permissions
     */
    public function __construct()
    {
        $this->middleware('permission:lead-list|lead-create|lead-edit|lead-delete|lead-import|lead-export', ['only' => ['index', 'import_leads']]);
        $this->middleware('permission:lead-import', ['only' => ['import_leads', 'import']]);
        $this->middleware('permission:lead-export', ['only' => ['exportCsv', 'export']]);
    }

    /**
     * Process business import from uploaded file
     * @param Request $request HTTP request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function processBusiness(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file',
        ]);

        if ($validator->fails()) {
            toastr()->error('File is required for using the import feature.');
            return back()->withErrors($validator->errors()->all());
        }

        // Validate and secure file - prevents malicious file attacks
        $fileValidation = $this->validateAndSecureFile($request);
        if ($fileValidation['error']) {
            toastr()->error($fileValidation['message']);
            return redirect()->back();
        }

        $extension = $fileValidation['extension'];

        if (!file_exists($request->file) || !is_readable($request->file)) {
            toastr()->error('Invalid file !');
            return redirect()->back();
        }

        if (!$this->isExtensionAllowed($extension)) {
            toastr()->error('The file must be a file of type: csv, xlsx, xls.');
            return redirect()->back();
        }

        $dataSuccess = collect();
        $dataErrors = collect();
        $createdLeads = [];
        $newEntries = $request->new_entries == 'on' ? true : false;
        $niceNames = Lead::niceNames();

        $fileData = $this->readDataFromBusinessCsv($request->file, $extension);

        if (isset($fileData['errors'])) {
            toastr()->error($fileData['errors']);
            return redirect()->back();
        }

        $created = 0;
        $updated = 1;

        foreach ($fileData as $key => $data) {
            $updated++;
            $checkImportType = $data['Business_Type'] ?? null;
            $checkBusinessName = $data['Business_Name'] ?? null;
            $checkBusinessCity = $data['Business_City'] ?? null;
            $checkBusinessZip = $data['Business_Zip'] ?? null;

            if ($checkBusinessName) {
                $leadName = $checkBusinessName;
                $leadSlug = $this->generateSlug([$checkImportType, $leadName, $checkBusinessCity, $checkBusinessZip]);
            } else {
                $leadName = '';
                $leadSlug = '';
                $dataErrors->push([[
                    'row' => $updated,
                    'attribute' => 'Business_Name',
                    'errors' => "Business Name can't be empty. Lead was not imported",
                    'values' => '',
                ]]);
            }

            $lead = Lead::where('lead_slug', $leadSlug)->first();
            $allData = self::formatCsvData($data, $updated, $niceNames);
            $data = $allData['data'];

            if (count($allData['errors']) > 0) {
                $dataErrors->push($allData['errors']);
            }

            if (!$lead && $leadSlug) {
                try {
                    $lead = $this->createLeadFromData($data, $leadName);
                    $leadLog = new Log;
                    $leadLog->action = 'Import Lead : ' . $leadName;
                    $leadLog->users()->associate(auth()->user())->save();
                    $lead->logs()->save($leadLog);

                    $dataSuccess->push('<i class="fas fa-file-import"></i> Row: ' . $updated . '- Lead: <b>' . $lead->name . '</b> successfully imported.');
                    $created++;
                    $createdLeads[] = $lead->id;
                } catch (\Throwable $th) {
                    toastr()->error($th);
                    throw $th;
                }
            } elseif ($lead && !$newEntries) {
                $updateData = [];
                $fields = ['type', 'creation_date', 'address1', 'address2', 'city', 'state', 'zip', 'county', 'unit_count', 'renewal_date', 'renewal_month', 'premium', 'insured_amount', 'manag_company', 'prop_manager', 'current_agency', 'current_agent', 'ins_prop_carrier', 'renewal_carrier_month', 'ins_flood', 'general_liability', 'GL_ren_month', 'crime_insurance', 'CI_ren_month', 'directors_officers', 'DO_ren_month', 'workers_compensation', 'WC_ren_month', 'umbrella', 'U_ren_month', 'flood', 'F_ren_month'];
                $csvFields = ['Business_Type', 'Business_Creation_Date', 'Business_Address1', 'Business_Address2', 'Business_City', 'Business_State', 'Business_Zip', 'Business_County', 'Business_Unit_Count', 'Property_Insurance_Renewal_Date', 'Property_Insurance_Renewal_Month', 'Business_Premium', 'Business_Insured_Amount', 'Management_Company', 'Property_Manager', 'Current_Agency', 'Current_Agent', 'Insurance_Property_Carrier', 'Insurance_Property_Carrier_Renewal_Month', 'Insurance_Flood', 'General_Liability', 'General_Liability_Renewal_Month', 'Crime_Insurance', 'Crime_Insurance_Renewal_Month', 'Directors_Officers', 'Directors_Officers_Renewal_Month', 'Workers_Compensation', 'Workers_Compensation_Renewal_Month', 'Umbrella', 'Umbrella_Renewal_Month', 'Flood', 'Flood_General_Liability_Renewal_Month'];

                foreach ($fields as $index => $field) {
                    if (isset($data[$csvFields[$index]]) && !empty($data[$csvFields[$index]])) {
                        $updateData[$field] = $data[$csvFields[$index]];
                    }
                }

                if (!empty($updateData)) {
                    $lead->update($updateData);
                    $changes = $lead->getChanges();
                    foreach ($changes as $ckey => $c) {
                        if ($ckey != 'updated_at' && isset($niceNames[$ckey])) {
                            $dataSuccess->push('<i class="fas fa-file-import"></i> Row: ' . $updated . '- Lead ' . $lead->name . ' updated: <b>' . $niceNames[$ckey] . '</b> was updated to <b>' . $c . '</b>');
                        }
                    }
                }

                if (!in_array($lead->id, $createdLeads)) {
                    $dataErrors->push([['row' => $updated, 'attribute' => '', 'errors' => 'Lead ' . $lead->name . ' already exists.', 'values' => '']]);
                }
            } elseif ($lead && $newEntries) {
                $dataErrors->push([['row' => $updated, 'attribute' => '', 'errors' => 'Lead ' . $lead->name . ' already exists. It was skipped.', 'values' => '']]);
            }
        }

        $messages = [];
        if ($dataSuccess->isNotEmpty()) $messages['success'] = $dataSuccess;
        if ($dataErrors->isNotEmpty()) $messages['failures'] = $dataErrors;

        toastr()->success($created . ' leads created and ' . ($updated - 1) . ' rows processed!', 'Import Success!');
        return redirect()->back()->with('messages', $messages);
    }

    /**
     * Validate and secure file upload - prevents malicious file attacks
     * @param Request $request HTTP request
     * @return array Validation result with secure extension and name
     */
    private function validateAndSecureFile(Request $request): array
    {
        $file = $request->file('file');

        // Get the actual mime type from file content (more secure than client-provided extension)
        $mimeType = $file->getMimeType();

        // Map mime types to extensions
        $mimeToExtension = [
            'text/csv' => 'csv',
            'application/vnd.ms-excel' => 'xls',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
        ];

        // Use client extension as fallback but validate it
        $clientExtension = strtolower($file->getClientOriginalExtension());
        $clientName = $file->getClientOriginalName();

        // Sanitize filename to prevent path traversal attacks
        $sanitizedName = $this->sanitizeFilename($clientName);

        // Use mime type if available, otherwise use validated client extension
        $secureExtension = $mimeToExtension[$mimeType] ?? $clientExtension;

        // Double-check extension is in our whitelist
        if (!in_array($secureExtension, self::ALLOWED_EXTENSIONS)) {
            return ['error' => true, 'message' => 'Invalid file type.'];
        }

        return ['error' => false, 'extension' => $secureExtension, 'name' => $sanitizedName];
    }

    /**
     * Sanitize filename to prevent path traversal and injection attacks
     * @param string $filename Original filename
     * @return string Sanitized filename
     */
    private function sanitizeFilename(string $filename): string
    {
        // Remove any path components (prevent directory traversal)
        $filename = basename($filename);

        // Remove potentially dangerous characters
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);

        // Prevent null byte injection
        $filename = str_replace("\0", '', $filename);

        // Limit filename length
        $filename = substr($filename, 0, 255);

        return $filename;
    }

    /**
     * Check if extension is in allowed whitelist
     * @param string $extension File extension
     * @return bool True if allowed
     */
    private function isExtensionAllowed(string $extension): bool
    {
        return in_array(strtolower($extension), self::ALLOWED_EXTENSIONS);
    }

    /**
     * Create lead from CSV data
     * @param array $data CSV row data
     * @param string $leadName Lead name
     * @return Lead Created lead
     */
    private function createLeadFromData(array $data, string $leadName): Lead
    {
        $lead = new Lead;
        $lead->name = $leadName;
        $lead->type = $data['Business_Type'] ?? null;
        $lead->creation_date = $data['Business_Creation_Date'] ?? null;
        $lead->address1 = $data['Business_Address1'] ?? null;
        $lead->address2 = $data['Business_Address2'] ?? null;
        $lead->city = $data['Business_City'] ?? null;
        $lead->state = $data['Business_State'] ?? null;
        $lead->zip = $data['Business_Zip'] ?? null;
        $lead->county = $data['Business_County'] ?? null;
        $lead->unit_count = $data['Business_Unit_Count'] ?? null;
        $lead->renewal_date = $data['Property_Insurance_Renewal_Date'] ?? null;
        $lead->renewal_month = $data['Property_Insurance_Renewal_Month'] ?? null;
        $lead->premium = $data['Business_Premium'] ?? null;
        $lead->insured_amount = $data['Business_Insured_Amount'] ?? null;
        $lead->manag_company = $data['Management_Company'] ?? null;
        $lead->prop_manager = $data['Property_Manager'] ?? null;
        $lead->current_agency = $data['Current_Agency'] ?? null;
        $lead->current_agent = $data['Current_Agent'] ?? null;
        $lead->ins_prop_carrier = $data['Insurance_Property_Carrier'] ?? null;
        $lead->renewal_carrier_month = $data['Insurance_Property_Carrier_Renewal_Month'] ?? null;
        $lead->ins_flood = $data['Insurance_Flood'] ?? null;
        $lead->general_liability = $data['General_Liability'] ?? null;
        $lead->GL_ren_month = $data['General_Liability_Renewal_Month'] ?? null;
        $lead->crime_insurance = $data['Crime_Insurance'] ?? null;
        $lead->CI_ren_month = $data['Crime_Insurance_Renewal_Month'] ?? null;
        $lead->directors_officers = $data['Directors_Officers'] ?? null;
        $lead->DO_ren_month = $data['Directors_Officers_Renewal_Month'] ?? null;
        $lead->workers_compensation = $data['Workers_Compensation'] ?? null;
        $lead->WC_ren_month = $data['Workers_Compensation_Renewal_Month'] ?? null;
        $lead->umbrella = $data['Umbrella'] ?? null;
        $lead->U_ren_month = $data['Umbrella_Renewal_Month'] ?? null;
        $lead->flood = $data['Flood'] ?? null;
        $lead->F_ren_month = $data['Flood_General_Liability_Renewal_Month'] ?? null;
        $lead->save();

        return $lead;
    }

    /**
     * Import leads with contacts from uploaded file
     * @param Request $request HTTP request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file',
        ]);

        if ($validator->fails()) {
            toastr()->error('File is required for using the import feature.');
            return back()->withErrors($validator->errors()->all());
        }

        $fileValidation = $this->validateAndSecureFile($request);
        if ($fileValidation['error']) {
            toastr()->error($fileValidation['message']);
            return redirect()->back();
        }

        $extension = $fileValidation['extension'];

        if (!file_exists($request->file) || !is_readable($request->file)) {
            toastr()->error('Invalid file !');
            return redirect()->back();
        }

        if (!$this->isExtensionAllowed($extension)) {
            toastr()->error('The file must be a file of type: csv, xlsx, xls.');
            return redirect()->back();
        }

        $dataSuccess = collect();
        $dataErrors = collect();
        $csvLeads = collect();
        $createdLeads = [];
        $newEntries = $request->new_entries == 'on' ? true : false;
        $niceNames = Lead::niceNames();
        $createCampaign = $request->create_campaign;
        $campaignDate = $request->campaign_date;

        $fileData = self::readDataFromCsv($request->file, $extension);

        if (isset($fileData['errors'])) {
            toastr()->error($fileData['errors']);
            return redirect()->back();
        }

        $created = 0;
        $updated = 1;
        $fileDataChunks = array_chunk($fileData, 100);

        foreach ($fileDataChunks as $fileDataChunk) {
            foreach ($fileDataChunk as $key => $data) {
                $updated++;
                $checkImportType = $data['Business_Type'] ?? null;
                $checkBusinessName = $data['Business_Name'] ?? null;
                $checkBusinessCity = $data['Business_City'] ?? null;
                $checkBusinessZip = $data['Business_Zip'] ?? null;

                $leadSlug = $leadName = '';
                if ($data['Business_Name'] && $checkImportType && $checkBusinessCity && $checkBusinessZip) {
                    $leadName = $data['Business_Name'];
                    $leadSlug = $this->generateSlug([$checkImportType, $leadName, $checkBusinessCity, $checkBusinessZip]);
                } else {
                    $leadName = '';
                    $leadSlug = '';
                    $dataErrors->push([['row' => $updated, 'attribute' => 'Business_Name', 'errors' => "Business Name, Business type, business city, business zip can't be empty.", 'values' => '']]);
                }

                $lead = Lead::where('lead_slug', $leadSlug)->first();
                $allData = self::formatCsvData($data, $updated, $niceNames);
                $data = $allData['data'];

                if (count($allData['errors']) > 0) {
                    $dataErrors->push($allData['errors']);
                }

                if (!$lead && $leadSlug) {
                    try {
                        $lead = $this->createLeadFromData($data, $leadName);
                        $leadLog = new Log;
                        $leadLog->action = 'Import Lead : ' . $leadName;
                        $leadLog->users()->associate(auth()->user())->save();
                        $lead->logs()->save($leadLog);

                        $dataSuccess->push('<i class="fas fa-file-import"></i> Row: ' . $updated . '- Lead: <b>' . $lead->name . '</b> successfully imported.');
                        $created++;
                        $createdLeads[] = $lead->id;
                        $csvLeads->push($lead->id);
                    } catch (\Throwable $th) {
                        toastr()->error($th);
                        throw $th;
                    }
                } elseif ($lead && !$newEntries) {
                    $this->updateLeadFromData($lead, $data, $dataSuccess, $dataErrors, $createdLeads, $updated, $niceNames);
                }

                // Process contacts
                if ($lead) {
                    $this->processContactFromData($lead, $data, $dataSuccess, $dataErrors, $updated);
                }
            }
        }

        if ($createCampaign != null && !$csvLeads->isEmpty()) {
            $campaign = new Campaign;
            $campaign->name = $request->campaign_name;
            $campaign->campaign_date = $campaignDate;
            $campaign->status = 'COMPLETED';
            $campaign->save();

            $dataSuccess->push('<i class="fas fa-chart-bar nav-icon"></i> Campaign created: <b>' . $campaign->name . '</b>');

            foreach ($csvLeads as $id) {
                $leadAttached = $campaign->leads()->where('lead_id', $id)->exists();
                if (!$leadAttached) {
                    $campaign->leads()->attach($id);
                    $lead = Lead::find($id);
                    $dataSuccess->push('<i class="fas fa-plus"></i> Lead ' . $lead->name . ' attached to campaign');
                    create_log($lead, 'Attach Lead to Campaign : ' . $campaign->name, '');
                }
            }

            $campaign->update(['lead_number' => count($campaign->leads)]);
            updateLeadActions($campaign);
        }

        $messages = [];
        if ($dataSuccess->isNotEmpty()) $messages['success'] = $dataSuccess;
        if ($dataErrors->isNotEmpty()) $messages['failures'] = $dataErrors;

        toastr()->success($created . ' leads created and ' . ($updated - 1) . ' rows processed!', 'Import Success!');
        return redirect()->back()->with('messages', $messages);
    }

    /**
     * Update lead from CSV data
     * @param Lead $lead Lead to update
     * @param array $data CSV row data
     * @param collection $dataSuccess Success messages
     * @param collection $dataErrors Error messages
     * @param array $createdLeads Created lead IDs
     * @param int $updated Row number
     * @param array $niceNames Nice names
     */
    private function updateLeadFromData(Lead $lead, array $data, $dataSuccess, $dataErrors, array &$createdLeads, int $updated, array $niceNames): void
    {
        $updateData = [];
        $fields = ['type', 'creation_date', 'address1', 'address2', 'city', 'state', 'zip', 'county', 'unit_count', 'renewal_date', 'renewal_month', 'premium', 'insured_amount', 'manag_company', 'prop_manager', 'current_agency', 'current_agent', 'ins_prop_carrier', 'renewal_carrier_month', 'ins_flood', 'general_liability', 'GL_ren_month', 'crime_insurance', 'CI_ren_month', 'directors_officers', 'DO_ren_month', 'workers_compensation', 'WC_ren_month', 'umbrella', 'U_ren_month', 'flood', 'F_ren_month'];
        $csvFields = ['Business_Type', 'Business_Creation_Date', 'Business_Address1', 'Business_Address2', 'Business_City', 'Business_State', 'Business_Zip', 'Business_County', 'Business_Unit_Count', 'Property_Insurance_Renewal_Date', 'Property_Insurance_Renewal_Month', 'Business_Premium', 'Business_Insured_Amount', 'Management_Company', 'Property_Manager', 'Current_Agency', 'Current_Agent', 'Insurance_Property_Carrier', 'Insurance_Property_Carrier_Renewal_Month', 'Insurance_Flood', 'General_Liability', 'General_Liability_Renewal_Month', 'Crime_Insurance', 'Crime_Insurance_Renewal_Month', 'Directors_Officers', 'Directors_Officers_Renewal_Month', 'Workers_Compensation', 'Workers_Compensation_Renewal_Month', 'Umbrella', 'Umbrella_Renewal_Month', 'Flood', 'Flood_General_Liability_Renewal_Month'];

        foreach ($fields as $index => $field) {
            if (isset($data[$csvFields[$index]]) && !empty($data[$csvFields[$index]])) {
                $updateData[$field] = $data[$csvFields[$index]];
            }
        }

        if (!empty($updateData)) {
            $lead->update($updateData);
            $changes = $lead->getChanges();
            foreach ($changes as $ckey => $c) {
                if ($ckey != 'updated_at' && isset($niceNames[$ckey])) {
                    $dataSuccess->push('<i class="fas fa-file-import"></i> Row: ' . $updated . '- Lead ' . $lead->name . ' updated: <b>' . $niceNames[$ckey] . '</b> was updated to <b>' . $c . '</b>');
                }
            }
        }

        if (!in_array($lead->id, $createdLeads)) {
            $dataErrors->push([['row' => $updated, 'attribute' => '', 'errors' => 'Lead ' . $lead->name . ' already exists.', 'values' => '']]);
        }
    }

    /**
     * Process contact from CSV data
     * @param Lead $lead Parent lead
     * @param array $data CSV data
     * @param collection $dataSuccess Success messages
     * @param collection $dataErrors Error messages
     * @param int $updated Row number
     */
    private function processContactFromData(Lead $lead, array $data, $dataSuccess, $dataErrors, int $updated): void
    {
        $contactFName = $data['Contact_First_Name'] ?? '';
        $contactLName = $data['Contact_Last_Name'] ?? '';
        $contactAddress1 = $data['Contact_Address1'] ?? '';

        $addressNumeric = '';
        if (preg_match('/\d+/', $contactAddress1, $matches)) {
            $addressNumeric = $matches[0];
        }

        $contactSlug = $this->generateSlug([$contactFName, $contactLName, $addressNumeric]);
        $contact = $lead->contacts()->where('contact_slug', $contactSlug)->first();

        if (!$contact && $lead && $contactSlug && $contactFName && $contactLName && $contactAddress1) {
            try {
                $contact = new Contact;
                $contact->c_full_name = $contactFName . ' ' . $contactLName;
                $contact->c_first_name = $contactFName;
                $contact->c_last_name = $contactLName;
                $contact->contact_slug = $contactSlug;
                $contact->c_title = $data['Contact_Title'] ?? null;
                $contact->c_address1 = $contactAddress1;
                $contact->c_address2 = $data['Contact_Address2'] ?? null;
                $contact->c_city = $data['Contact_City'] ?? null;
                $contact->c_state = $data['Contact_State'] ?? null;
                $contact->c_zip = $data['Contact_Zip'] ?? null;
                $contact->c_phone = $data['Contact_Phone'] ?? null;
                $contact->c_email = $data['Contact_Email'] ?? null;
                $contact->save();
                $lead->contacts()->save($contact);

                $contactLog = new Log;
                $contactLog->action = 'Import Contact to Lead : ' . $contactFName . ' ' . $contactLName;
                $contactLog->users()->associate(auth()->user())->save();
                $lead->logs()->save($contactLog);

                $dataSuccess->push('<i class="fas fa-level-up-alt"></i> Row: ' . $updated . '- Contact: <b>' . $contact->c_first_name . ' ' . $contact->c_last_name . '</b> for lead <b>' . $lead->name . '</b> successfully imported.');
            } catch (\Throwable $th) {
                toastr()->error($th);
                throw $th;
            }
        }
    }

    /**
     * Process contacts import
     * @param Request $request HTTP request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function processContacts(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file',
        ]);

        if ($validator->fails()) {
            toastr()->error('File is required for using the import feature.');
            return back()->withErrors($validator->errors()->all());
        }

        $fileValidation = $this->validateAndSecureFile($request);
        if ($fileValidation['error']) {
            toastr()->error($fileValidation['message']);
            return redirect()->back();
        }

        $extension = $fileValidation['extension'];

        if (!$this->isExtensionAllowed($extension)) {
            toastr()->error('The file must be a file of type: csv, xlsx, xls.');
            return redirect()->back();
        }

        $dataSuccess = collect();
        $dataErrors = collect();
        $createdLeads = [];
        $newEntries = $request->new_entries == 'on' ? true : false;

        $created = 0;
        $updated = 1;

        $path = $request->file('file')->getRealPath();
        $csv = array_map('str_getcsv', file($path));

        if (count($csv[0]) < 2) {
            $csv = array_map(function ($v) { return str_getcsv($v, "\t"); }, file($path));
        }
        if (count($csv[0]) < 2) {
            $csv = array_map(function ($v) { return str_getcsv($v, ';'); }, file($path));
        }
        if (count($csv[0]) < 2) {
            throw ValidationException::withMessages([
                'file' => 'Parsing failed. Found only: ' . count($csv[0]) . ' columns',
            ]);
        }

        foreach ($csv as $key => $row) {
            if ($key > 0) {
                $updated++;
                $slug = $row[9] ?? '';
                $address = $row[2] ?? '';

                if (preg_match('/\d+/', $address, $matches)) {
                    $address = $matches[0];
                }

                $contactSlug = $this->generateSlug([$row[0] ?? '', $row[1] ?? '', $address]);
                $contact = Contact::where('contact_slug', $contactSlug)->first();

                if (!$contact && $contactSlug) {
                    try {
                        $contact = new Contact;
                        $contact->c_full_name = trim(($row[0] ?? '') . ' ' . ($row[1] ?? ''));
                        $contact->c_first_name = $row[0] ?? null;
                        $contact->c_last_name = $row[1] ?? null;
                        $contact->c_address1 = $row[2] ?? null;
                        $contact->c_address2 = $row[3] ?? null;
                        $contact->c_city = $row[4] ?? null;
                        $contact->c_state = $row[5] ?? null;
                        $contact->c_zip = $row[6] ?? null;
                        $contact->c_phone = $row[7] ?? null;
                        $contact->c_is_client = $row[8] ?? null;
                        $contact->contact_slug = $contactSlug;
                        $contact->c_email = $row[9] ?? null;
                        $contact->save();

                        $dataSuccess->push('<i class="fas fa-file-import"></i> Row: ' . $updated . '- Contact: <b>' . $contact->c_full_name . '</b> successfully imported.');
                        $created++;
                        $createdLeads[] = $contact->id;
                    } catch (\Throwable $th) {
                        toastr()->error($th);
                        throw $th;
                    }
                } elseif ($contact && !$newEntries) {
                    $updateData = [
                        'c_full_name' => isset($row[0]) && isset($row[1]) ? trim($row[0] . ' ' . $row[1]) : $contact->c_full_name,
                        'c_first_name' => $row[0] ?? $contact->c_first_name,
                        'c_last_name' => $row[1] ?? $contact->c_last_name,
                        'c_address1' => $row[2] ?? $contact->c_address1,
                        'c_address2' => $row[3] ?? $contact->c_address2,
                        'c_city' => $row[4] ?? $contact->c_city,
                        'c_state' => $row[5] ?? $contact->c_state,
                        'c_zip' => $row[6] ?? $contact->c_zip,
                        'c_phone' => $row[7] ?? $contact->c_phone,
                        'c_is_client' => $row[8] ?? $contact->c_is_client,
                        'c_email' => $row[9] ?? $contact->c_email,
                    ];
                    $contact->update($updateData);

                    if (!in_array($contact->id, $createdLeads)) {
                        $dataErrors->push([['row' => $updated, 'attribute' => '', 'errors' => 'Contact ' . $contact->c_first_name . ' already exists.', 'values' => '']]);
                    }
                }
            }
        }

        $messages = [];
        if ($dataSuccess->isNotEmpty()) $messages['success'] = $dataSuccess;
        if ($dataErrors->isNotEmpty()) $messages['failures'] = $dataErrors;

        toastr()->success($created . ' contacts created and ' . ($updated - 1) . ' rows processed!', 'Import Success!');
        return redirect()->back()->with('messages', $messages);
    }

    /**
     * Display import leads page
     * @return \Illuminate\View\View
     */
    public function importLeads()
    {
        return view('leads.import');
    }

    /**
     * Display import businesses page
     * @return \Illuminate\View\View
     */
    public function importBusinesses()
    {
        return view('leads.importBusiness');
    }

    /**
     * Display import contacts page
     * @return \Illuminate\View\View
     */
    public function importContacts()
    {
        return view('leads.importContacts');
    }

    /**
     * Export leads to CSV file
     * @param Request $request HTTP request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function exportCsv(Request $request)
    {
        $leads = Session::get('leads');
        return (new LeadsExport($leads))->download('report.csv');
    }

    /**
     * Read data from CSV file
     * @param string $csvFile Path to CSV file
     * @param string $extension File extension
     * @return array CSV data
     */
    private static function readDataFromCsv(string $csvFile, string $extension): array
    {
        $fileName = Carbon::now()->format('mdYHisu');

        if ($extension == 'xlsx' || $extension == 'xls') {
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($extension == 'xlsx' ? 'Xlsx' : 'Xls');
            $reader->setReadDataOnly(true);

            $path = '../storage/app/public/uploads/' . $fileName . '.csv';
            $excel = $reader->load($csvFile);
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($excel);
            $writer->setUseBOM(false);
            $writer->setOutputEncoding('UTF-8');
            $writer->setEnclosureRequired(false);
            $writer->save($path);

            $csvFile = $path;
        } else {
            Storage::putFileAs('public/uploads', $csvFile, $fileName . '.csv');
        }

        $delimiter = ',';
        $header = null;
        $csvData = [];
        $requiredColumns = ['Business_Name', 'Contact_First_Name', 'Contact_Last_Name', 'Contact_Address1', 'Contact_Zip', 'Contact_State', 'Contact_County', 'Contact_City'];

        if (($handle = fopen($csvFile, 'r')) !== false) {
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== false) {
                if (!$header) {
                    $header = $row;
                    foreach ($requiredColumns as $req) {
                        if (!in_array($req, $header)) {
                            return ['errors' => 'Column ' . $req . ' is missing. File was not imported'];
                        }
                    }
                } else {
                    if (count($header) > count($row)) {
                        $csvData[] = mb_convert_encoding(array_combine($header, array_pad($row, count($header), '')), 'UTF-8', 'UTF-8');
                    } elseif (count($header) < count($row)) {
                        $csvData[] = mb_convert_encoding(array_combine($header, array_slice($row, 0, count($header))), 'UTF-8', 'UTF-8');
                    } else {
                        $csvData[] = mb_convert_encoding(array_combine($header, $row), 'UTF-8', 'UTF-8');
                    }
                }
            }
            fclose($handle);
        }

        return $csvData;
    }

    /**
     * Read data from business CSV file
     * @param string $csvFile Path to CSV file
     * @param string $extension File extension
     * @return array CSV data
     */
    public function readDataFromBusinessCsv(string $csvFile, string $extension): array
    {
        $fileName = Carbon::now()->format('mdYHisu');

        if ($extension == 'xlsx' || $extension == 'xls') {
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($extension == 'xlsx' ? 'Xlsx' : 'Xls');
            $reader->setReadDataOnly(true);

            $path = '../storage/app/public/uploads/' . $fileName . '.csv';
            $excel = $reader->load($csvFile);
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($excel);
            $writer->setUseBOM(false);
            $writer->setOutputEncoding('UTF-8');
            $writer->setEnclosureRequired(false);
            $writer->save($path);

            $csvFile = $path;
        } else {
            Storage::putFileAs('public/uploads', $csvFile, $fileName . '.csv');
        }

        $delimiter = ',';
        $header = null;
        $csvData = [];
        $requiredColumns = ['Business_Name'];

        if (($handle = fopen($csvFile, 'r')) !== false) {
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== false) {
                if (!$header) {
                    $header = $row;
                    foreach ($requiredColumns as $req) {
                        if (!in_array($req, $header)) {
                            return ['errors' => 'Column ' . $req . ' is missing. File was not imported'];
                        }
                    }
                } else {
if (count($header) > count($row)) {
                        $csvData[] = mb_convert_encoding(array_combine($header, array_pad($row, count($header), '')), 'UTF-8', 'UTF-8');
                    } elseif (count($header) < count($row)) {
                        $csvData[] = mb_convert_encoding(array_combine($header, array_slice($row, 0, count($header))), 'UTF-8', 'UTF-8');
                    } else {
                        $csvData[] = mb_convert_encoding(array_combine($header, $row), 'UTF-8', 'UTF-8');
                    }
                }
            }
            fclose($handle);
        }

        return $csvData;
    }

    /**
     * Format CSV data with validations
     * @param array $data Row data
     * @param int $updated Row number
     * @param array $niceNames Nice names for fields
     * @return array Formatted data and errors
     */
    private static function formatCsvData(array $data, int $updated, array $niceNames): array
    {
        $dataErrors = collect();
        $unitCountMax4Dig = '/^\d{0,4}$/';
        $twoDecimals = '/^[0-9]+(\.[0-9]{1,2})?$/';

        foreach ($data as $key => $value) {
            if (empty($key)) continue;

            switch ($key) {
                case 'Business_Premium':
                case 'Business_Insured_Amount':
                    if (!preg_match($twoDecimals, $value) && !empty($value)) {
                        $dataErrors->push(['row' => $updated, 'attribute' => $key, 'errors' => "Invalid '" . $key . "' value: " . $value . ' - was not imported', 'values' => $value]);
                        $data[$key] = null;
                    } elseif (empty($value)) {
                        $data[$key] = null;
                    }
                    break;

                case 'Business_Creation_Date':
                case 'Property_Insurance_Renewal_Date':
                case 'Response_Date':
                    if (!empty($value)) {
                        $data[$key] = self::formatCellDate($data, $key, $dataErrors, $updated, $niceNames);
                    } else {
                        $data[$key] = null;
                    }
                    break;

                case 'Contact_Phone':
                    if (!empty($value)) {
                        $phone = preg_replace('/[-!$%^&*()_+|~=`{}\[\]:";<>?,. \/]/', '', $value);
                        $data[$key] = $phone;
                    } else {
                        $data[$key] = null;
                    }
                    break;

                case 'Insurance_Flood':
                    $data[$key] = ucwords($value);
                    break;

                case 'Business_Unit_Count':
                    if (!preg_match($unitCountMax4Dig, $value) && !empty($value)) {
                        $dataErrors->push(['row' => $updated, 'attribute' => $key, 'errors' => 'Invalid ' . $key . ' value: ' . $value . '- was not imported', 'values' => $value]);
                        $data[$key] = null;
                    } elseif (empty($value)) {
                        $data[$key] = null;
                    }
                    break;

                case 'Contact_State':
                case 'Business_State':
                    $data[$key] = self::formatState($value);
                    break;

                case 'Property_Insurance_Renewal_Month':
                    $data[$key] = ucwords($value);
                    break;
            }
        }

        return ['data' => $data, 'errors' => $dataErrors];
    }

    /**
     * Format US state name to abbreviation
     * @param string $state State name
     * @return string State abbreviation
     */
    public static function formatState(string $state): string
    {
        $states = [
            'Alabama' => 'AL', 'Alaska' => 'AK', 'Arizona' => 'AZ', 'Arkansas' => 'AR',
            'California' => 'CA', 'Colorado' => 'CO', 'Connecticut' => 'CT', 'Delaware' => 'DE',
            'District of Columbia' => 'DC', 'Florida' => 'FL', 'Georgia' => 'GA', 'Hawaii' => 'HI',
            'Idaho' => 'ID', 'Illinois' => 'IL', 'Indiana' => 'IN', 'Iowa' => 'IA',
            'Kansas' => 'KS', 'Kentucky' => 'KY', 'Louisiana' => 'LA', 'Maine' => 'ME',
            'Maryland' => 'MD', 'Massachusetts' => 'MA', 'Michigan' => 'MI', 'Minnesota' => 'MN',
            'Mississippi' => 'MS', 'Missouri' => 'MO', 'Montana' => 'MT', 'Nebraska' => 'NE',
            'Nevada' => 'NV', 'New Hampshire' => 'NH', 'New Jersey' => 'NJ', 'New Mexico' => 'NM',
            'New York' => 'NY', 'North Carolina' => 'NC', 'North Dakota' => 'ND', 'Ohio' => 'OH',
            'Oklahoma' => 'OK', 'Oregon' => 'OR', 'Pennsylvania' => 'PA', 'Rhode Island' => 'RI',
            'South Carolina' => 'SC', 'South Dakota' => 'SD', 'Tennessee' => 'TN', 'Texas' => 'TX',
            'Utah' => 'UT', 'Vermont' => 'VT', 'Virginia' => 'VA', 'Washington' => 'WA',
            'West Virginia' => 'WV', 'Wisconsin' => 'WI', 'Wyoming' => 'WY',
        ];

        return $states[$state] ?? ucwords($state);
    }

    /**
     * Format date cell value
     * @param array $data Row data
     * @param string $key Field key
     * @param collection $dataErrors Error collection
     * @param int $updated Row number
     * @param array $niceNames Nice names
     * @return string|null Formatted date
     */
    public static function formatCellDate(array $data, string $key, $dataErrors, int $updated, array $niceNames): ?string
    {
        $patterns = [
            'dayMonthYear' => '/^(0?[1-9]|[12]\d|3[01]).(0?[1-9]|1[012]).(?:[0-9]{4})$/',
            'YearMonthDay' => '/^(?:[0-9]{4}).[0-3]?[0-9].[0-3]?[0-9]$/',
            'MonthDay' => '/^(0?[1-9]|1[012]).(0?[1-9]|[12]\d|3[01])$/',
            'monthDayYear' => '/^(0?[1-9]|1[012]).(0?[1-9]|[12]\d|3[01]).(?:[0-9]{4})$/',
        ];

        $value = $data[$key];

        foreach ($patterns as $name => $pattern) {
            if (preg_match($pattern, $value, $matches)) {
                $value = preg_replace('/[-!\\\\$%^&*()_+|~=`{}\[\]:";<>?,.\/ ]/', '-', $value);
                $newDate = explode('-', $value);

                switch ($name) {
                    case 'dayMonthYear':
                        return count($newDate) == 3 ? $newDate[2] . '-' . $newDate[1] . '-' . $newDate[0] : null;
                    case 'MonthDay':
                        return isset($newDate[1]) ? date('Y') . '-' . $newDate[0] . '-' . $newDate[1] : null;
                    case 'monthDayYear':
                        return $newDate[2] . '-' . $newDate[1] . '-' . $newDate[0];
                    case 'YearMonthDay':
                        return $newDate[0] . '-' . $newDate[1] . '-' . $newDate[2];
                }
            }
        }

        if (!empty($value)) {
            $dataErrors->push([
                'row' => $updated,
                'attribute' => $key,
                'errors' => 'Invalid ' . $key . ' date format value: ' . $value . ' - was not imported',
                'values' => $value,
            ]);
        }

        return null;
    }
}
