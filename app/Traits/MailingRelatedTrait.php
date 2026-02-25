<?php

namespace App\Traits;

trait MailingRelatedTrait
{
    /**
     * Get lead columns part 1
     */
    protected function getLeadColumnsPart1(): array
    {
        return ['id', 'type', 'name', 'creation_date', 'address1', 'address2', 'city', 'state', 'zip', 'county'];
    }

    /**
     * Get lead columns part 2
     */
    protected function getLeadColumnsPart2(): array
    {
        return ['unit_count', 'renewal_date', 'renewal_month', 'premium', 'insured_amount', 'manag_company', 'prop_manager'];
    }

    /**
     * Get lead columns part 3
     */
    protected function getLeadColumnsPart3(): array
    {
        return ['current_agency', 'current_agent', 'ins_prop_carrier', 'renewal_carrier_month', 'ins_flood', 'general_liability'];
    }

    /**
     * Get lead columns part 4
     */
    protected function getLeadColumnsPart4(): array
    {
        return ['GL_ren_month', 'crime_insurance', 'CI_ren_month', 'directors_officers', 'DO_ren_month', 'workers_compensation'];
    }

    /**
     * Get lead columns part 5
     */
    protected function getLeadColumnsPart5(): array
    {
        return ['WC_ren_month', 'umbrella', 'U_ren_month', 'flood', 'F_ren_month', 'response_date'];
    }

    public function getleadColumns()
    {
        return array_merge(
            $this->getLeadColumnsPart1(),
            $this->getLeadColumnsPart2(),
            $this->getLeadColumnsPart3(),
            $this->getLeadColumnsPart4(),
            $this->getLeadColumnsPart5()
        );
    }

    public function getcontactColumns()
    {
        return ['id', 'c_first_name', 'c_last_name', 'c_title', 'c_address1', 'c_address2', 'c_city', 'c_state', 'c_zip', 'c_county', 'c_phone', 'c_email', 'verified_status'];
    }

    /**
     * Get file columns part 1
     */
    protected function getFileColumnsPart1(): array
    {
        return ['Lead Id', 'Business_Type', 'Business_Name', 'Business_Creation_Date', 'Business_Address1', 'Business_Address2'];
    }

    /**
     * Get file columns part 2
     */
    protected function getFileColumnsPart2(): array
    {
        return ['Business_City', 'Business_State', 'Business_Zip', 'Business_County', 'Business_Unit_Count', 'Property_Insurance_Renewal_Date'];
    }

    /**
     * Get file columns part 3
     */
    protected function getFileColumnsPart3(): array
    {
        return ['Property_Insurance_Renewal_Month', 'Business_Premium', 'Business_Insured_Amount', 'Management_Company', 'Property_Manager'];
    }

    /**
     * Get file columns part 4
     */
    protected function getFileColumnsPart4(): array
    {
        return ['Current_Agency', 'Current_Agent', 'Insurance_Property_Career', 'Renewal_Carrier_Month', 'Insurance_Flood'];
    }

    /**
     * Get file columns part 5
     */
    protected function getFileColumnsPart5(): array
    {
        return ['General_Liability', 'General_Liability_Renewal_Month', 'Crime_Insurance', 'Crime_Insurance_Renewal_Month'];
    }

    /**
     * Get file columns part 6
     */
    protected function getFileColumnsPart6(): array
    {
        return ['Directors_Officers', 'Directors_Officers_Renewal_Month', 'Workers_Compensation', 'Workers_Compensation_Renewal_Month'];
    }

    /**
     * Get file columns part 7
     */
    protected function getFileColumnsPart7(): array
    {
        return ['Umbrella', 'Umbrella_Renewal_Month', 'Flood', 'Flood_General_Liability_Renewal_Month'];
    }

    /**
     * Get file columns part 8
     */
    protected function getFileColumnsPart8(): array
    {
        return ['Contact_id', 'Contact_First_Name', 'Contact_Last_Name', 'Contact_Title', 'Contact_Address1'];
    }

    /**
     * Get file columns part 9
     */
    protected function getFileColumnsPart9(): array
    {
        return ['Contact_Address2', 'Contact_City', 'Contact_State', 'Contact_Zip', 'Contact_County'];
    }

    /**
     * Get file columns part 10
     */
    protected function getFileColumnsPart10(): array
    {
        return ['Contact_Phone', 'Contact_Email', 'Verification_Status', 'Response_Date'];
    }

    public function columninsidefile()
    {
        return array_merge(
            $this->getFileColumnsPart1(),
            $this->getFileColumnsPart2(),
            $this->getFileColumnsPart3(),
            $this->getFileColumnsPart4(),
            $this->getFileColumnsPart5(),
            $this->getFileColumnsPart6(),
            $this->getFileColumnsPart7(),
            $this->getFileColumnsPart8(),
            $this->getFileColumnsPart9(),
            $this->getFileColumnsPart10()
        );
    }
}
