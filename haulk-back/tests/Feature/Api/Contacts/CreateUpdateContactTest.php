<?php

namespace Tests\Feature\Api\Contacts;

use App\Models\Contacts\Contact;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;

class CreateUpdateContactTest extends ContactTestCase
{

    public function test_it_no_required_fields()
    {
        $this->loginAsCarrierDispatcher();

        $response = $this->postJson(route('contacts.store'), $this->getContactFieldsOther());

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertDatabaseMissing(
            Contact::TABLE_NAME,
            [
                'user_id' => $this->authenticatedUser->id,
                'state_id' => $this->getContactFieldsOther()['state_id'],
            ]
        );
    }

    public function test_it_contact_created()
    {
        $this->loginAsCarrierDispatcher();

        $response = $this->postJson(
            route('contacts.store'),
            $this->getContactFieldsRequired() + $this->getContactFieldsOther()
        )
            ->assertCreated();

        $contact = $response->json('data');

        $this->assertDatabaseHas(
            'contacts',
            [
                'id' => $contact['id']
            ]
        );
    }

    public function test_it_contact_updated()
    {
        $this->loginAsCarrierDispatcher();

        $response = $this->postJson(
            route('contacts.store'),
            $this->getContactFieldsRequired() + $this->getContactFieldsOther()
        )
            ->assertCreated();

        $contact = $response->json('data');
        $fields = $this->getContactFieldsUpdate();
        $this
            ->putJson(route('contacts.update', $contact['id']), $fields)
            ->assertOk();

        $this->assertDatabaseHas(
            Contact::TABLE_NAME,
            [
                'id' => $contact['id']
            ] + Arr::except($fields, 'state_id')
        );
    }

    public function test_it_create_contact_with_not_completely_filled_phone()
    {
        $this->loginAsCarrierDispatcher();

        $contactFieldsRequired = $this->getContactFieldsRequired();
        $contactFieldsOther = $this->getContactFieldsOther();

        $notCorrectPhone = ['phone' => '+123'];
        $this->postJson(
            route('contacts.store'),
            $contactFieldsRequired
            + $contactFieldsOther
            + $notCorrectPhone
        )
            ->assertCreated();

        $this->assertDatabaseMissing(Contact::TABLE_NAME, $notCorrectPhone);

        $this->assertDatabaseHas(Contact::TABLE_NAME, $contactFieldsRequired);
    }
}
