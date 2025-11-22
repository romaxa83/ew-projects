<?php

namespace Tests\Feature\Api\OrderComments;

use App\Models\Orders\OrderComment;
use App\Models\Users\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Feature\Api\Orders\OrderTestCase;
use Tests\Helpers\Traits\OrderFactoryHelper;
use Tests\Helpers\Traits\UserFactoryHelper;

class GetOrderCommentsListTest extends OrderTestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;
    use OrderFactoryHelper;

    private array $order_fields_required = [
        'load_id' => 'qwe123',
        'status' => '1',
    ];

    public function testIfNotAuthorized(): void
    {
        $response = $this->getJson(route('order-comments.index', 1));

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_it_authorized_allowed(): void
    {
        $this->loginAsCarrierDispatcher();

        $response = $this->postJson(route('orders.store'), $this->getRequiredFields())
            ->assertCreated();
        $order = $response->json('data');

        $response = $this->postJson(
            route('order-comments.store', $order['id']),
            [
                'comment' => 'comment text',
            ]
        );
        $response->assertStatus(Response::HTTP_CREATED);

        $this->getJson(route('order-comments.index', $order['id']))
            ->assertOk()
            ->assertJsonStructure(['data']);
    }

    public function test_it_get_comment_list_with_deleted_users(): void
    {
        $user = $this->userFactory(User::DISPATCHER_ROLE, ['deleted_at' => now()]);

        $this->loginAsCarrierDispatcher();

        $order = $this->orderFactory(['carrier_id' => $user->carrier_id]);

        OrderComment::factory()->create(
            [
                'user_id' => $user,
                'order_id' => $order,
            ]
        );

        $this->getJson(route('order-comments.index', $order->id))
            ->assertOk()
            ->assertJsonStructure(['data']);
    }
}
