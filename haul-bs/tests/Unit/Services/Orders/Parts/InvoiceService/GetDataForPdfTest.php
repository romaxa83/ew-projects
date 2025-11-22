<?php

namespace Tests\Unit\Services\Orders\Parts\InvoiceService;

use App\Enums\Orders\Parts\DeliveryType;
use App\Enums\Orders\Parts\PaymentMethod;
use App\Foundations\Modules\Location\Models\State;
use App\Models\Inventories\Inventory;
use App\Models\Orders\Parts\Order;
use App\Services\Orders\Parts\InvoiceService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Inventories\InventoryBuilder;
use Tests\Builders\Orders\Parts\ItemBuilder;
use Tests\Builders\Orders\Parts\OrderBuilder;
use Tests\Builders\Orders\Parts\PaymentBuilder;
use Tests\TestCase;
use Tests\Traits\SettingsData;

class GetDataForPdfTest extends TestCase
{
    use DatabaseTransactions;
    use SettingsData;


    protected OrderBuilder $orderBuilder;
    protected InventoryBuilder $inventoryBuilder;
    protected ItemBuilder $itemBuilder;
    protected PaymentBuilder $paymentBuilder;

    public function setUp(): void
    {
        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->inventoryBuilder = resolve(InventoryBuilder::class);
        $this->itemBuilder = resolve(ItemBuilder::class);
        $this->paymentBuilder = resolve(PaymentBuilder::class);

        parent::setUp();
    }

    /** @test */
    public function check_data()
    {
        $settingData = $this->setSettings();

        /** @var $inventory Inventory */
        $inventory = $this->inventoryBuilder->quantity(10)->create();
        /** @var $model Order */
        $model = $this->orderBuilder->delivery_type(DeliveryType::Delivery)->create();
        $item = $this->itemBuilder
            ->order($model)
            ->inventory($inventory)
            ->qty(2)
            ->create();

        /** @var $service InvoiceService */
        $service = resolve(InvoiceService::class);

        $data = $service->getDataForPdf($model, now());

        $this->assertEquals($data['state'], State::find($settingData['ecommerce_state_id'])->name);
        $this->assertEquals($data['settings']['company_name'], $settingData['ecommerce_company_name']);
        $this->assertEquals($data['settings']['address'], $settingData['ecommerce_address']);
        $this->assertEquals($data['settings']['city'], $settingData['ecommerce_city']);
        $this->assertEquals($data['settings']['zip'], $settingData['ecommerce_zip']);
        $this->assertEquals($data['settings']['email'], $settingData['ecommerce_email']);
        $this->assertEquals($data['settings']['phone'], $settingData['ecommerce_phone']);
        $this->assertEquals($data['settings']['payment_details'], $settingData['ecommerce_billing_payment_details']);
        $this->assertEquals(
            $data['settings']['payment_options'],
            $settingData['ecommerce_billing_payment_options']
        );
        $this->assertEquals($data['settings']['terms_and_conditions'], $settingData['ecommerce_billing_terms']);

        $this->assertFalse($data['order']['is_pickup']);
        $this->assertEquals($data['order']['number'], $model->order_number);
        $this->assertEquals($data['order']['billing_address'], $model->billing_address);
        $this->assertEquals($data['order']['delivery_address'], $model->delivery_address);
        $this->assertEquals($data['order']['payment']['method'], $model->payment_method->label());
        $this->assertEquals($data['order']['payment']['terms'], $model->payment_terms->value);
        $this->assertEquals($data['order']['is_paid'], $model->is_paid);
        $this->assertEquals($data['order']['paid_at'], $model->paid_at);
        $this->assertEquals($data['order']['inventory_amount'], $model->getTotalOnlyItems());
        $this->assertEquals($data['order']['tax_amount'], $model->getTax());
        $this->assertEquals($data['order']['total_amount'], $model->getAmount());
        $this->assertEquals($data['order']['subtotal_amount'], $model->getSubtotal());

        $this->assertEquals($data['order']['customer']['name'], $model->customer->full_name);
        $this->assertEquals($data['order']['customer']['phone'], $model->customer->phone->getValue());
        $this->assertEquals($data['order']['customer']['email'], $model->customer->email->getValue());


        $this->assertEquals(
            $data['order']['items'][0]['stock_number'],
            $model->items[0]->inventory->stock_number
        );
        $this->assertEquals(
            $data['order']['items'][0]['name'],
            $model->items[0]->inventory->brand->name . ' ' . $model->items[0]->inventory->name
        );
        $this->assertEquals(
            $data['order']['items'][0]['price'],
            $model->items[0]->price
        );
        $this->assertEquals(
            $data['order']['items'][0]['qty'],
            $model->items[0]->qty
        );
        $this->assertEquals(
            $data['order']['items'][0]['total'],
            $model->items[0]->price * $model->items[0]->qty
        );
    }

    /** @test */
    public function check_data_payment_method()
    {
        $settingData = $this->setSettings();

        /** @var $inventory Inventory */
        $inventory = $this->inventoryBuilder->quantity(10)->create();
        /** @var $model Order */
        $model = $this->orderBuilder->delivery_type(DeliveryType::Delivery)->create();
        $item = $this->itemBuilder
            ->order($model)
            ->inventory($inventory)
            ->qty(2)
            ->create();

        $payment_1 = $this->paymentBuilder->method(PaymentMethod::ACH)->order($model)->create();
        $payment_2 = $this->paymentBuilder->method(PaymentMethod::Wire)->order($model)->create();

        /** @var $service InvoiceService */
        $service = resolve(InvoiceService::class);

        $data = $service->getDataForPdf($model, now());

        $this->assertEquals($data['state'], State::find($settingData['ecommerce_state_id'])->name);
        $this->assertEquals($data['settings']['company_name'], $settingData['ecommerce_company_name']);
        $this->assertEquals($data['settings']['address'], $settingData['ecommerce_address']);
        $this->assertEquals($data['settings']['city'], $settingData['ecommerce_city']);
        $this->assertEquals($data['settings']['zip'], $settingData['ecommerce_zip']);
        $this->assertEquals($data['settings']['email'], $settingData['ecommerce_email']);
        $this->assertEquals($data['settings']['phone'], $settingData['ecommerce_phone']);
        $this->assertEquals($data['settings']['payment_details'], $settingData['ecommerce_billing_payment_details']);
        $this->assertEquals(
            $data['settings']['payment_options'],
            $settingData['ecommerce_billing_payment_options']
        );
        $this->assertEquals($data['settings']['terms_and_conditions'], $settingData['ecommerce_billing_terms']);

        $this->assertFalse($data['order']['is_pickup']);
        $this->assertEquals($data['order']['number'], $model->order_number);
        $this->assertEquals($data['order']['billing_address'], $model->billing_address);
        $this->assertEquals($data['order']['delivery_address'], $model->delivery_address);
        $this->assertEquals($data['order']['payment']['method'], $payment_1->payment_method->label().','.$payment_2->payment_method->label());
        $this->assertEquals($data['order']['payment']['terms'], $model->payment_terms->value);
        $this->assertEquals($data['order']['is_paid'], $model->is_paid);
        $this->assertEquals($data['order']['paid_at'], $model->paid_at);
        $this->assertEquals($data['order']['inventory_amount'], $model->getTotalOnlyItems());
        $this->assertEquals($data['order']['tax_amount'], $model->getTax());
        $this->assertEquals($data['order']['total_amount'], $model->getAmount());
        $this->assertEquals($data['order']['save_amount'], $model->getSavingAmount());
        $this->assertEquals($data['order']['subtotal_amount'], $model->getSubtotal());

        $this->assertEquals($data['order']['customer']['name'], $model->customer->full_name);
        $this->assertEquals($data['order']['customer']['phone'], $model->customer->phone->getValue());
        $this->assertEquals($data['order']['customer']['email'], $model->customer->email->getValue());


        $this->assertEquals(
            $data['order']['items'][0]['stock_number'],
            $model->items[0]->inventory->stock_number
        );
        $this->assertEquals(
            $data['order']['items'][0]['name'],
            $model->items[0]->inventory->brand->name . ' ' . $model->items[0]->inventory->name
        );
        $this->assertEquals(
            $data['order']['items'][0]['price'],
            $model->items[0]->price
        );
        $this->assertEquals(
            $data['order']['items'][0]['qty'],
            $model->items[0]->qty
        );
        $this->assertEquals(
            $data['order']['items'][0]['total'],
            $model->items[0]->price * $model->items[0]->qty
        );
    }

    /** @test */
    public function check_data_if_not_customer_but_exist_client()
    {
        /** @var $model Order */
        $model = $this->orderBuilder
            ->customer(null)
            ->delivery_type(DeliveryType::Pickup)
            ->ecommerce_client()
            ->create();

        /** @var $service InvoiceService */
        $service = resolve(InvoiceService::class);

        $data = $service->getDataForPdf($model, now());

        $this->assertEquals(
            $data['order']['customer']['name'],
            $model->ecommerce_client->getFullNameAttribute()
        );
        $this->assertEquals(
            $data['order']['customer']['email'],
            $model->ecommerce_client->email->getValue()
        );
        $this->assertNull($data['order']['customer']['phone']);

        $this->assertTrue($data['order']['is_pickup']);
    }

    /** @test */
    public function check_data_if_not_customer()
    {
        /** @var $model Order */
        $model = $this->orderBuilder
            ->customer(null)
            ->create();

        /** @var $service InvoiceService */
        $service = resolve(InvoiceService::class);

        $data = $service->getDataForPdf($model, now());
        $this->assertNull($data['order']['customer']['name']);
        $this->assertNull($data['order']['customer']['email']);
        $this->assertNull($data['order']['customer']['phone']);
    }
}
