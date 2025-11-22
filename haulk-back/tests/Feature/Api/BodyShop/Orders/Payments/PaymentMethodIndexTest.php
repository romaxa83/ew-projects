<?php

namespace Api\BodyShop\Orders\Payments;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PaymentMethodIndexTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_list(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $this->getJson(route('body-shop.orders.payment-methods'))
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'key',
                        'title',
                    ],
                ],
            ]);
    }
}
