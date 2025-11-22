<?php

namespace Tests\Unit\Documents\Orders;

use App\Documents\CompanyDocument;
use App\Documents\Filters\Exceptions\DocumentFilterMethodNotFoundException;
use App\Documents\Filters\OrderDocumentFilter;
use App\Models\Orders\Order;
use App\Models\Orders\Payment;
use App\Models\Orders\PaymentStage;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Tests\ElasticsearchClear;
use Tests\Helpers\Traits\Orders\OrderESSavingHelper;
use Tests\TestCase;

class CompanyDocumentSearchTest extends TestCase
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
        $order1 = Order::factory()
            ->assignedStatus()
            ->shipperFullName($this->faker->company)
            ->create();
        Order::factory()
            ->assignedStatus()
            ->shipperFullName($order1->shipper_full_name)
            ->create();
        Order::factory()
            ->assignedStatus()
            ->create();
        $this->makeDocuments(true);
        $result = CompanyDocument::filter(['company_name' => $order1->shipper_full_name])
            ->search();
        $this->assertCount(1, $result);
        $this->assertEquals(2, $result[0]->orderCount);
    }

    /**
     * @return void
     * @throws DocumentFilterMethodNotFoundException
     */
    public function test_payment_status_filter(): void
    {
        $order = Order::factory()
            ->assignedStatus()
            ->has(
                Payment::factory()->broker()->paid(),
                'payment'
            )
            ->shipperFullName($this->faker->company)
            ->create();
        Order::factory()
            ->assignedStatus()
            ->has(
                Payment::factory()->broker(),
                'payment'
            )
            ->shipperFullName($order->shipper_full_name)
            ->create();
        Order::factory()
            ->assignedStatus()
            ->has(
                Payment::factory()->broker(),
                'payment'
            )
            ->create();
        $this->makeDocuments(true);
        $result = CompanyDocument::filter(['payment_status' => OrderDocumentFilter::PAYMENT_STATUS_NOT_PAID])
            ->search();
        $this->assertCount(2, $result);
        $result = CompanyDocument::filter(['payment_status' => OrderDocumentFilter::PAYMENT_STATUS_PAID])
            ->search();
        $this->assertCount(1, $result);
    }

    /**
     * @return void
     * @throws DocumentFilterMethodNotFoundException
     */
    public function test_reference_number_filter(): void
    {
        $part1 = Str::random(5);
        $part2 = Str::random(5);
        $order = Order::factory()
            ->assignedStatus()
            ->has(
                PaymentStage::factory()
                    ->broker(100.0)
                    ->referenceNumber(Str::random(5) . $part1 . Str::random(5)),
                'paymentStages'
            )
            ->shipperFullName($this->faker->company)
            ->create();
        Order::factory()
            ->assignedStatus()
            ->has(
                PaymentStage::factory()
                    ->broker(100.0)
                    ->referenceNumber(Str::random(5) . $part2 . Str::random(5)),
                'paymentStages'
            )
            ->shipperFullName($order->shipper_full_name)
            ->create();
        Order::factory()
            ->assignedStatus()
            ->has(
                PaymentStage::factory()
                    ->broker(100.0)
                    ->referenceNumber(Str::random(5) . $part1 . Str::random(5)),
                'paymentStages'
            )
            ->create();
        $this->makeDocuments(true);
        $result = CompanyDocument::filter(['reference_number' => $part1])
            ->search();
        $this->assertCount(2, $result);
        $result = CompanyDocument::filter(['reference_number' => $part2])
            ->search();
        $this->assertCount(1, $result);
    }

    /**
     * @return void
     * @throws DocumentFilterMethodNotFoundException
     */
    public function test_invoice_filter(): void
    {
        $part1 = Str::random(5);
        $part2 = Str::random(5);
        $order = Order::factory()
            ->assignedStatus()
            ->has(
                Payment::factory()
                    ->broker(100.0)
                    ->brokerInvoice(Str::random(5) . $part1 . Str::random(5)),
                'payment'
            )
            ->shipperFullName($this->faker->company)
            ->create();
        Order::factory()
            ->assignedStatus()
            ->has(
                Payment::factory()
                    ->broker(100.0)
                    ->brokerInvoice(Str::random(5) . $part2 . Str::random(5)),
                'payment'
            )
            ->shipperFullName($order->shipper_full_name)
            ->create();
        Order::factory()
            ->assignedStatus()
            ->has(
                Payment::factory()
                    ->broker(100.0)
                    ->brokerInvoice(Str::random(5) . $part1 . Str::random(5)),
                'payment'
            )
            ->create();
        $this->makeDocuments(true);
        $result = CompanyDocument::filter(['invoice' => $part1])
            ->search();
        $this->assertCount(2, $result);
        $result = CompanyDocument::filter(['invoice' => $part2])
            ->search();
        $this->assertCount(1, $result);
    }

    /**
     * @return void
     * @throws DocumentFilterMethodNotFoundException
     */
    public function test_payment_method_filter(): void
    {
        $order = Order::factory()
            ->assignedStatus()
            ->has(
                Payment::factory()
                    ->broker(null, Payment::METHOD_CASH),
                'payment'
            )
            ->shipperFullName($this->faker->company)
            ->create();
        Order::factory()
            ->assignedStatus()
            ->has(
                Payment::factory()
                    ->broker(null, Payment::METHOD_QUICKPAY),
                'payment'
            )
            ->shipperFullName($order->shipper_full_name)
            ->create();
        Order::factory()
            ->assignedStatus()
            ->has(
                Payment::factory()
                    ->broker(null, Payment::METHOD_CASH),
                'payment'
            )
            ->create();
        $this->makeDocuments(true);
        $result = CompanyDocument::filter(['payment_method_id' => Payment::METHOD_CASH])
            ->search();
        $this->assertCount(2, $result);
        $result = CompanyDocument::filter(['payment_method_id' => Payment::METHOD_QUICKPAY])
            ->search();
        $this->assertCount(1, $result);
    }

    /**
     * @return void
     * @throws DocumentFilterMethodNotFoundException
     */
    public function test_invoice_send_date_filter(): void
    {
        $order = Order::factory()
            ->assignedStatus()
            ->has(
                Payment::factory(['broker_payment_invoice_issue_date' => Carbon::now()->subDays(2)->getTimestamp()])
                    ->broker(),
                'payment'
            )
            ->shipperFullName($this->faker->company)
            ->create();
        Order::factory()
            ->assignedStatus()
            ->has(
                Payment::factory(['broker_payment_invoice_issue_date' => Carbon::now()->subDays(4)->getTimestamp()])
                    ->broker(),
                'payment'
            )
            ->shipperFullName($order->shipper_full_name)
            ->create();
        Order::factory()
            ->assignedStatus()
            ->has(
                Payment::factory(['broker_payment_invoice_issue_date' => Carbon::now()->subDay()->getTimestamp()])
                    ->broker(null, Payment::METHOD_CASH),
                'payment'
            )
            ->create();
        $this->makeDocuments(true);
        $result = CompanyDocument::filter(
            [
                'invoice_send_date' => [
                    'from' => Carbon::now()->subDays(3)->startOfDay(),
                    'to' => Carbon::now()->endOfDay(),
                ]
            ]
        )
            ->search();
        $this->assertCount(2, $result);
        $result = CompanyDocument::filter(
            [
                'invoice_send_date' => [
                    'from' => Carbon::now()->subDays(5)->startOfDay(),
                    'to' => Carbon::now()->subDays(4)->endOfDay(),
                ]
            ]
        )->search();
        $this->assertCount(1, $result);
    }
}
