<?php

namespace Tests\Feature\Api\BodyShop\Orders\Comments;

use App\Models\BodyShop\Orders\Order;
use App\Models\BodyShop\Orders\OrderComment;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Feature\Api\Orders\OrderTestCase;
use Tests\Helpers\Traits\UserFactoryHelper;

class GetOrderCommentsListTest extends OrderTestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    public function testIfNotAuthorized(): void
    {
        $response = $this->getJson(route('body-shop.order-comments.index', 1));

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_it_authorized_allowed(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $order = factory(Order::class)->create();
        $user1 = $this->bsAdminFactory();
        $comment1 = factory(OrderComment::class)->create([
            'order_id' => $order->id,
            'user_id' => $user1->id,
        ]);
        $user2 = $this->bsAdminFactory();
        $comment2 = factory(OrderComment::class)->create([
            'order_id' => $order->id,
            'user_id' => $user2->id,
        ]);

        $order2 = factory(Order::class)->create();
        factory(OrderComment::class)->create([
            'order_id' => $order2->id,
            'user_id' => $user1->id,
        ]);

        $response = $this->getJson(route('body-shop.order-comments.index', $order))
            ->assertOk();

        $comments = $response['data'];
        $this->assertCount(2, $comments);
        $this->assertEquals($comment1->id, $comments[0]['id']);
        $this->assertEquals($comment2->id, $comments[1]['id']);
    }

    public function test_it_get_comment_list_with_deleted_users(): void
    {
        $user = $this->bsAdminFactory(['deleted_at' => now()]);

        $this->loginAsBodyShopAdmin();

        $order = factory(Order::class)->create();
        factory(OrderComment::class)->create([
            'order_id' => $order->id,
            'user_id' => $user->id,
        ]);

        $response = $this->getJson(route('body-shop.order-comments.index', $order))
            ->assertOk();

        $comments = $response['data'];
        $this->assertCount(1, $comments);
    }
}
