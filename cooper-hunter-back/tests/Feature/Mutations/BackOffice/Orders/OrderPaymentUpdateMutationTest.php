<?php


namespace Feature\Mutations\BackOffice\Orders;


use App\Enums\Orders\OrderStatusEnum;
use App\GraphQL\Mutations\BackOffice\Orders\OrderPaymentUpdateMutation;
use App\Models\Orders\OrderStatusHistory;
use App\Permissions\Orders\OrderUpdatePermission;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\Models\OrderCreateTrait;
use Tests\Traits\Models\ProjectCreateTrait;
use Tests\Traits\Permissions\AdminManagerHelperTrait;

class OrderPaymentUpdateMutationTest extends TestCase
{

    use DatabaseTransactions;
    use WithFaker;
    use ProjectCreateTrait;
    use OrderCreateTrait;
    use AdminManagerHelperTrait;

    public function test_update_payment_data(): void
    {
        $this->loginByAdminManager([OrderUpdatePermission::KEY]);

        $order = $this->createPendingPaidOrder();

        $price = round($this->faker->randomFloat(2, 100, 10000));

        $payment = [
            'order_price' => $price,
            'order_price_with_discount' => $price,
            'shipping_cost' => 0.0,
            'tax' => 0.0,
            'discount' => 0.0
        ];

        $query = new GraphQLQuery(
            OrderPaymentUpdateMutation::NAME,
            [
                'id' => $order->id,
                'payment' => $payment
            ],
            [
                'id',
                'status',
                'payment' => [
                    'order_price',
                    'order_price_with_discount',
                    'shipping_cost',
                    'tax',
                    'discount',
                    'paid_at'
                ]
            ]
        );

        $this->postGraphQLBackOffice($query->getMutation())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        OrderPaymentUpdateMutation::NAME => [
                            'id' => (string)$order->id,
                            'status' => OrderStatusEnum::PENDING_PAID,
                            'payment' => [
                                'order_price' => $price,
                                'order_price_with_discount' => $price,
                                'shipping_cost' => 0.0,
                                'tax' => 0.0,
                                'discount' => 0.0,
                                'paid_at' => null
                            ]
                        ]
                    ]
                ]
            );
    }

    public function test_update_payment_data_with_paid_at(): void
    {
        $this->loginByAdminManager([OrderUpdatePermission::KEY]);

        $order = $this->createPendingPaidOrder();

        $price = round($this->faker->randomFloat(2, 100, 10000));

        $payment = [
            'order_price' => $price,
            'order_price_with_discount' => $price,
            'shipping_cost' => 0.0,
            'tax' => 0.0,
            'discount' => 0.0,
            'paid_at' => time()
        ];

        $query = new GraphQLQuery(
            OrderPaymentUpdateMutation::NAME,
            [
                'id' => $order->id,
                'payment' => $payment
            ],
            [
                'id',
                'status',
                'payment' => [
                    'order_price',
                    'order_price_with_discount',
                    'shipping_cost',
                    'tax',
                    'discount',
                    'paid_at'
                ]
            ]
        );

        $this->postGraphQLBackOffice($query->getMutation())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        OrderPaymentUpdateMutation::NAME => [
                            'id' => (string)$order->id,
                            'status' => OrderStatusEnum::PAID,
                            'payment' => $payment
                        ]
                    ]
                ]
            );

        $this->assertDatabaseHas(
            OrderStatusHistory::class,
            [
                'order_id' => $order->id,
                'status' => OrderStatusEnum::PAID
            ]
        );
    }

    public function test_update_payment_data_with_zero_price(): void
    {
        $this->loginByAdminManager([OrderUpdatePermission::KEY]);

        $order = $this->createPendingPaidOrder();

        $price = round($this->faker->randomFloat(2, 100, 10000));

        $payment = [
            'order_price' => $price,
            'order_price_with_discount' => 0.0,
            'shipping_cost' => 0.0,
            'tax' => 0.0,
            'discount' => $price
        ];

        $query = new GraphQLQuery(
            OrderPaymentUpdateMutation::NAME,
            [
                'id' => $order->id,
                'payment' => $payment
            ],
            [
                'id',
                'status',
                'payment' => [
                    'paid_at'
                ]
            ]
        );

        $paidAt = $this->postGraphQLBackOffice($query->getMutation())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        OrderPaymentUpdateMutation::NAME => [
                            'id' => (string)$order->id,
                            'status' => OrderStatusEnum::PAID
                        ]
                    ]
                ]
            )
            ->json('data.' . OrderPaymentUpdateMutation::NAME . '.payment.paid_at');

        $this->assertNotNull($paidAt);

        $this->assertDatabaseHas(
            OrderStatusHistory::class,
            [
                'order_id' => $order->id,
                'status' => OrderStatusEnum::PAID
            ]
        );
    }

    public function test_update_payment_without_data(): void
    {
        $this->loginByAdminManager([OrderUpdatePermission::KEY]);

        $order = $this->createPendingPaidOrder();

        $query = new GraphQLQuery(
            OrderPaymentUpdateMutation::NAME,
            [
                'id' => $order->id
            ],
            [
                'id',
                'status',
                'payment' => [
                    'order_price',
                    'order_price_with_discount',
                    'shipping_cost',
                    'tax',
                    'discount',
                    'paid_at'
                ]
            ]
        );

        $this->postGraphQLBackOffice($query->getMutation())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        OrderPaymentUpdateMutation::NAME => [
                            'id' => (string)$order->id,
                            'status' => OrderStatusEnum::CREATED,
                            'payment' => [
                                'order_price' => null,
                                'order_price_with_discount' => null,
                                'shipping_cost' => null,
                                'tax' => null,
                                'discount' => null,
                                'paid_at' => null,
                            ]
                        ]
                    ]
                ]
            );

        $this->assertDatabaseHas(
            OrderStatusHistory::class,
            [
                'order_id' => $order->id,
                'status' => OrderStatusEnum::CREATED
            ]
        );
    }
}
