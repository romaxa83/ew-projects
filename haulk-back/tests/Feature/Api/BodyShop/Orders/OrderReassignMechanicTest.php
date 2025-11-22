<?php

namespace Api\BodyShop\Orders;

use App\Models\BodyShop\Orders\Order;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class OrderReassignMechanicTest extends TestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    public function test_reassign_mechanic(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $order = factory(Order::class)->create();
        $mechanic = $this->bsMechanicFactory();
        $attributes = ['mechanic_id' => $mechanic->id];

        $this->putJson(route('body-shop.orders.reassign-mechanic', $order), $attributes)
            ->assertOk();

        $this->assertDatabaseHas(
            Order::TABLE_NAME,
            [
                'mechanic_id' => $mechanic->id,
                'id' => $order->id,
            ]
        );
    }

    public function test_reassign_mechanic_validation_error(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $order = factory(Order::class)->create();
        $attributes = [];

        $this->putJson(route('body-shop.orders.reassign-mechanic', $order), $attributes)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
