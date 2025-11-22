<?php

namespace Feature\Http\Api\V1\Orders\Parts\Catalog;

use App\Enums\Orders\Parts\DeliveryMethod;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DeliveryMethodTest extends TestCase
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

        $this->getJson(route('api.v1.orders.parts.catalog.delivery-methods'))
            ->assertJson([
                'data' => [
                    ['key' => DeliveryMethod::USPS->value, 'title' => DeliveryMethod::USPS->label()],
                    ['key' => DeliveryMethod::UPS->value, 'title' => DeliveryMethod::UPS->label()],
                    ['key' => DeliveryMethod::Fedex->value, 'title' => DeliveryMethod::Fedex->label()],
                    ['key' => DeliveryMethod::LTL->value, 'title' => DeliveryMethod::LTL->label()],
                    ['key' => DeliveryMethod::Our_delivery->value, 'title' => DeliveryMethod::Our_delivery->label()],
                ],
            ])
            ->assertJsonCount(count(DeliveryMethod::cases()), 'data')
        ;
    }

    /** @test */
    public function not_auth()
    {
        $res = $this->getJson(route('api.v1.orders.parts.catalog.delivery-methods'));

        self::assertUnauthenticatedMessage($res);
    }
}
