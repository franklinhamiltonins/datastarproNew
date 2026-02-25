<?php

namespace App\Model\LeadsModel\LeadTrait;

/**
 * Trait for static data methods in Lead model.
 * Contains methods for states, counties, months, roof covering, etc.
 */
trait LeadStaticDataTrait
{
    /**
     * Get list of US states for lead form.
     */
    public static function leadStatesData(): array
    {
        return [
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
            'WY' => 'Wyoming',
        ];
    }

    /**
     * Get list of contact titles.
     */
    public static function contactTitle(): array
    {
        return [
            '' => 'Select Title',
            'President' => 'President',
            'Vice President' => 'Vice President',
            'Treasurer' => 'Treasurer',
            'Secretary' => 'Secretary',
            'Director' => 'Director',
            'Property Manager' => 'Property Manager',
        ];
    }

    /**
     * Get list of Florida counties for lead form.
     */
    public static function leadCountiesData(): array
    {
        return [
            '' => 'Select County',
            'Alachua' => 'Alachua',
            'Baker' => 'Baker',
            'Bay' => 'Bay',
            'Bradford' => 'Bradford',
            'Brevard' => 'Brevard',
            'Broward' => 'Broward',
            'Calhoun' => 'Calhoun',
            'Charlotte' => 'Charlotte',
            'Citrus' => 'Citrus',
            'Clay' => 'Clay',
            'Collier' => 'Collier',
            'Columbia' => 'Columbia',
            'DeSoto' => 'DeSoto',
            'Dixie' => 'Dixie',
            'Duval' => 'Duval',
            'Escambia' => 'Escambia',
            'Flagler' => 'Flagler',
            'Franklin' => 'Franklin',
            'Gadsden' => 'Gadsden',
            'Gilchrist' => 'Gilchrist',
            'Glades' => 'Glades',
            'Gulf' => 'Gulf',
            'Hamilton' => 'Hamilton',
            'Hardee' => 'Hardee',
            'Hendry' => 'Hendry',
            'Hernando' => 'Hernando',
            'Highlands' => 'Highlands',
            'Hillsborough' => 'Hillsborough',
            'Holmes' => 'Holmes',
            'Indian River' => 'Indian River',
            'Jackson' => 'Jackson',
            'Jefferson' => 'Jefferson',
            'Lafayette' => 'Lafayette',
            'Lake' => 'Lake',
            'Lee' => 'Lee',
            'Leon' => 'Leon',
            'Levy' => 'Levy',
            'Liberty' => 'Liberty',
            'Madison' => 'Madison',
            'Manatee' => 'Manatee',
            'Marion' => 'Marion',
            'Martin' => 'Martin',
            'Miami-Dade' => 'Miami-Dade',
            'Monroe' => 'Monroe',
            'Nassau' => 'Nassau',
            'Okaloosa' => 'Okaloosa',
            'Okeechobee' => 'Okeechobee',
            'Orange' => 'Orange',
            'Osceola' => 'Osceola',
            'Palm Beach' => 'Palm Beach',
            'Pasco' => 'Pasco',
            'Pinellas' => 'Pinellas',
            'Polk' => 'Polk',
            'Putnam' => 'Putnam',
            'Santa Rosa' => 'Santa Rosa',
            'Sarasota' => 'Sarasota',
            'Seminole' => 'Seminole',
            'St. Johns' => 'St. Johns',
            'St. Lucie' => 'St. Lucie',
            'Sumter' => 'Sumter',
            'Suwannee' => 'Suwannee',
            'Taylor' => 'Taylor',
            'Union' => 'Union',
            'Volusia' => 'Volusia',
            'Wakulla' => 'Wakulla',
            'Walton' => 'Walton',
            'Washington' => 'Washington',
            'other' => 'Other',
        ];
    }

    /**
     * Get list of months.
     */
    public static function leadMonthsData(): array
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
            'December' => 'December',
        ];
    }

    /**
     * Get list of roof covering types.
     */
    public static function leadRoofCoveringData(): array
    {
        return [
            '' => 'Select Roof Covering',
            'Shingle' => 'Shingle',
            'Concrete Tile' => 'Concrete Tile',
            'Metal' => 'Metal',
            'Built Up' => 'Built Up',
            'Membrane' => 'Membrane',
            'Concrete' => 'Concrete',
            'Other' => 'Other',
        ];
    }

    /**
     * Get list of roof geometry types.
     */
    public static function leadRoofGeometryData(): array
    {
        return [
            '' => 'Select Roof Geometry',
            'Hip' => 'Hip',
            'Gable' => 'Gable',
            'Flat' => 'Flat',
            'Other' => 'Other',
        ];
    }
}
