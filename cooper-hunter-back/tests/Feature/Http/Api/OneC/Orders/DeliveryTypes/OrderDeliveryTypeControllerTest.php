<?php

namespace Tests\Feature\Http\Api\OneC\Orders\DeliveryTypes;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class OrderDeliveryTypeControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_list(): void
    {
        $this->loginAsModerator();

        $this->getJson(route('1c.orders.delivery_types.index'))
            ->assertOk();
    }
}
