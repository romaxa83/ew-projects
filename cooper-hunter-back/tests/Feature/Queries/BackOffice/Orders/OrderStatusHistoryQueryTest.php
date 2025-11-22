<?php


namespace Feature\Queries\BackOffice\Orders;


use App\Enums\Orders\OrderStatusEnum;
use App\GraphQL\Queries\BackOffice\Orders\OrderStatusHistoryQuery;
use App\Permissions\Orders\OrderListPermission;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\Models\OrderCreateTrait;
use Tests\Traits\Models\ProjectCreateTrait;
use Tests\Traits\Permissions\AdminManagerHelperTrait;

class OrderStatusHistoryQueryTest extends TestCase
{

    use DatabaseTransactions;
    use WithFaker;
    use ProjectCreateTrait;
    use OrderCreateTrait;
    use AdminManagerHelperTrait;

    public function test_get_one_status_history(): void
    {
        $admin = $this->loginByAdminManager([OrderListPermission::KEY]);

        $order = $this->createCreatedOrder();

        $query = new GraphQLQuery(
            OrderStatusHistoryQuery::NAME,
            [
                'id' => $order->id
            ],
            [
                'status',
                'changer' => [
                    'id',
                    'type',
                    'name',
                    'email'
                ],
                'created_at'
            ]
        );

        $this->postGraphQLBackOffice($query->getQuery())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        OrderStatusHistoryQuery::NAME => [
                            [
                                'status' => OrderStatusEnum::CREATED,
                                'changer' => [
                                    'id' => (string)$admin->id,
                                    'type' => $admin->getMorphType(),
                                    'name' => $admin->getName(),
                                    'email' => $admin->getEmail()
                                ]
                            ]
                        ]
                    ]
                ]
            );
    }

    public function test_get_two_status_history(): void
    {
        $admin = $this->loginByAdminManager([OrderListPermission::KEY]);

        $order = $this->createCreatedOrder();

        $order->status = OrderStatusEnum::PAID;
        $order->save();

        $query = new GraphQLQuery(
            OrderStatusHistoryQuery::NAME,
            [
                'id' => $order->id
            ],
            [
                'status',
                'changer' => [
                    'id',
                    'type',
                    'name',
                    'email'
                ],
                'created_at'
            ]
        );

        $this->postGraphQLBackOffice($query->getQuery())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        OrderStatusHistoryQuery::NAME => [
                            [
                                'status' => OrderStatusEnum::CREATED,
                                'changer' => [
                                    'id' => (string)$admin->id,
                                    'type' => $admin->getMorphType(),
                                    'name' => $admin->getName(),
                                    'email' => $admin->getEmail()
                                ]
                            ],
                            [
                                'status' => OrderStatusEnum::PAID,
                                'changer' => [
                                    'id' => (string)$admin->id,
                                    'type' => $admin->getMorphType(),
                                    'name' => $admin->getName(),
                                    'email' => $admin->getEmail()
                                ]
                            ],
                        ]
                    ]
                ]
            );
    }
}
