<?php

namespace Tests\Feature\Queries\FrontOffice\Orders;

use App\Enums\Orders\OrderCostStatusEnum;
use App\Enums\Orders\OrderFilterTabEnum;
use App\Enums\Orders\OrderFilterTrkNumberExistsEnum;
use App\Enums\Orders\OrderStatusEnum;
use App\GraphQL\Queries\FrontOffice\Orders\OrderQuery;
use App\Models\Orders\Order;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Core\Testing\GraphQL\Scalar\EnumValue;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\Models\OrderCreateTrait;
use Tests\Traits\Models\ProjectCreateTrait;

class OrderQueryTest extends TestCase
{

    use DatabaseTransactions;
    use WithFaker;
    use ProjectCreateTrait;
    use OrderCreateTrait;

    public function test_get_active_order_tab(): void
    {
        $user = $this->loginAsTechnicianWithRole();

        $orders = $this->setOrderTechnician($user)
            ->createAllStatusesOrder();

        $query = new GraphQLQuery(
            name: OrderQuery::NAME,
            args: [
            'tab' => new EnumValue(OrderFilterTabEnum::ACTIVE),
        ],
            select: [
                'data' => [
                    'id',
                    'status',
                    'product' => [
                        'title',
                    ],
                    'shipping' => [
                        'trk_number' => [
                            'number',
                            'tracking_url'
                        ]
                    ],
                    'payment' => [
                        'cost_status',
                        'paid_at'
                    ]
                ]
            ]
        );

        $this->postGraphQL($query->getQuery())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        OrderQuery::NAME => [
                            'data' => [
                                [
                                    'id' => (string)$orders[OrderStatusEnum::PAID]->id,
                                    'status' => OrderStatusEnum::PAID,
                                    'product' => [
                                        'title' => $orders[OrderStatusEnum::PAID]->product->title
                                    ],
                                    'shipping' => [
                                        'trk_number' => null
                                    ],
                                    'payment' => [
                                        'cost_status' => OrderCostStatusEnum::PAID,
                                        'paid_at' => $orders[OrderStatusEnum::PAID]->payment->paid_at
                                    ]
                                ],
                                [
                                    'id' => (string)$orders[OrderStatusEnum::PENDING_PAID]->id,
                                    'status' => OrderStatusEnum::PENDING_PAID,
                                    'product' => [
                                        'title' => $orders[OrderStatusEnum::PENDING_PAID]->product->title
                                    ],
                                    'shipping' => [
                                        'trk_number' => null
                                    ],
                                    'payment' => [
                                        'cost_status' => OrderCostStatusEnum::WAITING_TO_PAY,
                                        'paid_at' => null
                                    ]
                                ],
                                [
                                    'id' => (string)$orders[OrderStatusEnum::CREATED]->id,
                                    'status' => OrderStatusEnum::CREATED,
                                    'product' => [
                                        'title' => $orders[OrderStatusEnum::CREATED]->product->title
                                    ],
                                    'shipping' => [
                                        'trk_number' => null
                                    ],
                                    'payment' => [
                                        'cost_status' => OrderCostStatusEnum::NOT_FORMED,
                                        'paid_at' => null
                                    ]
                                ],
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(3, 'data.' . OrderQuery::NAME . '.data');
    }

    public function test_get_history_order_tab(): void
    {
        $user = $this->loginAsTechnicianWithRole();

        $orders = $this->setOrderTechnician($user)
            ->createAllStatusesOrder();

        $query = new GraphQLQuery(
            name: OrderQuery::NAME,
            args: [
            'tab' => new EnumValue(OrderFilterTabEnum::HISTORY)
        ],
            select: [
                'data' => [
                    'id',
                    'status',
                    'shipping' => [
                        'trk_number' => [
                            'number',
                            'tracking_url'
                        ]
                    ],
                    'payment' => [
                        'cost_status',
                        'paid_at'
                    ]
                ]
            ]
        );

        $this->postGraphQL($query->getQuery())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        OrderQuery::NAME => [
                            'data' => [
                                [
                                    'id' => (string)$orders[OrderStatusEnum::CANCELED]->id,
                                    'status' => OrderStatusEnum::CANCELED,
                                    'shipping' => [
                                        'trk_number' => null
                                    ],
                                    'payment' => [
                                        'cost_status' => OrderCostStatusEnum::WAITING_TO_PAY,
                                        'paid_at' => null
                                    ]
                                ],
                                [
                                    'id' => (string)$orders[OrderStatusEnum::SHIPPED]->id,
                                    'status' => OrderStatusEnum::SHIPPED,
                                    'shipping' => [
                                        'trk_number' => [
                                            'number' => $orders[OrderStatusEnum::SHIPPED]->shipping->trk_number,
                                            'tracking_url' => config(
                                                    'orders.tracking_url'
                                                ) . $orders[OrderStatusEnum::SHIPPED]->shipping->trk_number
                                        ]
                                    ],
                                    'payment' => [
                                        'cost_status' => OrderCostStatusEnum::PAID,
                                        'paid_at' => $orders[OrderStatusEnum::PAID]->payment->paid_at
                                    ]
                                ],
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(2, 'data.' . OrderQuery::NAME . '.data');
    }

    public function test_status_filter(): void
    {
        $user = $this->loginAsTechnicianWithRole();

        $orders = $this->setOrderTechnician($user)
            ->manyOrder(3)
            ->createAllStatusesOrder();

        $query = new GraphQLQuery(
            name: OrderQuery::NAME,
            args: [
            'status' => [
                new EnumValue(OrderStatusEnum::PENDING_PAID)
            ]
        ],
            select: [
                'data' => [
                    'id',
                    'status',
                    'shipping' => [
                        'trk_number' => [
                            'number',
                            'tracking_url'
                        ]
                    ],
                    'payment' => [
                        'cost_status',
                        'paid_at'
                    ]
                ]
            ]
        );

        $this->postGraphQL($query->getQuery())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        OrderQuery::NAME => [
                            'data' => [
                                [
                                    'id' => (string)$orders[OrderStatusEnum::PENDING_PAID][2]->id,
                                    'status' => OrderStatusEnum::PENDING_PAID,
                                    'shipping' => [
                                        'trk_number' => null
                                    ],
                                    'payment' => [
                                        'cost_status' => OrderCostStatusEnum::WAITING_TO_PAY,
                                        'paid_at' => null
                                    ]
                                ],
                                [
                                    'id' => (string)$orders[OrderStatusEnum::PENDING_PAID][1]->id,
                                    'status' => OrderStatusEnum::PENDING_PAID,
                                    'shipping' => [
                                        'trk_number' => null
                                    ],
                                    'payment' => [
                                        'cost_status' => OrderCostStatusEnum::WAITING_TO_PAY,
                                        'paid_at' => null
                                    ]
                                ],
                                [
                                    'id' => (string)$orders[OrderStatusEnum::PENDING_PAID][0]->id,
                                    'status' => OrderStatusEnum::PENDING_PAID,
                                    'shipping' => [
                                        'trk_number' => null
                                    ],
                                    'payment' => [
                                        'cost_status' => OrderCostStatusEnum::WAITING_TO_PAY,
                                        'paid_at' => null
                                    ]
                                ],
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(3, 'data.' . OrderQuery::NAME . '.data');
    }

    public function test_cost_status_filter(): void
    {
        $user = $this->loginAsTechnicianWithRole();

        $orders = $this->setOrderTechnician($user)
            ->manyOrder(3)
            ->createAllStatusesOrder();

        $query = new GraphQLQuery(
            name: OrderQuery::NAME,
            args: [
            'tab' => new EnumValue(OrderFilterTabEnum::ACTIVE),
            'cost_status' => [
                new EnumValue(OrderCostStatusEnum::WAITING_TO_PAY),
                new EnumValue(OrderCostStatusEnum::PAID),
            ],
            'limit' => 10
        ],
            select: [
                'data' => [
                    'id',
                    'status',
                    'payment' => [
                        'cost_status',
                        'paid_at'
                    ]
                ]
            ]
        );

        $this->postGraphQL($query->getQuery())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        OrderQuery::NAME => [
                            'data' => [
                                [
                                    'id' => (string)$orders[OrderStatusEnum::PAID][2]->id,
                                    'status' => OrderStatusEnum::PAID,
                                    'payment' => [
                                        'cost_status' => OrderCostStatusEnum::PAID,
                                        'paid_at' => $orders[OrderStatusEnum::PAID][2]->payment->paid_at
                                    ]
                                ],
                                [
                                    'id' => (string)$orders[OrderStatusEnum::PAID][1]->id,
                                    'status' => OrderStatusEnum::PAID,
                                    'payment' => [
                                        'cost_status' => OrderCostStatusEnum::PAID,
                                        'paid_at' => $orders[OrderStatusEnum::PAID][1]->payment->paid_at
                                    ]
                                ],
                                [
                                    'id' => (string)$orders[OrderStatusEnum::PAID][0]->id,
                                    'status' => OrderStatusEnum::PAID,
                                    'payment' => [
                                        'cost_status' => OrderCostStatusEnum::PAID,
                                        'paid_at' => $orders[OrderStatusEnum::PAID][0]->payment->paid_at
                                    ]
                                ],
                                [
                                    'id' => (string)$orders[OrderStatusEnum::PENDING_PAID][2]->id,
                                    'status' => OrderStatusEnum::PENDING_PAID,
                                    'payment' => [
                                        'cost_status' => OrderCostStatusEnum::WAITING_TO_PAY,
                                        'paid_at' => null
                                    ]
                                ],
                                [
                                    'id' => (string)$orders[OrderStatusEnum::PENDING_PAID][1]->id,
                                    'status' => OrderStatusEnum::PENDING_PAID,
                                    'payment' => [
                                        'cost_status' => OrderCostStatusEnum::WAITING_TO_PAY,
                                        'paid_at' => null
                                    ]
                                ],
                                [
                                    'id' => (string)$orders[OrderStatusEnum::PENDING_PAID][0]->id,
                                    'status' => OrderStatusEnum::PENDING_PAID,
                                    'payment' => [
                                        'cost_status' => OrderCostStatusEnum::WAITING_TO_PAY,
                                        'paid_at' => null
                                    ]
                                ],
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(6, 'data.' . OrderQuery::NAME . '.data');
    }

    public function test_trk_number_filter(): void
    {
        $user = $this->loginAsTechnicianWithRole();

        $orders = $this->setOrderTechnician($user)
            ->manyOrder(3)
            ->createAllStatusesOrder();

        $query = new GraphQLQuery(
            name: OrderQuery::NAME,
            args: [
            'trk_number_exists' => new EnumValue(OrderFilterTrkNumberExistsEnum::WITH_NUMBER),
            'tab' => new EnumValue(OrderFilterTabEnum::HISTORY)
        ],
            select: [
                'data' => [
                    'id',
                    'status',
                    'payment' => [
                        'cost_status',
                        'paid_at'
                    ]
                ]
            ]
        );

        $this->postGraphQL($query->getQuery())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        OrderQuery::NAME => [
                            'data' => [
                                [
                                    'id' => (string)$orders[OrderStatusEnum::SHIPPED][2]->id,
                                    'status' => OrderStatusEnum::SHIPPED,
                                    'payment' => [
                                        'cost_status' => OrderCostStatusEnum::PAID,
                                        'paid_at' => $orders[OrderStatusEnum::SHIPPED][2]->payment->paid_at
                                    ]
                                ],
                                [
                                    'id' => (string)$orders[OrderStatusEnum::SHIPPED][1]->id,
                                    'status' => OrderStatusEnum::SHIPPED,
                                    'payment' => [
                                        'cost_status' => OrderCostStatusEnum::PAID,
                                        'paid_at' => $orders[OrderStatusEnum::SHIPPED][1]->payment->paid_at
                                    ]
                                ],
                                [
                                    'id' => (string)$orders[OrderStatusEnum::SHIPPED][0]->id,
                                    'status' => OrderStatusEnum::SHIPPED,
                                    'payment' => [
                                        'cost_status' => OrderCostStatusEnum::PAID,
                                        'paid_at' => $orders[OrderStatusEnum::SHIPPED][0]->payment->paid_at
                                    ]
                                ],
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(3, 'data.' . OrderQuery::NAME . '.data');
    }

    public function test_model_name_filter(): void
    {
        $user = $this->loginAsTechnicianWithRole();

        $orders = $this->setOrderTechnician($user)
            ->manyOrder(3)
            ->createAllStatusesOrder();

        $query = new GraphQLQuery(
            name: OrderQuery::NAME,
            args: [
            'tab' => new EnumValue(OrderFilterTabEnum::HISTORY),
            'query' => $orders[OrderStatusEnum::SHIPPED][0]->product->title
        ],
            select: [
                'data' => [
                    'id',
                    'status',
                    'payment' => [
                        'cost_status',
                        'paid_at'
                    ]
                ]
            ]
        );

        $this->postGraphQL($query->getQuery())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        OrderQuery::NAME => [
                            'data' => [
                                [
                                    'id' => (string)$orders[OrderStatusEnum::SHIPPED][2]->id,
                                    'status' => OrderStatusEnum::SHIPPED,
                                    'payment' => [
                                        'cost_status' => OrderCostStatusEnum::PAID,
                                        'paid_at' => $orders[OrderStatusEnum::SHIPPED][2]->payment->paid_at
                                    ]
                                ],
                                [
                                    'id' => (string)$orders[OrderStatusEnum::SHIPPED][1]->id,
                                    'status' => OrderStatusEnum::SHIPPED,
                                    'payment' => [
                                        'cost_status' => OrderCostStatusEnum::PAID,
                                        'paid_at' => $orders[OrderStatusEnum::SHIPPED][1]->payment->paid_at
                                    ]
                                ],
                                [
                                    'id' => (string)$orders[OrderStatusEnum::SHIPPED][0]->id,
                                    'status' => OrderStatusEnum::SHIPPED,
                                    'payment' => [
                                        'cost_status' => OrderCostStatusEnum::PAID,
                                        'paid_at' => $orders[OrderStatusEnum::SHIPPED][0]->payment->paid_at
                                    ]
                                ],
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(3, 'data.' . OrderQuery::NAME . '.data');
    }

    public function test_project_id_filter(): void
    {
        $user = $this->loginAsTechnicianWithRole();

        $orders = $this->setOrderTechnician($user)
            ->manyOrder(2)
            ->withProject()
            ->createPaidOrder();

        $this->manyOrder(3)
            ->createCreatedOrder();

        $this->assertDatabaseCount(
            Order::class,
            5
        );

        $query = new GraphQLQuery(
            OrderQuery::NAME,
            [
                'project_id' => $orders[0]->project_id
            ],
            [
                'data' => [
                    'id',
                    'status',
                    'payment' => [
                        'cost_status',
                        'paid_at'
                    ]
                ]
            ]
        );

        $this->postGraphQL($query->getQuery())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        OrderQuery::NAME => [
                            'data' => [
                                [
                                    'id' => (string)$orders[1]->id,
                                    'status' => OrderStatusEnum::PAID,
                                    'payment' => [
                                        'cost_status' => OrderCostStatusEnum::PAID,
                                        'paid_at' => $orders[1]->payment->paid_at
                                    ]
                                ],
                                [
                                    'id' => (string)$orders[0]->id,
                                    'status' => OrderStatusEnum::PAID,
                                    'payment' => [
                                        'cost_status' => OrderCostStatusEnum::PAID,
                                        'paid_at' => $orders[0]->payment->paid_at
                                    ]
                                ],
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(2, 'data.' . OrderQuery::NAME . '.data');
    }
}
