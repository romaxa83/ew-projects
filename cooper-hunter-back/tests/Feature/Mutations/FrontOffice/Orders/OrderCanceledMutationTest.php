<?php

namespace Tests\Feature\Mutations\FrontOffice\Orders;

use App\Enums\Orders\OrderCostStatusEnum;
use App\Enums\Orders\OrderStatusEnum;
use App\Enums\Payments\PayPalCheckoutStatusEnum;
use App\Enums\Payments\PayPalRefundStatusEnum;
use App\GraphQL\Mutations\FrontOffice\Orders\OrderCanceledMutation;
use App\Models\Orders\Order;
use App\Models\Orders\OrderPayment;
use App\Models\Payments\PayPalCheckout;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Cache;
use Mockery\MockInterface;
use Tests\TestCase;
use Tests\Traits\Models\OrderCreateTrait;
use Tests\Traits\Models\ProjectCreateTrait;

class OrderCanceledMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;
    use ProjectCreateTrait;
    use OrderCreateTrait;

    private GraphQLQuery $query;

    public function setUp(): void
    {
        parent::setUp();

        $this->query = GraphQLQuery::mutation(OrderCanceledMutation::NAME)
            ->select(
                [
                    'id',
                    'status'
                ]
            );
    }

    public function test_set_canceled_status(): void
    {
        $member = $this->loginAsTechnicianWithRole();

        $order = $this->setOrderTechnician($member)
            ->createCreatedOrder();

        $this->postGraphQL(
            $this->query->args(['id' => $order->id])
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        OrderCanceledMutation::NAME => [
                            'id' => $order->id,
                            'status' => OrderStatusEnum::CANCELED
                        ]
                    ]
                ]
            );

        $this->assertDatabaseHas(
            Order::class,
            [
                'id' => $order->id,
                'status' => OrderStatusEnum::CANCELED
            ]
        );
    }

    public function test_try_to_set_canceled_status_on_not_canceling_order(): void
    {
        $member = $this->loginAsTechnicianWithRole();

        $order = $this->setOrderTechnician($member)
            ->createShippedOrder();

        $this->postGraphQL(
            $this->query->args(['id' => $order->id])
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'errors' => [
                        [
                            'message' => 'validation'
                        ]
                    ]
                ]
            );

        $this->assertDatabaseMissing(
            Order::class,
            [
                'id' => $order->id,
                'status' => OrderStatusEnum::CANCELED
            ]
        );
    }

    public function test_set_canceled_status_with_refund(): void
    {
        $member = $this->loginAsTechnicianWithRole();

        $order = $this->setOrderTechnician($member)
            ->createPaidOrder();

        PayPalCheckout::factory()
            ->create(
                [
                    'order_id' => $order->id,
                    'amount' => $order->payment->order_price_with_discount,
                    'checkout_status' => PayPalCheckoutStatusEnum::COMPLETED
                ]
            );

        Cache::shouldReceive('get')
            ->once()
            ->andReturn($this->faker->lexify);

        $refundId = $this->faker->lexify;

        $this->mock(
            Client::class,
            fn(MockInterface $mock) => $mock->shouldReceive('request')
                ->once()
                ->andReturn(
                    new Response(
                        body: json_encode(
                            [
                                'id' => $refundId,
                                'status' => PayPalRefundStatusEnum::COMPLETED
                            ]
                        )
                    )
                )
        )
            ->makePartial();

        $this->postGraphQL(
            $this->query->args(['id' => $order->id])
                ->select(
                    [
                        'id',
                        'status',
                        'payment' => [
                            'cost_status'
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        OrderCanceledMutation::NAME => [
                            'id' => $order->id,
                            'status' => OrderStatusEnum::CANCELED,
                            'payment' => [
                                'cost_status' => OrderCostStatusEnum::REFUND_COMPLETE
                            ]
                        ]
                    ]
                ]
            );

        $this->assertDatabaseHas(
            Order::class,
            [
                'id' => $order->id,
                'status' => OrderStatusEnum::CANCELED
            ]
        );

        $this->assertDatabaseMissing(
            OrderPayment::class,
            [
                'order_id' => $order->id,
                'refund_at' => null
            ]
        );

        $this->assertDatabaseHas(
            PayPalCheckout::class,
            [
                'order_id' => $order->id,
                'refund_id' => $refundId,
                'refund_status' => PayPalRefundStatusEnum::COMPLETED
            ]
        );
    }
}
