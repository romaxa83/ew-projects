<?php

namespace Tests\Feature\Api\Contacts;

use App\Models\Contacts\Contact;

class DeleteContactTest extends ContactTestCase
{
    public function test_it_contact_created()
    {
        $this->loginAsCarrierDispatcher();

        $response = $this
            ->postJson(
                route('contacts.store'),
                $this->getContactFieldsRequired() + $this->getContactFieldsOther()
            )
            ->assertCreated();

        // get created contact data
        $contact = $response->getData(true)['data'];

        // check if exists in database
        $this->assertDatabaseHas(
            Contact::TABLE_NAME,
            [
                'id' => $contact['id'],
                'deleted_at' => null,
            ]
        );

        $this->deleteJson(route('contacts.destroy', $contact['id']))
            ->assertNoContent();

        $this->assertDatabaseMissing(
            Contact::TABLE_NAME,
            [
                'id' => $contact['id'],
                'deleted_at' => null,
            ]
        );
    }
}
