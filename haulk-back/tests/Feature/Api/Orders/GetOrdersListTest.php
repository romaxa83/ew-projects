<?php

namespace Tests\Feature\Api\Orders;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\TestCase;

class GetOrdersListTest extends TestCase
{
    use DatabaseTransactions;

    public function testIfNotAuthorized()
    {
        $response = $this->getJson(route('orders.index'));

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function testIfAuthorizedAllowed()
    {
        $this->loginAsCarrierSuperAdmin();

        $this->getJson(route('orders.index'))
            ->assertOk()
            ->assertJsonStructure(['data', 'links', 'meta',]);
    }
}
