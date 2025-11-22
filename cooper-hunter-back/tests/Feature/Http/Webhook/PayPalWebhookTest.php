<?php


namespace Feature\Http\Webhook;


use App\Enums\Payments\PayPalCheckoutStatusEnum;
use App\Enums\Payments\PayPalRefundStatusEnum;
use App\Events\Payments\PayPalCheckoutSavedEvent;
use App\Models\Orders\OrderPayment;
use App\Models\Payments\PayPalCheckout;
use App\Services\Payment\PayPalService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;
use Tests\Traits\Models\OrderCreateTrait;

class PayPalWebhookTest extends TestCase
{

    use WithoutMiddleware;
    use DatabaseTransactions;
    use OrderCreateTrait;

    public function test_get_webhook_approved()
    {
        $order = $this->createPendingPaidOrder();

        $checkout = PayPalCheckout::factory()
            ->create(
                [
                    'order_id' => $order->id,
                    'amount' => $order->payment->order_price_with_discount
                ]
            );

        Event::fake();

        $this->postJson(
            route('webhook.paypal'),
            [
                'id' => $this->faker->lexify,
                'event_type' => PayPalService::W_EVENT_ORDER_APPROVED,
                'resource' => [
                    'id' => $checkout->id,
                    'status' => PayPalCheckoutStatusEnum::APPROVED
                ]
            ]
        )
            ->assertOk();

        Event::assertDispatched(PayPalCheckoutSavedEvent::class);

        $this->assertDatabaseHas(
            PayPalCheckout::class,
            [
                'id' => $checkout->id,
                'checkout_status' => PayPalCheckoutStatusEnum::APPROVED
            ]
        );
    }

    public function test_get_webhook_complete()
    {
        $order = $this->createPendingPaidOrder();

        $checkout = PayPalCheckout::factory()
            ->create(
                [
                    'order_id' => $order->id,
                    'amount' => $order->payment->order_price_with_discount,
                ]
            );

        Event::fake();

        $this->postJson(
            route('webhook.paypal'),
            [
                'id' => $this->faker->lexify,
                'event_type' => PayPalService::W_EVENT_CAPTURE_COMPLETED,
                'resource' => [
                    'supplementary_data' => [
                        'related_ids' => [
                            'order_id' => $checkout->id
                        ]
                    ],
                    'status' => PayPalCheckoutStatusEnum::APPROVED,
                ]
            ]
        )
            ->assertOk();

        Event::assertDispatched(PayPalCheckoutSavedEvent::class);

        $this->assertDatabaseHas(
            PayPalCheckout::class,
            [
                'id' => $checkout->id,
                'checkout_status' => PayPalCheckoutStatusEnum::COMPLETED
            ]
        );

        $order->refresh();

        $this->assertNotNull($order->payment->paid_at);
    }

    public function test_get_webhook_complete_on_already_paid_order()
    {
        $order = $this->createPaidOrder();

        $paidAt = $order->payment->paid_at = Carbon::now()
            ->subDays(1)
            ->getTimestamp();
        $order->payment->save();

        $checkout = PayPalCheckout::factory()
            ->create(
                [
                    'order_id' => $order->id,
                    'amount' => $order->payment->order_price_with_discount,
                ]
            );

        Event::fake();

        $this->postJson(
            route('webhook.paypal'),
            [
                'id' => $this->faker->lexify,
                'event_type' => PayPalService::W_EVENT_CAPTURE_COMPLETED,
                'resource' => [
                    'supplementary_data' => [
                        'related_ids' => [
                            'order_id' => $checkout->id
                        ]
                    ],
                    'status' => PayPalCheckoutStatusEnum::APPROVED,
                ]
            ]
        )
            ->assertOk();

        Event::assertDispatched(PayPalCheckoutSavedEvent::class);

        $this->assertDatabaseHas(
            PayPalCheckout::class,
            [
                'id' => $checkout->id,
                'checkout_status' => PayPalCheckoutStatusEnum::COMPLETED
            ]
        );

        $order->refresh();

        $this->assertEquals($order->payment->paid_at, $paidAt);
    }

    public function test_get_webhook_refund_complete()
    {
        $order = $this->createCanceledOrder();

        $checkout = PayPalCheckout::factory()
            ->create(
                [
                    'order_id' => $order->id,
                    'amount' => $order->payment->order_price_with_discount,
                    'refund_id' => $this->faker->lexify,
                    'refund_status' => PayPalRefundStatusEnum::PENDING
                ]
            );

        $this->postJson(
            route('webhook.paypal'),
            [
                'id' => $this->faker->lexify,
                'event_type' => PayPalService::W_EVENT_CAPTURE_REFUND,
                'resource' => [
                    'id' => $checkout->refund_id,
                    'status' => PayPalCheckoutStatusEnum::COMPLETED
                ]
            ]
        )
            ->assertOk();

        $this->assertDatabaseHas(
            PayPalCheckout::class,
            [
                'id' => $checkout->id,
                'refund_status' => PayPalCheckoutStatusEnum::COMPLETED
            ]
        );

        $this->assertDatabaseMissing(
            OrderPayment::class,
            [
                'order_id' => $order->id,
                'refund_at' => null
            ]
        );
    }
}
