<?php

namespace Tests\Unit\Services\Orders;

use App\Models\Orders\Order;
use App\Models\Orders\Payment;
use App\Models\Orders\Vehicle;
use App\Services\Orders\OrderSearchService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\ElasticsearchClear;
use Tests\Helpers\Traits\Orders\OrderESSavingHelper;
use Tests\TestCase;

class OrderSearchServiceTest extends TestCase
{
    use DatabaseTransactions;
    use ElasticsearchClear;
    use OrderESSavingHelper;

    private OrderSearchService $service;

    public function test_search_same_load_id(): void
    {
        $order1 = Order::factory()
            ->assignedStatus()
            ->create();
        $order2 = Order::factory()
            ->deletedStatus()
            ->create();
        $this->makeDocuments();

        $result = $this->service->searchSameLoadId($order1->load_id, null);
        $this->assertCount(1, $result);
        $this->assertEquals($order1->id, $result[0]->id);

        $this->assertTrue(
            $this->service->searchSameLoadId($order1->load_id, $order1->id)->isEmpty()
        );

        $this->assertTrue(
            $this->service->searchSameLoadId($order2->load_id, null)->isEmpty()
        );
    }

    public function test_search_same_vin(): void
    {
        $order1 = Order::factory()
            ->assignedStatus()
            ->has(
                Vehicle::factory(),
                'vehicles'
            )
            ->create();
        $order2 = Order::factory()
            ->deletedStatus()
            ->has(
                Vehicle::factory(),
                'vehicles'
            )
            ->create();
        $this->makeDocuments();

        $result = $this->service->searchSameVin($order1->vehicles[0]->vin, null);
        $this->assertCount(1, $result);
        $this->assertEquals($order1->id, $result[0]->id);

        $this->assertTrue(
            $this->service->searchSameVin($order1->vehicles[0]->vin, $order1->id)->isEmpty()
        );

        $this->assertTrue(
            $this->service->searchSameVin($order2->vehicles[0]->vin, null)->isEmpty()
        );
    }

    public function test_get_order_total_info(): void
    {
        Order::factory()
            ->assignedStatus()
            ->create();

        $order2 = Order::factory()
            ->deliveredStatus()
            ->has(
                Payment::factory()->brokerFee(),
                'payment'
            )
            ->create();

        $order3 = Order::factory()
            ->deliveredStatus()
            ->has(
                Payment::factory()->brokerFee()
                    ->brokerFeePaid(),
                'payment'
            )
            ->create();

        $order4 = Order::factory()
            ->deliveredStatus()
            ->has(
                Payment::factory()->brokerFee()->brokerFeeOverdue(),
                'payment'
            )
            ->create();

        $order5 = Order::factory()
            ->deliveredStatus()
            ->has(
                Payment::factory()->broker(),
                'payment'
            )
            ->create();

        $order6 = Order::factory()
            ->deliveredStatus()
            ->has(
                Payment::factory()->broker()->paid(),
                'payment'
            )
            ->create();

        $order7 = Order::factory()
            ->deliveredStatus()
            ->has(
                Payment::factory()->broker()->brokerOverdue(),
                'payment'
            )
            ->create();
        $this->makeDocuments();

        $getTotalAmount = static fn(Order $order) => ($order->payment->broker_payment_amount +
                $order->payment->customer_payment_amount) - $order->payment->broker_fee_amount;

        $this->assertEquals(
            [
                'total' => 7,
                'total_carrier_amount' => $getTotalAmount($order2) + $getTotalAmount($order3) + $getTotalAmount($order4)
                    + $getTotalAmount($order5) + $getTotalAmount($order6) + $getTotalAmount($order7),
                'broker_fee_amount_forecast' => $order2->payment->broker_fee_amount +
                    $order4->payment->broker_fee_amount,
                'broker_fee_total_due' => $order2->payment->broker_fee_amount +
                    $order4->payment->broker_fee_amount,
                'broker_fee_past_due' => $order4->payment->broker_fee_amount,
                'broker_fee_current_due' => $order2->payment->broker_fee_amount,
                'customer_amount_forecast' => $order2->payment->customer_payment_amount +
                    $order3->payment->customer_payment_amount + $order4->payment->customer_payment_amount +
                    $order5->payment->customer_payment_amount + $order7->payment->customer_payment_amount,
                'broker_amount_forecast' => $order5->payment->broker_payment_amount +
                    $order7->payment->broker_payment_amount,
                'broker_total_due' => $order5->payment->broker_payment_amount +
                    $order7->payment->broker_payment_amount,
                'broker_current_due' => $order5->payment->broker_payment_amount,
                'broker_past_due' => $order7->payment->broker_payment_amount,
            ],
            $this->service->getOrderTotal([])
        );
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = resolve(OrderSearchService::class);
    }
}
