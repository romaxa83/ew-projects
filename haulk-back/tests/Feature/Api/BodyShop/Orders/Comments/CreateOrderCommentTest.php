<?php

namespace Tests\Feature\Api\BodyShop\Orders\Comments;

use App\Models\BodyShop\Orders\Order;
use App\Models\BodyShop\Orders\OrderComment;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Api\Orders\OrderTestCase;

class CreateOrderCommentTest extends OrderTestCase
{
    use DatabaseTransactions;

    public function test_it_comment_created()
    {
        $user = $this->loginAsBodyShopAdmin();

        $order = factory(Order::class)->create();

        $this->postJson(
            route('body-shop.order-comments.store', $order->id),
            [
                'comment' => 'comment text',
            ]
        )->assertCreated();

        $this->assertDatabaseHas(
            OrderComment::TABLE_NAME,
            [
                'order_id' => $order->id,
                'comment' => 'comment text',
                'user_id' => $user->id,
            ]
        );
    }
}
