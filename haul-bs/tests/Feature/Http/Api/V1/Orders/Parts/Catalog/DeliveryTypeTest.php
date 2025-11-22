<?php

namespace Feature\Http\Api\V1\Orders\Parts\Catalog;

use App\Enums\Orders\Parts\DeliveryType;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DeliveryTypeTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function success_list()
    {
        $this->loginUserAsSuperAdmin();

        $this->getJson(route('api.v1.orders.parts.catalog.delivery-types'))
            ->assertJson([
                'data' => [
                    ['key' => DeliveryType::Delivery->value, 'title' => DeliveryType::Delivery->label()],
                    ['key' => DeliveryType::Pickup->value, 'title' => DeliveryType::Pickup->label()],
                ],
            ])
            ->assertJsonCount(count(DeliveryType::cases()), 'data')
        ;
    }

    /** @test */
    public function not_auth()
    {
        $res = $this->getJson(route('api.v1.orders.parts.catalog.delivery-types'));

        self::assertUnauthenticatedMessage($res);
    }
}
