<?php

namespace Tests\Feature\Api\OrderComments;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Feature\Api\Orders\OrderTestCase;

class CreateOrderCommentTest extends OrderTestCase
{
    use DatabaseTransactions;

    public function test_it_comment_created()
    {
        $this->withoutExceptionHandling();
        $this->loginAsCarrierDispatcher();

        $response = $this->postJson(route('orders.store'), $this->getRequiredFields())
            ->assertCreated();

        $order = $response->getData(true)['data'];

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
    }
}
