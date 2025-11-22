<?php

namespace Api\Orders;

use App\Models\Orders\Order;
use App\Models\Orders\Payment;
use App\Models\Orders\PaymentStage;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Helpers\Traits\OrderFactoryHelper;
use Tests\TestCase;

class OrderPaymentStageTest extends TestCase
{
    use DatabaseTransactions;
    use OrderFactoryHelper;

    public function test_add_payment_stage(): void
    {
        $this->loginAsCarrierSuperAdmin();

        $order = $this->orderFactory();
        $this->createOrderPayment($order->id, 2000);

        $this->postJson(
            route('order.add-payment-stage', $order),
            [
                'amount' => 1500,
                'payment_date' => now()->format('m/d/Y'),
                'payer' => Payment::PAYER_CUSTOMER,
                'method_id' => Payment::METHOD_ACH,
            ]
        )
            ->assertCreated();

        $this->postJson(
            route('order.add-payment-stage', $order),
            [
                'amount' => 500,
                'payment_date' => now()->format('m/d/Y'),
                'payer' => Payment::PAYER_CUSTOMER,
                'method_id' => Payment::METHOD_ACH,
            ]
        )
            ->assertCreated();

    }

    public function test_delete_payment_stage(): void
    {
        $this->loginAsCarrierSuperAdmin();

        $order = $this->orderFactory();

        $this->setPaidAt($order);

        PaymentStage::factory()->createMany(
            [
                [
                    'order_id' => $order->id,
                ],
                [
                    'order_id' => $order->id,
                ],
            ]
        );

        $order->refresh();

        $this->assertCount(3, $order->paymentStages);

        $this->deleteJson(
            route(
                'order.delete-payment-stage',
                [
                    $order,
                    $order->paymentStages->first()->id
                ]
            )
        )
            ->assertNoContent();

        $order->refresh();

        $this->assertCount(2, $order->paymentStages);
    }

    public function test_payment_stage_structure(): void
    {
        $this->loginAsCarrierSuperAdmin();

        $order = $this->orderFactory(
            [
                'paid_at' => now()->timestamp,
            ]
        );

        $this->createOrderPayment($order->id, 2000);

        PaymentStage::factory()->createMany(
            [
                [
                    'order_id' => $order->id,
                    'amount' => 1500,
                ],
                [
                    'order_id' => $order->id,
                    'amount' => 1500,
                ],
            ]
        );

        $this->getJson(route('orders.show', $order))
            ->assertOk()
            ->assertJsonCount(2, 'data.payment_stages')
            ->assertJsonPath('data.payment_stages.0.amount', 1500);
    }

    public function test_broker_fee_paid(): void
    {
        $this->loginAsCarrierSuperAdmin();

        $order = $this->orderFactory();

        $this->setPaidAt($order);

        $order->refresh();

        $order->payment->broker_fee_amount = 1000;
        $order->payment->broker_fee_method_id = Payment::METHOD_ACH;
        $order->payment->broker_fee_days = 2;
        $order->payment->broker_fee_begins = Order::LOCATION_PICKUP;
        $order->payment->save();

        $this->postJson(
            route('order.add-payment-stage', $order),
            [
                'amount' => 500,
                'payment_date' => now()->format('m/d/Y'),
                'payer' => Payment::PAYER_CARRIER,
                'method_id' => Payment::METHOD_ACH,
            ]
        )
            ->assertCreated();

        $this->postJson(
            route('order.add-payment-stage', $order),
            [
                'amount' => 600,
                'payment_date' => now()->format('m/d/Y'),
                'payer' => Payment::PAYER_CARRIER,
                'method_id' => Payment::METHOD_ACH,
            ]
        )
            ->assertCreated();
    }

    public function test_add_payment_stage_credit_card_method(): void
    {
        $this->loginAsCarrierSuperAdmin();

        $order = $this->orderFactory();
        $this->createOrderPayment($order->id, 2000);

        $this->postJson(
            route('order.add-payment-stage', $order),
            [
                'amount' => 1500,
                'payment_date' => now()->format('m/d/Y'),
                'payer' => Payment::PAYER_CUSTOMER,
                'method_id' => Payment::METHOD_CREDIT_CARD,
            ]
        )
            ->assertCreated();

        $this->postJson(
            route('order.add-payment-stage', $order),
            [
                'amount' => 500,
                'payment_date' => now()->format('m/d/Y'),
                'payer' => Payment::PAYER_CUSTOMER,
                'method_id' => Payment::METHOD_CREDIT_CARD,
            ]
        )
            ->assertCreated();

    }
}
