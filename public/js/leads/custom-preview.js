function formatDateToDMY(date) {
        let parsedDate;

        // Check if the input is a string in the format "YYYY-MM-DD"
        if (typeof date === 'string' && /^\d{4}-\d{2}-\d{2}$/.test(date)) {
            parsedDate = new Date(date); // Parse the date string
        } else if (date instanceof Date) {
            parsedDate = date; // Use the Date object directly
        } else {
            console.warn('Invalid date input:', date);
            return 'Invalid Date'; // Handle invalid input
        }

        // Ensure the parsed date is valid
        if (isNaN(parsedDate)) {
            console.warn('Invalid date input:', date);
            return 'Invalid Date';
        }

        const day = String(parsedDate.getDate()).padStart(2, '0');
        const month = String(parsedDate.getMonth() + 1).padStart(2, '0');
        const year = parsedDate.getFullYear();

        return `${month}/${day}/${year}`;
    }


    function assign_value_inputbased(value,type,comp='') {
        if(type == 1){
            if(value == '' || value == undefined || value == null){
                return "N/A";
            }
        }
        else if(type == 2){
            if(value == '' || value == undefined || value == null){
                return "No";
            }
        }
        else if(type == 3){
            if(value == '' || value == undefined || value == null){
                return "N/A";
            }
            else if(value == 'other' || value == 'Others'){
                return document.getElementById(comp).value;
            }
        }
        else if(type == 4){
            // console.log(value,type,comp);
            var exclusions = document.getElementById(comp);
            var exclusions_value = '';
            if (exclusions) {
                // Get all selected options
                let selectedValues = Array.from(exclusions.selectedOptions).map(option => option.value);
                let selval = selectedValues.join(', ');

                if(selval == '' || selval == undefined || selval == null){
                    return "N/A";
                }
                else{
                    return selval;
                }
            }
            return "N/A";
        }
        else if(type == 5){
            if(value == '' || value == undefined || value == null){
                return "N/A";
            }
            else{
                return "$"+formatUSNumberJs(value);
            }
        }
        else if(type == 6){
            if(value == '' || value == undefined || value == null){
                return "N/A";
            }
            return formatDateToDMY(value);
        }
        else if(type == 7){
            if(value){
                return "Coastal";
            }
            return "Non Coastal";
        }
        return value;
    }
    function currentClientBannerDisplay() {
        let element = document.getElementById("current_client_area");
        if (element) {
            element.remove();
        }
        const isChecked = document.getElementsByClassName('current_clientswitch')[0].checked;
        if(isChecked){
            const htmlContent = `
                    <div class="form-group mb-2 ml-2 " id="current_client_area">
                        <p class="font-weight-bold text-success mb-0">Current Client</p>
                    </div>
                `;
            document.getElementById('name_preview').insertAdjacentHTML('afterend', htmlContent);
        }
    }
    const checkifNAthenNotAddpercantage = (value) => {
        // console.log(value);
        if(value == "N/A"){
            return value;
        }
        else{
            return value+"%";
        }
    };
    function loadBusinessInfoData() {
        currentClientBannerDisplay();
        document.getElementById('name_preview').textContent = assign_value_inputbased(document.getElementById('name').value,1);
        document.getElementById('type_preview').textContent = assign_value_inputbased(document.getElementById('type').value,1);
        document.getElementById('creation_date_preview').textContent = assign_value_inputbased(document.getElementById('creation_date').value,6);
        document.getElementById('unit_count_preview').textContent = assign_value_inputbased(document.getElementById('unit_count').value,1);
        document.getElementById('address1_preview').textContent = assign_value_inputbased(document.getElementById('address1').value,1);
        document.getElementById('address2_preview').textContent = assign_value_inputbased(document.getElementById('address2').value,1);
        document.getElementById('city_preview').textContent = assign_value_inputbased(document.getElementById('city').value,1);
        document.getElementById('county_preview').textContent = assign_value_inputbased(document.getElementById('county').value,1);
        document.getElementById('coastal_preview').textContent = assign_value_inputbased(document.getElementById('coastal').value,7);
        document.getElementById('state_preview').textContent = assign_value_inputbased(document.getElementById('state').value,1);
        document.getElementById('zip_preview').textContent = assign_value_inputbased(document.getElementById('zip').value,1);
        document.getElementById('ins_flood_preview').textContent = assign_value_inputbased(document.getElementById('ins_flood').value,2);
        document.getElementById('prop_floor_preview').textContent = assign_value_inputbased(document.getElementById('prop_floor').value,1);
        document.getElementById('total_square_footage_preview').textContent = assign_value_inputbased(document.getElementById('total_square_footage').value,1);
        document.getElementById('roof_connection_preview').textContent = assign_value_inputbased(document.getElementById('roof_connection').value,1);
        document.getElementById('roof_geom_preview').textContent = assign_value_inputbased(document.getElementById('roof_geom').value,1);
        document.getElementById('roof_covering_preview').textContent = assign_value_inputbased(document.getElementById('roof_covering').value,1);
        document.getElementById('roof_year_preview').textContent = assign_value_inputbased(document.getElementById('roof_year').value,1);
        document.getElementById('lead_source_preview').textContent = assign_value_inputbased(document.getElementById('lead_source').value,1);
        document.getElementById('business_tiv_preview').textContent = assign_value_inputbased(document.getElementById('business_tiv').value,5);
    }
    function loadCommunicationInfoData() {
        document.getElementById('pool_preview').textContent = assign_value_inputbased(document.getElementById('pool').value,2);
        document.getElementById('lakes_preview').textContent = assign_value_inputbased(document.getElementById('lakes').value,2);
        document.getElementById('clubhouse_preview').textContent = assign_value_inputbased(document.getElementById('clubhouse').value,2);
        document.getElementById('tennis_basketball_preview').textContent = assign_value_inputbased(document.getElementById('tennis_basketball').value,2);
        document.getElementById('other_community_info_preview').textContent = assign_value_inputbased(document.getElementById('other_community_info').value,1);
        document.getElementById('iso_preview').textContent = assign_value_inputbased(document.getElementById('iso').value,1);
    }
    function loadPropertyInfoData() {
        document.getElementById('premium_preview').textContent = assign_value_inputbased(document.getElementById('premium').value,5);
        document.getElementById('premium_year_preview').textContent = assign_value_inputbased(document.getElementById('premium_year').value,1);
        document.getElementById('insured_amount_preview').textContent = assign_value_inputbased(document.getElementById('insured_amount').value,5);
        document.getElementById('insured_year_preview').textContent = assign_value_inputbased(document.getElementById('insured_year').value,1);
        document.getElementById('appraisal_name_preview').textContent = assign_value_inputbased(document.getElementById('appraisal_name').value,1);
        document.getElementById('appraisal_company_preview').textContent = assign_value_inputbased(document.getElementById('appraisal_company').value,1);
        document.getElementById('appraisal_date_preview').textContent = assign_value_inputbased(document.getElementById('appraisal_date').value,6);
        document.getElementById('incumbent_agency_preview').textContent = assign_value_inputbased(document.getElementById('incumbent_agency').value,1);
        document.getElementById('incumbent_agent_preview').textContent = assign_value_inputbased(document.getElementById('incumbent_agent').value,1);
        document.getElementById('policy_renewal_date_preview').textContent = assign_value_inputbased(document.getElementById('policy_renewal_date').value,6);
        document.getElementById('wind_mitigation_date_preview').textContent = assign_value_inputbased(document.getElementById('wind_mitigation_date').value,6);
        const ratingselectElem = document.getElementById('rating');
        let ratingSelectedText = "";
        if(ratingselectElem.selectedIndex !== -1 && ratingselectElem.value !== "") {
            ratingSelectedText = ratingselectElem.options[ratingselectElem.selectedIndex].text;
        }
        document.getElementById('rating_preview').textContent = assign_value_inputbased(ratingSelectedText,3,"rating-other");
        document.getElementById('skin_hole_preview').textContent = assign_value_inputbased(document.getElementById('skin_hole').value,2);
        document.getElementById('all_other_perils_preview').textContent = assign_value_inputbased(document.getElementById('all_other_perils').value,5);
        document.getElementById('ordinance_of_law_preview').textContent = checkifNAthenNotAddpercantage(assign_value_inputbased(document.getElementById('ordinance_of_law').value,3,"ordinance_of_law-other"));
        document.getElementById('tiv_matches_appraisal_preview').textContent = assign_value_inputbased(document.getElementById('tiv_matches_appraisal').value,2);
        document.getElementById('secondary_water_insurance_preview').textContent = assign_value_inputbased(document.getElementById('secondary_water_insurance').value,2);
        document.getElementById('opening_protection_preview').textContent = assign_value_inputbased(document.getElementById('opening_protection').value,2);

        // carrier input need to be added - ins_prop_carrier
        const properyselectElem = document.getElementById('ins_prop_carrier');
        let propertySelectedText = "";
        if(properyselectElem.selectedIndex !== -1 && properyselectElem.value !== "") {
            propertySelectedText = properyselectElem.options[properyselectElem.selectedIndex].text;
        } 

        document.getElementById('ins_prop_carrier_preview').textContent = assign_value_inputbased(propertySelectedText, 3, "ins_prop_carrier-other");

        // document.getElementById('ins_prop_carrier_preview').textContent = assign_value_inputbased(document.getElementById('ins_prop_carrier').value,3,"ins_prop_carrier-other");
        document.getElementById('renewal_carrier_month_preview').textContent = assign_value_inputbased(document.getElementById('renewal_carrier_month').value,1);
        document.getElementById('hurricane_deductible_preview').textContent = checkifNAthenNotAddpercantage(assign_value_inputbased(document.getElementById('hurricane_deductible').value,1));
        document.getElementById('hurricane_deductible_occurrence_preview').textContent = assign_value_inputbased(document.getElementById('hurricane_deductible_occurrence').value,1);
        document.getElementById('property_insurance_coverage_preview').textContent = assign_value_inputbased(document.getElementById('property_insurance_coverage').value,1);
 
        const glselectElem = document.getElementById('general_liability');
        let glselectedText = "";
        if(glselectElem.selectedIndex !== -1 && glselectElem.value !== "") {
            glselectedText = glselectElem.options[glselectElem.selectedIndex].text;
        }
        document.getElementById('general_liability_preview').textContent = assign_value_inputbased(glselectedText,3,"general_liability-other");
        document.getElementById('GL_ren_month_preview').textContent = assign_value_inputbased(document.getElementById('GL_ren_month').value,1);
        document.getElementById('gl_expiry_premium_preview').textContent = assign_value_inputbased(document.getElementById('gl_expiry_premium').value,5);
        document.getElementById('gl_policy_renewal_date_preview').textContent = assign_value_inputbased(document.getElementById('gl_policy_renewal_date').value,6);
        const glratingselectElem = document.getElementById('gl_rating');
        let glratingSelectedText = "";
        if(glratingselectElem.selectedIndex !== -1 && glratingselectElem.value !== "") {
            glratingSelectedText = glratingselectElem.options[glratingselectElem.selectedIndex].text;
        }
        document.getElementById('gl_rating_preview').textContent = assign_value_inputbased(glratingSelectedText,3,"gl_rating-other");
        document.getElementById('gl_exclusions_preview').textContent = assign_value_inputbased('',4,"gl_exclusions");
        document.getElementById('gl_other_exclusions_preview').textContent = assign_value_inputbased(document.getElementById('gl_other_exclusions').value,1);
        document.getElementById('gl_insurance_coverage_preview').textContent = assign_value_inputbased(document.getElementById('gl_insurance_coverage').value,1);

        // console.log(assign_value_inputbased(document.getElementById('gl_insurance_coverage').value,1));

        const ciselectElem = document.getElementById('crime_insurance');
        let ciselectedText = "";
        if(ciselectElem.selectedIndex !== -1 && ciselectElem.value !== "") {
            ciselectedText = ciselectElem.options[ciselectElem.selectedIndex].text;
        }
        document.getElementById('crime_insurance_preview').textContent = assign_value_inputbased(ciselectedText,3,"crime_insurance-other");
        document.getElementById('CI_ren_month_preview').textContent = assign_value_inputbased(document.getElementById('CI_ren_month').value,1);
        document.getElementById('ci_expiry_premium_preview').textContent = assign_value_inputbased(document.getElementById('ci_expiry_premium').value,5);
        document.getElementById('ci_policy_renewal_date_preview').textContent = assign_value_inputbased(document.getElementById('ci_policy_renewal_date').value,6);
        const ciratingselectElem = document.getElementById('ci_rating');
        let ciratingSelectedText = "";
        if(ciratingselectElem.selectedIndex !== -1 && ciratingselectElem.value !== "") {
            ciratingSelectedText = ciratingselectElem.options[ciratingselectElem.selectedIndex].text;
        }
        document.getElementById('ci_rating_preview').textContent = assign_value_inputbased(ciratingSelectedText,3,"ci_rating-other");
        document.getElementById('employee_theft_preview').textContent = assign_value_inputbased(document.getElementById('employee_theft').value,1);
        document.getElementById('operating_reserves_preview').textContent = assign_value_inputbased(document.getElementById('operating_reserves').value,1);
        document.getElementById('pending_litigation_preview').textContent = assign_value_inputbased(document.getElementById('pending_litigation').value,2);
        document.getElementById('litigation_date_preview').textContent = assign_value_inputbased(document.getElementById('litigation_date').value,6);
        document.getElementById('ci_insurance_coverage_preview').textContent = assign_value_inputbased(document.getElementById('ci_insurance_coverage').value,1);


        const doselectElem = document.getElementById('directors_officers');
        let doselectedText = "";
        if(doselectElem.selectedIndex !== -1 && doselectElem.value !== "") {
            doselectedText = doselectElem.options[doselectElem.selectedIndex].text;
        }
        document.getElementById('directors_officers_preview').textContent = assign_value_inputbased(doselectedText,3,"directors_officers-other");
        document.getElementById('DO_ren_month_preview').textContent = assign_value_inputbased(document.getElementById('DO_ren_month').value,1);
        document.getElementById('do_expiry_premium_preview').textContent = assign_value_inputbased(document.getElementById('do_expiry_premium').value,5);
        document.getElementById('do_policy_renewal_date_preview').textContent = assign_value_inputbased(document.getElementById('do_policy_renewal_date').value,6);
        const doratingselectElem = document.getElementById('do_rating');
        let doratingSelectedText = "";
        if(doratingselectElem.selectedIndex !== -1 && doratingselectElem.value !== "") {
            doratingSelectedText = doratingselectElem.options[doratingselectElem.selectedIndex].text;
        }
        document.getElementById('do_rating_preview').textContent = assign_value_inputbased(doratingSelectedText,3,"do_rating-other");
        document.getElementById('claims_made_preview').textContent = assign_value_inputbased(document.getElementById('claims_made').value,2);
        document.getElementById('do_insurance_coverage_preview').textContent = assign_value_inputbased(document.getElementById('do_insurance_coverage').value,1);


        const uselectElem = document.getElementById('umbrella');
        let uselectedText = "";
        if(uselectElem.selectedIndex !== -1 && uselectElem.value !== "") {
            uselectedText = uselectElem.options[uselectElem.selectedIndex].text;
        }
        document.getElementById('umbrella_preview').textContent = assign_value_inputbased(uselectedText,3,"umbrella-other");
        document.getElementById('U_ren_month_preview').textContent = assign_value_inputbased(document.getElementById('U_ren_month').value,1);
        document.getElementById('umbrella_expiry_premium_preview').textContent = assign_value_inputbased(document.getElementById('umbrella_expiry_premium').value,5);
        document.getElementById('umbrella_policy_renewal_date_preview').textContent = assign_value_inputbased(document.getElementById('umbrella_policy_renewal_date').value,6);
        document.getElementById('umbrella_exclusions_preview').textContent = assign_value_inputbased('',4,"umbrella_exclusions");
        document.getElementById('umbrella_other_exclusions_preview').textContent = assign_value_inputbased(document.getElementById('umbrella_other_exclusions').value,1);
        const uratingselectElem = document.getElementById('umbrella_rating');
        let uratingSelectedText = "";
        if(uratingselectElem.selectedIndex !== -1 && uratingselectElem.value !== "") {
            uratingSelectedText = uratingselectElem.options[uratingselectElem.selectedIndex].text;
        }
        document.getElementById('umbrella_rating_preview').textContent = assign_value_inputbased(uratingSelectedText,3,"umbrella_rating-other");
        document.getElementById('u_insurance_coverage_preview').textContent = assign_value_inputbased(document.getElementById('u_insurance_coverage').value,1);
        document.getElementById('correct_underlying_preview').textContent = assign_value_inputbased(document.getElementById('correct_underlying').value,1);


        const wcselectElem = document.getElementById('workers_compensation');
        let wcselectedText = "";
        if(wcselectElem.selectedIndex !== -1 && wcselectElem.value !== "") {
            wcselectedText = wcselectElem.options[wcselectElem.selectedIndex].text;
        }
        document.getElementById('workers_compensation_preview').textContent = assign_value_inputbased(wcselectedText,3,"workers_compensation-other");
        document.getElementById('WC_ren_month_preview').textContent = assign_value_inputbased(document.getElementById('WC_ren_month').value,1);
        document.getElementById('wc_expiry_premium_preview').textContent = assign_value_inputbased(document.getElementById('wc_expiry_premium').value,5);
        document.getElementById('wc_policy_renewal_date_preview').textContent = assign_value_inputbased(document.getElementById('wc_policy_renewal_date').value,6);
        const wcratingselectElem = document.getElementById('wc_rating');
        let wcratingSelectedText = "";
        if(wcratingselectElem.selectedIndex !== -1 && wcratingselectElem.value !== "") {
            wcratingSelectedText = wcratingselectElem.options[wcratingselectElem.selectedIndex].text;
        }
        document.getElementById('wc_rating_preview').textContent = assign_value_inputbased(wcratingSelectedText,3,"wc_rating-other");
        document.getElementById('employee_count_preview').textContent = assign_value_inputbased(document.getElementById('employee_count').value,1);
        document.getElementById('employee_payroll_preview').textContent = assign_value_inputbased(document.getElementById('employee_payroll').value,1);
        document.getElementById('wc_insurance_coverage_preview').textContent = assign_value_inputbased(document.getElementById('wc_insurance_coverage').value,1);


        const fselectElem = document.getElementById('flood');
        let fselectedText = "";
        if(fselectElem.selectedIndex !== -1 && fselectElem.value !== "") {
            fselectedText = fselectElem.options[fselectElem.selectedIndex].text;
        }
        document.getElementById('flood_preview').textContent = assign_value_inputbased(fselectedText,3,"flood-other");
        document.getElementById('F_ren_month_preview').textContent = assign_value_inputbased(document.getElementById('F_ren_month').value,1);
        document.getElementById('flood_expiry_premium_preview').textContent = assign_value_inputbased(document.getElementById('flood_expiry_premium').value,5);
        document.getElementById('flood_policy_renewal_date_preview').textContent = assign_value_inputbased(document.getElementById('flood_policy_renewal_date').value,6);
        const fratingselectElem = document.getElementById('flood_rating');
        let fratingSelectedText = "";
        if(fratingselectElem.selectedIndex !== -1 && fratingselectElem.value !== "") {
            fratingSelectedText = fratingselectElem.options[fratingselectElem.selectedIndex].text;
        }
        document.getElementById('flood_rating_preview').textContent = assign_value_inputbased(fratingSelectedText,3,"flood_rating-other");
        document.getElementById('elevation_certificate_preview').textContent = assign_value_inputbased(document.getElementById('elevation_certificate').value,2);
        document.getElementById('loma_letter_preview').textContent = assign_value_inputbased(document.getElementById('loma_letter').value,2);
        document.getElementById('f_insurance_coverage_preview').textContent = assign_value_inputbased(document.getElementById('f_insurance_coverage').value,1);


        const dcselectElem = document.getElementById('difference_in_condition');
        let dcselectedText = "";
        if(dcselectElem.selectedIndex !== -1 && dcselectElem.value !== "") {
            dcselectedText = dcselectElem.options[dcselectElem.selectedIndex].text;
        }
        document.getElementById('difference_in_condition_preview').textContent = assign_value_inputbased(dcselectedText,3,"difference_in_condition-other");
        document.getElementById('dic_ren_month_preview').textContent = assign_value_inputbased(document.getElementById('dic_ren_month').value,1);
        document.getElementById('dic_expiry_premium_preview').textContent = assign_value_inputbased(document.getElementById('dic_expiry_premium').value,5);
        document.getElementById('dic_policy_renewal_date_preview').textContent = assign_value_inputbased(document.getElementById('dic_policy_renewal_date').value,6);
        document.getElementById('dic_hurricane_deductible_preview').textContent = checkifNAthenNotAddpercantage(assign_value_inputbased(document.getElementById('dic_hurricane_deductible').value,1));
        document.getElementById('dic_all_other_perils_preview').textContent = assign_value_inputbased(document.getElementById('dic_all_other_perils').value,1);
        document.getElementById('dic_insurance_coverage_preview').textContent = assign_value_inputbased(document.getElementById('dic_insurance_coverage').value,1);


        const xwselectElem = document.getElementById('x_wind');
        let xwselectedText = "";
        if(xwselectElem.selectedIndex !== -1 && xwselectElem.value !== "") {
            xwselectedText = xwselectElem.options[xwselectElem.selectedIndex].text;
        }
        // console.log(xwselectedText);
        document.getElementById('x_wind_preview').textContent = assign_value_inputbased(xwselectedText,3,"x_wind-other");
        document.getElementById('xw_ren_month_preview').textContent = assign_value_inputbased(document.getElementById('xw_ren_month').value,1);
        document.getElementById('xw_expiry_premium_preview').textContent = assign_value_inputbased(document.getElementById('xw_expiry_premium').value,5);
        document.getElementById('xw_policy_renewal_date_preview').textContent = assign_value_inputbased(document.getElementById('xw_policy_renewal_date').value,6);
        document.getElementById('xw_hurricane_deductible_preview').textContent = checkifNAthenNotAddpercantage(assign_value_inputbased(document.getElementById('xw_hurricane_deductible').value,1));
        document.getElementById('xw_all_other_perils_preview').textContent = assign_value_inputbased(document.getElementById('xw_all_other_perils').value,1);
        document.getElementById('xw_insurance_coverage_preview').textContent = assign_value_inputbased(document.getElementById('xw_insurance_coverage').value,1);


        const ebselectElem = document.getElementById('equipment_breakdown');
        let ebselectedText = "";
        if(ebselectElem.selectedIndex !== -1 && ebselectElem.value !== "") {
            ebselectedText = ebselectElem.options[ebselectElem.selectedIndex].text;
        }
        document.getElementById('equipment_breakdown_preview').textContent = assign_value_inputbased(ebselectedText,3,"equipment_breakdown-other");
        document.getElementById('eb_ren_month_preview').textContent = assign_value_inputbased(document.getElementById('eb_ren_month').value,1);
        document.getElementById('eb_expiry_premium_preview').textContent = assign_value_inputbased(document.getElementById('eb_expiry_premium').value,5);
        document.getElementById('eb_policy_renewal_date_preview').textContent = assign_value_inputbased(document.getElementById('eb_policy_renewal_date').value,6);
        document.getElementById('eb_hurricane_deductible_preview').textContent = checkifNAthenNotAddpercantage(assign_value_inputbased(document.getElementById('eb_hurricane_deductible').value,1));
        document.getElementById('eb_all_other_perils_preview').textContent = assign_value_inputbased(document.getElementById('eb_all_other_perils').value,1);
        document.getElementById('eb_insurance_coverage_preview').textContent = assign_value_inputbased(document.getElementById('eb_insurance_coverage').value,1);


        const caselectElem = document.getElementById('commercial_automobiles');
        let caselectedText = "";
        if(caselectElem.selectedIndex !== -1 && caselectElem.value !== "") {
            caselectedText = caselectElem.options[caselectElem.selectedIndex].text;
        }
        document.getElementById('commercial_automobiles_preview').textContent = assign_value_inputbased(caselectedText,3,"commercial_automobiles-other");
        document.getElementById('ca_ren_month_preview').textContent = assign_value_inputbased(document.getElementById('ca_ren_month').value,1);
        document.getElementById('ca_expiry_premium_preview').textContent = assign_value_inputbased(document.getElementById('ca_expiry_premium').value,5);
        document.getElementById('ca_policy_renewal_date_preview').textContent = assign_value_inputbased(document.getElementById('ca_policy_renewal_date').value,6);
        document.getElementById('ca_hurricane_deductible_preview').textContent = checkifNAthenNotAddpercantage(assign_value_inputbased(document.getElementById('ca_hurricane_deductible').value,1));
        document.getElementById('ca_all_other_perils_preview').textContent = assign_value_inputbased(document.getElementById('ca_all_other_perils').value,1);
        document.getElementById('ca_insurance_coverage_preview').textContent = assign_value_inputbased(document.getElementById('ca_insurance_coverage').value,1);


        const mselectElem = document.getElementById('marina');
        let mselectedText = "";
        if(mselectElem.selectedIndex !== -1 && mselectElem.value !== "") {
            mselectedText = mselectElem.options[mselectElem.selectedIndex].text;
        }
        document.getElementById('marina_preview').textContent = assign_value_inputbased(mselectedText,3,"marina-other");
        document.getElementById('m_ren_month_preview').textContent = assign_value_inputbased(document.getElementById('m_ren_month').value,1);
        document.getElementById('m_expiry_premium_preview').textContent = assign_value_inputbased(document.getElementById('m_expiry_premium').value,5);
        document.getElementById('m_policy_renewal_date_preview').textContent = assign_value_inputbased(document.getElementById('m_policy_renewal_date').value,6);
        document.getElementById('m_hurricane_deductible_preview').textContent = checkifNAthenNotAddpercantage(assign_value_inputbased(document.getElementById('m_hurricane_deductible').value,1));
        document.getElementById('m_all_other_perils_preview').textContent = assign_value_inputbased(document.getElementById('m_all_other_perils').value,1);
        document.getElementById('m_insurance_coverage_preview').textContent = assign_value_inputbased(document.getElementById('m_insurance_coverage').value,1);
    }

    function loadAdditionalPolicyData() {
        $(".appended_area_policy").remove();
        // let count_loop = $('.additional_policy').length;
        // console.log(count_loop);

        let appended_data = '';

        $(".additional_policy").each(function(index) {
            const i = $(this).data('id');
            const adcarrelectElem = document.getElementById('carrier'+i);
            let adcarrelectedText = "";
            if(adcarrelectElem.selectedIndex !== -1 && adcarrelectElem.value !== "") {
                adcarrelectedText = adcarrelectElem.options[adcarrelectElem.selectedIndex].text;
            }
            const carrier = assign_value_inputbased(adcarrelectedText,3,"carrier"+i+"-other");
            const policy_type = assign_value_inputbased($("#policy_type"+i).val(),1);
            const expiry_premium = assign_value_inputbased($("#a_expiry_premium"+i).val(),5);
            const polcy_renewal = assign_value_inputbased($("#a_policy_renewal_date"+i).val(),6);
            const huricane_deductable = checkifNAthenNotAddpercantage(assign_value_inputbased($("#a_hurricane_deductible"+i).val(),1));
            const all_other_perlis = assign_value_inputbased($("#a_all_other_perils"+i).val(),1);
            const notes = assign_value_inputbased($("#insurance_coverage"+i).val(),1);

            let additional_name;

            if(policy_type == ""){
                additional_name = `Additional Policy ${index + 1}`;
            }
            else{
                additional_name = `Additional Policy (${policy_type})`;
            }


            appended_data += `<div class="p-2 mt-4 pt-3 pb-0 mx-0 rounded border position-relative appended_area_policy">
                            <div class="section-head position-absolute m-0 p-0 z-5 d-inline-block font-weight-bold px-1 bg-white">${additional_name} :</div>
                            <div class="form-row">
                                <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
                                    <strong>Policy Type:</strong> <span class="small" id="policy_type${i}">${policy_type}</span>
                                </div>
                                <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
                                    <strong>Carrier:</strong> <span class="small" id="carrier${i}">${carrier}</span>
                                </div>
                                <div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
                                    <strong>Expiring Premium:</strong> <span class="small" id="expiry_premium${i}">${expiry_premium}</span>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-12 col-md-6 col-lg-4 mb-1 px-2">
                                    <strong>Policy Renewal Date:</strong> <span class="small" id="policy_renewal${i}">${polcy_renewal}</span>
                                </div>
                                <div class="form-group col-12 col-md-6 col-lg-3 mb-1 px-2">
                                    <strong>Hurricane Deductible:</strong> <span class="small" id="hurricane_deduct${i}">${huricane_deductable}</span>
                                </div>
                                <div class="form-group col-12 col-md-6 col-lg-5 mb-1 px-2">
                                    <strong>All Other Perils Deductible:</strong> <span class="small" id="all_other_peril${i}">${all_other_perlis}</span>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-12 col-md-12 col-lg-12 mb-1 px-2">
                                    <strong>Notes:</strong> <span class="small longtextarea" id="notes${i}">${notes}</span>
                                </div>
                            </div>
                        </div>`;
        });

        $(".preview_card_body_append").append(appended_data);

    }
    function loadPreviewData() {
        loadBusinessInfoData();
        loadCommunicationInfoData();
        loadPropertyInfoData();
        loadAdditionalPolicyData();
        // console.log("function called");
    }
    // loadPreviewData();
    document.getElementById('downloadBtn').addEventListener('click', () => {
        // const modal = document.getElementById('previewLeadModal');
        const printSection = document.getElementById('printSection');

        printSection.classList.add('a4-style');

        // Temporarily show the modal fully
        // modal.style.display = 'block';
        // modal.style.opacity = '1';
        // modal.style.zIndex = '1050'; // Ensure modal is above other elements
        // modal.style.overflow = 'visible';

        // // Disable body scrolling
        // document.body.style.overflow = 'hidden';
        
        // Configure html2pdf options
        const options = {
            margin: 0.5,
            filename: 'lead_preview.pdf',
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: {
                scale: 2,
                useCORS: true,
                scrollX: 0,
                scrollY: 0,
            },
            jsPDF: {
                unit: 'in',
                format: 'letter',
                orientation: 'portrait',
            }
        };

        // Generate the PDF
        setTimeout(() => {
            html2pdf().set(options).from(printSection).save().finally(() => {
                // Restore modal state and body scrolling
                // modal.style.display = '';
                // modal.style.opacity = '';
                // modal.style.zIndex = '';
                // modal.style.overflow = '';
                // document.body.style.overflow = '';

                printSection.classList.remove('a4-style');

                // Ensure modal is fully hidden
                // modal.classList.remove('show');
                // document.body.classlist.remove('modal-open')
                // Remove any modal-backdrop elements (if manually added by Bootstrap styles)
                // const backdrops = document.querySelectorAll('.modal-backdrop');
                // backdrops.forEach((backdrop) => backdrop.remove());
            });
        }, 500);

        // Ensure modal works correctly if opened again
        // modal.addEventListener('hidden.bs.modal', () => {
        //     modal.style.display = '';
        //     modal.style.opacity = '';
        //     modal.style.zIndex = '';
        //     modal.style.overflow = '';
        //     document.body.style.overflow = '';
        // });
    });