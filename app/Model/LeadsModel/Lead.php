<?php

namespace App\Model\LeadsModel;

use App\Traits\CommonFunctionsTrait;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use App\Model\LeadsModel\LeadTrait\LeadStaticDataTrait;
use App\Model\LeadsModel\LeadTrait\LeadRelationshipsTraitOne;
use App\Model\LeadsModel\LeadTrait\LeadRelationshipsTraitTwo;

/**
 * Lead Model representing business leads in the insurance system.
 *
 * @property int $id
 * @property string $type
 * @property string $name
 * @property string|null $address1
 * @property string|null $address2
 * @property string|null $city
 * @property string|null $state
 * @property string|null $zip
 * @property string|null $county
 * @property float|null $latitude
 * @property float|null $longitude
 * @property int|null $pipeline_status_id
 * @property int|null $pipeline_agent_id
 * @property float|null $total_premium
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class Lead extends Model
{
    use CommonFunctionsTrait;
    use HasFactory;
    use SoftDeletes;
    use LeadStaticDataTrait;
    use LeadRelationshipsTraitOne;
    use LeadRelationshipsTraitTwo;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * The attributes that are mass assignable.
     * Split into multiple lines for better readability (max 120 chars per line).
     *
     * @var array
     */
    protected $fillable = [
        // Basic Information
        'type',
        'name',
        'creation_date',
        'address1',
        'address2',
        'city',
        'state',
        'zip',
        'county',
        'county_id',

        // Location
        'latitude',
        'longitude',

        // Property Details
        'unit_count',
        'total_square_footage',
        'roof_connection',
        'roof_year',
        'roof_geom',
        'roof_covering',
        'prop_floor',
        'pool',
        'lakes',
        'clubhouse',
        'tennis_basketball',
        'other_community_info',

        // Insurance Information
        'renewal_date',
        'renewal_month',
        'premium',
        'premium_year',
        'insured_amount',
        'insured_year',
        'total_premium',

        // Company Information
        'manag_company',
        'prop_manager',
        'current_agency',
        'current_agent',
        'incumbent_agency',
        'incumbent_agent',

        // Property Carrier
        'ins_prop_carrier',
        'renewal_carrier_month',
        'ins_flood',

        // General Liability
        'general_liability',
        'GL_ren_month',
        'gl_expiry_premium',
        'gl_policy_renewal_date',
        'gl_rating',
        'gl_exclusions',
        'gl_other_exclusions',
        'gl_insurance_coverage',

        // Crime Insurance
        'crime_insurance',
        'CI_ren_month',
        'ci_expiry_premium',
        'ci_policy_renewal_date',
        'ci_rating',
        'ci_insurance_coverage',
        'employee_theft',
        'operating_reserves',

        // Directors & Officers
        'directors_officers',
        'DO_ren_month',
        'do_expiry_premium',
        'do_policy_renewal_date',
        'do_rating',
        'claims_made',
        'do_insurance_coverage',

        // Workers Compensation
        'workers_compensation',
        'WC_ren_month',
        'wc_expiry_premium',
        'wc_policy_renewal_date',
        'wc_rating',
        'employee_count',
        'employee_payroll',
        'wc_insurance_coverage',

        // Umbrella
        'umbrella',
        'U_ren_month',
        'umbrella_expiry_premium',
        'umbrella_policy_renewal_date',
        'umbrella_rating',
        'umbrella_exclusions',
        'umbrella_other_exclusions',
        'u_insurance_coverage',

        // Flood
        'flood',
        'F_ren_month',
        'flood_expiry_premium',
        'flood_policy_renewal_date',
        'flood_rating',
        'elevation_certificate',
        'loma_letter',
        'f_insurance_coverage',

        // Difference In Conditions
        'difference_in_condition',
        'dic_ren_month',
        'dic_expiry_premium',
        'dic_policy_renewal_date',
        'dic_hurricane_deductible',
        'dic_all_other_perils',
        'dic_insurance_coverage',

        // X-Wind
        'x_wind',
        'xw_ren_month',
        'xw_expiry_premium',
        'xw_policy_renewal_date',
        'xw_hurricane_deductible',
        'xw_all_other_perils',
        'xw_insurance_coverage',

        // Equipment Breakdown
        'equipment_breakdown',
        'eb_ren_month',
        'eb_expiry_premium',
        'eb_policy_renewal_date',
        'eb_hurricane_deductible',
        'eb_all_other_perils',
        'eb_insurance_coverage',

        // Commercial Automobiles
        'commercial_automobiles',
        'ca_ren_month',
        'ca_expiry_premium',
        'ca_policy_renewal_date',
        'ca_hurricane_deductible',
        'ca_all_other_perils',
        'ca_insurance_coverage',

        // Marina
        'marina',
        'm_ren_month',
        'm_expiry_premium',
        'm_policy_renewal_date',
        'm_hurricane_deductible',
        'm_all_other_perils',
        'm_insurance_coverage',

        // Additional Fields
        'coastal',
        'iso',
        'lead_source',
        'lead_slug',
        'merge_status',
        'is_client',
        'is_added_by_bot',
        'sunbiz_list_url',
        'sunbiz_details_url',
        'sunbiz_registered_name',
        'sunbiz_registered_address',

        // Appraisal
        'appraisal_name',
        'appraisal_company',
        'appraisal_date',

        // Policy Details
        'policy_renewal_date',
        'wind_mitigation_date',
        'rating',
        'hurricane_deductible',
        'hurricane_deductible_occurrence',
        'skin_hole',
        'all_other_perils',
        'ordinance_of_law',
        'tiv_matches_appraisal',
        'secondary_water_insurance',
        'opening_protection',
        'correct_underlying',
        'property_insurance_coverage',
        'business_tiv',

        // Litigation
        'pending_litigation',
        'litigation_date',

        // Pipeline
        'pipeline_status_id',
        'pipeline_agent_id',
    ];

    /**
     * Validation rules for Basic Fields
     */
    protected static function getBasicFieldRules($id): array
    {
        return [
            'type' => 'required|string|max:191',
            'name' => 'required|string|max:191'.$id,
            'creation_date' => 'nullable|string|max:191|date_format:Y-m-d',
            'address1' => 'required|string|max:191|regex:/^\d.*/',
            'address2' => 'nullable|string|max:191',
            'city' => 'required|string|max:191',
            'state' => 'nullable|string|max:191',
            'zip' => 'required|string',
            'county' => 'nullable|string|max:191',
            'unit_count' => 'nullable|max:9999|integer',
        ];
    }

    /**
     * Validation rules for Renewal Information
     */
    protected static function getRenewalRules(): array
    {
        return [
            'renewal_date' => 'nullable|date_format:Y-m-d',
            'renewal_month' => 'nullable',
            'premium' => 'nullable|numeric|required_with:premium_year',
            'premium_year' => 'nullable|numeric|required_with:premium',
            'insured_amount' => 'nullable|numeric|required_with:insured_year',
            'insured_year' => 'nullable|numeric|required_with:insured_amount',
        ];
    }

    /**
     * Validation rules for Company Info
     */
    protected static function getCompanyInfoRules(): array
    {
        return [
            'manag_company' => 'nullable|string|max:191',
            'prop_manager' => 'nullable|string|max:191',
            'current_agency' => 'nullable|string|max:191',
            'current_agent' => 'nullable|string|max:191',
            'ins_flood' => 'nullable|string|max:191',
            'prop_floor' => 'nullable|numeric|max:191',
            'roof_geom' => 'nullable|string|max:191',
            'roof_covering' => 'nullable|string|max:191',
        ];
    }

    /**
     * Validation rules for Property Carrier
     */
    protected static function getPropertyCarrierRules(): array
    {
        return [
            'ins_prop_carrier' => 'nullable|string|max:191|required_with:renewal_carrier_month',
            'renewal_carrier_month' => 'nullable|required_with:ins_prop_carrier',
        ];
    }

    /**
     * Validation rules for General Liability
     */
    protected static function getGeneralLiabilityRules(): array
    {
        return [
            'general_liability' => 'nullable|string|max:191|required_with:GL_ren_month',
            'GL_ren_month' => 'nullable|string|max:191|required_with:general_liability',
            'gl_expiry_premium' => 'nullable|numeric|required_with:gl_policy_renewal_date',
            'gl_policy_renewal_date' => 'nullable|date_format:Y-m-d|required_with:gl_expiry_premium',
        ];
    }

    /**
     * Validation rules for Crime Insurance
     */
    protected static function getCrimeInsuranceRules(): array
    {
        return [
            'crime_insurance' => 'nullable|string|max:191|required_with:CI_ren_month',
            'CI_ren_month' => 'nullable|string|max:191|required_with:crime_insurance',
            'ci_expiry_premium' => 'nullable|numeric|required_with:ci_policy_renewal_date',
            'ci_policy_renewal_date' => 'nullable|date_format:Y-m-d|required_with:ci_expiry_premium',
        ];
    }

    /**
     * Validation rules for Directors & Officers
     */
    protected static function getDirectorsOfficersRules(): array
    {
        return [
            'directors_officers' => 'nullable|string|max:191|required_with:DO_ren_month',
            'DO_ren_month' => 'nullable|string|max:191|required_with:directors_officers',
            'do_expiry_premium' => 'nullable|numeric|required_with:do_policy_renewal_date',
            'do_policy_renewal_date' => 'nullable|date_format:Y-m-d|required_with:do_expiry_premium',
        ];
    }

    /**
     * Validation rules for Workers Compensation
     */
    protected static function getWorkersCompensationRules(): array
    {
        return [
            'workers_compensation' => 'nullable|string|max:191|required_with:WC_ren_month',
            'WC_ren_month' => 'nullable|string|max:191|required_with:workers_compensation',
            'wc_expiry_premium' => 'nullable|numeric|required_with:wc_policy_renewal_date',
            'wc_policy_renewal_date' => 'nullable|date_format:Y-m-d|required_with:wc_expiry_premium',
        ];
    }

    /**
     * Validation rules for Umbrella
     */
    protected static function getUmbrellaRules(): array
    {
        return [
            'umbrella' => 'nullable|string|max:191|required_with:U_ren_month',
            'U_ren_month' => 'nullable|string|max:191|required_with:umbrella',
            'umbrella_expiry_premium' => 'nullable|numeric|required_with:umbrella_policy_renewal_date',
            'umbrella_policy_renewal_date' => 'nullable|date_format:Y-m-d|required_with:umbrella_expiry_premium',
        ];
    }

    /**
     * Validation rules for Flood
     */
    protected static function getFloodRules(): array
    {
        return [
            'flood' => 'nullable|string|max:191|required_with:F_ren_month',
            'F_ren_month' => 'nullable|string|max:191|required_with:flood',
            'flood_expiry_premium' => 'nullable|numeric|required_with:flood_policy_renewal_date',
            'flood_policy_renewal_date' => 'nullable|date_format:Y-m-d|required_with:flood_expiry_premium',
        ];
    }

    /**
     * Validation rules for Difference In Condition
     */
    protected static function getDifferenceInConditionRules(): array
    {
        return [
            'difference_in_condition' => 'nullable|string|max:191|required_with:dic_ren_month',
            'dic_ren_month' => 'nullable|string|max:191|required_with:difference_in_condition',
            'dic_expiry_premium' => 'nullable|numeric|required_with:dic_policy_renewal_date',
            'dic_policy_renewal_date' => 'nullable|date_format:Y-m-d|required_with:dic_expiry_premium',
        ];
    }

    /**
     * Validation rules for X-Wind
     */
    protected static function getXWindRules(): array
    {
        return [
            'x_wind' => 'nullable|string|max:191|required_with:xw_ren_month',
            'xw_ren_month' => 'nullable|string|max:191|required_with:x_wind',
            'xw_expiry_premium' => 'nullable|numeric|required_with:xw_policy_renewal_date',
            'xw_policy_renewal_date' => 'nullable|date_format:Y-m-d|required_with:xw_expiry_premium',
        ];
    }

    /**
     * Validation rules for Equipment Breakdown
     */
    protected static function getEquipmentBreakdownRules(): array
    {
        return [
            'equipment_breakdown' => 'nullable|string|max:191|required_with:eb_ren_month',
            'eb_ren_month' => 'nullable|string|max:191|required_with:equipment_breakdown',
            'eb_expiry_premium' => 'nullable|numeric|required_with:eb_policy_renewal_date',
            'eb_policy_renewal_date' => 'nullable|date_format:Y-m-d|required_with:eb_expiry_premium',
        ];
    }

    /**
     * Validation rules for Commercial Automobiles
     */
    protected static function getCommercialAutoRules(): array
    {
        return [
            'commercial_automobiles' => 'nullable|string|max:191|required_with:ca_ren_month',
            'ca_ren_month' => 'nullable|string|max:191|required_with:commercial_automobiles',
            'ca_expiry_premium' => 'nullable|numeric|required_with:ca_policy_renewal_date',
            'ca_policy_renewal_date' => 'nullable|date_format:Y-m-d|required_with:ca_expiry_premium',
        ];
    }

    /**
     * Validation rules for Marina
     */
    protected static function getMarinaRules(): array
    {
        return [
            'marina' => 'nullable|string|max:191|required_with:m_ren_month',
            'm_ren_month' => 'nullable|string|max:191|required_with:marina',
            'm_expiry_premium' => 'nullable|numeric|required_with:m_policy_renewal_date',
            'm_policy_renewal_date' => 'nullable|date_format:Y-m-d|required_with:m_expiry_premium',
        ];
    }

    /**
     * Merge all validation rules
     */
    protected static function mergeAllRules($id): array
    {
        return array_merge(
            self::getBasicFieldRules($id),
            self::getRenewalRules(),
            self::getCompanyInfoRules(),
            self::getPropertyCarrierRules(),
            self::getGeneralLiabilityRules(),
            self::getCrimeInsuranceRules(),
            self::getDirectorsOfficersRules(),
            self::getWorkersCompensationRules(),
            self::getUmbrellaRules(),
            self::getFloodRules(),
            self::getDifferenceInConditionRules(),
            self::getXWindRules(),
            self::getEquipmentBreakdownRules(),
            self::getCommercialAutoRules(),
            self::getMarinaRules()
        );
    }

    /**
     * Validation rules for Lead model.
     *
     * @param int|null $id Lead ID for update scenarios
     * @return array
     */
    public static function rules($id)
    {
        return self::mergeAllRules($id);
    }

    /**
     * Get human-readable names for Basic Information
     */
    protected static function getBasicInfoNiceNames(): array
    {
        return [
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
        ];
    }

    /**
     * Get human-readable names for Renewal Information
     */
    protected static function getRenewalNiceNames(): array
    {
        return [
            'renewal_date' => 'Property Insurance Renewal Date',
            'renewal_month' => 'Property Insurance Renewal Month',
            'premium' => 'Business Premium',
            'premium_year' => 'Business Premium Year',
            'insured_amount' => 'Business Insured Amount',
            'insured_year' => 'Business Insured Year',
        ];
    }

    /**
     * Get human-readable names for Company Info
     */
    protected static function getCompanyInfoNiceNames(): array
    {
        return [
            'manag_company' => 'Management Company',
            'prop_manager' => 'Property Manager',
            'current_agency' => 'Current Agency',
            'current_agent' => 'Current Agent',
            'ins_prop_carrier' => 'Insurance Property Carrier',
            'renewal_carrier_month' => 'Insurance Property Renewal Career Month',
            'ins_flood' => 'Insurance Flood',
            'prop_floor' => 'Property Floors',
            'roof_geom' => 'Roof Geometry',
            'roof_covering' => 'Roof Covering',
        ];
    }

    /**
     * Get human-readable names for General Liability
     */
    protected static function getGeneralLiabilityNiceNames(): array
    {
        return [
            'general_liability' => 'General Liability',
            'GL_ren_month' => 'General Liability Renewal Month',
            'gl_expiry_premium' => 'General Liability Expiring Premium',
            'gl_policy_renewal_date' => 'General Liability Policy Renewal Date',
        ];
    }

    /**
     * Get human-readable names for Crime Insurance
     */
    protected static function getCrimeInsuranceNiceNames(): array
    {
        return [
            'crime_insurance' => 'Crime Insurance',
            'CI_ren_month' => 'Crime Insurance Renewal Month',
            'ci_expiry_premium' => 'Crime Insurance Expiring Premium',
            'ci_policy_renewal_date' => 'Crime Insurance Policy Renewal Date',
        ];
    }

    /**
     * Get human-readable names for Directors & Officers
     */
    protected static function getDirectorsOfficersNiceNames(): array
    {
        return [
            'directors_officers' => 'Directors & Officers',
            'DO_ren_month' => 'Directors & Officers Renewal Month',
            'do_expiry_premium' => 'Directors & Officers Expiring Premium',
            'do_policy_renewal_date' => 'Directors & Officers Policy Renewal Date',
        ];
    }

    /**
     * Get human-readable names for Workers Compensation
     */
    protected static function getWorkersCompensationNiceNames(): array
    {
        return [
            'workers_compensation' => 'Workers Compensation',
            'WC_ren_month' => 'Workers Compensation Renewal Month',
            'wc_expiry_premium' => 'Workers Compensation Expiring Premium',
            'wc_policy_renewal_date' => 'Workers Compensation Policy Renewal Date',
        ];
    }

    /**
     * Get human-readable names for Umbrella
     */
    protected static function getUmbrellaNiceNames(): array
    {
        return [
            'umbrella' => 'Umbrella',
            'U_ren_month' => 'Umbrella Renewal Month',
            'umbrella_expiry_premium' => 'Umbrella Expiring Premium',
            'umbrella_policy_renewal_date' => 'Umbrella Policy Renewal Date',
        ];
    }

    /**
     * Get human-readable names for Flood
     */
    protected static function getFloodNiceNames(): array
    {
        return [
            'flood' => 'Flood',
            'F_ren_month' => 'Flood Renewal Month',
            'flood_expiry_premium' => 'Flood Expiring Premium',
            'flood_policy_renewal_date' => 'Flood Policy Renewal Date',
        ];
    }

    /**
     * Get human-readable names for Difference In Conditions
     */
    protected static function getDifferenceInConditionNiceNames(): array
    {
        return [
            'difference_in_condition' => 'Difference In Conditions',
            'dic_ren_month' => 'Difference In Conditions Renewal Month',
            'dic_expiry_premium' => 'Difference In Conditions Expiring Premium',
            'dic_policy_renewal_date' => 'Difference In Conditions Policy Renewal Date',
        ];
    }

    /**
     * Get human-readable names for X-Wind
     */
    protected static function getXWindNiceNames(): array
    {
        return [
            'x_wind' => 'X-Wind',
            'xw_ren_month' => 'X-Wind Renewal Month',
            'xw_expiry_premium' => 'X-Wind Expiring Premium',
            'xw_policy_renewal_date' => 'X-Wind Policy Renewal Date',
        ];
    }

    /**
     * Get human-readable names for Equipment Breakdown
     */
    protected static function getEquipmentBreakdownNiceNames(): array
    {
        return [
            'equipment_breakdown' => 'Equipment Breakdown',
            'eb_ren_month' => 'Equipment Breakdown Renewal Month',
            'eb_expiry_premium' => 'Equipment Breakdown Expiring Premium',
            'eb_policy_renewal_date' => 'Equipment Breakdown Policy Renewal Date',
        ];
    }

    /**
     * Get human-readable names for Commercial Automobiles
     */
    protected static function getCommercialAutoNiceNames(): array
    {
        return [
            'commercial_automobiles' => 'Commercial AutoMobiles',
            'ca_ren_month' => 'Commercial AutoMobiles Renewal Month',
            'ca_expiry_premium' => 'Commercial AutoMobiles Expiring Premium',
            'ca_policy_renewal_date' => 'Commercial AutoMobiles Policy Renewal Date',
        ];
    }

    /**
     * Get human-readable names for Marina
     */
    protected static function getMarinaNiceNames(): array
    {
        return [
            'marina' => 'Marina',
            'm_ren_month' => 'Marina Renewal Month',
            'm_expiry_premium' => 'Marina Expiring Premium',
            'm_policy_renewal_date' => 'Marina Policy Renewal Date',
        ];
    }

    /**
     * Merge all nice names
     */
    protected static function mergeAllNiceNames(): array
    {
        return array_merge(
            self::getBasicInfoNiceNames(),
            self::getRenewalNiceNames(),
            self::getCompanyInfoNiceNames(),
            self::getGeneralLiabilityNiceNames(),
            self::getCrimeInsuranceNiceNames(),
            self::getDirectorsOfficersNiceNames(),
            self::getWorkersCompensationNiceNames(),
            self::getUmbrellaNiceNames(),
            self::getFloodNiceNames(),
            self::getDifferenceInConditionNiceNames(),
            self::getXWindNiceNames(),
            self::getEquipmentBreakdownNiceNames(),
            self::getCommercialAutoNiceNames(),
            self::getMarinaNiceNames()
        );
    }

    /**
     * Get human-readable names for validation attributes.
     *
     * @return array
     */
    public static function niceNames()
    {
        return self::mergeAllNiceNames();
    }

    /**
     * Get column types for lead (integer, date, other).
     * Cached for performance.
     *
     * @return array
     */
    public static function scopeGetColumnType()
    {
        return Cache::rememberForever('lead_column_types', function () {
            $tableHeading = Schema::getColumnListing('leads');
            $columns = [];

            foreach ($tableHeading as $head) {
                $type = DB::connection()->getDoctrineColumn('leads', $head)->getType()->getName();

                if (($type === 'bigint' || $type === 'decimal') && $head !== 'id') {
                    $columns['number'][] = $head;
                } elseif ($type === 'date') {
                    $columns['date'][] = $head;
                } else {
                    $columns['other'][] = $head;
                }
            }
            // Add distance manually
            $columns['number'][] = 'distance';
            return $columns;
        });
    }

    /**
     * Get full name attribute (accessor).
     */
    public function scopeGetFullNameAttribute()
    {
        return $this->first_name.' '.$this->last_name;
    }

    /**
     * Evaluate crawler leads before storing.
     *
     * @param array $allLeads Leads from crawler
     * @param string $search_keyword Search keyword
     * @return array
     */
    public static function evaluateCrawlerLeads($allLeads, $searchKeyword)
    {
        $storedLeads = [];
        $skippedLeadsCount = 0;

        $leadModel = new self;

        foreach ($allLeads as $lead) {
            $leadSlug = $searchKeyword.'-'.$lead['businessName'].'-'.$lead['city'].'-'.$lead['zip'];
            echo $leadSlug;

            $lead['lead_slug'] = strtolower(str_replace(' ', '-', $leadSlug));
            $lead['businessName'] = $leadModel->removeSpecialCharacters($lead['businessName']);

            if ($leadSlug) {
                $slugExistance = $leadModel->checkLeadSlugExistanceWithDistance(
                    $leadSlug,
                    $lead['latitude'],
                    $lead['longitude']
                );

                $lead['lead_slug'] = $leadSlug;

                if (is_array($slugExistance)
                    && isset($slugExistance['existanceCount'])
                    && $slugExistance['existanceCount'] > 0) {
                    $skippedLeadsCount++;
                } else {
                    $checkIfLeadExists = $leadModel->getLeadsByName($lead['businessName']);

                    if ($checkIfLeadExists !== '') {
                        // Check for percentage match
                        $matchPercentage = $leadModel->getStringsSimilarityPercentage(
                            $lead['businessName'],
                            $checkIfLeadExists
                        );

                        if ($matchPercentage > 0) {
                            $skippedLeadsCount++;
                        } else {
                            $leadId = $leadModel->createLead($lead, $searchKeyword);
                            $storedLeads[] = $leadId;
                        }
                    } else {
                        // Store the leads
                        $leadId = $leadModel->createLead($lead, $searchKeyword);
                        $storedLeads[] = $leadId;
                    }
                }
            } else {
                $skippedLeadsCount++;
            }
        }
        return [
            'storedLeads' => $storedLeads,
            'skippedLeadsCount' => $skippedLeadsCount,
        ];
    }

    /**
     * Create a new lead from crawler data.
     *
     * @param array $lead Lead data
     * @param string $searchKeyword Search keyword
     * @return int Lead ID
     */
    public static function createLead($lead, $searchKeyword)
    {
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

    /**
     * Calculate similarity percentage between two strings.
     *
     * @param string $str1 First string
     * @param string $str2 Second string
     * @return int Similarity percentage
     */
    public static function getStringsSimilarityPercentage($str1, $str2)
    {
        // Remove Roman numerals and Arabic numerals from both strings
        $str1 = preg_replace('/\b[IVXLCDM\d]+\b/', '', $str1);
        $str2 = preg_replace('/\b[IVXLCDM\d]+\b/', '', $str2);

        similar_text($str1, $str2, $percentage);
        $percentage = (int) round($percentage);

        if ($percentage === 100) {
            // Check if both strings contain Roman numerals or Arabic numerals
            $containsNumeral1 = preg_match('/\b[IVXLCDM]+\b/', $str1);
            $containsNumeral2 = preg_match('/\b[IVXLCDM]+\b/', $str2);

            // If both strings contain numerals, return 0% match
            if ($containsNumeral1 && $containsNumeral2) {
                return 0;
            }
        }

        return $percentage;
    }

    /**
     * Get leads by name (partial match).
     *
     * @param string $leadName Lead name to search
     * @return string|true First matching lead name or true if not found
     */
    public static function getLeadsByName($leadName)
    {
        $lead = Lead::where('name', 'LIKE', "%{$leadName}%")
            ->value('name'); // directly returns first match or null

        return $lead ?? true;
    }


    public static function leadStates(): array
    {
        return self::leadStatesData();
    }

    public static function leadCounties(): array
    {
        return self::leadCountiesData();
    }

    public static function leadMonths(): array
    {
        return self::leadMonthsData();
    }

    public static function leadRoofCovering(): array
    {
        return self::leadRoofCoveringData();
    }

    public static function leadRoofGeometry(): array
    {
        return self::leadRoofGeometryData();
    }

    public static function Get_column_type()
    {
        return self::scopeGetColumnType();
    }
}
