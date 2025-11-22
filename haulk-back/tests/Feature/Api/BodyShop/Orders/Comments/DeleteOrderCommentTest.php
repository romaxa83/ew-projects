<?php

namespace Tests\Feature\Api\BodyShop\Orders\Comments;

use App\Models\BodyShop\Orders\Order;
use App\Models\BodyShop\Orders\OrderComment;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Feature\Api\Orders\OrderTestCase;

class DeleteOrderCommentTest extends OrderTestCase
{
    use DatabaseTransactions;

    public function testIfCommentDeleted()
    {
        $user = $this->loginAsBodyShopSuperAdmin();

        $order = factory(Order::class)->create();
        $comment = factory(OrderComment::class)->create([
            'order_id' => $order->id,
            'user_id' => $user->id,
        ]);

        $this->deleteJson(route('body-shop.order-comments.destroy', [$order, $comment]))
            ->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseMissing(
            OrderComment::class,
            [
                'id' => $comment->id,
            ]
        );
    }
}
