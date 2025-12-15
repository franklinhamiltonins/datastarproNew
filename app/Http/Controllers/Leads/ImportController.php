<?php

namespace App\Http\Controllers\Leads;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use App\Model\Role;
use App\Model\LeadsModel\Lead;
use App\Model\LeadsModel\Log;
use App\Model\LeadsModel\Contact;
use App\Model\LeadsModel\Action;
use App\Model\Campaign;
use App\Imports\LeadsImport;
use App\Exports\LeadsExport;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Session;
use Validator;
// use Log;
use DB;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use App\Traits\CommonFunctionsTrait;

class ImportController extends Controller
{
	use CommonFunctionsTrait;

	function __construct()
	{
		$this->middleware('permission:lead-list|lead-create|lead-edit|lead-delete|lead-import|lead-export', ['only' => ['index', 'import_leads']]);
		$this->middleware('permission:lead-import', ['only' => ['import_leads', 'import']]);
		$this->middleware('permission:lead-export', ['only' => ['exportCsv', 'export']]);
	}

	public function process_business(Request $request)
	{

		$validator = Validator::make($request->all(), [
			'file' => 'required|file', // Ensure that 'file' is required and is a file
		]);

		if ($validator->fails()) {
			toastr()->error("File is required for using the import feature.");
			return back()->withErrors($validator->errors()->all());
		}


		//collect success/errors
		$dataSuccess = collect();
		$dataErrors = collect();
		$csvLeads = collect();
		$createdLeads = array();
		$createCampaign = $request->create_campaign;
		$campaignDate = $request->campaign_date;
		$newEntries = $request->new_entries == 'on' ? true : false;


		$niceNames = [
			'type' => 'Business Type',
			'name' => 'Business Name',
			'creation_date' => 'Business Creation Date',
			'address1' => 'Business Address 1',
			'address2' => 'Business Adress 2',
			'city' => 'Business City',
			'state' => 'Business State',
			'zip' => 'Business Zip',
			'county' => 'Business County',
			'unit_count' => 'Business Unit Count',
			'renewal_date' => 'Property Insurance Renewal Date',
			'renewal_month' => 'Property Insurance Renewal Month',
			'premium' => 'Business Premium',
			'insured_amount' => 'Business Insured Amount',
			'manag_company' => 'Management Company',
			'prop_manager' => 'Property Manager',
			'current_agency' => 'Current Agency',
			'current_agent' => 'Current Agent',
			'ins_prop_carrier' => 'Insurance_Property_Carrier',
			'renewal_carrier_month' => 'Insurance_Property_Carrier_Renewal_Month',
			'ins_flood' => 'Insurance_Flood',
			'general_liability' => 'General Liability',
			'GL_ren_month' => 'General Liability Renewal Month',
			'crime_insurance' => 'Crime Insurance',
			'CI_ren_month' => 'Crime Insurance Renewal Month',
			'directors_officers' => 'Directors & Officers',
			'DO_ren_month' => 'Directors & Officers Renewal Month',
			'workers_compensation' => 'Workers Compensation',
			'WC_ren_month' => 'Workers Compensation Renewal Month',
			'umbrella' => 'Umbrella',
			'U_ren_month' => 'Umbrella Renewal Month',
			'flood' => 'Flood',
			'F_ren_month' => 'Flood General Liability Renewal Month',
		];


		//check if file exist and is readable
		if (!file_exists($request->file) || !is_readable($request->file)) {
			toastr()->error('Invalid file !');
			return redirect()->back();
		}

		// get the file extension in order to validate
		$extension = $request->file('file')->getClientOriginalExtension();
		$name = $request->file('file')->getClientOriginalName();
		if ($extension == "xlsx" || $extension == "xls" || $extension == "csv") { // if the extension matches, proceed

			//get data from csv file
			$fileData = $this->readDataFromBusinessCsv($request->file, $extension);



			//if the fileData returns error, abort import
			if (isset($fileData['errors'])) {
				toastr()->error($fileData['errors']);
				return redirect()->back();
			}


			//created leads
			$created = 0;
			//updated rows (except heading)
			$updated = 1;
			//loop trough rows
			foreach ($fileData as $key => $data) {
				$updated++;  //increment updated rows

				$check_import_type = isset($data['Business_Type']) ? $data['Business_Type'] : null;
				$check_business_name = isset($data['Business_Name']) ? $data['Business_Name'] : null;
				$check_business_city = isset($data['Business_City']) ? $data['Business_City'] : null;
				$check_business_zip = isset($data['Business_Zip']) ? $data['Business_Zip'] : null;
				$leadSlug = $leadName = '';
				//get the row Bussiness_Name
				if ($data['Business_Name']) {
					$leadName = $data['Business_Name'];
					$leadSlug = $this->generateSlug([$check_import_type, $leadName, $check_business_city, $check_business_zip]);
				} else {
					$leadName = "";
					$leadSlug = "";
					$dataErrors->push(
						array(
							array(
								"row" => $updated,
								"attribute" => 'Business_Name',
								"errors" => "Business Name, Business type, business city, business zip can't be empty.  Lead was not imported",
								"values" => "",
							)
						)
					);
				}


				$lead = Lead::where('lead_slug', $leadSlug)->first(); // get the DB lead

				// dd($lead);


				//format fields that do not have the required DB format
				$alldata = self::format_csv_data($data, $updated, $niceNames); //returns data and errors


				$data = $alldata['data']; //get data

				//if there are errors , store them
				if (count($alldata['errors']) > 0) {
					$dataErrors->push($alldata['errors']);
				}
				// dd($alldata);

				if (!$lead && $leadSlug) {
					try {

						//create lead
						$lead = new Lead();

						$lead->type = isset($data['Business_Type']) ? $data['Business_Type'] : null;
						$lead->name = $leadName;
						$lead->lead_slug = $leadSlug;
						$lead->creation_date = isset($data['Business_Creation_Date']) ? $data['Business_Creation_Date'] : null;
						$lead->address1 = isset($data['Business_Address1']) ? $data['Business_Address1'] : null;
						$lead->address2 = isset($data['Business_Address2']) ? $data['Business_Address2'] : null;
						$lead->city = isset($data['Business_City']) ? $data['Business_City'] : null;
						$lead->state = isset($data['Business_State']) ? $data['Business_State'] : null;
						$lead->zip = isset($data['Business_Zip']) ? $data['Business_Zip'] : null;
						$lead->county = isset($data['Business_County']) ? $data['Business_County'] : null;
						$lead->unit_count = isset($data['Business_Unit_Count']) && !empty($data['Business_Unit_Count']) ? $data['Business_Unit_Count'] : null;
						$lead->renewal_date = isset($data['Property_Insurance_Renewal_Date']) ? $data['Property_Insurance_Renewal_Date'] : null;
						$lead->renewal_month = isset($data['Property_Insurance_Renewal_Month']) ? $data['Property_Insurance_Renewal_Month'] : null;
						$lead->premium = isset($data['Business_Premium']) ? $data['Business_Premium'] : null;
						$lead->insured_amount = isset($data['Business_Insured_Amount']) ? $data['Business_Insured_Amount'] : null;
						$lead->manag_company = isset($data['Management_Company']) ? $data['Management_Company'] : null;
						$lead->prop_manager = isset($data['Property_Manager']) ? $data['Property_Manager'] : null;
						$lead->current_agency = isset($data['Current_Agency']) ? $data['Current_Agency'] : null;
						$lead->current_agent = isset($data['Current_Agent']) ? $data['Current_Agent'] : null;
						$lead->ins_prop_carrier = isset($data['Insurance_Property_Carrier']) ? $data['Insurance_Property_Carrier'] : null;
						$lead->renewal_carrier_month = isset($data['Insurance_Property_Carrier_Renewal_Month']) ? $data['Insurance_Property_Carrier_Renewal_Month'] : null;
						$lead->ins_flood = isset($data['Insurance_Flood']) ? $data['Insurance_Flood'] : null;
						$lead->general_liability = isset($data['General_Liability']) ? $data['General_Liability'] : null;
						$lead->GL_ren_month = isset($data['General_Liability_Renewal_Month']) ? $data['General_Liability_Renewal_Month'] : null;
						$lead->crime_insurance = isset($data['Crime_Insurance']) ? $data['Crime_Insurance'] : null;
						$lead->CI_ren_month = isset($data['Crime_Insurance_Renewal_Month']) ? $data['Crime_Insurance_Renewal_Month'] : null;
						$lead->directors_officers = isset($data['Directors_Officers']) ? $data['Directors_Officers'] : null;
						$lead->DO_ren_month = isset($data['Directors_Officers_Renewal_Month']) ? $data['Directors_Officers_Renewal_Month'] : null;
						$lead->workers_compensation = isset($data['Workers_Compensation']) ? $data['Workers_Compensation'] : null;
						$lead->WC_ren_month = isset($data['Workers_Compensation_Renewal_Month']) ? $data['Workers_Compensation_Renewal_Month'] : null;
						$lead->umbrella = isset($data['Umbrella']) ? $data['Umbrella'] : null;
						$lead->U_ren_month = isset($data['Umbrella_Renewal_Month']) ? $data['Umbrella_Renewal_Month'] : null;
						$lead->flood = isset($data['Flood']) ? $data['Flood'] : null;
						$lead->F_ren_month = isset($data['Flood_General_Liability_Renewal_Month']) ? $data['Flood_General_Liability_Renewal_Month'] : null;
						$lead->save();
						//create Lead Log
						$leadlog = new Log();
						$leadlog->action = 'Import Lead : ' . $leadName;
						$leadlog->users()->associate(auth()->user())->save(); //associate user
						$lead->logs()->save($leadlog); //associate log to lead

						//store success message
						$dataSuccess->push('<i class="fas fa-file-import"></i> Row: ' . $updated . '- Lead: <b>' . $lead->name . '</b>  successfully imported.'); // get the success

						$created++; // increment created leads
						$createdLeads[] = $lead->id;
					} catch (\Throwable $th) {
						toastr()->error($th);
						throw ($th);
					}
				} else if ($lead) {


					if (!$newEntries) {
						// update only if DB fields are empty
						$lead->update([
							'type' => isset($data['Business_Type']) && !empty($data['Business_Type']) ? $data['Business_Type'] : $lead->type,
							$lead->name => $leadName,
							'creation_date' => isset($data['Business_Creation_Date']) && !empty($data['Business_Creation_Date']) ? $data['Business_Creation_Date'] : $lead->creation_date,
							'address1' => isset($data['Business_Address1']) && !empty($data['Business_Address1']) ? $data['Business_Address1'] : $lead->address1,
							'address2' => isset($data['Business_Address2']) && !empty($data['Business_Address2']) ? $data['Business_Address2'] : $lead->address2,
							'city' => isset($data['Business_City']) && !empty($data['Business_City']) ? $data['Business_City'] : $lead->city,
							'state' => isset($data['Business_State']) && !empty($data['Business_State']) ? $data['Business_State'] : $lead->state,
							'zip' => isset($data['Business_Zip']) && !empty($data['Business_Zip']) ? $data['Business_Zip'] : $lead->zip,
							'county' => isset($data['Business_County']) && !empty($data['Business_County']) ? $data['Business_County'] : $lead->county,
							'unit_count' => isset($data['Business_Unit_Count']) && !empty($data['Business_Unit_Count']) ? $data['Business_Unit_Count'] : $lead->unit_count,
							'renewal_date' => isset($data['Property_Insurance_Renewal_Date']) && !empty($data['Property_Insurance_Renewal_Date']) ? $data['Property_Insurance_Renewal_Date'] : $lead->renewal_date,
							'renewal_month' => isset($data['Property_Insurance_Renewal_Month']) && !empty($data['Property_Insurance_Renewal_Month']) ? $data['Property_Insurance_Renewal_Month'] : $lead->renewal_month,
							'premium' => isset($data['Business_Premium']) && !empty($data['Business_Premium']) ? $data['Business_Premium'] : $lead->premium,
							'insured_amount' => isset($data['Business_Insured_Amount']) && !empty($data['Business_Insured_Amount']) ? $data['Business_Insured_Amount'] : $lead->insured_amount,
							'manag_company' => isset($data['Management_Company']) && !empty($data['Management_Company']) ? $data['Management_Company'] : $lead->manag_company,
							'prop_manager' => isset($data['Property_Manager']) && !empty($data['Property_Manager']) ? $data['Property_Manager'] : $lead->prop_manager,
							'current_agency' => isset($data['Current_Agency']) && !empty($data['BuCurrent_Agency']) ? $data['Current_Agency'] : $lead->current_agency,
							'current_agent' => isset($data['Current_Agent']) && !empty($data['Current_Agent']) ? $data['Current_Agent'] : $lead->current_agent,
							'ins_prop_carrier' => isset($data['Insurance_Property_Carrier']) && !empty($data['Insurance_Property_Carrier']) ? $data['Insurance_Property_Carrier'] : $lead->ins_prop_carrier,
							'renewal_carrier_month' => isset($data['Insurance_Property_Carrier_Renewal_Month']) && !empty($data['Insurance_Property_Carrier_Renewal_Month']) ? $data['Insurance_Property_Carrier_Renewal_Month'] : $lead->renewal_carrier_month,
							'ins_flood' => isset($data['Insurance_Flood']) && !empty($data['Insurance_Flood']) ? $data['Insurance_Flood'] : $lead->ins_flood,
							'general_liability' => isset($data['General_Liability']) && !empty($data['General_Liability']) ? $data['General_Liability'] : $lead->general_liability,
							'GL_ren_month' => isset($data['General_Liability_Renewal_Month']) && !empty($data['General_Liability_Renewal_Month']) ? $data['General_Liability_Renewal_Month'] : $lead->GL_ren_month,
							'crime_insurance' => isset($data['Crime_Insurance']) && !empty($data['Crime_Insurance']) ? $data['Crime_Insurance'] : $lead->crime_insurance,
							'CI_ren_month' => isset($data['Crime_Insurance_Renewal_Month']) && !empty($data['Crime_Insurance_Renewal_Month']) ? $data['Crime_Insurance_Renewal_Month'] : $lead->CI_ren_month,
							'directors_officers' => isset($data['Directors_Officers']) && !empty($data['Directors_Officers']) ? $data['Directors_Officers'] : $lead->directors_officers,
							'DO_ren_month' => isset($data['Directors_Officers_Renewal_Month']) && !empty($data['Directors_Officers_Renewal_Month']) ? $data['Directors_Officers_Renewal_Month'] : $lead->DO_ren_month,
							'workers_compensation' => isset($data['Workers_Compensation']) && !empty($data['Workers_Compensation']) ? $data['Workers_Compensation'] : $lead->workers_compensation,
							'WC_ren_month' => isset($data['Workers_Compensation_Renewal_Month']) && !empty($data['Workers_Compensation_Renewal_Month']) ? $data['Workers_Compensation_Renewal_Month'] : $lead->WC_ren_month,
							'umbrella' => isset($data['Umbrella']) && !empty($data['Umbrella']) ? $data['Umbrella'] : $lead->umbrella,
							'U_ren_month' => isset($data['Umbrella_Renewal_Month']) && !empty($data['Umbrella_Renewal_Month']) ? $data['Umbrella_Renewal_Month'] : $lead->U_ren_month,
							'flood' => isset($data['Flood']) && !empty($data['Flood']) ? $data['Flood'] : $lead->flood,
							'F_ren_month' => isset($data['Flood_General_Liability_Renewal_Month']) && !empty($data['Flood_General_Liability_Renewal_Month']) ? $data['Flood_General_Liability_Renewal_Month'] : $lead->F_ren_month,

						]);




						if (!in_array($lead->id, $createdLeads)) {
							$dataErrors->push(
								array(
									array(
										"row" => $updated,
										"attribute" => "",
										"errors" => "Lead " . $lead->name . " already exists.",
										"values" => "",

									)
								)
							);
						}

						$changes = $lead->getChanges(); //get what was updated
						foreach ($changes as $key => $c) {
							if ($key != "updated_at") {
								// store success to messages
								$dataSuccess->push('<i class="fas fa-file-import"></i> Row: ' . $updated . '- Lead ' . $lead->name . ' updated during import: empty field - <b>' . $niceNames[$key] . '</b> was updated to <b>' . $c . '</b>');
							}
						}
					} else {
						$dataErrors->push(
							array(
								array(
									"row" => $updated,
									"attribute" => "",
									"errors" => "Lead " . $lead->name . " already exists. It was skipped.",
									"values" => "",

								)
							)
						);
					}
				}
			}


			// messages variable to use in blade
			$dataSuccess ? $messages['success'] = $dataSuccess : '';
			$dataErrors ? $messages['failures'] = $dataErrors : '';
			$row_updated = $updated - 1;
			toastr()->success($created . ' leads created and ' . $row_updated . ' rows processed!', 'Import Success!');
			return redirect()->back()->with('messages', $messages);
		} else { //if the file exension doesn't match the required
			toastr()->error('The file must be a file of type: csv, xlsx, xls.');
			return redirect()->back();
		}
	}

	// import business 

	public function import(Request $request)
	{

		$validator = Validator::make($request->all(), [
			'file' => 'required|file', // Ensure that 'file' is required and is a file
		]);

		if ($validator->fails()) {
			toastr()->error("File is required for using the import feature.");
			return back()->withErrors($validator->errors()->all());
		}

		//collect success/errors
		$dataSuccess = collect();
		$dataErrors = collect();
		$csvLeads = collect();
		$createdLeads = array();
		$createCampaign = $request->create_campaign;
		$campaignDate = $request->campaign_date;
		$newEntries = $request->new_entries == 'on' ? true : false;


		$niceNames = [
			'type' => 'Business Type',
			'name' => 'Business Name',
			'creation_date' => 'Business Creation Date',
			'address1' => 'Business Address 1',
			'address2' => 'Business Adress 2',
			'city' => 'Business City',
			'state' => 'Business State',
			'zip' => 'Business Zip',
			'county' => 'Business County',
			'unit_count' => 'Business Unit Count',
			'renewal_date' => 'Property Insurance Renewal Date',
			'renewal_month' => 'Property Insurance Renewal Month',
			'premium' => 'Business Premium',
			'insured_amount' => 'Business Insured Amount',
			'manag_company' => 'Management Company',
			'prop_manager' => 'Property Manager',
			'current_agency' => 'Current Agency',
			'current_agent' => 'Current Agent',
			'ins_prop_carrier' => 'Insurance_Property_Carrier',
			'renewal_carrier_month' => 'Insurance_Property_Carrier_Renewal_Month',
			'ins_flood' => 'Insurance_Flood',
			'general_liability' => 'General Liability',
			'GL_ren_month' => 'General Liability Renewal Month',
			'crime_insurance' => 'Crime Insurance',
			'CI_ren_month' => 'Crime Insurance Renewal Month',
			'directors_officers' => 'Directors & Officers',
			'DO_ren_month' => 'Directors & Officers Renewal Month',
			'workers_compensation' => 'Workers Compensation',
			'WC_ren_month' => 'Workers Compensation Renewal Month',
			'umbrella' => 'Umbrella',
			'U_ren_month' => 'Umbrella Renewal Month',
			'flood' => 'Flood',
			'F_ren_month' => 'Flood General Liability Renewal Month',
			'c_first_name' => 'First Name',
			'c_last_name' => 'Last Name',
			'c_address1' => 'Address 1',
			'c_address2' => 'Adress 2',
			'c_title' => 'Contact Title',
			'c_city' => 'City',
			'c_state' => 'State',
			'c_zip' => 'Zip',
			'c_county' => 'County',
			'c_phone' => 'Phone',
			'c_email' => 'Email',
			'campaign_date' => 'Response_Date'
		];

		//check if file exist and is readable
		if (!file_exists($request->file) || !is_readable($request->file)) {
			toastr()->error('Invalid file !');
			return redirect()->back();
		}

		// get the file extension in order to validate
		$extension = $request->file('file')->getClientOriginalExtension();
		$name = $request->file('file')->getClientOriginalName();
		if ($extension == "xlsx" || $extension == "xls" || $extension == "csv") { // if the extension matches, proceed

			//get data from csv file
			$fileData = self::readDataFromCsv($request->file, $extension);

			//if the fileData returns error, abort import
			if (isset($fileData['errors'])) {
				toastr()->error($fileData['errors']);
				return redirect()->back();
			}
			//created leads
			$created = 0;
			$updated = 1;

			$fileDataChunks = array_chunk($fileData, 100);

			foreach ($fileDataChunks as $fileDataChunk) {
				foreach ($fileDataChunk as $key => $data) {
					$updated++;

					$check_import_type = isset($data['Business_Type']) ? $data['Business_Type'] : null;
					$check_business_name = isset($data['Business_Name']) ? $data['Business_Name'] : null;
					$check_business_city = isset($data['Business_City']) ? $data['Business_City'] : null;
					$check_business_zip = isset($data['Business_Zip']) ? $data['Business_Zip'] : null;

					$leadSlug = $leadName = '';


					// empty business_name start
					if ($data['Business_Name'] && $check_import_type && $check_business_city && $check_business_zip) {
						$leadName = $data['Business_Name'];
						$leadSlug = $this->generateSlug([$check_import_type, $leadName, $check_business_city, $check_business_zip]);
					} else {
						$leadName = "";
						$leadSlug = "";
						$dataErrors->push(
							array(
								array(
									"row" => $updated,
									"attribute" => 'Business_Name',
									"errors" => "Business Name, Business type, business city, business zip can't be empty. Lead was not imported",
									"values" => "",
								)
							)
						);
					}
					// empty business_name end



					$lead = Lead::where('lead_slug', $leadSlug)->first();

					$alldata = self::format_csv_data($data, $updated, $niceNames);

					$data = $alldata['data'];

					if (count($alldata['errors']) > 0) {
						$dataErrors->push($alldata['errors']);
					}

					$data['Property_Insurance_Renewal_Month'] = (isset($data['Property_Insurance_Renewal_Month']) && $data['Property_Insurance_Renewal_Month'] == 'This Year') ? $data['Property_Insurance_Renewal_Month'] : null;

					$contactFName = $data['Contact_First_Name'];
					$contactLName = $data['Contact_Last_Name'];
					$contactAddress1 = $data['Contact_Address1'];
					$ctAddress = "Contact_Address1";
					$ctZip = "Contact_Zip";
					$ctState = "Contact_State";
					$ctCounty = "Contact_County";
					$ctCity = "Contact_City";

					$responseDate = isset($data['Response_Date']) ? $data['Response_Date'] : '';

					if (!$lead && $leadSlug) {
						try {
							$lead = new Lead();

							$lead->type = isset($data['Business_Type']) ? $data['Business_Type'] : null;
							$lead->lead_slug = $leadSlug;
							$lead->name = $leadName;
							$lead->creation_date = isset($data['Business_Creation_Date']) ? $data['Business_Creation_Date'] : null;
							$lead->address1 = isset($data['Business_Address1']) ? $data['Business_Address1'] : null;
							$lead->address2 = isset($data['Business_Address2']) ? $data['Business_Address2'] : null;
							$lead->city = isset($data['Business_City']) ? $data['Business_City'] : null;
							$lead->state = isset($data['Business_State']) ? $data['Business_State'] : null;
							$lead->zip = isset($data['Business_Zip']) ? $data['Business_Zip'] : null;
							$lead->county = isset($data['Business_County']) ? $data['Business_County'] : null;
							$lead->unit_count = isset($data['Business_Unit_Count']) && !empty($data['Business_Unit_Count']) ? $data['Business_Unit_Count'] : null;
							$lead->renewal_date = isset($data['Property_Insurance_Renewal_Date']) ? $data['Property_Insurance_Renewal_Date'] : null;
							$lead->renewal_month = isset($data['Property_Insurance_Renewal_Month']) ? $data['Property_Insurance_Renewal_Month'] : null;
							$lead->premium = isset($data['Business_Premium']) ? $data['Business_Premium'] : null;
							$lead->insured_amount = isset($data['Business_Insured_Amount']) ? $data['Business_Insured_Amount'] : null;
							$lead->manag_company = isset($data['Management_Company']) ? $data['Management_Company'] : null;
							$lead->prop_manager = isset($data['Property_Manager']) ? $data['Property_Manager'] : null;
							$lead->current_agency = isset($data['Current_Agency']) ? $data['Current_Agency'] : null;
							$lead->current_agent = isset($data['Current_Agent']) ? $data['Current_Agent'] : null;
							$lead->ins_prop_carrier = isset($data['Insurance_Property_Carrier']) ? $data['Insurance_Property_Carrier'] : null;
							$lead->renewal_carrier_month = isset($data['Insurance_Property_Carrier_Renewal_Month']) ? $data['Insurance_Property_Carrier_Renewal_Month'] : null;
							$lead->ins_flood = isset($data['Insurance_Flood']) ? $data['Insurance_Flood'] : null;
							$lead->general_liability = isset($data['General_Liability']) ? $data['General_Liability'] : null;
							$lead->GL_ren_month = isset($data['General_Liability_Renewal_Month']) ? $data['General_Liability_Renewal_Month'] : null;
							$lead->crime_insurance = isset($data['Crime_Insurance']) ? $data['Crime_Insurance'] : null;
							$lead->CI_ren_month = isset($data['Crime_Insurance_Renewal_Month']) ? $data['Crime_Insurance_Renewal_Month'] : null;
							$lead->directors_officers = isset($data['Directors_Officers']) ? $data['Directors_Officers'] : null;
							$lead->DO_ren_month = isset($data['Directors_Officers_Renewal_Month']) ? $data['Directors_Officers_Renewal_Month'] : null;
							$lead->workers_compensation = isset($data['Workers_Compensation']) ? $data['Workers_Compensation'] : null;
							$lead->WC_ren_month = isset($data['Workers_Compensation_Renewal_Month']) ? $data['Workers_Compensation_Renewal_Month'] : null;
							$lead->umbrella = isset($data['Umbrella']) ? $data['Umbrella'] : null;
							$lead->U_ren_month = isset($data['Umbrella_Renewal_Month']) ? $data['Umbrella_Renewal_Month'] : null;
							$lead->flood = isset($data['Flood']) ? $data['Flood'] : null;
							$lead->F_ren_month = isset($data['Flood_General_Liability_Renewal_Month']) ? $data['Flood_General_Liability_Renewal_Month'] : null;
							$lead->save();

							$leadlog = new Log();
							$leadlog->users()->associate(auth()->user())->save(); //associate user
							$lead->logs()->save($leadlog);

							$dataSuccess->push('<i class="fas fa-file-import"></i> Row: ' . $updated . '- Lead: <b>' . $lead->name . '</b>  successfully imported.');

							$created++;
							$createdLeads[] = $lead->id;
						} catch (\Throwable $th) {
							toastr()->error($th);
							throw ($th);
						}
					} else if ($lead) {
						// Update existing lead
						if (!$newEntries) {
							// update only if DB fields are empty
							$lead->update([
								'type' => !$lead->type && isset($data['Business_Type']) && !empty($data['Business_Type']) ? $data['Business_Type'] : $lead->type,
								'lead_slug' => $leadSlug,
								'creation_date' => !$lead->creation_date && isset($data['Business_Creation_Date']) && !empty($data['Business_Creation_Date']) ? $data['Business_Creation_Date'] : $lead->creation_date,
								'address1' => !$lead->address1 && isset($data['Business_Address1']) && !empty($data['Business_Address1']) ? $data['Business_Address1'] : $lead->address1,
								'address2' => !$lead->address2 && isset($data['Business_Address2']) && !empty($data['Business_Address2']) ? $data['Business_Address2'] : $lead->address2,
								'city' => !$lead->city && isset($data['Business_City']) && !empty($data['Business_City']) ? $data['Business_City'] : $lead->city,
								'state' => !$lead->state && isset($data['Business_State']) && !empty($data['Business_State']) ? $data['Business_State'] : $lead->state,
								'zip' => !$lead->zip && isset($data['Business_Zip']) && !empty($data['Business_Zip']) ? $data['Business_Zip'] : $lead->zip,
								'county' => !$lead->county && isset($data['Business_County']) && !empty($data['Business_County']) ? $data['Business_County'] : $lead->county,
								'unit_count' => !$lead->unit_count && isset($data['Business_Unit_Count']) && !empty($data['Business_Unit_Count']) ? $data['Business_Unit_Count'] : $lead->unit_count,
								'renewal_date' => !$lead->renewal_date && isset($data['Property_Insurance_Renewal_Date']) && !empty($data['Property_Insurance_Renewal_Date']) ? $data['Property_Insurance_Renewal_Date'] : $lead->renewal_date,
								'renewal_month' => !$lead->renewal_month && isset($data['Property_Insurance_Renewal_Month']) && !empty($data['Property_Insurance_Renewal_Month']) ? $data['Property_Insurance_Renewal_Month'] : $lead->renewal_month,
								'premium' => !$lead->premium && isset($data['Business_Premium']) && !empty($data['Business_Premium']) ? $data['Business_Premium'] : $lead->premium,
								'insured_amount' => !$lead->insured_amount && isset($data['Business_Insured_Amount']) && !empty($data['Business_Insured_Amount']) ? $data['Business_Insured_Amount'] : $lead->insured_amount,
								'manag_company' => !$lead->manag_company && isset($data['Management_Company']) && !empty($data['Management_Company']) ? $data['Management_Company'] : $lead->manag_company,
								'prop_manager' => !$lead->prop_manager && isset($data['Property_Manager']) && !empty($data['Property_Manager']) ? $data['Property_Manager'] : $lead->prop_manager,
								'current_agency' => !$lead->current_agency && isset($data['Current_Agency']) && !empty($data['BuCurrent_Agency']) ? $data['Current_Agency'] : $lead->current_agency,
								'current_agent' => !$lead->current_agent && isset($data['Current_Agent']) && !empty($data['Current_Agent']) ? $data['Current_Agent'] : $lead->current_agent,
								'ins_prop_carrier' => !$lead->ins_prop_carrier && isset($data['Insurance_Property_Carrier']) && !empty($data['Insurance_Property_Carrier']) ? $data['Insurance_Property_Carrier'] : $lead->ins_prop_carrier,
								'renewal_carrier_month' => !$lead->renewal_carrier_month && isset($data['Insurance_Property_Carrier_Renewal_Month']) && !empty($data['Insurance_Property_Carrier_Renewal_Month']) ? $data['Insurance_Property_Carrier_Renewal_Month'] : $lead->renewal_carrier_month,
								'ins_flood' => !$lead->ins_flood && isset($data['Insurance_Flood']) && !empty($data['Insurance_Flood']) ? $data['Insurance_Flood'] : $lead->ins_flood,
								'general_liability' => !$lead->general_liability && isset($data['General_Liability']) && !empty($data['General_Liability']) ? $data['General_Liability'] : $lead->general_liability,
								'GL_ren_month' => !$lead->GL_ren_month && isset($data['General_Liability_Renewal_Month']) && !empty($data['General_Liability_Renewal_Month']) ? $data['General_Liability_Renewal_Month'] : $lead->GL_ren_month,
								'crime_insurance' => !$lead->crime_insurance && isset($data['Crime_Insurance']) && !empty($data['Crime_Insurance']) ? $data['Crime_Insurance'] : $lead->crime_insurance,
								'CI_ren_month' => !$lead->CI_ren_month && isset($data['Crime_Insurance_Renewal_Month']) && !empty($data['Crime_Insurance_Renewal_Month']) ? $data['Crime_Insurance_Renewal_Month'] : $lead->CI_ren_month,
								'directors_officers' => !$lead->directors_officers && isset($data['Directors_Officers']) && !empty($data['Directors_Officers']) ? $data['Directors_Officers'] : $lead->directors_officers,
								'DO_ren_month' => !$lead->DO_ren_month && isset($data['Directors_Officers_Renewal_Month']) && !empty($data['Directors_Officers_Renewal_Month']) ? $data['Directors_Officers_Renewal_Month'] : $lead->DO_ren_month,
								'workers_compensation' => !$lead->workers_compensation && isset($data['Workers_Compensation']) && !empty($data['Workers_Compensation']) ? $data['Workers_Compensation'] : $lead->workers_compensation,
								'WC_ren_month' => !$lead->WC_ren_month && isset($data['Workers_Compensation_Renewal_Month']) && !empty($data['Workers_Compensation_Renewal_Month']) ? $data['Workers_Compensation_Renewal_Month'] : $lead->WC_ren_month,
								'umbrella' => !$lead->umbrella && isset($data['Umbrella']) && !empty($data['Umbrella']) ? $data['Umbrella'] : $lead->umbrella,
								'U_ren_month' => !$lead->U_ren_month && isset($data['Umbrella_Renewal_Month']) && !empty($data['Umbrella_Renewal_Month']) ? $data['Umbrella_Renewal_Month'] : $lead->U_ren_month,
								'flood' => !$lead->flood && isset($data['Flood']) && !empty($data['Flood']) ? $data['Flood'] : $lead->flood,
								'F_ren_month' => !$lead->F_ren_month && isset($data['Flood_General_Liability_Renewal_Month']) && !empty($data['Flood_General_Liability_Renewal_Month']) ? $data['Flood_General_Liability_Renewal_Month'] : $lead->F_ren_month,

							]);

							$changes = $lead->getChanges(); //get what was updated
							foreach ($changes as $key => $c) {
								if ($key != "updated_at") {
									// store success to messages
									$dataSuccess->push('<i class="fas fa-file-import"></i> Row: ' . $updated . '- Lead ' . $lead->name . ' updated during import: empty field - <b>' . $niceNames[$key] . '</b> was updated to <b>' . $c . '</b>');
								}
							}
						}
					}

					$contact = false;
					$contact_slug = '';
					if ($lead) {
						$csvLeads->push($lead->id);
						$address_numeric = '';
						if (preg_match('/\d+/', $contactAddress1, $matches)) {
							$address_numeric = $matches[0];
						}
						$contactSlug =  $this->generateSlug([$contactFName, $contactLName, $address_numeric]);
						// search for contact
						$contact = $lead->contacts()->where('contact_slug', $contactSlug)->first();
					}


					if (!$contact && $lead && $contactSlug && $contactFName && $contactLName && $contactAddress1) {
						// echo 'empty contact and empty slug';
						try {
							// Create contact
							$contact = new Contact();
							$contact->c_full_name = $contactFName . ' ' . $contactLName;
							$contact->c_first_name = $contactFName;
							$contact->c_last_name = $contactLName;
							$contact->contact_slug = $contactSlug;
							$contact->c_title = isset($data['Contact_Title']) ? $data['Contact_Title'] : null;
							$contact->c_address1 = $contactAddress1;
							$contact->c_address2 = isset($data['Contact_Address2']) ? $data['Contact_Address2'] : null;
							$contact->c_city = isset($data['Contact_City']) ? $data['Contact_City'] : null;
							$contact->c_state = isset($data['Contact_State']) ? $data['Contact_State'] : null;
							$contact->c_zip = isset($data['Contact_Zip']) ? $data['Contact_Zip'] : null;
							$contact->c_county = isset($data['Contact_County']) ? $data['Contact_County'] : null;
							$contact->c_phone = isset($data['Contact_Phone']) ? $data['Contact_Phone'] : null;
							$contact->c_email = isset($data['Contact_Email']) ? $data['Contact_Email'] : null;
							$contact->save();
							$lead->contacts()->save($contact); //associate log to lead

							// dd($contact);
							//create log
							$contactlog = new Log();
							$contactlog->action = 'Import Contact to Lead : ' . $contactFName . ' ' . $contactLName;
							$contactlog->users()->associate(auth()->user())->save(); //associate user
							$lead->logs()->save($contactlog); //associate log to lead
							$dataSuccess->push('<i class="fas fa-level-up-alt"></i> Row: ' . $updated . '- Contact: <b>' . $contact->c_first_name . ' ' . $contact->c_last_name . '</b> for lead <b>' . $lead->name . '</b>  successfully imported.'); // get the success message to show in blade
						} catch (\Throwable $th) {
							toastr()->error($th);
							throw ($th);
						}
					} else if ($contact && $contactSlug) { //if the contact exists store the info message
						// echo ' contact and  slug present';
						if (!$newEntries) {
							$contact->update([
								'c_title' => !$contact->c_title && isset($data['Contact_Title']) && !empty($data['Contact_Title']) ? $data['Contact_Title'] : $contact->c_title,
								'c_address2' => !$contact->c_address2 && isset($data['Contact_Address2']) && !empty($data['Contact_Address2']) ? $data['Contact_Address2'] : $contact->c_address2,
								'c_phone' => !$contact->c_phone && isset($data['Contact_Phone']) && !empty($data['Contact_Phone']) ? $data['Contact_Phone'] : $contact->c_phone,
								'c_email ' => !$contact->c_email && isset($data['Contact_Email']) && !empty($data['Contact_Email']) ? $data['Contact_Email'] : $contact->c_email,
								'contact_slug ' => $contactSlug
							]);
							$contactChanges = $contact->getChanges();

							foreach ($contactChanges as $key => $c) {
								// store success to messages
								if ($key != "updated_at") {
									$dataSuccess->push('<i class="fas fa-file-import"></i> Contact <b>' . $contact->c_first_name . ' ' . $contact->c_last_name . '</b> already exists for lead <b>' . $contact->leads->name . '</b>. Contact updated during import: empty field - <b>' . $niceNames[$key] . '</b> was updated to <b>' . $c . '</b>');
								}
							}
						} else {
							$dataErrors->push(
								array(
									array(
										"row" => $updated,
										"attribute" => "",
										"errors" => "Contact " . $contact->c_first_name . ' ' . $contact->c_last_name . " already exists. It was skipped.",
										"values" => "",

									)
								)
							);
						}
					} else if (!$contactFName || !$contactLName || !$contactAddress1  || empty($contactSlug)) {
						$dataErrors->push(
							array(
								array(
									"row" => $updated,
									"attribute" => "",
									"errors" => "Contact Firstname, lastname and address are required and can not be empty. Contact was not imported ",
									"values" => "",

								)
							)
						);
					}
					// die();

					($responseDate && $data['Contact_First_Name'] && $data['Contact_Last_Name']) ? $actionName = $data['Contact_First_Name'] . ' ' . $data['Contact_Last_Name'] : $actionName = '';

					$newAction = false;

					if ($lead && $contactFName && $contactLName) {
						$newAction = $lead->actions()->where('contact_name', $actionName)->where('contact_date', $responseDate)->where('action', 'Phone')->where('contact_id', $contact ? $contact->id : '')->exists();
					}

					if (
						$responseDate && !$newAction && $lead && $contactFName && $contactLName
					) {
						try {
							// Create action
							$newAction = new Action();

							$newAction->action = 'Phone';
							$newAction->contact_name = $contact ? $contact->c_first_name . ' ' . $contact->c_last_name : $data['Contact_First_Name'] . ' ' . $data['Contact_Last_Name'];
							$newAction->contact_id = $contact ? $contact->id : null;
							$newAction->contact_date = $responseDate ? $responseDate : null;
							$newAction->save();

							$newAction->users()->associate(auth()->user())->save();
							$lead->actions()->save($newAction);
							//create log for the action
							$newActionlog = new Log();
							$newActionlog->action = 'Add Action trough import  : ' . $newAction->action . ', initiated by Contact - ' . $newAction->contact_name . ', on: ' . $responseDate . ' 00:00:00';
							$newActionlog->users()->associate(auth()->user())->save(); //associate user
							$lead->logs()->save($newActionlog); //associate log to lead

							$dataSuccess->push('<i class="fas fa-mouse-pointer"></i> Row: ' . $updated . '- Action created during import: <b>Phone - initiated by Contact ' . $newAction->contact_name . '</b> for Lead ' . $lead->name); //store success message


						} catch (\Throwable $th) {
							toastr()->error($th);
							throw ($th);
						}
					}
				}
			}




			// if Create campaign is checked, create a campaign and use the selected date and name
			if ($createCampaign != null && !empty($csvLeads)) {
				// $campaign = Campaign::where('name', $request->campaign_name)->where('campaign_date',$campaignDate)->first();
				// if(!$campaign){
				$campaign = new Campaign();
				$campaign->name = $request->campaign_name;
				$campaign->campaign_date = $campaignDate;
				$campaign->status = 'COMPLETED';
				$campaign->save();



				// get the success messages to show in blade
				$dataSuccess->push('<i class="fas fa-chart-bar nav-icon"></i> Campaign created during import: <b>' . $campaign->name . '</b>');
				// }


				foreach ($csvLeads as $id) {

					$leadAttached = $campaign->leads()->where('lead_id', $id)->exists();

					if (!$leadAttached) {

						$campaign->leads()->attach($id);

						$dataSuccess->push('<i class="fas fa-plus"></i> Lead ' . Lead::find($id)->name . ' attached to campaign');
						create_log(Lead::find($id), 'Attach Lead to Campaign : ' . $campaign->name, '');
					}
				}
				$campaign->update([
					'lead_number' => count($campaign->leads)

				]);
				update_leadActions($campaign);
			}

			// messages variable to use in blade
			$dataSuccess ? $messages['success'] = $dataSuccess : '';
			$dataErrors ? $messages['failures'] = $dataErrors : '';
			$row_updated_count = $updated - 1;
			toastr()->success($created . ' leads created and ' . $row_updated_count . ' rows processed!', 'Import Success!');
			return redirect()->back()->with('messages', $messages);
		} else { //if the file exension doesn't match the required
			toastr()->error('The file must be a file of type: csv, xlsx, xls.');
			return redirect()->back();
		}
	}

	public function process_contacts(Request $request)
	{

		$validator = Validator::make($request->all(), [
			// 'file' => 'required|file|mimes:csv,txt' // Ensure that 'file' is required and is a file
			'file' => 'required|file' // Ensure that 'file' is required and is a file
		]);

		if ($validator->fails()) {
			toastr()->error("File is required for using the import feature.");
			return back()->withErrors($validator->errors()->all());
		}

		$dataSuccess = collect();
		$dataErrors = collect();
		$csvLeads = collect();
		$createdLeads = array();
		$createCampaign = $request->create_campaign;
		$campaignDate = $request->campaign_date;
		$newEntries = $request->new_entries == 'on' ? true : false;


		// get the file extension in order to validate
		$extension = $request->file('file')->getClientOriginalExtension();
		if ($extension == "xlsx" || $extension == "xls" || $extension == "csv") { // if the extension matches, proceed
			$created = 0;
			//updated rows (except heading)
			$updated = 1;


			$path = $request->file('file')->getRealPath();
			$csv = array_map('str_getcsv', file($path));
			if (count($csv[0]) < 2) {
				$csv = array_map(function ($v) {
					return str_getcsv($v, "\t");
				}, file($path));
			}
			if (count($csv[0]) < 2) {
				$csv = array_map(function ($v) {
					return str_getcsv($v, ";");
				}, file($path));
			}
			if (count($csv[0]) < 2) {
				throw ValidationException::withMessages([
					'file' => 'Parsing failed, there is something terribly wrong with this csv!. Found only: ' . count($csv[0]) . ' columns',
				]);
			}

			foreach ($csv as $key => $row) {
				if ($key > 0) {

					$updated++;  //increment updated rows
					$slug = $row[9];
					$address = $row[2];

					// Use preg_match to extract the number
					if (preg_match('/\d+/', $address, $matches)) {
						$address = $matches[0];
					}
					$contactSlug =  $this->generateSlug([$row[0], $row[1], $address]);

					$contact = Contact::where('contact_slug', $contactSlug)->first();

					// dd($contact);
					if (!$contact && $contactSlug) {
						try {

							//create lead
							$contact = new Contact();
							$contact->c_full_name = trim($row[0] . ' ' . $row['1']);
							$contact->c_first_name = isset($row['0']) ? $row['0'] : null;
							$contact->c_last_name = isset($row['1']) ? $row['1'] : null;
							$contact->c_address1 = isset($row['2']) ? $row['2'] : null;
							$contact->c_address2 = isset($row['3']) ? $row['3'] : null;
							$contact->c_city = isset($row['4']) ? $row['4'] : null;
							$contact->c_state = isset($row['5']) ? $row['5'] : null;
							$contact->c_zip = isset($row['6']) ? $row['6'] : null;
							$contact->c_phone = isset($row['7']) ? $row['7'] : null;

							$contact->c_is_client = isset($row['8']) ? $row['8'] : null;
							$contact->contact_slug = $contactSlug;
							$contact->c_email = isset($row['9']) ? $row['9'] : null;
							$contact->save();

							//store success message
							$dataSuccess->push('<i class="fas fa-file-import"></i> Row: ' . $updated . '- Lead: <b>' . $contact->c_full_name . '</b>  successfully imported.'); // get the success

							$created++; // increment created leads
							$createdLeads[] = $contact->id;
						} catch (\Throwable $th) {
							toastr()->error($th);
							throw ($th);
						}
					} else if ($contact) {


						if (!$newEntries) {
							// update only if DB fields are empty
							$contact->update([
								'c_full_name' => isset($row['0']) && isset($row['1']) ? trim($row[0] . ' ' . $row['1']) : $contact->c_full_name,
								'contact_slug' => $contactSlug,
								'c_first_name' => isset($row['0']) ? $row['0'] : $contact->c_first_name,
								'c_last_name' => isset($row['1']) ? $row['1'] : $contact->c_last_name,
								'c_address1' => isset($row['2']) ? $row['2'] : $contact->c_address1,
								'c_address2' => isset($row['3']) ? $row['3'] : $contact->c_address2,
								'c_city' => isset($row['4']) ? $row['4'] : $contact->c_city,
								'c_state' => isset($row['5']) ? $row['5'] : $contact->c_state,
								'c_zip' => isset($row['6']) ? $row['6'] : $contact->c_zip,
								'c_phone' => isset($row['7']) ? $row['7'] : $contact->c_phone,
								'c_is_client' => isset($row['8']) ? $row['8'] : $contact->c_is_client,
								'c_email' => isset($row['9']) ? $row['9'] : $contact->c_email,
							]);




							if (!in_array($contact->id, $createdLeads)) {
								$dataErrors->push(
									array(
										array(
											"row" => $updated,
											"attribute" => "",
											"errors" => "Contact " . $contact->c_first_name . " already exists.",
											"values" => "",

										)
									)
								);
							}

							$changes = $contact->getChanges(); //get what was updated
							foreach ($changes as $key => $c) {
								if ($key != "updated_at") {
									// store success to messages
									$dataSuccess->push('<i class="fas fa-file-import"></i> Row: ' . $updated . '- Contact ' . $contact->c_name . ' updated during import: empty field - was updated to <b>' . $c . '</b>');
								}
							}
						} else {
							$dataErrors->push(
								array(
									array(
										"row" => $updated,
										"attribute" => "",
										"errors" => "Lead " . $contact->c_first_name . " already exists. It was skipped.",
										"values" => "",

									)
								)
							);
						}
					}
				}
			}
			$dataSuccess ? $messages['success'] = $dataSuccess : '';
			$dataErrors ? $messages['failures'] = $dataErrors : '';
			$row_updated = $updated - 1;
			toastr()->success($created . ' contacts created and ' . $row_updated . ' rows processed!', 'Import Success!');
			return redirect()->back()->with('messages', $messages);
		} else { //if the file exension doesn't match the required
			toastr()->error('The file must be a file of type: csv, xlsx, xls.');
			return redirect()->back();
		}
	}

	public function import_leads(Request $request)
	{
		// $importErrors=[];
		return view('leads.import'); //,compact('importErrors')

	}

	public function import_businesses()
	{

		return view('leads.importBusiness');
	}




	public function import_contacts()
	{
		// dd(Contact::where('c_first_name','John')->where('c_last_name','Quigley')->where('c_address1','112 Corey Colonial')->get());
		return view('leads.importContacts');
	}


	private function handleUpdateContactConditionally($contact, $data, $overwrite)
	{
		$interpolation = [
			'c_address2' => $data[3],
			'c_city' => $data[4],
			'c_state' => $data[5],
			'c_zip' => $data[6],
			'c_phone' => $data[7],
			'c_is_client' => isset($data[8]) ? $data[8] : null // new field not globally present
		];
		$x = 0;
		foreach ($contact->getFillable() as $key => $prop) {
			foreach ($interpolation as $key => $newValue) {
				if ($key == $prop && $newValue) {
					if (!$contact->$prop) {
						\Log::notice($contact->id . ' updating ' . $prop . ' to ' . $newValue);
						$contact->update([$prop => $newValue]);
						$x++;
					} else {
						// if($overwrite){
						if ($overwrite && $contact->$prop != $newValue) {
							\Log::notice($contact->id . ' updating (forced) ' . $prop . ' from ' . $contact->$prop . ' to ' . $newValue);
							$contact->update([$prop => $newValue]);
							$x++;
						}
					}
				}
			}
		}
		return $x;
	}
	/**
	 * Export the Leads from DB to CSV file
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function exportCsv(Request $request)
	{
		$leads = Session::get('leads');
		return (new LeadsExport($leads))->download('report.csv');
	}


	private static function readDataFromCsv($csvFile, $extension)
	{

		//store file
		$fileName = Carbon::now()->format('mdYHisu');
		//if the file is xlsx or xls , convert it to csv
		if ($extension == "xlsx" || $extension == "xls") {
			if ($extension == "xlsx") {
				$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xlsx");
			} else if ($extension == "xls") {
				$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xls");
			}

			$reader->setReadDataOnly(true);


			$path = '../storage/app/public/uploads/' . $fileName . '.csv';
			$excel = $reader->load($csvFile);
			// dd($excel);
			$writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($excel);
			// $writer->setUseBOM(true);
			// $writer->setOutputEncoding('UTF-8');
			$writer->setUseBOM(false);
			$writer->setOutputEncoding('UTF-8');
			$writer->setEnclosureRequired(false);
			$writer->save($path);

			$csvFile = $path;
		} else {

			$file = Storage::putFileAs('public/uploads', $csvFile, $fileName . '.csv');
		}

		$delimiter = ',';
		$header = null;
		$csvData = array();
		//the required columns
		$requiredColumns = array(
			0 => "Business_Name",
			1 => "Contact_First_Name",
			2 => "Contact_Last_Name",
			3 => "Contact_Address1",
			4 => "Contact_Zip",
			5 => "Contact_State",
			6 => "Contact_County",
			7 => "Contact_City",
		);
		//read data and add it to array
		if (($handle = fopen($csvFile, 'r')) !== false) {
			while (($row = fgetcsv($handle, 1000, $delimiter)) !== false) {

				if (!$header) {
					$header = $row;
					//loop trough required columns and if one of them is missing in csv, send error
					foreach ($requiredColumns as $req) {

						if (!in_array($req, $header)) {

							$ColumnError = 'Column ' . $req . ' is missing. File was not imported';
							return array('errors' => $ColumnError);
						}
					}
				} else {

					if (count($header) > count($row)) {

						$csvData[] = mb_convert_encoding(array_combine($header, array_pad($row, count($header), "")), 'UTF-8', 'UTF-8');
					} else if (count($header) < count($row)) {
						$csvData[] = mb_convert_encoding(array_combine($header, array_slice($rows, 0, count($header))), 'UTF-8', 'UTF-8');
					} else {
						$csvData[] = mb_convert_encoding(array_combine($header, $row), 'UTF-8', 'UTF-8');
					}
				}
			}
			fclose($handle);
		}
		return $csvData;
	}

	public function readDataFromBusinessCsv($csvFile, $extension)
	{

		//store file
		$fileName = Carbon::now()->format('mdYHisu');
		//if the file is xlsx or xls , convert it to csv
		if ($extension == "xlsx" || $extension == "xls") {
			if ($extension == "xlsx") {
				$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xlsx");
			} else if ($extension == "xls") {
				$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xls");
			}

			$reader->setReadDataOnly(true);


			$path = '../storage/app/public/uploads/' . $fileName . '.csv';
			$excel = $reader->load($csvFile);
			// dd($excel);
			$writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($excel);
			// $writer->setUseBOM(true);
			// $writer->setOutputEncoding('UTF-8');
			$writer->setUseBOM(false);
			$writer->setOutputEncoding('UTF-8');
			$writer->setEnclosureRequired(false);
			$writer->save($path);

			$csvFile = $path;
		} else {

			$file = Storage::putFileAs('public/uploads', $csvFile, $fileName . '.csv');
		}

		$delimiter = ',';
		$header = null;
		$csvData = array();
		//the required columns
		$requiredColumns = array(
			0 => "Business_Name"
		);
		//read data and add it to array
		if (($handle = fopen($csvFile, 'r')) !== false) {
			while (($row = fgetcsv($handle, 1000, $delimiter)) !== false) {

				if (!$header) {
					$header = $row;
					//loop trough required columns and if one of them is missing in csv, send error
					foreach ($requiredColumns as $req) {

						if (!in_array($req, $header)) {

							$ColumnError = 'Column ' . $req . ' is missing. File was not imported';
							return array('errors' => $ColumnError);
						}
					}
				} else {

					if (count($header) > count($row)) {

						$csvData[] = mb_convert_encoding(array_combine($header, array_pad($row, count($header), "")), 'UTF-8', 'UTF-8');
					} else if (count($header) < count($row)) {
						$csvData[] = mb_convert_encoding(array_combine($header, array_slice($rows, 0, count($header))), 'UTF-8', 'UTF-8');
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
	 *  Format data
	 * @param object $data
	 * @return array $messages
	 */
	private static function format_csv_data($data, $updated, $niceNames)
	{
		$dataErrors = collect();

		//the unit_count regex
		$unitCountMax4Dig = '/^\d{0,4}$/';

		// regex for numbers with decimals
		$twoDecimals = '/^[0-9]+(\.[0-9]{1,2})?$/';

		//store fields values
		$bussName = "Business_Name";
		$bussPremium = "Business_Premium";
		$bussInAm = "Business_Insured_Amount";
		$bussCrDate = "Business_Creation_Date";
		$bussRenDate = "Property_Insurance_Renewal_Date";
		$ResDate = "Response_Date";
		$ctPhone = "Contact_Phone";
		$insFlood = "Insurance_Flood";
		$unitCount = "Business_Unit_Count";
		$contactState = "Contact_State";
		$businessState = "Business_State";
		$bussRenMonth = "Property_Insurance_Renewal_Month";




		foreach ($data as $key => $r) { // loop trough row cells

			if ($key) { //if the eky is not empty
				switch ($key) {
						//field that needs int with 2 decimals
					case $bussPremium:
						if (preg_match($twoDecimals, $data[$key], $matches)) {
						} else if (!preg_match($twoDecimals, $data[$key], $matches) && !empty($data[$key])) {

							// validate 9999 digit max 4 numbers

							$dataErrors->push(
								array(
									"row" => $updated,
									"attribute" => $key,
									"errors" => "Invalid '" . $key . "'  value: " . $data[$key] . " - was not imported ",
									"values" => $data[$key],

								)
							);

							$data[$key] = null;
						} else if (empty($data[$key])) {
							$data[$key] = null;
						}

						break;
						//field that needs int with 2 decimals
					case $bussInAm:
						if (preg_match($twoDecimals, $data[$key], $matches)) {
						} else if (!preg_match($twoDecimals, $data[$key], $matches) && !empty($data[$key])) {

							// validate 9999 digit max 4 numbers

							$dataErrors->push(
								array(
									"row" => $updated,
									"attribute" => $key,
									"errors" => "Invalid '" . $key . "'  value: " . $data[$key] . " - was not imported ",
									"values" => $data[$key],

								)
							);

							$data[$key] = null;
						} else if (empty($data[$key])) {
							$data[$key] = null;
						}

						break;
						//field that needs dates with specific format YYYY-MM-DD
					case $bussCrDate:
						if (!empty($data[$key])) {
							$data[$key] = self::format_cell_date($data, $key, $dataErrors, $updated, $niceNames);
						} else {
							$data[$key] = null;
						}
						break;
						//field that needs dates with specific format YYYY-MM-DD
					case $bussRenDate:

						if (!empty($data[$key])) {

							$data[$key] = self::format_cell_date($data, $key, $dataErrors, $updated, $niceNames);
						} else {
							$data[$key] = null;
						}

						break;
						//field that needs dates with specific format YYYY-MM-DD
					case $ResDate:

						if (!empty($data[$key])) {
							$data[$key] = self::format_cell_date($data, $key, $dataErrors, $updated, $niceNames);
						} else {
							$data[$key] = null;
						}

						break;

					case $ctPhone:
						if (isset($data[$ctPhone]) && !empty($data[$ctPhone])) {
							//format phone to xxx-xxx-xxxx
							$phone = $data[$key];
							if (preg_match('/[-!$%^&*()_+|~=`{}\[\]:";<>?,. \/]/', $phone, $matches)) {
								$phone = preg_replace('/[-!$%^&*()_+|~=`{}\[\]:";<>?,. \/]/', '', $phone);
							}

							$data[$key] = $phone;
						} else if (empty($data[$key])) {
							$data[$key] = null;
						}

						break;

					case $insFlood:

						$data[$key] = ucwords($data[$key]);

						break;

					case $unitCount:
						if (preg_match($unitCountMax4Dig, $data[$key], $matches)) {
							//do nothing  . Didn't work by making condition using only !preg_match condition
						} else if (!preg_match($unitCountMax4Dig, $data[$key], $matches) && !empty($data[$key])) {
							// validate 9999 digit max 4 numbers

							$dataErrors->push(
								array(
									"row" => $updated,
									"attribute" => $key,
									"errors" => "Invalid " . $key . " value: " . $data[$key] . "- was not imported",
									"values" => $data[$key],

								)
							);
							$data[$key] = null;
						} else if (empty($data[$key])) {
							$data[$key] = null;
						}


						break;

					case $contactState:
						$data[$key] = self::format_state($data[$key]);
						break;
					case $businessState:
						$data[$key] = self::format_state($data[$key]);
						break;
					case $bussRenMonth:
						$data['Property_Insurance_Renewal_Month'] = ucwords($data['Property_Insurance_Renewal_Month']);
						break;
				}
			}
		}

		return array('data' => $data, 'errors' => $dataErrors);
	}
	public static function format_state($state)
	{
		switch ($state) {
			case 'Alabama':
				$state = 'AL';
				break;
			case 'Alaska':
				$state = 'AK';
				break;
			case 'Arizona':
				$state = 'AZ';
				break;
			case 'Arkansas':
				$state = 'AR';
				break;
			case 'California':
				$state = 'CA';
				break;
			case 'Colorado':
				$state = 'CO';
				break;
			case 'Connecticut':
				$state = 'CT';
				break;
			case 'Delaware':
				$state = 'DE';
				break;
			case 'District of Columbia':
				$state = 'DC';
				break;
			case 'Florida':
				$state = 'FL';
				break;
			case 'Georgia':
				$state = 'GA';
				break;
			case 'Hawaii':
				$state = 'HI';
				break;
			case 'Idaho':
				$state = 'ID';
				break;
			case 'Illinois':
				$state = 'IL';
				break;
			case 'Indiana':
				$state = 'IN';
				break;
			case 'Iowa':
				$state = 'IA';
				break;
			case 'Kansas':
				$state = 'KS';
				break;
			case 'Kentucky':
				$state = 'KY';
				break;
			case 'Louisiana':
				$state = 'LA';
				break;
			case 'Maine':
				$state = 'ME';
				break;
			case 'Maryland':
				$state = 'MD';
				break;
			case 'Massachusetts':
				$state = 'MA';
				break;
			case 'Michigan':
				$state = 'MI';
				break;
			case 'Minnesota':
				$state = 'MN';
				break;
			case 'Mississippi':
				$state = 'MS';
				break;
			case 'Missouri':
				$state = 'MO';
				break;
			case 'Montana':
				$state = 'MT';
				break;
			case 'Nebraska':
				$state = 'NE';
				break;
			case 'Nevada':
				$state = 'NV';
				break;
			case 'New Hampshire':
				$state = 'NH';
				break;
			case 'New Jersey':
				$state = 'NJ';
				break;
			case 'New Mexico':
				$state = 'NM';
				break;
			case 'New York':
				$state = 'NY';
				break;
			case 'North Carolina':
				$state = 'NC';
				break;
			case 'North Dakota':
				$state = 'ND';
				break;
			case 'Ohio':
				$state = 'OH';
				break;
			case 'Oklahoma':
				$state = 'OK';
				break;
			case 'Oregon':
				$state = 'OR';
				break;
			case 'Pennsylvania':
				$state = 'PA';
				break;
			case 'Rhode Island':
				$state = 'RI';
				break;
			case 'South Carolina':
				$state = 'SC';
				break;
			case 'South Dakota':
				$state = 'SD';
				break;
			case 'Tennessee':
				$state = 'TN';
				break;
			case 'Texas':
				$state = 'TX';
				break;
			case 'Utah':
				$state = 'UT';
				break;
			case 'Vermont':
				$state = 'VT';
				break;
			case 'Virginia':
				$state = 'VA';
				break;
			case 'Washington':
				$state = 'WA';
				break;
			case 'West Virginia':
				$state = 'WV';
				break;
			case 'Wisconsin':
				$state = 'WI';
				break;
			case 'Wyoming':
				$state = 'WY';
				break;
			default:
				$state = ucwords($state);
		}

		return $state;
	}

	/**
	 *  Format dates
	 * @param object $data
	 * @return value $data[key]
	 */
	public static function format_cell_date($data, $key, $dataErrors, $updated, $niceNames)
	{
		// regex for dates
		$dayMonthYear = '/^(0?[1-9]|[12]\d|3[01]).(0?[1-9]|1[012]).(?:[0-9]{4})$/';
		$YearMonthDay = '/^(?:[0-9]{4}).[0-3]?[0-9].[0-3]?[0-9]$/';
		$MonthDay = '/^(0?[1-9]|1[012]).(0?[1-9]|[12]\d|3[01])$/';
		$monthDayYear = '/^(0?[1-9]|1[012]).(0?[1-9]|[12]\d|3[01]).(?:[0-9]{4})$/';


		switch ($data[$key]) {
			case (preg_match($dayMonthYear, $data[$key], $matches) ? true : false):
				// convert from d/m/y:
				//  01/01/2011 or 01.01.2011  or 01-01-2011
				//  01/1/2011  or 01.1.2011   or 01-1-2011
				//  1/11/2011  or 1.11.2011   or 1-11-2011
				//to "2011-1-11" y-m-d;

				$data[$key] = preg_replace('/[-!\\\\$%^&*()_+|~=`{}\[\]:";<>?,.\/ ]/', '-', $data[$key]);
				$newDate = explode('-', $data[$key]);
				$data[$key] = (count($newDate) == 3) ? $newDate[2] . '-' . $newDate[1] . '-' . $newDate[0]
					: $dataErrors->push(
						array(
							"row" => $updated,
							"attribute" => $key,
							"errors" => "Invalid " . $key . " date format  value: " . $data[$key] . " - was not imported",
							"values" => $data[$key],

						)
					);

				break;

			case (preg_match($MonthDay, $data[$key], $matches) ? true : false):
				//convert from 12/31 m/d in 12/31/2021 y-m-d
				$data[$key] = preg_replace('/[-!\\\\$%^&*()_+|~=`{}\[\]:";<>?,.\/ ]/', '-', $data[$key]);

				$newDate = explode('-', $data[$key]);
				if (!isset($newDate[1])) {
					$data[$key] = null;
				} else {
					$data[$key] = date("Y") . '-' . $newDate[0] . '-' . $newDate[1];
				}


				break;

			case (preg_match($monthDayYear, $data[$key], $matches) ? true : false):
				// convert 7/28/1977 to 1977-28-07  (MM-DD-YYYY)
				$data[$key] = preg_replace('/[-!\\\\$%^&*()_+|~=`{}\[\]:";<>?,.\/ ]/', '-', $data[$key]);
				$newDate = explode('-', $data[$key]);
				$data[$key] = $newDate[2] . '-' . $newDate[1] . '-' . $newDate[0];
				break;
			case (preg_match($YearMonthDay, $data[$key], $matches) ? true : false):
				// convert from :
				// 2020.12.25 - any char instead of "-", will be converted to -
				//to "12-25-2011";
				$data[$key] = preg_replace('/[-!\\\\$%^&*()_+|~=`{}\[\]:";<>?,.\/ ]/', '-', $data[$key]);
				$newDate = explode('-', $data[$key]);
				$data[$key] = $newDate[0] . '-' . $newDate[1] . '-' . $newDate[2];


				break;

			case ((!preg_match($dayMonthYear, $data[$key], $matches) ? true : false)
				&& (!preg_match($MonthDay, $data[$key], $matches) ? true : false)
				&& (!preg_match($YearMonthDay, $data[$key], $matches) ? true : false)
				&& (!preg_match($monthDayYear, $data[$key], $matches) ? true : false)
				&& (!empty($data[$key]) ? true : false)
			): // if the date doesn't match the regez 

				$dataErrors->push(
					array(
						"row" => $updated,
						"attribute" => $key,
						"errors" => "Invalid " . $key . " date format  value: " . $data[$key] . " - was not imported",
						"values" => $data[$key],

					)
				);

				$data[$key] = null;
				break;

			default:
				$data[$key] = null;
				$dataErrors->push(
					array(
						"row" => $updated,
						"attribute" => $key,
						"errors" => "Invalid " . $key . " date format  value: " . $data[$key] . " - was not imported",
						"values" => $data[$key],

					)
				);
		}
		return $data[$key];
	}
}
