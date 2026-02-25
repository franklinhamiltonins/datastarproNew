<?php

namespace App\Traits;

use App\Model\LeadsModel\Contact;
use DB;

trait SunbizDataTrait
{
    /**
     * Save members to database
     */
    protected function saveMemberData(array $member, int $leadId): void
    {
        $this->checkContactExistance(
            $leadId,
            $member['first_name'],
            $member['last_name'],
            $member['member_name'],
            $member['member_title']
        );

        DB::table('contactscraps')->insert([
            'c_full_name' => $member['member_name'],
            'c_title' => $member['member_title'],
            'lead_id' => $leadId,
            'c_first_name' => $member['first_name'],
            'c_last_name' => $member['last_name'],
            'added_by_scrap_apis' => 1,
        ]);
    }

    public function saveMembers($members, $lead_id)
    {
        foreach ($members as $member) {
            $this->saveMemberData($member, $lead_id);
        }
        Contact::where('lead_id', $lead_id)->whereNull('new_scrap_status')->update(['new_scrap_status' => 3]);
    }

    /**
     * Build contact existence query
     */
    protected function buildContactExistenceQuery($leadId, string $firstName, string $lastName)
    {
        return Contact::where('lead_id', $leadId)
            ->where(function ($query) use ($firstName, $lastName) {
                $query->where(function ($q) use ($firstName, $lastName) {
                    $q->where('c_first_name', $firstName)
                        ->where('c_last_name', $lastName);
                })->orWhere(function ($q) use ($firstName, $lastName) {
                    $q->where('c_first_name', $lastName)
                        ->where('c_last_name', $firstName);
                });
            });
    }

    /**
     * Update existing contact status
     */
    protected function updateExistingContact($contact): void
    {
        $contact->new_scrap_status = 1;
        $contact->save();
    }

    /**
     * Create new contact
     */
    protected function createNewContact(
        int $leadId,
        string $firstName,
        string $lastName,
        string $c_full_name,
        string $c_title
    ): void {
        $contact = new Contact;
        $contact->lead_id = $leadId;
        $contact->c_first_name = $firstName;
        $contact->c_last_name = $lastName;
        $contact->c_full_name = $c_full_name;
        $contact->c_title = $c_title;
        $contact->new_scrap_status = 2;
        $contact->save();
    }

    public function checkContactExistance($leadId, $firstName, $lastName, $c_full_name, $c_title)
    {
        $firstName = trim($firstName);
        $lastName = trim($lastName);

        $check_contact_existance = $this->buildContactExistenceQuery($leadId, $firstName, $lastName)->first();

        if ($check_contact_existance) {
            $this->updateExistingContact($check_contact_existance);
        } else {
            $this->createNewContact($leadId, $firstName, $lastName, $c_full_name, $c_title);
        }
    }
}
