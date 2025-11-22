<?php

namespace Tests\Unit\Documents\Orders;

use App\Documents\Filters\Exceptions\DocumentFilterMethodNotFoundException;
use App\Documents\Filters\OrderDocumentFilter;
use App\Documents\OrderDocument;
use App\Models\Orders\Order;
use App\Models\Orders\Payment;
use App\Models\Orders\PaymentStage;
use App\Models\Orders\Vehicle;
use App\Models\Tags\Tag;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Tests\ElasticsearchClear;
use Tests\Helpers\Traits\Orders\OrderESSavingHelper;
use Tests\TestCase;

class OrderDocumentSearchTest extends TestCase
{
    use DatabaseTransactions;
    use ElasticsearchClear;
    use WithFaker;
    use OrderESSavingHelper;

    /**
     * @return void
     * @throws DocumentFilterMethodNotFoundException
     */
    public function test_review_filter(): void
    {
        $orderReviewed = Order::factory()->assignedStatus()->reviewed()->create();
        $orderNotReviewed = Order::factory()->assignedStatus()->reviewed(false)->create();
        Order::factory()->assignedStatus()->create();
        $this->makeDocuments();
        $result = OrderDocument::filter(['has_review' => true])
            ->search();
        $this->assertCount(1, $result);
        $this->assertEquals($orderReviewed->id, $result[0]->id);

        $result = OrderDocument::filter(['has_review' => false])
            ->search();
        $this->assertCount(1, $result);
        $this->assertEquals($orderNotReviewed->id, $result[0]->id);
    }

    /**
     * @return void
     * @throws DocumentFilterMethodNotFoundException
     */
    public function test_state_filter(): void
    {
        $offer = Order::factory()->create();
        $new = Order::factory()->newStatus()->create();
        $assigned = Order::factory()->assignedStatus()->create();
        $pickedUp = Order::factory()->pickedUpStatus()->create();
        $delivered = Order::factory()->deliveredStatus()->create();
        $deleted = Order::factory()->deletedStatus()->create();
        $this->makeDocuments();
        $result = OrderDocument::filter(['state' => [Order::CALCULATED_STATUS_OFFER]])
            ->search();
        $this->assertCount(1, $result);
        $this->assertEquals($offer->id, $result[0]->id);
        $result = OrderDocument::filter(['state' => [Order::CALCULATED_STATUS_NEW]])
            ->search();
        $this->assertCount(1, $result);
        $this->assertEquals($new->id, $result[0]->id);
        $result = OrderDocument::filter(['state' => [Order::CALCULATED_STATUS_ASSIGNED]])
            ->search();
        $this->assertCount(1, $result);
        $this->assertEquals($assigned->id, $result[0]->id);
        $result = OrderDocument::filter(['state' => [Order::CALCULATED_STATUS_PICKED_UP]])
            ->search();
        $this->assertCount(1, $result);
        $this->assertEquals($pickedUp->id, $result[0]->id);
        $result = OrderDocument::filter(['state' => [Order::CALCULATED_STATUS_DELIVERED]])
            ->search();
        $this->assertCount(1, $result);
        $this->assertEquals($delivered->id, $result[0]->id);
        $result = OrderDocument::filter(['state' => [Order::CALCULATED_STATUS_DELETED]])
            ->search();
        $this->assertCount(1, $result);
        $this->assertEquals($deleted->id, $result[0]->id);
    }

    /**
     * @return void
     * @throws DocumentFilterMethodNotFoundException
     */
    public function test_has_broker_fee_filter(): void
    {
        $order1 = Order::factory()
            ->assignedStatus()
            ->has(
                Payment::factory()->brokerFee(),
                'payment'
            )
            ->create();
        $order2 = Order::factory()
            ->assignedStatus()
            ->has(
                Payment::factory()->brokerFee(),
                'payment'
            )
            ->create();
        $order3 = Order::factory()
            ->assignedStatus()
            ->has(
                Payment::factory()->customer(),
                'payment'
            )
            ->create();
        $order4 = Order::factory()
            ->assignedStatus()
            ->has(
                Payment::factory()->broker(),
                'payment'
            )
            ->create();
        $order5 = Order::factory()
            ->assignedStatus()
            ->has(
                Payment::factory()->broker()->customer(),
                'payment'
            )
            ->create();
        $this->makeDocuments();
        $result = OrderDocument::filter(['has_broker_fee' => true])
            ->search();
        $this->assertCount(2, $result);
        $this->assertEquals($order1->id, $result[0]->id);
        $this->assertEquals($order2->id, $result[1]->id);
        $result = OrderDocument::filter(['has_broker_fee' => false])
            ->search();
        $this->assertCount(3, $result);
        $this->assertEquals($order3->id, $result[0]->id);
        $this->assertEquals($order4->id, $result[1]->id);
        $this->assertEquals($order5->id, $result[2]->id);
    }

    /**
     * @return void
     * @throws DocumentFilterMethodNotFoundException
     */
    public function test_paid_filter(): void
    {
        $paid = Order::factory()
            ->assignedStatus()
            ->has(
                Payment::factory()
                    ->customer()
                    ->paid(),
                'payment'
            )
            ->create();
        $notPaid = Order::factory()
            ->assignedStatus()
            ->has(
                Payment::factory()
                    ->broker()
                    ->customer(),
                'payment'
            )
            ->create();
        $this->makeDocuments();
        $result = OrderDocument::filter(['paid' => true])->search();
        $this->assertCount(1, $result);
        $this->assertEquals($paid->id, $result[0]->id);
        $result = OrderDocument::filter(['paid' => false])->search();
        $this->assertCount(1, $result);
        $this->assertEquals($notPaid->id, $result[0]->id);
    }

    /**
     * @return void
     * @throws DocumentFilterMethodNotFoundException
     */
    public function test_driver_dispatcher_filters(): void
    {
        $order = Order::factory()->assignedStatus()->create();
        Order::factory()->assignedStatus()->count(10)->create();
        $this->makeDocuments();
        $result = OrderDocument::filter(['driver_id' => $order->driver_id])->search();
        $this->assertCount(1, $result);
        $this->assertEquals($order->id, $result[0]->id);
        $result = OrderDocument::filter(['dispatcher_id' => $order->dispatcher_id])->search();
        $this->assertCount(1, $result);
        $this->assertEquals($order->id, $result[0]->id);
    }

    /**
     * @return void
     * @throws DocumentFilterMethodNotFoundException
     */
    public function test_vehicles_filter(): void
    {
        $order1 = Order::factory()
            ->assignedStatus()
            ->has(
                Vehicle::factory(['make' => 'make1', 'model' => 'model1', 'year' => '2020']),
                'vehicles'
            )
            ->has(
                Vehicle::factory(['make' => 'mk2', 'model' => 'mdl2', 'year' => '1999']),
                'vehicles'
            )
            ->create();
        $order2 = Order::factory()
            ->assignedStatus()
            ->has(
                Vehicle::factory(['make' => 'mk1', 'model' => 'mdl1', 'year' => '1999']),
                'vehicles'
            )
            ->has(
                Vehicle::factory(['make' => 'make2', 'model' => 'model2', 'year' => '2022']),
                'vehicles'
            )
            ->create();
        Order::factory()
            ->assignedStatus()
            ->create();
        $this->makeDocuments();
        $result = OrderDocument::filter(['make' => 'make'])->search();
        $this->assertCount(2, $result);
        $this->assertEquals($order1->id, $result[0]->id);
        $this->assertEquals($order2->id, $result[1]->id);
        $result = OrderDocument::filter(['model' => 'mdl'])->search();
        $this->assertCount(2, $result);
        $this->assertEquals($order1->id, $result[0]->id);
        $this->assertEquals($order2->id, $result[1]->id);
        $result = OrderDocument::filter(['year' => '22'])->search();
        $this->assertCount(1, $result);
        $this->assertEquals($order2->id, $result[0]->id);
    }

    /**
     * @return void
     * @throws DocumentFilterMethodNotFoundException
     */
    public function test_load_id_filter(): void
    {
        $order1 = Order::factory(['load_id' => 'load1'])
            ->assignedStatus()
            ->create();
        $order2 = Order::factory(['load_id' => 'load2'])
            ->assignedStatus()
            ->create();
        $this->makeDocuments();
        $result = OrderDocument::filter(['load_id' => 'load'])
            ->search();
        $this->assertCount(2, $result);
        $this->assertEquals($order1->id, $result[0]->id);
        $this->assertEquals($order2->id, $result[1]->id);
        $result = OrderDocument::filter(['load_id' => 'load2'])
            ->search();
        $this->assertCount(1, $result);
        $this->assertEquals($order2->id, $result[0]->id);
    }

    /**
     * @return void
     * @throws DocumentFilterMethodNotFoundException
     */
    public function test_invoice_filter(): void
    {
        $prefix = Str::random(5);
        $order1 = Order::factory()
            ->assignedStatus()
            ->has(
                Payment::factory()->broker()->brokerInvoice($prefix . Str::random()),
                'payment'
            )
            ->create();
        $order2 = Order::factory()
            ->assignedStatus()
            ->has(
                Payment::factory()->customer()->customerInvoice($prefix . Str::random()),
                'payment'
            )
            ->create();
        Order::factory()
            ->assignedStatus()
            ->has(
                Payment::factory()->customer()->customerInvoice(),
                'payment'
            )
            ->create();
        $this->makeDocuments();
        $result = OrderDocument::filter(['broker_invoice_id' => $prefix])
            ->search();
        $this->assertCount(1, $result);
        $this->assertEquals($order1->id, $result[0]->id);
        $result = OrderDocument::filter(['invoice_id' => $prefix])
            ->search();
        $this->assertCount(2, $result);
        $this->assertEquals($order1->id, $result[0]->id);
        $this->assertEquals($order2->id, $result[1]->id);
    }

    /**
     * @return void
     * @throws DocumentFilterMethodNotFoundException
     */
    public function test_search_by_s_with_sort(): void
    {
        $part = Str::random(5);
        $orders = [];

        $orders[] = Order::factory()
            ->assignedStatus()
            ->has(
                Payment::factory()->customer()->customerInvoice(Str::random() . $part . Str::random()),
                'payment'
            )
            ->create();

        $orders[] = Order::factory()
            ->assignedStatus()
            ->has(
                Payment::factory()->customer()->customerInvoice($part . Str::random()),
                'payment'
            )
            ->create();

        $orders[] = Order::factory()
            ->assignedStatus()
            ->has(
                Payment::factory()->broker()->brokerInvoice(Str::random() . $part . Str::random()),
                'payment'
            )
            ->create();

        $orders[] = Order::factory()
            ->assignedStatus()
            ->has(
                Payment::factory()->broker()->brokerInvoice($part . Str::random()),
                'payment'
            )
            ->create();

        $orders[] = Order::factory()
            ->assignedStatus()
            ->shipperFullName(Str::random() . $part . Str::random())
            ->create();

        $orders[] = Order::factory()
            ->assignedStatus()
            ->shipperFullName($part . Str::random())
            ->create();

        $orders[] = Order::factory()
            ->assignedStatus()
            ->deliveryFullName(Str::random() . $part . Str::random())
            ->create();

        $orders[] = Order::factory()
            ->assignedStatus()
            ->deliveryFullName($part . Str::random())
            ->create();

        $orders[] = Order::factory()
            ->assignedStatus()
            ->pickupFullName(Str::random() . $part . Str::random())
            ->create();

        $orders[] = Order::factory()
            ->assignedStatus()
            ->pickupFullName($part . Str::random())
            ->create();

        $orders[] = Order::factory()
            ->assignedStatus()
            ->has(
                Vehicle::factory(['vin' => Str::random(2) . $part . Str::random(10)]),
                'vehicles'
            )
            ->has(
                Vehicle::factory(),
                'vehicles'
            )
            ->create();

        $orders[] = Order::factory()
            ->assignedStatus()
            ->has(
                Vehicle::factory(['vin' => $part . Str::random(12)]),
                'vehicles'
            )
            ->has(
                Vehicle::factory(),
                'vehicles'
            )
            ->create();

        $orders[] = Order::factory(['load_id' => Str::random() . $part . Str::random()])
            ->assignedStatus()
            ->create();

        $orders[] = Order::factory(['load_id' => $part . Str::random()])
            ->assignedStatus()
            ->create();

        Order::factory()
            ->assignedStatus()
            ->has(
                Payment::factory()->customer()->broker(),
                'payment'
            )
            ->has(
                Vehicle::factory()->count(2),
                'vehicles'
            )
            ->count(10)
            ->create();
        $this->makeDocuments();
        $result = OrderDocument::filter(['s' => $part])
            ->sortBySearch()
            ->size(100)
            ->search();

        $this->assertCount(14, $result);
        for ($i = 0; $i < 14; $i++) {
            $this->assertEquals($orders[13 - $i]->id, $result[$i]->id);
        }
    }

    /**
     * @return void
     * @throws DocumentFilterMethodNotFoundException
     */
    public function test_broker_fee_paid_filter(): void
    {
        $order1 = Order::factory()
            ->assignedStatus()
            ->has(
                Payment::factory()->brokerFee()->brokerFeePaid(),
                'payment'
            )
            ->create();
        $order2 = Order::factory()
            ->assignedStatus()
            ->has(
                Payment::factory()->brokerFee(),
                'payment'
            )
            ->create();
        Order::factory()
            ->assignedStatus()
            ->count(20)
            ->create();
        $this->makeDocuments();
        $result = OrderDocument::filter(['broker_fee_paid' => true])->search();
        $this->assertCount(1, $result);
        $this->assertEquals($order1->id, $result[0]->id);
        $result = OrderDocument::filter(['broker_fee_paid' => false])->search();
        $this->assertCount(1, $result);
        $this->assertEquals($order2->id, $result[0]->id);
    }

    /**
     * @return void
     * @throws DocumentFilterMethodNotFoundException
     */
    public function test_billed_filter(): void
    {
        Order::factory(['is_billed' => true])
            ->assignedStatus()
            ->has(
                Payment::factory()->customer()->broker()->paid(),
                'payment'
            )
            ->create();
        Order::factory(['is_billed' => false])
            ->assignedStatus()
            ->has(
                Payment::factory()->customer()->broker()->paid(),
                'payment'
            )
            ->create();
        $order1 = Order::factory(['is_billed' => true])
            ->assignedStatus()
            ->has(
                Payment::factory()->customer()->broker(),
                'payment'
            )
            ->create();
        $order2 = Order::factory(['is_billed' => false])
            ->assignedStatus()
            ->has(
                Payment::factory()->customer()->broker(),
                'payment'
            )
            ->create();
        $this->makeDocuments();
        $result = OrderDocument::filter(['billed' => true])->search();
        $this->assertCount(1, $result);
        $this->assertEquals($order1->id, $result[0]->id);
        $result = OrderDocument::filter(['billed' => false])->search();
        $this->assertCount(1, $result);
        $this->assertEquals($order2->id, $result[0]->id);
    }

    /**
     * @return void
     * @throws DocumentFilterMethodNotFoundException
     */
    public function test_overdue_filter(): void
    {
        $order = Order::factory()
            ->deliveredStatus()
            ->has(
                Payment::factory()->customer()->broker()->paid(),
                'payment'
            )
            ->create();
        $orders = [
            Order::factory()
                ->pickupOverdue()
                ->has(
                    Payment::factory()->customer()->broker()->paid(),
                    'payment'
                )
                ->create(),
            Order::factory()
                ->deliveryOverdue()
                ->has(
                    Payment::factory()->customer()->broker()->paid(),
                    'payment'
                )
                ->create(),
            Order::factory()
                ->deliveredStatus()
                ->has(
                    Payment::factory()->customer()->broker()->brokerOverdue(),
                    'payment'
                )
                ->create(),
            Order::factory()
                ->deliveredStatus()
                ->has(
                    Payment::factory()->customer()->broker()->customerOverdue(),
                    'payment'
                )
                ->create(),
        ];
        $this->makeDocuments();
        $result = OrderDocument::filter(['overdue' => true])->search();
        $this->assertCount(4, $result);
        $this->assertEquals($orders[0]->id, $result[0]->id);
        $this->assertEquals($orders[1]->id, $result[1]->id);
        $this->assertEquals($orders[2]->id, $result[2]->id);
        $this->assertEquals($orders[3]->id, $result[3]->id);
        $result = OrderDocument::filter(['overdue' => false])->search();
        $this->assertCount(1, $result);
        $this->assertEquals($order->id, $result[0]->id);
    }

    /**
     * @return void
     * @throws DocumentFilterMethodNotFoundException
     */
    public function test_filter_attributes(): void
    {
        $order1 = Order::factory()
            ->assignedStatus()
            ->reviewed()
            ->has(
                Payment::factory()->customer()->broker()->paid(),
                'payment'
            )
            ->create();
        $order2 = Order::factory()
            ->assignedStatus()
            ->reviewed(false)
            ->has(
                Payment::factory()->customer()->broker(),
                'payment'
            )
            ->create();
        $order3 = Order::factory()
            ->assignedStatus()
            ->pickupOverdue()
            ->has(
                Payment::factory()->brokerFee()->brokerFeePaid(),
                'payment'
            )
            ->create();
        $order4 = Order::factory()
            ->assignedStatus()
            ->has(
                Payment::factory()->brokerFee(),
                'payment'
            )
            ->create();
        $this->makeDocuments();
        $result = OrderDocument::filter(
            [
                'attributes' => [
                    OrderDocumentFilter::ATTRIBUTE_PAID,
                    OrderDocumentFilter::ATTRIBUTE_REVIEWED,
                ]
            ]
        )
            ->search();
        $this->assertCount(1, $result);
        $this->assertEquals($order1->id, $result[0]->id);
        $result = OrderDocument::filter(
            [
                'attributes' => [
                    OrderDocumentFilter::ATTRIBUTE_NOT_PAID,
                    OrderDocumentFilter::ATTRIBUTE_NOT_REVIEWED,
                ]
            ]
        )
            ->search();
        $this->assertCount(1, $result);
        $this->assertEquals($order2->id, $result[0]->id);
        $result = OrderDocument::filter(
            [
                'attributes' => [
                    OrderDocumentFilter::ATTRIBUTE_OVERDUE,
                    OrderDocumentFilter::ATTRIBUTE_BROKER_FEE_PAID,
                ]
            ]
        )
            ->search();
        $this->assertCount(1, $result);
        $this->assertEquals($order3->id, $result[0]->id);
        $result = OrderDocument::filter(
            [
                'attributes' => [
                    OrderDocumentFilter::ATTRIBUTE_NOT_OVERDUE,
                    OrderDocumentFilter::ATTRIBUTE_BROKER_FEE_NOT_PAID,
                ]
            ]
        )
            ->search();
        $this->assertCount(1, $result);
        $this->assertEquals($order4->id, $result[0]->id);
    }

    /**
     * @return void
     * @throws DocumentFilterMethodNotFoundException
     */
    public function test_check_id_filter(): void
    {
        $part = Str::random(10);
        $order1 = Order::factory()
            ->has(
                PaymentStage::factory()
                    ->broker(100.0)
                    ->referenceNumber(Str::random(5) . $part . Str::random(5)),
                'paymentStages'
            )
            ->create();
        $order2 = Order::factory()
            ->has(
                PaymentStage::factory()
                    ->customer(100.0)
                    ->referenceNumber(Str::random(5) . $part . Str::random(5)),
                'paymentStages'
            )
            ->create();
        Order::factory()
            ->has(
                PaymentStage::factory()
                    ->customer(100.0)
                    ->referenceNumber(),
                'paymentStages'
            )
            ->create();
        $this->makeDocuments();
        $result = OrderDocument::filter(['check_id' => $part])
            ->search();
        $this->assertCount(2, $result);
        $this->assertEquals($order1->id, $result[0]->id);
        $this->assertEquals($order2->id, $result[1]->id);
        $result = OrderDocument::filter(['broker_check_id' => $part])
            ->search();
        $this->assertCount(1, $result);
        $this->assertEquals($order1->id, $result[0]->id);
    }

    /**
     * @return void
     * @throws DocumentFilterMethodNotFoundException
     */
    public function test_payment_method_id_filter(): void
    {
        $order1 = Order::factory()
            ->assignedStatus()
            ->reviewed()
            ->has(
                Payment::factory()
                    ->customer(null, Payment::METHOD_CASH)
                    ->broker(null, Payment::METHOD_CHECK),
                'payment'
            )
            ->create();
        $order2 = Order::factory()
            ->assignedStatus()
            ->reviewed()
            ->has(
                Payment::factory()
                    ->customer(null, Payment::METHOD_CHECK)
                    ->broker(null, Payment::METHOD_CASH),
                'payment'
            )
            ->create();
        Order::factory()
            ->assignedStatus()
            ->reviewed()
            ->has(
                Payment::factory()
                    ->customer(null, Payment::METHOD_QUICKPAY)
                    ->broker(null, Payment::METHOD_USHIP),
                'payment'
            )
            ->create();
        $this->makeDocuments();
        $result = OrderDocument::filter(['payment_method_id' => Payment::METHOD_CHECK])
            ->search();
        $this->assertCount(2, $result);
        $this->assertEquals($order1->id, $result[0]->id);
        $this->assertEquals($order2->id, $result[1]->id);
        $result = OrderDocument::filter(['broker_payment_method_id' => Payment::METHOD_CASH])
            ->search();
        $this->assertCount(1, $result);
        $this->assertEquals($order2->id, $result[0]->id);
    }

    /**
     * @return void
     * @throws DocumentFilterMethodNotFoundException
     */
    public function test_pickup_date_filter(): void
    {
        $order1 = Order::factory()
            ->pickedUpStatus()
            ->create(['pickup_date_actual' => Carbon::now()->subDays(2)->getTimestamp()]);
        $order2 = Order::factory()
            ->pickedUpStatus()
            ->create(['pickup_date_actual' => Carbon::now()->subDays(3)->getTimestamp()]);
        Order::factory()
            ->deliveredStatus()
            ->create(['delivery_date_actual' => Carbon::now()->subDays(3)->getTimestamp()]);
        Order::factory()
            ->pickedUpStatus()
            ->create();
        $this->makeDocuments();
        $result = OrderDocument::filter(
            [
                'destination_date' => [
                    'location' => Order::LOCATION_PICKUP,
                    'dates' => [
                        'from' => Carbon::now()->subDays(3)->startOfDay()->setTimezone('UTC'),
                        'to' => Carbon::now()->subDays(2)->endOfDay()->setTimezone('UTC'),
                    ]
                ]
            ]
        )
            ->search();
        $this->assertCount(2, $result);
        $this->assertEquals($order1->id, $result[0]->id);
        $this->assertEquals($order2->id, $result[1]->id);
    }

    /**
     * @return void
     * @throws DocumentFilterMethodNotFoundException
     */
    public function test_delivery_date_filter(): void
    {
        $order1 = Order::factory()
            ->deliveredStatus()
            ->create(['delivery_date_actual' => Carbon::now()->subDays(2)->getTimestamp()]);
        $order2 = Order::factory()
            ->deliveredStatus()
            ->create(['delivery_date_actual' => Carbon::now()->subDays(3)->getTimestamp()]);
        Order::factory()
            ->pickedUpStatus()
            ->create(['pickup_date_actual' => Carbon::now()->subDays(3)->getTimestamp()]);
        Order::factory()
            ->pickedUpStatus()
            ->create();
        $this->makeDocuments();
        $result = OrderDocument::filter(
            [
                'destination_date' => [
                    'location' => Order::LOCATION_DELIVERY,
                    'dates' => [
                        'from' => Carbon::now()->subDays(3)->startOfDay()->setTimezone('UTC'),
                        'to' => Carbon::now()->subDays(2)->endOfDay()->setTimezone('UTC'),
                    ]
                ]
            ]
        )
            ->search();
        $this->assertCount(2, $result);
        $this->assertEquals($order1->id, $result[0]->id);
        $this->assertEquals($order2->id, $result[1]->id);
    }

    /**
     * @return void
     * @throws DocumentFilterMethodNotFoundException
     */
    public function test_created_at_date_filter(): void
    {
        $order1 = Order::factory()
            ->assignedStatus()
            ->create(['created_at' => Carbon::now()->subDays(3)]);
        $order2 = Order::factory()
            ->assignedStatus()
            ->create(['created_at' => Carbon::now()->subDays(4)]);
        Order::factory()
            ->pickedUpStatus()
            ->create();
        $this->makeDocuments();
        $result = OrderDocument::filter(
            [
                'created_at_date' => [
                    'from' => Carbon::now()->subDays(4)->startOfDay()->setTimezone('UTC'),
                    'to' => Carbon::now()->subDays(3)->endOfDay()->setTimezone('UTC'),
                ]
            ]
        )
            ->search();
        $this->assertCount(2, $result);
        $this->assertEquals($order1->id, $result[0]->id);
        $this->assertEquals($order2->id, $result[1]->id);
    }

    /**
     * @return void
     * @throws DocumentFilterMethodNotFoundException
     */
    public function test_invoice_send_date_filter(): void
    {
        $order1 = Order::factory()
            ->assignedStatus()
            ->reviewed()
            ->has(
                Payment::factory(['broker_payment_invoice_issue_date' => Carbon::now()->subDays(3)->getTimestamp()])->broker(),
                'payment'
            )
            ->create();
        $order2 = Order::factory()
            ->assignedStatus()
            ->reviewed()
            ->has(
                Payment::factory(['broker_payment_invoice_issue_date' => Carbon::now()->subDays(5)->getTimestamp()])->broker(),
                'payment'
            )
            ->create();
        $order3 = Order::factory()
            ->assignedStatus()
            ->reviewed()
            ->has(
                Payment::factory(['customer_payment_invoice_issue_date' => Carbon::now()->subDays(4)->getTimestamp()])
                    ->customer(),
                'payment'
            )
            ->create();
        Order::factory()
            ->assignedStatus()
            ->reviewed()
            ->has(
                Payment::factory(['customer_payment_invoice_issue_date' => Carbon::now()->getTimestamp()])
                    ->customer(),
                'payment'
            )
            ->create();
        $this->makeDocuments();
        $result = OrderDocument::filter(
            [
                'broker_invoice_send_date' => [
                    'from' => Carbon::now()->subDays(5)->startOfDay()->setTimezone('UTC'),
                    'to' => Carbon::now()->subDays(3)->endOfDay()->setTimezone('UTC'),
                ]
            ]
        )
            ->search();
        $this->assertCount(2, $result);
        $this->assertEquals($order1->id, $result[0]->id);
        $this->assertEquals($order2->id, $result[1]->id);
        $result = OrderDocument::filter(
            [
                'invoice_send_date' => [
                    'from' => Carbon::now()->subDays(5)->startOfDay()->setTimezone('UTC'),
                    'to' => Carbon::now()->subDays(3)->endOfDay()->setTimezone('UTC'),
                ]
            ]
        )
            ->search();
        $this->assertCount(3, $result);
        $this->assertEquals($order1->id, $result[0]->id);
        $this->assertEquals($order2->id, $result[1]->id);
        $this->assertEquals($order3->id, $result[2]->id);
    }

    /**
     * @return void
     * @throws DocumentFilterMethodNotFoundException
     */
    public function test_paid_date_filter(): void
    {
        $order1 = Order::factory()
            ->assignedStatus()
            ->reviewed()
            ->has(
                Payment::factory()->broker()->paid(),
                'payment'
            )
            ->create();
        $order2 = Order::factory()
            ->assignedStatus()
            ->reviewed()
            ->has(
                Payment::factory()->customer()->paid(),
                'payment'
            )
            ->create();
        Order::factory()
            ->assignedStatus()
            ->reviewed()
            ->has(
                Payment::factory()->customer()->broker()->paid(),
                'payment'
            )
            ->create();
        $order1->paymentStages[0]->payment_date = Carbon::now()->subDays(3)->getTimestamp();
        $order1->paymentStages[0]->save();
        $order1->paymentStages[1]->payment_date = Carbon::now()->subDays(3)->getTimestamp();
        $order1->paymentStages[1]->save();
        $order2->paymentStages[0]->payment_date = Carbon::now()->subDays(2)->getTimestamp();
        $order2->paymentStages[0]->save();
        $this->makeDocuments();
        $result = OrderDocument::filter(
            [
                'paid_at_date' => [
                    'from' => Carbon::now()->subDays(3)->startOfDay()->setTimezone('UTC'),
                    'to' => Carbon::now()->subDays(2)->endOfDay()->setTimezone('UTC'),
                ]
            ]
        )
            ->search();
        $this->assertCount(2, $result);
        $this->assertEquals($order1->id, $result[0]->id);
        $this->assertEquals($order2->id, $result[1]->id);
    }

    /**
     * @return void
     * @throws DocumentFilterMethodNotFoundException
     */
    public function test_tags_filter(): void
    {
        $tag = Tag::factory()
            ->hasAttached(
                Order::factory()
                    ->assignedStatus()
                    ->count(3)
                    ->create(),
                [],
                'orders'
            )
            ->create();
        Tag::factory()
            ->hasAttached(
                Order::factory()
                    ->assignedStatus()
                    ->count(5),
                [],
                'orders'
            )
            ->create();
        $this->makeDocuments();
        $result = OrderDocument::filter(
            [
                'tag_id' => $tag->id
            ]
        )
            ->search();
        $this->assertCount(3, $result);
    }

    /**
     * @return void
     * @throws DocumentFilterMethodNotFoundException
     */
    public function test_mobile_tab_filter(): void
    {
        $work = [
            Order::factory(
                [
                    'has_delivery_signature' => true,
                    'has_delivery_inspection' => true,
                ]
            )
                ->deliveredStatus()
                ->has(
                    Payment::factory(['driver_payment_data_sent' => false])
                        ->customer(null, Payment::METHOD_CASH),
                    'payment'
                )
                ->create(),
            Order::factory(
                [
                    'has_pickup_signature' => true,
                    'has_pickup_inspection' => true
                ]
            )
                ->pickedUpStatus()
                ->create(),
            Order::factory(
                [
                    'pickup_date' => Carbon::now()->subDays(2)->getTimestamp()
                ]
            )
                ->assignedStatus()
                ->create(),
        ];
        $plan = [
            Order::factory(
                [
                    'pickup_date' => Carbon::now()->addDay()->getTimestamp()
                ]
            )
                ->assignedStatus()
                ->create(),
        ];
        $history = [
            Order::factory(
                [
                    'has_delivery_signature' => true,
                    'has_delivery_inspection' => true,
                    'delivery_date_actual' => Carbon::now()->subDays(config('orders.mobile.history.days') - 1)
                ]
            )
                ->deliveredStatus()
                ->create(),
        ];
        $this->makeDocuments();
        $result = OrderDocument::filter(['mobile_tab' => Order::MOBILE_TAB_IN_WORK])
            ->search();
        $this->assertCount(3, $result);
        $this->assertEquals($work[0]->id, $result[0]->id);
        $this->assertEquals($work[1]->id, $result[1]->id);
        $this->assertEquals($work[2]->id, $result[2]->id);
        $result = OrderDocument::filter(['mobile_tab' => Order::MOBILE_TAB_PLAN])
            ->search();
        $this->assertCount(1, $result);
        $this->assertEquals($plan[0]->id, $result[0]->id);
        $result = OrderDocument::filter(['mobile_tab' => Order::MOBILE_TAB_HISTORY])
            ->search();
        $this->assertCount(1, $result);
        $this->assertEquals($history[0]->id, $result[0]->id);
    }

    public function test_dashboard_filters(): void
    {
        $deliveredToday = Order::factory()
            ->deliveredStatus()
            ->create();
        $paidToday = Order::factory()
            ->assignedStatus()
            ->has(
                Payment::factory()->customer()->paid(),
                'payment'
            )
            ->create();
        $pickupOverdue = Order::factory()
            ->pickupOverdue()
            ->create();
        $deliveryOverdue = Order::factory()
            ->deliveryOverdue()
            ->create();
        $todayDelivery = Order::factory(['delivery_date' => Carbon::now()->getTimestamp()])
            ->pickedUpStatus()
            ->create();
        $todayPickup = Order::factory(['pickup_date' => Carbon::now()->getTimestamp()])
            ->assignedStatus()
            ->create();
        $paymentOverdue = Order::factory()
            ->assignedStatus()
            ->has(
                Payment::factory()->customer()->customerOverdue(),
                'payment'
            )
            ->create();
        $this->makeDocuments();
        $result = OrderDocument::filter(['dashboard_filter' => OrderDocumentFilter::DASHBOARD_TODAY_DELIVERED_ORDERS])
            ->search();
        $this->assertCount(1, $result);
        $this->assertEquals($deliveredToday->id, $result[0]->id);
        $result = OrderDocument::filter(['dashboard_filter' => OrderDocumentFilter::DASHBOARD_TODAY_PAID_ORDERS])
            ->search();
        $this->assertCount(1, $result);
        $this->assertEquals($paidToday->id, $result[0]->id);
        $result = OrderDocument::filter(['dashboard_filter' => OrderDocumentFilter::DASHBOARD_PICKUP_OVERDUE_ORDERS])
            ->search();
        $this->assertCount(1, $result);
        $this->assertEquals($pickupOverdue->id, $result[0]->id);
        $result = OrderDocument::filter(['dashboard_filter' => OrderDocumentFilter::DASHBOARD_DELIVERY_OVERDUE_ORDERS])
            ->search();
        $this->assertCount(1, $result);
        $this->assertEquals($deliveryOverdue->id, $result[0]->id);
        $result = OrderDocument::filter(['dashboard_filter' => OrderDocumentFilter::DASHBOARD_TODAY_PICKUP_ORDERS])
            ->search();
        $this->assertCount(1, $result);
        $this->assertEquals($todayPickup->id, $result[0]->id);
        $result = OrderDocument::filter(['dashboard_filter' => OrderDocumentFilter::DASHBOARD_TODAY_DELIVERY_ORDERS])
            ->search();
        $this->assertCount(1, $result);
        $this->assertEquals($todayDelivery->id, $result[0]->id);
        $result = OrderDocument::filter(['dashboard_filter' => OrderDocumentFilter::DASHBOARD_PAYMENT_OVERDUE_ORDERS])
            ->search();
        $this->assertCount(1, $result);
        $this->assertEquals($paymentOverdue->id, $result[0]->id);
        $result = OrderDocument::filter(['dashboard_filter' => OrderDocumentFilter::DASHBOARD_MONTH_PAID_ORDERS])
            ->search();
        $this->assertCount(1, $result);
        $this->assertEquals($paidToday->id, $result[0]->id);
    }
}
