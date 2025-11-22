<?php

namespace Tests\Feature\Api\Contacts;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\TestCase;

class GetContactsListTest extends TestCase
{
    use DatabaseTransactions;

    public function testIfNotAuthorized()
    {
        $response = $this->getJson(route('contacts.index'));

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function testIfAuthorizedAllowed()
    {
        $this->loginAsCarrierDispatcher();

        $this->getJson(route('contacts.index'))
            ->assertOk()
            ->assertJsonStructure(['data', 'links', 'meta',]);
    }
}
