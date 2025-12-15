<?php


namespace App\Traits;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Config;
use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Builder;
use Vonage\Client;
use Illuminate\Support\Facades\Log;
use App\Model\LeadsModel\Lead;
use App\Model\LeadsModel\Contact;
use Illuminate\Support\Facades\Auth;
use Validator;
use DB;
use Hash;

trait SunbizDataTrait
{
	public function saveMembers($members, $lead_id)
    {
        foreach ($members as $member) {
            $this->checkContactExistance($lead_id,$member['first_name'],$member['last_name'],$member['member_name'],$member['member_title']);
            DB::table('contactscraps')->insert([
                'c_full_name' => $member['member_name'],
                'c_title' => $member['member_title'],
                'lead_id' => $lead_id,
                'c_first_name' => $member['first_name'],
                'c_last_name' => $member['last_name'],
                'added_by_scrap_apis' => 1,
            ]);
        }
        Contact::where('lead_id', $lead_id)->whereNull("new_scrap_status")->update(["new_scrap_status"=> 3]);
    }

    public  function checkContactExistance($leadId,$firstName,$lastName,$c_full_name,$c_title)
    {
        $firstName = trim($firstName);
        $lastName = trim($lastName);
        // echo "<br>"; echo $leadId;echo "<br>"; echo $firstName;echo "<br>"; echo $lastName;echo "<br>"; echo $c_full_name;echo "<br>"; echo $c_title;exit;
        $check_contact_existance = Contact::where('lead_id', $leadId)
        ->where(function ($query) use ($firstName, $lastName)  {
            $query->where(function ($q) use ($firstName, $lastName)  {
                $q->where('c_first_name', $firstName)
                  ->where('c_last_name', $lastName);
            })->orWhere(function ($q) use ($firstName, $lastName)  {
                $q->where('c_first_name', $lastName)
                  ->where('c_last_name', $firstName);
            });
        })
        ->first();

        if($check_contact_existance){
            $check_contact_existance->new_scrap_status = 1;
            $check_contact_existance->save();
        }
        else{
            $contact = new Contact();
            $contact->lead_id = $leadId;
            $contact->c_first_name = $firstName;
            $contact->c_last_name = $lastName;
            $contact->c_full_name = $c_full_name;
            $contact->c_title = $c_title;
            $contact->new_scrap_status = 2;
            $contact->save();
        }
        return;
    }
}