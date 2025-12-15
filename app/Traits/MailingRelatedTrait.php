<?php

namespace App\Traits;

use Illuminate\Support\Str;
use App\Model\LeadsModel\Lead;
use App\Model\LeadsModel\Contact;
use App\Model\SmtpConfiguration;
use Illuminate\Support\Facades\Crypt;
use App\Model\Email;
use Config;
use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Controllers\Controller;

trait MailingRelatedTrait
{
    public function getleadColumns()
    {
    	return array('id', 'type', 'name', 'creation_date', 'address1', 'address2', 'city', 'state', 'zip', 'county', 'unit_count', 'renewal_date', 'renewal_month',  'premium', 'insured_amount', 'manag_company', 'prop_manager', 'current_agency', 'current_agent', 'ins_prop_carrier', 'renewal_carrier_month', 'ins_flood', 'general_liability', 'GL_ren_month', 'crime_insurance', 'CI_ren_month', 'directors_officers', 'DO_ren_month', 'workers_compensation', 'WC_ren_month', 'umbrella', 'U_ren_month', 'flood', 'F_ren_month', 'response_date');
    }

    public function getcontactColumns()
    {
    	return array('id','c_first_name', 'c_last_name', 'c_title', 'c_address1', 'c_address2', 'c_city', 'c_state', 'c_zip', 'c_county', 'c_phone', 'c_email','verified_status');
    }

    public function columninsidefile()
    {
    	return array('Lead Id', 'Business_Type', 'Business_Name', 'Business_Creation_Date', 'Business_Address1', 'Business_Address2', 'Business_City', 'Business_State', 'Business_Zip', 'Business_County', 'Business_Unit_Count', 'Property_Insurance_Renewal_Date', 'Property_Insurance_Renewal_Month', 'Business_Premium', 'Business_Insured_Amount', 'Management_Company', 'Property_Manager', 'Current_Agency', 'Current_Agent', 'Insurance_Property_Career','Renewal_Carrier_Month', 'Insurance_Flood', 'General_Liability', 'General_Liability_Renewal_Month', 'Crime_Insurance', 'Crime_Insurance_Renewal_Month', 'Directors_Officers', 'Directors_Officers_Renewal_Month', 'Workers_Compensation', 'Workers_Compensation_Renewal_Month', 'Umbrella', 'Umbrella_Renewal_Month', 'Flood', 'Flood_General_Liability_Renewal_Month','Contact_id', 'Contact_First_Name', 'Contact_Last_Name', 'Contact_Title', 'Contact_Address1', 'Contact_Address2', 'Contact_City', 'Contact_State', 'Contact_Zip', 'Contact_County', 'Contact_Phone', 'Contact_Email','Verification_Status', 'Response_Date');
    }
}
