<?php

namespace Tests\Feature\Http\Api\OneC\Dealers\Order;

use App\Models\Orders\Dealer\Order;
use App\Models\Orders\Dealer\PackingSlip;
use App\Services\Orders\Dealer\PackingSlipService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery\MockInterface;
use Tests\Builders\Orders\Dealer\OrderBuilder;
use Tests\Builders\Orders\Dealer\PackingSlipBuilder;
use Tests\TestCase;

class AddInvoiceDataTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    protected OrderBuilder $orderBuilder;
    protected PackingSlipBuilder $packingSlipBuilder;


    public function setUp(): void
    {
        parent::setUp();
        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->packingSlipBuilder = resolve(PackingSlipBuilder::class);
    }

    /** @test */
    public function add_to_packing_slip_only_invoice_data(): void
    {
        $this->loginAsModerator();

        /** @var $order Order */
        $order = $this->orderBuilder->create();
        /** @var $packingSlip PackingSlip */
        $packingSlip = $this->packingSlipBuilder->setOrder($order)->create();

        $data = $this->data();
        $data['packing_slip_guid'] = $packingSlip->guid;
        $data['order_guid'] = null;

        $this->assertNotEquals($packingSlip->invoice, data_get($data, 'invoice'));
        $this->assertNotEquals($packingSlip->invoice_at->format('Y-m-d'), data_get($data, 'invoice_date'));

        $this->postJson(
            route('1c.dealer-order.add-invoice-data'), $data
        )
            ->assertOk()
            ->assertJson([
                'data' => 'Done',
                'success' => true
            ]);

        $packingSlip->refresh();

        $this->assertEquals($packingSlip->invoice, data_get($data, 'invoice'));
        $this->assertEquals($packingSlip->invoice_at->format('Y-m-d'), data_get($data, 'invoice_date'));
        $this->assertEquals($packingSlip->tax, 0);
        $this->assertEquals($packingSlip->shipping_price, 0);
        $this->assertEquals($packingSlip->total, 0);
        $this->assertEquals($packingSlip->total_with_discount, 0);
        $this->assertEquals($packingSlip->total_discount, 0);

        $this->assertEquals(
            $packingSlip->getInvoiceFileStorageUrl(),
            url("storage/" . PackingSlip::PDF_FILE_GENERATE_DIR . "/Order-{$order->id}-packing-slim-{$packingSlip->id}-invoice.pdf")
        );

        unlink($packingSlip->getInvoiceFileStoragePath());
    }

    /** @test */
    public function add_to_packing_slip_all_data(): void
    {
        $this->loginAsModerator();

        /** @var $order Order */
        $order = $this->orderBuilder->create();
        /** @var $packingSlip PackingSlip */
        $packingSlip = $this->packingSlipBuilder->setOrder($order)->create();

        $data = $this->data();
        $data['packing_slip_guid'] = $packingSlip->guid;
        $data['order_guid'] = null;
        $data['tax'] = 0.9;
        $data['shipping_price'] = 10.9;
        $data['total'] = 9;
        $data['total_discount'] = 5;
        $data['total_with_discount'] = 2;

        $this->assertNotEquals($packingSlip->invoice, data_get($data, 'invoice'));
        $this->assertNotEquals($packingSlip->invoice_at->format('Y-m-d'), data_get($data, 'invoice_date'));
        $this->assertNotEquals($packingSlip->tax, data_get($data, 'tax'));
        $this->assertNotEquals($packingSlip->shipping_price, data_get($data, 'shipping_price'));
        $this->assertNotEquals($packingSlip->total, data_get($data, 'total'));
        $this->assertNotEquals($packingSlip->total_discount, data_get($data, 'total_discount'));
        $this->assertNotEquals($packingSlip->total_with_discount, data_get($data, 'total_with_discount'));

        $this->postJson(
            route('1c.dealer-order.add-invoice-data'), $data
        )
            ->assertOk()
            ->assertJson([
                'data' => 'Done',
                'success' => true
            ]);

        $packingSlip->refresh();

        $this->assertEquals($packingSlip->invoice, data_get($data, 'invoice'));
        $this->assertEquals($packingSlip->invoice_at->format('Y-m-d'), data_get($data, 'invoice_date'));
        $this->assertEquals($packingSlip->tax, data_get($data, 'tax'));
        $this->assertEquals($packingSlip->shipping_price, data_get($data, 'shipping_price'));
        $this->assertEquals($packingSlip->total, data_get($data, 'total'));
        $this->assertEquals($packingSlip->total_with_discount, data_get($data, 'total_with_discount'));
        $this->assertEquals($packingSlip->total_discount, data_get($data, 'total_discount'));

        $this->assertEquals(
            $packingSlip->getInvoiceFileStorageUrl(),
            url("storage/" . PackingSlip::PDF_FILE_GENERATE_DIR . "/Order-{$order->id}-packing-slim-{$packingSlip->id}-invoice.pdf")
        );

        unlink($packingSlip->getInvoiceFileStoragePath());
    }

    /** @test */
    public function add_to_order_only_invoice_data(): void
    {
        $this->loginAsModerator();

        /** @var $order Order */
        $order = $this->orderBuilder->create();

        $data = $this->data();
        $data['order_guid'] = $order->guid;
        $data['packing_slip_guid'] = null;

        $this->assertNotEquals($order->invoice, data_get($data, 'invoice'));
        $this->assertNotEquals($order->invoice_at?->format('Y-m-d'), data_get($data, 'invoice_date'));
        $this->assertFalse($order->has_invoice);

        $this->postJson(
            route('1c.dealer-order.add-invoice-data'), $data
        )
            ->assertOk()
            ->assertJson([
                'data' => 'Done',
                'success' => true
            ]);

        $order->refresh();

        $this->assertEquals($order->invoice, data_get($data, 'invoice'));
        $this->assertEquals($order->invoice_at->format('Y-m-d'), data_get($data, 'invoice_date'));
        $this->assertTrue($order->has_invoice);
        $this->assertEquals($order->tax, 0);
        $this->assertEquals($order->shipping_price, 0);
        $this->assertEquals($order->total, 0);
        $this->assertEquals($order->total_with_discount, 0);
        $this->assertEquals($order->total_discount, 0);
    }

    /** @test */
    public function add_to_order_data_with_price(): void
    {
        $this->loginAsModerator();

        /** @var $order Order */
        $order = $this->orderBuilder->create();

        $data = $this->data();
        $data['order_guid'] = $order->guid;
        $data['packing_slip_guid'] = null;
        $data['tax'] = 0.9;
        $data['shipping_price'] = 10.9;
        $data['total'] = 9;
        $data['total_discount'] = 5;
        $data['total_with_discount'] = 2;

        $this->assertNotEquals($order->invoice, data_get($data, 'invoice'));
        $this->assertNotEquals($order->invoice_at?->format('Y-m-d'), data_get($data, 'invoice_date'));
        $this->assertFalse($order->has_invoice);
        $this->assertNotEquals($order->tax, data_get($data, 'tax'));
        $this->assertNotEquals($order->shipping_price, data_get($data, 'shipping_price'));
        $this->assertNotEquals($order->total, data_get($data, 'total'));
        $this->assertNotEquals($order->total_discount, data_get($data, 'total_discount'));
        $this->assertNotEquals($order->total_with_discount, data_get($data, 'total_with_discount'));

        $this->postJson(
            route('1c.dealer-order.add-invoice-data'), $data
        )
            ->assertOk()
            ->assertJson([
                'data' => 'Done',
                'success' => true
            ])
        ;

        $order->refresh();

        $this->assertEquals($order->invoice, data_get($data, 'invoice'));
        $this->assertEquals($order->invoice_at->format('Y-m-d'), data_get($data, 'invoice_date'));
        $this->assertTrue($order->has_invoice);
        $this->assertEquals($order->tax, data_get($data, 'tax'));
        $this->assertEquals($order->shipping_price, data_get($data, 'shipping_price'));
        $this->assertEquals($order->total, data_get($data, 'total'));
        $this->assertEquals($order->total_discount, data_get($data, 'total_discount'));
        $this->assertEquals($order->total_with_discount, data_get($data, 'total_with_discount'));
    }

    /** @test */
    public function fail_not_order_or_packing_slip_guid(): void
    {
        $this->loginAsModerator();

        $data = $this->data();
        $data['order_guid'] = null;
        $data['packing_slip_guid'] = null;

        $this->postJson(
            route('1c.dealer-order.add-invoice-data'), $data
        )
            ->assertJson([
                'category' => 'validation',
            ]);
    }

    /** @test */
    public function fail_not_found_order(): void
    {
        $this->loginAsModerator();

        $guid = '6586ugyugf76567896876';

        $data = $this->data();
        $data['order_guid'] = $guid;
        $data['packing_slip_guid'] = null;

        $this->postJson(
            route('1c.dealer-order.add-invoice-data'), $data
        )
            ->assertJson([
                'data' => __('exceptions.dealer.order.not found by guid', ['guid' => $guid]),
                'success' => false
            ])
        ;
    }

    /** @test */
    public function fail_not_found_packing_slip(): void
    {
        $this->loginAsModerator();

        $guid = '6586ugyugf76567896876';

        $data = $this->data();
        $data['order_guid'] = null;
        $data['packing_slip_guid'] = $guid;

        $this->postJson(
            route('1c.dealer-order.add-invoice-data'), $data
        )
            ->assertJson([
                'data' => __('exceptions.dealer.order.packing_slip.not found by guid', ['guid' => $guid]),
                'success' => false
            ])
        ;
    }

    /** @test */
    public function fail_something_wrong_to_service(): void
    {
        $this->loginAsModerator();

        /** @var $order Order */
        $order = $this->orderBuilder->create();
        /** @var $packingSlip PackingSlip */
        $packingSlip = $this->packingSlipBuilder->setOrder($order)->create();

        $data = $this->data();
        $data['packing_slip_guid'] = $packingSlip->guid;
        $data['order_guid'] = null;

        $this->mock(PackingSlipService::class, function(MockInterface $mock){
            $mock->shouldReceive("addOrUpdatePackingSlipInvoice")
                ->andThrows(\Exception::class, "some exception message");
        });

        $this->postJson(
            route('1c.dealer-order.add-invoice-data'), $data
        )
            ->assertStatus(500)
            ->assertJson([
                'data' => "some exception message",
                'success' => false
            ]);
    }

    private function data(): array
    {
        return [
            'order_guid' => $this->faker->uuid,
            'packing_slip_guid' => $this->faker->uuid,
            'invoice' => $this->faker->creditCardNumber,
            'invoice_date' => CarbonImmutable::now()->addDay()->format('Y-m-d'),
        ];
    }
}
