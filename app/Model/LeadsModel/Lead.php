<?php

namespace App\Model\LeadsModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;
use Illuminate\Support\Facades\Schema;
use App\Model\File;
use App\Model\Campaign;
use App\Model\Carrier;
use App\Model\Rating;
use App\Model\LeadSource;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use App\Model\ContactStatus;
use App\Model\LeadAdditionalPolicy;
use App\Model\LeadAsanaDetail;
use Illuminate\Support\Facades\Cache;
use App\Model\User;

use App\Traits\CommonFunctionsTrait;


class Lead extends Model
{
	use HasFactory, SoftDeletes,CommonFunctionsTrait;

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = ['deleted_at'];
	protected $fillable = [
		'type', 'name', 'creation_date', 'address1', 'address2', 'city', 'state', 'zip', 'county', 'unit_count', 'renewal_date', 'renewal_month', 'premium', 'premium_year', 'insured_amount', 'insured_year', 'manag_company', 'prop_manager', 'current_agency', 'current_agent', 'ins_prop_carrier', 'renewal_carrier_month', 'ins_flood', 'prop_floor', 'roof_geom', 'roof_covering', 'general_liability', 'GL_ren_month', 'crime_insurance', 'CI_ren_month', 'directors_officers', 'DO_ren_month', 'workers_compensation', 'WC_ren_month', 'umbrella', 'U_ren_month', 'flood', 'F_ren_month', 'county_id', 'coastal', 'latitude', 'longitude', 'is_added_by_bot', 'lead_slug', 'merge_status', 'is_client', 'sunbiz_list_url', 'sunbiz_details_url','pipeline_status_id','pipeline_agent_id','total_square_footage','roof_connection','roof_year','pool','lakes','clubhouse','tennis_basketball','other_community_info','iso','appraisal_name','appraisal_company','appraisal_date','incumbent_agency','incumbent_agent',
		'policy_renewal_date','wind_mitigation_date','rating','hurricane_deductible','hurricane_deductible_occurrence','skin_hole','all_other_perils','ordinance_of_law','tiv_matches_appraisal','secondary_water_insurance','opening_protection','gl_expiry_premium','gl_policy_renewal_date','gl_rating','gl_exclusions','gl_other_exclusions','ci_expiry_premium','ci_policy_renewal_date','ci_rating','employee_theft','operating_reserves','pending_litigation','litigation_date','do_expiry_premium','do_policy_renewal_date','do_rating','claims_made','umbrella_expiry_premium',
		'umbrella_policy_renewal_date','umbrella_rating','umbrella_exclusions','umbrella_other_exclusions','wc_expiry_premium','wc_policy_renewal_date','wc_rating','employee_count','employee_payroll','flood_expiry_premium','flood_policy_renewal_date','flood_rating','elevation_certificate','loma_letter','gl_insurance_coverage','ci_insurance_coverage','do_insurance_coverage','u_insurance_coverage','wc_insurance_coverage','f_insurance_coverage','lead_source','correct_underlying','property_insurance_coverage','business_tiv','difference_in_condition','dic_ren_month','dic_expiry_premium','dic_policy_renewal_date','dic_hurricane_deductible','dic_all_other_perils','dic_insurance_coverage','x_wind','xw_ren_month','xw_expiry_premium','xw_policy_renewal_date','xw_hurricane_deductible','xw_all_other_perils','xw_insurance_coverage','equipment_breakdown','eb_ren_month','eb_expiry_premium','eb_policy_renewal_date','eb_hurricane_deductible','eb_all_other_perils','eb_insurance_coverage','commercial_automobiles','ca_ren_month','ca_expiry_premium','ca_policy_renewal_date','ca_hurricane_deductible','ca_all_other_perils','ca_insurance_coverage','marina','m_ren_month','m_expiry_premium','m_policy_renewal_date','m_hurricane_deductible','m_all_other_perils','m_insurance_coverage','sunbiz_registered_name','sunbiz_registered_address','total_premium'
	];

	public function propertyCarrier()
	{
		return $this->belongsTo(Carrier::class, 'ins_prop_carrier');
	}

	public function glCarrier()
	{
		return $this->belongsTo(Carrier::class, 'general_liability');
	}

	public function ciCarrier()
	{
		return $this->belongsTo(Carrier::class, 'crime_insurance');
	}

	public function doCarrier()
	{
		return $this->belongsTo(Carrier::class, 'directors_officers');
	}

	public function umbrellaCarrier()
	{
		return $this->belongsTo(Carrier::class, 'umbrella');
	}

	public function wcCarrier()
	{
		return $this->belongsTo(Carrier::class, 'workers_compensation');
	}

	public function floodCarrier()
	{
		return $this->belongsTo(Carrier::class, 'flood');
	}

	public function dcCarrier()
	{
		return $this->belongsTo(Carrier::class, 'difference_in_condition');
	}

	public function xwindCarrier()
	{
		return $this->belongsTo(Carrier::class, 'x_wind');
	}

	public function ebCarrier()
	{
		return $this->belongsTo(Carrier::class, 'equipment_breakdown');
	}

	public function caCarrier()
	{
		return $this->belongsTo(Carrier::class, 'commercial_automobiles');
	}

	public function marinaCarrier()
	{
		return $this->belongsTo(Carrier::class, 'marina');
	}

	public function propertyRating()
	{
		return $this->belongsTo(Rating::class, 'rating');
	}

	public function generaLiablityRating()
	{
		return $this->belongsTo(Rating::class, 'gl_rating');
	}

	public function crimeInsuranceRating()
	{
		return $this->belongsTo(Rating::class, 'ci_rating');
	}

	public function directorOfficerRating()
	{
		return $this->belongsTo(Rating::class, 'do_rating');
	}

	public function uRating()
	{
		return $this->belongsTo(Rating::class, 'umbrella_rating');
	}

	public function workerCompansestionRating()
	{
		return $this->belongsTo(Rating::class, 'wc_rating');
	}

	public function fRating()
	{
		return $this->belongsTo(Rating::class, 'flood_rating');
	}

	public function contactscraps()
	{
		return $this->hasMany(ContactScrap::class, 'lead_id');
	}

	public function contacts()
	{

		return $this->hasMany(Contact::class);
	}

	public function logs()
	{

		return $this->hasMany(Log::class);
	}

	public function notes()
	{

		return $this->hasMany(Note::class);
	}

	public function insurances()
	{

		return $this->hasMany(Insurance::class);
	}

	public function files()
	{
		return $this->morphMany(File::class, 'uploaded_files');
	}

	public function actions()
	{

		return $this->hasMany(Action::class);
	}

	public function leadAdditionalpolicy()
	{

		return $this->hasMany(LeadAdditionalPolicy::class);
	}

	public function collaborators()
	{
	    return $this->belongsToMany(User::class, 'collaborators', 'lead_id', 'user_id');
	}


	public function campaigns()
	{
		return $this->belongsToMany(Campaign::class, 'campaigns_leads');
	}

	public function leadAsanaDetail()
    {
        return $this->hasOne(LeadAsanaDetail::class, 'lead_id', 'id');
    }

    // Attempt to get assigned user directly through hasOneThrough
    public function assignedUser()
    {
    	return $this->hasOne(User::class, 'id', 'assigned_user_id');
    }

    public function customUserGetting()
	{
	    if ($this->assigned_user_id && $this->assigned_user_id == -1) {
	        return (object)[
	            'id' => -1,
	            'name' => 'Service Team',
	            'email' => '',
	            'laravel_through_key' => null
	        ]; 
	    }
	    return $this->assigned_user;
	}

	// get date and integer columns
	public function scopeGet_column_type()
	{
		//get integer columns
		return Cache::rememberForever('lead_column_types', function () {
			$tableheading  = Schema::getColumnListing('leads'); //get columns name
			$columns = array();
			foreach ($tableheading as $head) {
				$type =  DB::connection()->getDoctrineColumn('leads', $head)->getType()->getName(); //get column type
				if ($type == 'bigint' && $head != 'id' || $type == 'decimal' && $head != 'id') { //if it is integer or decimal, except the id table
					$columns['number'][] = $head;
				} else if ($type == 'date') { //if it is integer or decimal, except the id table
					$columns['date'][] = $head;
				} else {
					$columns['other'][] = $head;
				}
			}
			// Adding distance manually
			array_push($columns['number'], 'distance');
			return $columns;
		});
	}

	public static function Lead_States(): array
	{

		return array(
			'' => 'Select State',
			'AL' => 'Alabama',
			'AK' => 'Alaska',
			'AZ' => 'Arizona',
			'AR' => 'Arkansas',
			'CA' => 'California',
			'CO' => 'Colorado',
			'CT' => 'Connecticut',
			'DE' => 'Delaware',
			'DC' => 'District of Columbia',
			'FL' => 'Florida',
			'GA' => 'Georgia',
			'HI' => 'Hawaii',
			'ID' => 'Idaho',
			'IL' => 'Illinois',
			'IN' => 'Indiana',
			'IA' => 'Iowa',
			'KS' => 'Kansas',
			'KY' => 'Kentucky',
			'LA' => 'Louisiana',
			'ME' => 'Maine',
			'MD' => 'Maryland',
			'MA' => 'Massachusetts',
			'MI' => 'Michigan',
			'MN' => 'Minnesota',
			'MS' => 'Mississippi',
			'MO' => 'Missouri',
			'MT' => 'Montana',
			'NE' => 'Nebraska',
			'NV' => 'Nevada',
			'NH' => 'New Hampshire',
			'NJ' => 'New Jersey',
			'NM' => 'New Mexico',
			'NY' => 'New York',
			'NC' => 'North Carolina',
			'ND' => 'North Dakota',
			'OH' => 'Ohio',
			'OK' => 'Oklahoma',
			'OR' => 'Oregon',
			'PA' => 'Pennsylvania',
			'RI' => 'Rhode Island',
			'SC' => 'South Carolina',
			'SD' => 'South Dakota',
			'TN' => 'Tennessee',
			'TX' => 'Texas',
			'UT' => 'Utah',
			'VT' => 'Vermont',
			'VA' => 'Virginia',
			'WA' => 'Washington',
			'WV' => 'West Virginia',
			'WI' => 'Wisconsin',
			'WY' => 'Wyoming'
		);
	}

	public static function contactTitle(): array
	{
		return array('' => 'Select Title', 'President' => 'President', 'Vice President' => 'Vice President', 'Treasurer' => 'Treasurer', 'Secretary' => 'Secretary', 'Director' => 'Director', 'Property Manager' => 'Property Manager');
	}

	public static function Lead_Counties(): array
	{
		return array(
			'' => 'Select County',
			"Alachua" => "Alachua",
			"Baker" => "Baker",
			"Bay" => "Bay",
			"Bradford" => "Bradford",
			"Brevard" => "Brevard",
			"Broward" => "Broward",
			"Calhoun" => "Calhoun",
			"Charlotte" => "Charlotte",
			"Citrus" => "Citrus",
			"Clay" => "Clay",
			"Collier" => "Collier",
			"Columbia" => "Columbia",
			"DeSoto" => "DeSoto",
			"Dixie" => "Dixie",
			"Duval" => "Duval",
			"Escambia" => "Escambia",
			"Flagler" => "Flagler",
			"Franklin" => "Franklin",
			"Gadsden" => "Gadsden",
			"Gilchrist" => "Gilchrist",
			"Glades" => "Glades",
			"Gulf" => "Gulf",
			"Hamilton" => "Hamilton",
			"Hardee" => "Hardee",
			"Hendry" => "Hendry",
			"Hernando" => "Hernando",
			"Highlands" => "Highlands",
			"Hillsborough" => "Hillsborough",
			"Holmes" => "Holmes",
			"Indian River" => "Indian River",
			"Jackson" => "Jackson",
			"Jefferson" => "Jefferson",
			"Lafayette" => "Lafayette",
			"Lake" => "Lake",
			"Lee" => "Lee",
			"Leon" => "Leon",
			"Levy" => "Levy",
			"Liberty" => "Liberty",
			"Madison" => "Madison",
			"Manatee" => "Manatee",
			"Marion" => "Marion",
			"Martin" => "Martin",
			"Miami-Dade" => "Miami-Dade",
			"Monroe" => "Monroe",
			"Nassau" => "Nassau",
			"Okaloosa" => "Okaloosa",
			"Okeechobee" => "Okeechobee",
			"Orange" => "Orange",
			"Osceola" => "Osceola",
			"Palm Beach" => "Palm Beach",
			"Pasco" => "Pasco",
			"Pinellas" => "Pinellas",
			"Polk" => "Polk",
			"Putnam" => "Putnam",
			"Santa Rosa" => "Santa Rosa",
			"Sarasota" => "Sarasota",
			"Seminole" => "Seminole",
			"St. Johns" => "St. Johns",
			"St. Lucie" => "St. Lucie",
			"Sumter" => "Sumter",
			"Suwannee" => "Suwannee",
			"Taylor" => "Taylor",
			"Union" => "Union",
			"Volusia" => "Volusia",
			"Wakulla" => "Wakulla",
			"Walton" => "Walton",
			"Washington" => "Washington",
			"other" => "Other"
		);
	}

	public static function Lead_Months(): array
    {
        return [
            '' => 'Select month',
			'January' => 'January',
			'February' => 'February',
			'March' => 'March',
			'April' => 'April',
			'May' => 'May',
			'June' => 'June',
			'July' => 'July',
			'August' => 'August',
			'September' => 'September',
			'October' => 'October',
			'November' => 'November',
			'December' => 'December'
        ];
    }
	public static function Lead_Roof_Covering(): array
	{
		return array(
			'' => 'Select Roof Covering',
			'Shingle' => 'Shingle',
			'Concrete Tile' => 'Concrete Tile',
			'Metal' => 'Metal',
			'Built Up' => 'Built Up',
			'Membrane' => 'Membrane',
			'Concrete' => 'Concrete',
			'Other' => 'Other',
		);
	}
	public function leadSource()
    {
        return $this->hasOne(LeadSource::class,'id', 'lead_source');
    }
	public static function Lead_Roof_Geometry(): array
	{
		return array(
			'' => 'Select Roof Geometry',
			'Hip' => 'Hip',
			'Gable' => 'Gable',
			'Flat' => 'Flat',
			'Other' => 'Other',
		);
	}

	public function scopegetFullNameAttribute()
	{
		return $this->first_name . ' ' . $this->last_name;
	}


	public function dialings()
	{
		return $this->belongsToMany(Dialing::class, 'dialings_leads');
	}


	//evaluate Crawler Leads before storing
	public static function evaluateCrawlerLeads($allLeads,$search_keyword)
	{
		// dd($allLeads);
		$storedLeads = [];
		$skippedLeadsCount = 0;

		$leadModel = new self;
		foreach ($allLeads as $key => $lead) {

			$lead_slug = $search_keyword . '-' . $lead['businessName'] . '-' . $lead['city'] . '-' . $lead['zip'];
			echo $lead_slug;
			$lead['lead_slug'] = strtolower(str_replace(" ", "-", $lead_slug));
			$lead['businessName'] = $leadModel->removeSpecialCharacters($lead['businessName']);

			if ($lead_slug) {

				$slugExistance = $leadModel->checkLeadSlugExistanceWithDistance($lead_slug, $lead['latitude'], $lead['longitude']);
				$lead['lead_slug'] = $lead_slug;
				if (is_array($slugExistance) && isset($slugExistance["existanceCount"]) && $slugExistance['existanceCount'] > 0) {
					$skippedLeadsCount++;
				}
				else{
					$checkIfLeadExists = $leadModel->getLeadsByName($lead['businessName']);

					if ($checkIfLeadExists != '') {
						//check for Percentage
						$matchPercentage = $leadModel->getStringsSimilarityPercentage($lead['businessName'], $checkIfLeadExists);

						if ($matchPercentage > 0) {
							echo "dublicate---";
							$skippedLeadsCount++;
						} else {
							echo "new---2";
							$leadId = $leadModel->createLead($lead,$search_keyword);
							array_push($storedLeads, $leadId);
						}
					} else {
						//store the leads
						echo "new---1";
						$leadId = $leadModel->createLead($lead,$search_keyword);
						array_push($storedLeads, $leadId);
					}
				}
			}
			else{
				$skippedLeadsCount++;
			}
		}
		return array(
			'storedLeads' => $storedLeads,
			'skippedLeadsCount' => $skippedLeadsCount
		);
	}

	//store leads
	public static function createLead($lead, $search_keyword)
	{
		// dd($lead);
		$leadData = [
			'type' => $lead['type'],
			'name' => $lead['businessName'],
			'lead_slug' => $lead['lead_slug'],
			'is_added_by_bot' => 1,
			'address1' => $lead['address1'],
			'address2' => '',
			'city' => $lead['city'],
			'state' => $lead['state'],
			'zip' => $lead['zip'],
			'creation_date' => Carbon::now(),
			'county' => $lead['county'],
			'county_id' => $lead['county_id'],
			'latitude' => $lead['latitude'],
			'longitude' => $lead['longitude'],
		];
		$lead = Lead::create($leadData);
		return $lead->id;
	}

	//get Strings Similarity Percentage
	public static function getStringsSimilarityPercentage($str1, $str2)
	{
		// Remove Roman numerals and Arabic numerals from both strings
		$str1 = preg_replace('/\b[IVXLCDM\d]+\b/', '', $str1);
		$str2 = preg_replace('/\b[IVXLCDM\d]+\b/', '', $str2);
		similar_text($str1, $str2, $percentage);
		$percentage  = (int) round($percentage);
		if ($percentage === 100) {
			// Check if both strings contain Roman numerals or Arabic numerals
			$containsNumeral1 = preg_match('/\b[IVXLCDM]+\b/', $str1);
			$containsNumeral2 = preg_match('/\b[IVXLCDM]+\b/', $str2);
			// If both strings contain Roman numerals or Arabic numerals, return 0% match
			if ($containsNumeral1 && $containsNumeral2) {
				return (int) 0;
			}
		}
		return $percentage;
	}

	//get Leads By Name
	public static function getLeadsByName($leadName)
	{
		$getLead = Lead::where('name', 'LIKE', "%$leadName%")->get()->pluck('name');
		if (count($getLead) > 0) {
			return $getLead[0];
		}
		return true;
	}

	public function leadStatus()
    {
        return $this->hasOne(ContactStatus::class, 'id', 'pipeline_status_id');
    }

    public function ownedAgent()
    {
    	return $this->hasOne(User::class, 'id', 'pipeline_agent_id');
    }
}
