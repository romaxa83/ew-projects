<?php

namespace Tests\Feature\Api\OrderComments;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Feature\Api\Orders\OrderTestCase;

class DeleteOrderCommentTest extends OrderTestCase
{
    use DatabaseTransactions;

    public function testIfCommentCreated()
    {
        $this->loginAsCarrierDispatcher();

        // create order
        $response = $this->postJson(route('orders.store'), $this->getRequiredFields());
        // check if created
        $response->assertStatus(Response::HTTP_CREATED);
        // get created order data
        $order = $response->getData(true)['data'];

        // create comment
        $response = $this->postJson(
            route('order-comments.store', $order['id']),
            [
                'comment' => 'comment text',
            ]
        );

        // check if created
        $response->assertStatus(Response::HTTP_CREATED);

        // get created comment data
        $comment = $response->getData(true)['data'];

        // check if exists in database
        $this->assertDatabaseHas(
            'order_comments',
            [
                'id' => $comment['id']
            ]
        );

        // delete comment error for dispatcher
        $this->deleteJson(route('order-comments.destroy', [$order['id'], $comment['id']]))
            ->assertStatus(Response::HTTP_FORBIDDEN);

        // delete comment success for superadmin
        $this->loginAsCarrierSuperAdmin();

        $this->deleteJson(route('order-comments.destroy', [$order['id'], $comment['id']]))
            ->assertStatus(Response::HTTP_NO_CONTENT);

        // check if missing in db
        $this->assertDatabaseMissing(
            'order_comments',
            [
                'id' => $comment['id']
            ]
        );
    }
}
