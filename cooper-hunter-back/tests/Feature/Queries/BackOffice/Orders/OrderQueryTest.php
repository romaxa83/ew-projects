<?php

namespace Tests\Feature\Queries\BackOffice\Orders;

use App\Enums\Orders\OrderStatusEnum;
use App\GraphQL\Queries\BackOffice\Orders\OrderQuery;
use App\Models\Orders\Order;
use App\Permissions\Orders\OrderListPermission;
use Carbon\Carbon;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Core\Testing\GraphQL\Scalar\EnumValue;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\Models\OrderCreateTrait;
use Tests\Traits\Models\ProjectCreateTrait;
use Tests\Traits\Permissions\AdminManagerHelperTrait;

class OrderQueryTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;
    use ProjectCreateTrait;
    use OrderCreateTrait;
    use AdminManagerHelperTrait;

    /**@var Order[] $orders */
    private array $orders;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginByAdminManager([OrderListPermission::KEY]);

        $this->orders = $this->createAllStatusesOrder();
    }

    public function test_get_all_order_with_different_technician(): void
    {
        $query = new GraphQLQuery(
            name: OrderQuery::NAME,
            select: [
                'data' => [
                    'id',
                    'status',
                    'technician' => [
                        'id'
                    ]
                ]
            ]
        );

        $this->postGraphQLBackOffice($query->getQuery())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        OrderQuery::NAME => [
                            'data' => [
                                [
                                    'id' => (string)$this->orders[OrderStatusEnum::CANCELED]->id,
                                    'status' => OrderStatusEnum::CANCELED,
                                    'technician' => [
                                        'id' => $this->orders[OrderStatusEnum::CANCELED]->technician_id
                                    ]
                                ],
                                [
                                    'id' => (string)$this->orders[OrderStatusEnum::SHIPPED]->id,
                                    'status' => OrderStatusEnum::SHIPPED,
                                    'technician' => [
                                        'id' => $this->orders[OrderStatusEnum::SHIPPED]->technician_id
                                    ]
                                ],
                                [
                                    'id' => (string)$this->orders[OrderStatusEnum::PAID]->id,
                                    'status' => OrderStatusEnum::PAID,
                                    'technician' => [
                                        'id' => $this->orders[OrderStatusEnum::PAID]->technician_id
                                    ]
                                ],
                                [
                                    'id' => (string)$this->orders[OrderStatusEnum::PENDING_PAID]->id,
                                    'status' => OrderStatusEnum::PENDING_PAID,
                                    'technician' => [
                                        'id' => $this->orders[OrderStatusEnum::PENDING_PAID]->technician_id
                                    ]
                                ],
                                [
                                    'id' => (string)$this->orders[OrderStatusEnum::CREATED]->id,
                                    'status' => OrderStatusEnum::CREATED,
                                    'technician' => [
                                        'id' => $this->orders[OrderStatusEnum::CREATED]->technician_id
                                    ]
                                ],
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(5, 'data.' . OrderQuery::NAME . '.data');
    }

    public function test_status_filter(): void
    {
        $query = new GraphQLQuery(
            OrderQuery::NAME,
            [
                'status' => [
                    new EnumValue(OrderStatusEnum::PAID),
                    new EnumValue(OrderStatusEnum::SHIPPED),
                ]
            ],
            [
                'data' => [
                    'id',
                    'status',
                    'technician' => [
                        'id'
                    ]
                ]
            ]
        );

        $this->postGraphQLBackOffice($query->getQuery())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        OrderQuery::NAME => [
                            'data' => [
                                [
                                    'id' => (string)$this->orders[OrderStatusEnum::SHIPPED]->id,
                                    'status' => OrderStatusEnum::SHIPPED,
                                    'technician' => [
                                        'id' => $this->orders[OrderStatusEnum::SHIPPED]->technician_id
                                    ]
                                ],
                                [
                                    'id' => (string)$this->orders[OrderStatusEnum::PAID]->id,
                                    'status' => OrderStatusEnum::PAID,
                                    'technician' => [
                                        'id' => $this->orders[OrderStatusEnum::PAID]->technician_id
                                    ]
                                ],
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(2, 'data.' . OrderQuery::NAME . '.data');
    }

    public function test_technician_id_filter(): void
    {
        $query = new GraphQLQuery(
            OrderQuery::NAME,
            [
                'technician_id' => (string)$this->orders[OrderStatusEnum::PENDING_PAID]->technician_id
            ],
            [
                'data' => [
                    'id',
                    'status',
                    'technician' => [
                        'id'
                    ]
                ]
            ]
        );

        $this->postGraphQLBackOffice($query->getQuery())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        OrderQuery::NAME => [
                            'data' => [
                                [
                                    'id' => (string)$this->orders[OrderStatusEnum::PENDING_PAID]->id,
                                    'status' => OrderStatusEnum::PENDING_PAID,
                                    'technician' => [
                                        'id' => $this->orders[OrderStatusEnum::PENDING_PAID]->technician_id
                                    ]
                                ],
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(1, 'data.' . OrderQuery::NAME . '.data');
    }

    public function test_technician_name_filter(): void
    {
        $query = new GraphQLQuery(
            OrderQuery::NAME,
            [
                'technician_name' => (string)$this->orders[OrderStatusEnum::CANCELED]->technician->last_name
            ],
            [
                'data' => [
                    'id',
                    'status',
                    'technician' => [
                        'id'
                    ]
                ]
            ]
        );

        $this->postGraphQLBackOffice($query->getQuery())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        OrderQuery::NAME => [
                            'data' => [
                                [
                                    'id' => (string)$this->orders[OrderStatusEnum::CANCELED]->id,
                                    'status' => OrderStatusEnum::CANCELED,
                                    'technician' => [
                                        'id' => $this->orders[OrderStatusEnum::CANCELED]->technician_id
                                    ]
                                ],
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(1, 'data.' . OrderQuery::NAME . '.data');
    }

    public function test_id_filter(): void
    {
        $query = new GraphQLQuery(
            OrderQuery::NAME,
            [
                'query' => (string)$this->orders[OrderStatusEnum::CREATED]->id
            ],
            [
                'data' => [
                    'id',
                    'status',
                    'technician' => [
                        'id'
                    ]
                ]
            ]
        );

        $this->postGraphQLBackOffice($query->getQuery())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        OrderQuery::NAME => [
                            'data' => [
                                [
                                    'id' => (string)$this->orders[OrderStatusEnum::CREATED]->id,
                                    'status' => OrderStatusEnum::CREATED,
                                    'technician' => [
                                        'id' => $this->orders[OrderStatusEnum::CREATED]->technician_id
                                    ]
                                ],
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(1, 'data.' . OrderQuery::NAME . '.data');
    }

    public function test_date_from_filter(): void
    {
        $this->changeCreatedAtTime($this->orders[OrderStatusEnum::CREATED]);

        $query = new GraphQLQuery(
            OrderQuery::NAME,
            [
                'date_from' => Carbon::now()
                    ->toDateString()
            ],
            [
                'data' => [
                    'id',
                    'status'
                ]
            ]
        );

        $this->postGraphQLBackOffice($query->getQuery())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        OrderQuery::NAME => [
                            'data' => [
                                [
                                    'id' => (string)$this->orders[OrderStatusEnum::CANCELED]->id,
                                    'status' => OrderStatusEnum::CANCELED
                                ],
                                [
                                    'id' => (string)$this->orders[OrderStatusEnum::SHIPPED]->id,
                                    'status' => OrderStatusEnum::SHIPPED
                                ],
                                [
                                    'id' => (string)$this->orders[OrderStatusEnum::PAID]->id,
                                    'status' => OrderStatusEnum::PAID
                                ],
                                [
                                    'id' => (string)$this->orders[OrderStatusEnum::PENDING_PAID]->id,
                                    'status' => OrderStatusEnum::PENDING_PAID
                                ],
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(4, 'data.' . OrderQuery::NAME . '.data');
    }

    private function changeCreatedAtTime(Order $order, bool $sub = true): void
    {
        $order->created_at = Carbon::now()
            ->{$sub ? 'subDays' : 'addDays'}(
                10
            )
            ->toDateTimeString();

        $order->save();
    }

    public function test_date_to_filter(): void
    {
        $this->changeCreatedAtTime($this->orders[OrderStatusEnum::CANCELED], false);

        $query = new GraphQLQuery(
            OrderQuery::NAME,
            [
                'date_to' => Carbon::now()
                    ->toDateString()
            ],
            [
                'data' => [
                    'id',
                    'status'
                ]
            ]
        );

        $this->postGraphQLBackOffice($query->getQuery())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        OrderQuery::NAME => [
                            'data' => [
                                [
                                    'id' => (string)$this->orders[OrderStatusEnum::SHIPPED]->id,
                                    'status' => OrderStatusEnum::SHIPPED
                                ],
                                [
                                    'id' => (string)$this->orders[OrderStatusEnum::PAID]->id,
                                    'status' => OrderStatusEnum::PAID
                                ],
                                [
                                    'id' => (string)$this->orders[OrderStatusEnum::PENDING_PAID]->id,
                                    'status' => OrderStatusEnum::PENDING_PAID
                                ],
                                [
                                    'id' => (string)$this->orders[OrderStatusEnum::CREATED]->id,
                                    'status' => OrderStatusEnum::CREATED
                                ],
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(4, 'data.' . OrderQuery::NAME . '.data');
    }

    public function test_date_filter(): void
    {
        $this->changeCreatedAtTime($this->orders[OrderStatusEnum::CREATED]);
        $this->changeCreatedAtTime($this->orders[OrderStatusEnum::CANCELED], false);

        $query = new GraphQLQuery(
            OrderQuery::NAME,
            [
                'date_from' => Carbon::now()
                    ->subDay()
                    ->toDateString(),
                'date_to' => Carbon::now()
                    ->addDay()
                    ->toDateString()
            ],
            [
                'data' => [
                    'id',
                    'status'
                ]
            ]
        );

        $this->postGraphQLBackOffice($query->getQuery())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        OrderQuery::NAME => [
                            'data' => [
                                [
                                    'id' => (string)$this->orders[OrderStatusEnum::SHIPPED]->id,
                                    'status' => OrderStatusEnum::SHIPPED
                                ],
                                [
                                    'id' => (string)$this->orders[OrderStatusEnum::PAID]->id,
                                    'status' => OrderStatusEnum::PAID
                                ],
                                [
                                    'id' => (string)$this->orders[OrderStatusEnum::PENDING_PAID]->id,
                                    'status' => OrderStatusEnum::PENDING_PAID
                                ],
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(3, 'data.' . OrderQuery::NAME . '.data');
    }

    public function test_serial_number_filter(): void
    {
        $query = new GraphQLQuery(
            OrderQuery::NAME,
            [
                'serial_number' => $this->orders[OrderStatusEnum::PAID]->serial_number
            ],
            [
                'data' => [
                    'id',
                    'status',
                    'serial_number'
                ]
            ]
        );

        $this->postGraphQLBackOffice($query->getQuery())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        OrderQuery::NAME => [
                            'data' => [
                                [
                                    'id' => (string)$this->orders[OrderStatusEnum::PAID]->id,
                                    'status' => OrderStatusEnum::PAID,
                                    'serial_number' => $this->orders[OrderStatusEnum::PAID]->serial_number
                                ],
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(1, 'data.' . OrderQuery::NAME . '.data');
    }

    public function test_recipient_name_filter(): void
    {
        $shipping = $this->orders[OrderStatusEnum::PAID]->shipping;

        $query = new GraphQLQuery(
            OrderQuery::NAME,
            [
                'recipient_name' => $shipping->first_name . ' ' . $shipping->last_name,
            ],
            [
                'data' => [
                    'id',
                    'status',
                    'serial_number'
                ]
            ]
        );

        $this->postGraphQLBackOffice($query->getQuery())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        OrderQuery::NAME => [
                            'data' => [
                                [
                                    'id' => (string)$this->orders[OrderStatusEnum::PAID]->id,
                                    'status' => OrderStatusEnum::PAID,
                                    'serial_number' => $this->orders[OrderStatusEnum::PAID]->serial_number
                                ],
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(1, 'data.' . OrderQuery::NAME . '.data');
    }

    public function test_combine_filter_filter(): void
    {
        $this->changeCreatedAtTime($this->orders[OrderStatusEnum::CREATED]);
        $this->changeCreatedAtTime($this->orders[OrderStatusEnum::CANCELED], false);

        $query = new GraphQLQuery(
            OrderQuery::NAME,
            [
                'date_from' => Carbon::now()
                    ->subDay()
                    ->toDateString(),
                'date_to' => Carbon::now()
                    ->addDay()
                    ->toDateString(),
                'status' => [
                    new EnumValue(OrderStatusEnum::PAID),
                    new EnumValue(OrderStatusEnum::PENDING_PAID),
                ],
                'technician_id' => $this->orders[OrderStatusEnum::PAID]->technician_id
            ],
            [
                'data' => [
                    'id',
                    'status'
                ]
            ]
        );

        $this->postGraphQLBackOffice($query->getQuery())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        OrderQuery::NAME => [
                            'data' => [
                                [
                                    'id' => (string)$this->orders[OrderStatusEnum::PAID]->id,
                                    'status' => OrderStatusEnum::PAID
                                ],
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(1, 'data.' . OrderQuery::NAME . '.data');
    }
}
