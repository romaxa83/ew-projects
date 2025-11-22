<?php


namespace Feature\Queries\FrontOffice\Orders;


use App\Enums\Orders\OrderCostStatusEnum;
use App\Enums\Orders\OrderStatusEnum;
use App\Enums\Payments\PayPalCheckoutStatusEnum;
use App\Enums\Payments\PayPalRefundStatusEnum;
use App\Events\Payments\PayPalCheckoutSavedEvent;
use App\GraphQL\Queries\FrontOffice\Orders\OrderPaymentQuery;
use App\Models\Orders\Order;
use App\Models\Payments\PayPalCheckout;
use App\Services\Payment\PayPalService;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Mockery\MockInterface;
use Tests\TestCase;
use Tests\Traits\Models\OrderCreateTrait;
use Tests\Traits\Models\ProjectCreateTrait;

class OrderPaymentQueryTest extends TestCase
{

    use DatabaseTransactions;
    use WithFaker;
    use ProjectCreateTrait;
    use OrderCreateTrait;

    private GraphQLQuery $query;

    private Order $order;

    public function setUp(): void
    {
        parent::setUp();

        $user = $this->loginAsTechnicianWithRole();

        $this->order = $this->setOrderTechnician($user)
            ->createPendingPaidOrder();

        $this->query = GraphQLQuery::query(OrderPaymentQuery::NAME)
            ->args(
                [
                    'id' => $this->order->id
                ]
            )
            ->select(
                [
                    'cost_status',
                    'order_price',
                    'paid_at'
                ]
            );
    }

    public function test_get_payment_without_token(): void
    {
        $this->postGraphQL($this->query->make())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        OrderPaymentQuery::NAME => [
                            'cost_status' => OrderCostStatusEnum::WAITING_TO_PAY,
                            'order_price' => $this->order->payment->order_price,
                            'paid_at' => null
                        ]
                    ]
                ]
            );
    }

    public function test_get_payment_with_token(): void
    {
        $checkout = PayPalCheckout::factory()
            ->create(
                [
                    'order_id' => $this->order->id,
                    'amount' => $this->order->payment->order_price_with_discount
                ]
            );

        Event::fake();

        Cache::shouldReceive('get')
            ->with(PayPalService::TOKEN_CACHE_KEY)
            ->once()
            ->andReturn($this->faker->lexify);

        $this->mock(
            Client::class,
            fn(MockInterface $mock) => $mock->shouldReceive('request')
                ->once()
                ->andReturns(
                    new Response(
                        body: json_encode(
                            [
                                'id' => $checkout->id,
                                'status' => PayPalCheckoutStatusEnum::APPROVED
                            ]
                        )
                    )
                )
        )
            ->makePartial();

        $this->query->args(
            [
                'id' => $this->order->id,
                'token_id' => $checkout->id
            ]
        );

        $this->postGraphQL($this->query->make())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        OrderPaymentQuery::NAME => [
                            'cost_status' => OrderCostStatusEnum::PAYMENT_IN_PROCESS,
                            'order_price' => $this->order->payment->order_price,
                            'paid_at' => null
                        ]
                    ]
                ]
            );

        Event::assertDispatched(PayPalCheckoutSavedEvent::class);

        $this->assertDatabaseHas(
            PayPalCheckout::class,
            [
                'id' => $checkout->id,
                'checkout_status' => PayPalCheckoutStatusEnum::APPROVED
            ]
        );
    }

    public function test_get_payment_waiting_to_pay(): void
    {
        $this->postGraphQL($this->query->make())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        OrderPaymentQuery::NAME => [
                            'cost_status' => OrderCostStatusEnum::WAITING_TO_PAY,
                            'order_price' => $this->order->payment->order_price,
                            'paid_at' => null
                        ]
                    ]
                ]
            );
    }

    public function test_get_payment_processing_paid(): void
    {
        PayPalCheckout::factory()
            ->create(
                [
                    'order_id' => $this->order->id,
                    'amount' => $this->order->payment->order_price_with_discount,
                    'checkout_status' => PayPalCheckoutStatusEnum::APPROVED
                ]
            );

        $this->postGraphQL($this->query->make())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        OrderPaymentQuery::NAME => [
                            'cost_status' => OrderCostStatusEnum::PAYMENT_IN_PROCESS,
                            'order_price' => $this->order->payment->order_price,
                            'paid_at' => null
                        ]
                    ]
                ]
            );
    }

    public function test_get_payment_paid(): void
    {
        $this->order->status = OrderStatusEnum::PAID;
        $this->order->save();

        $this->order->payment->paid_at = time();
        $this->order->payment->save();


        $this->postGraphQL($this->query->make())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        OrderPaymentQuery::NAME => [
                            'cost_status' => OrderCostStatusEnum::PAID,
                            'order_price' => $this->order->payment->order_price,
                            'paid_at' => $this->order->payment->paid_at
                        ]
                    ]
                ]
            );
    }

    public function test_get_payment_processing_refund(): void
    {
        $this->order->status = OrderStatusEnum::CANCELED;
        $this->order->save();

        $this->order->payment->paid_at = time();
        $this->order->payment->save();

        PayPalCheckout::factory()
            ->create(
                [
                    'order_id' => $this->order->id,
                    'amount' => $this->order->payment->order_price_with_discount,
                    'checkout_status' => PayPalCheckoutStatusEnum::COMPLETED,
                    'refund_status' => PayPalRefundStatusEnum::PENDING
                ]
            );

        $this->postGraphQL($this->query->make())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        OrderPaymentQuery::NAME => [
                            'cost_status' => OrderCostStatusEnum::REFUND_IN_PROCESS,
                            'order_price' => $this->order->payment->order_price,
                            'paid_at' => $this->order->payment->paid_at
                        ]
                    ]
                ]
            );
    }

    public function test_get_payment_processing_complete(): void
    {
        $this->order->status = OrderStatusEnum::CANCELED;
        $this->order->save();

        $this->order->payment->paid_at = time();
        $this->order->payment->refund_at = time();
        $this->order->payment->save();

        PayPalCheckout::factory()
            ->create(
                [
                    'order_id' => $this->order->id,
                    'amount' => $this->order->payment->order_price_with_discount,
                    'checkout_status' => PayPalCheckoutStatusEnum::COMPLETED,
                    'refund_status' => PayPalRefundStatusEnum::COMPLETED
                ]
            );

        $this->postGraphQL($this->query->make())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        OrderPaymentQuery::NAME => [
                            'cost_status' => OrderCostStatusEnum::REFUND_COMPLETE,
                            'order_price' => $this->order->payment->order_price,
                            'paid_at' => $this->order->payment->paid_at
                        ]
                    ]
                ]
            );
    }
}
