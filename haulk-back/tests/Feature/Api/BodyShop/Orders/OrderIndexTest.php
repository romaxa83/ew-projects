<?php

namespace Api\BodyShop\Orders;

use App\Models\BodyShop\Orders\Order;
use App\Models\BodyShop\Orders\OrderComment;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class OrderIndexTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_forbidden_for_not_authorized_users(): void
    {
        $this->getJson(route('body-shop.orders.index'))
            ->assertUnauthorized();
    }

    public function test_it_forbidden_for_permitted_users(): void
    {
        $this->loginAsBodyShopMechanic();

        $this->getJson(route('body-shop.orders.index'))
            ->assertForbidden();
    }

    public function test_it_show_all_for_bs_super_admin(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $this->getJson(route('body-shop.orders.index'))
            ->assertOk();
    }

    public function test_it_show_all_for_body_shop_admin(): void
    {
        $this->loginAsBodyShopAdmin();

        $this->getJson(route('body-shop.orders.index'))
            ->assertOk();
    }

    public function test_comments_count_field(): void
    {
        $user = $this->loginAsBodyShopAdmin();
        $order = factory(Order::class)->create();
        factory(OrderComment::class)->create([
            'order_id' => $order->id,
            'user_id' => $user->id,
        ]);
        factory(OrderComment::class)->create([
            'order_id' => $order->id,
            'user_id' => $user->id,
        ]);

        $response = $this->getJson(route('body-shop.orders.index'))
            ->assertOk();

        $this->assertEquals(2, $response['data'][0]['comments_count']);
    }
}
