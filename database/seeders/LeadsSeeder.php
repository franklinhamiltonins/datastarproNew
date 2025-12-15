<?php

use Illuminate\Database\Seeder;
use App\Model\LeadsModel\Lead;

class LeadsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //create 
      Lead::create([
        'type'=>'Condo',
        'name'=>'THE PALM ROYAL APARTMENTS, INC.',
        'creation_date'=>'2017-12-03',
        'address1'=>'519 Plaza Seville CT ',
        'address2'=>'Apt 39',
        'city'=>'Treasure Island',
        'state'=>'FL',
        'zip'=>'33881',
        'county'=>'polk',
        'unit_count'=>'292',
        'renewal_date'=>'2020-04-01',
        'renewal_month'=>'April',
        'premium'=>'1200.22',
        'insured_amount'=>'323211.22',
        'manag_company'=>'Frenchak Karis',
        'prop_manager'=> 'Frenchak Karis',
        'current_agency'=> 'Business Systems-America Inc',
        'current_agent'=>'Balis Otis W Jr.',
        'ins_prop_carrier'=>'Hunton Andrews Kurth ',
        'ins_flood' => 'Yes'
        ])->contacts()->create([
            'c_full_name'=>'Beckett Marjorie',
            'c_first_name'=>'Beckett',
            'c_last_name'=>'Marjorie',
            'c_title'=> 'Director',
            'c_address1'=>'45 SE 13TH St',
            'c_address2'=>' Apt A2',
            'c_city'=>'Boca Raton',
            'c_state'=>'FL',
            'c_zip'=> '33432',
            'c_county'=> 'palmbeach',
            'c_phone'=> '8634203310',
            'c_email'=> ''
        ]);

    Lead::create([
        'type'=>'HOA',
        'name'=>'PALM-WEST GARDENS INC.',
        'creation_date'=>'2018-05-03',
        'address1'=>'356 Winter Ridge Blvd',
        'address2'=>'',
        'city'=>'W Melbourne',
        'state'=>'FL',
        'zip'=>'32904',
        'county'=>'Beeville',
        'unit_count'=>'391',
        'renewal_date'=>'2020-03-03',
        'renewal_month'=>'March',
        'premium'=>'1400.22',
        'insured_amount'=>'350000',
        'manag_company'=>'Sanford George',
        'prop_manager'=> 'George',
        'current_agency'=> 'IMPERIAL TOWERS NORTH CONDOMINIUM, INC.',
        'current_agent'=>'Frankel Stuart',
        'ins_prop_carrier'=>'DPR Construction',
        'ins_flood' => 'No'
        ])->contacts()->create([
            'c_full_name'=>'Sanford George',
            'c_first_name'=>'Sanford',
            'c_last_name'=>'George',
            'c_title'=> 'President',
            'c_address1'=>'45 SE 13TH St',
            'c_address2'=>' Apt A2',
            'c_city'=>'Boca Raton',
            'c_state'=>'FL',
            'c_zip'=> '33432',
            'c_county'=> 'palmbeach',
            'c_phone'=> '8634203310',
            'c_email'=> ''
            ]);

    Lead::create([
        'type'=>'Commercial',
        'name'=>'KINGSWOOD ASSOCIATION NO. 3, INC.',
        'creation_date'=>'2019-01-05',
        'address1'=>'1087 S Hiawassee Rd',
        'address2'=>'Apt 422',
        'city'=>'Bohemia',
        'state'=>'NY',
        'zip'=>'56421',
        'county'=>'volusia',
        'unit_count'=>'1171',
        'renewal_date'=>'2020-01-01',
        'renewal_month'=>'January',
        'premium'=>'450',
        'insured_amount'=>'15000',
        'manag_company'=>'Cavanaugh Frank',
        'prop_manager'=> 'Frank',
        'current_agency'=> 'RIVEREDGE CONDOMINIUM, INC.',
        'current_agent'=>'Clark Diane',
        'ins_prop_carrier'=>'KnowBe4',
        'ins_flood' => 'Yes'
        ])->contacts()->create([
            'c_full_name'=>'Donna Berger',
            'c_first_name'=>'Donna',
            'c_last_name'=>'Berger',
            'c_title'=> 'Treasurer',
            'c_address1'=>'45 SE 13TH St',
            'c_address2'=>' Apt A2',
            'c_city'=>'Boca Raton',
            'c_state'=>'FL',
            'c_zip'=> '33432',
            'c_county'=> 'palmbeach',
            'c_phone'=> '8634203310',
            'c_email'=> ''
        ]);
    }
}
