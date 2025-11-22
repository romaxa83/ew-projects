<?php

namespace Tests\Feature\Mutations\BackOffice\Orders;

use App\Enums\Orders\OrderStatusEnum;
use App\GraphQL\Mutations\BackOffice\Orders\OrderStatusChangeMutation;
use App\Models\Orders\Order;
use App\Models\Orders\OrderStatusHistory;
use App\Permissions\Orders\OrderUpdatePermission;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Core\Testing\GraphQL\Scalar\EnumValue;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\Models\OrderCreateTrait;
use Tests\Traits\Models\ProjectCreateTrait;
use Tests\Traits\Permissions\AdminManagerHelperTrait;

class OrderStatusChangeMutationTest extends TestCase
{

    use DatabaseTransactions;
    use WithFaker;
    use ProjectCreateTrait;
    use OrderCreateTrait;
    use AdminManagerHelperTrait;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginByAdminManager([OrderUpdatePermission::KEY]);
    }

    public function test_change_status_to_canceled(): void
    {
        $order = $this->createCreatedOrder();

        $query = new GraphQLQuery(
            OrderStatusChangeMutation::NAME,
            [
                'id' => $order->id,
                'status' => new EnumValue(OrderStatusEnum::CANCELED)
            ],
            [
                'id',
                'status'
            ]
        );

        $this->postGraphQLBackOffice($query->getMutation())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        OrderStatusChangeMutation::NAME => [
                            'id' => (string)$order->id,
                            'status' => OrderStatusEnum::CANCELED,
                        ]
                    ]
                ]
            );

        $this->assertDatabaseHas(
            Order::class,
            [
                'id' => $order->id,
                'status' => OrderStatusEnum::CANCELED,
            ]
        );

        $this->assertDatabaseHas(
            OrderStatusHistory::class,
            [
                'order_id' => $order->id,
                'status' => OrderStatusEnum::CANCELED,
            ]
        );
    }

    public function test_change_status_to_broken_status(): void
    {
        $order = $this->createCreatedOrder();

        $query = new GraphQLQuery(
            OrderStatusChangeMutation::NAME,
            [
                'id' => $order->id,
                'status' => new EnumValue(OrderStatusEnum::PAID)
            ],
            [
                'id',
                'status'
            ]
        );

        $this->postGraphQLBackOffice($query->getMutation())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        OrderStatusChangeMutation::NAME => [
                            'id' => (string)$order->id,
                            'status' => OrderStatusEnum::CREATED,
                        ]
                    ]
                ]
            );

        $this->assertDatabaseMissing(
            Order::class,
            [
                'id' => $order->id,
                'status' => OrderStatusEnum::PAID,
            ]
        );

        $this->assertDatabaseHas(
            OrderStatusHistory::class,
            [
                'order_id' => $order->id,
                'status' => OrderStatusEnum::PAID,
            ]
        );

        $this->assertDatabaseHas(
            OrderStatusHistory::class,
            [
                'order_id' => $order->id,
                'status' => OrderStatusEnum::CREATED,
            ]
        );
    }
}
