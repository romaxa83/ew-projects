<?php

namespace Tests\Feature\Http\Api\OneC\Dealers\Order;

use App\Enums\Orders\Dealer\OrderStatus;
use App\Models\Orders\Dealer\Order;
use App\Models\Orders\Dealer\PackingSlip;
use App\Services\Orders\Dealer\PackingSlipService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery\MockInterface;
use Tests\Builders\Catalog\ProductBuilder;
use Tests\Builders\Orders\Dealer\DimensionsBuilder;
use Tests\Builders\Orders\Dealer\ItemBuilder;
use Tests\Builders\Orders\Dealer\OrderBuilder;
use Tests\Builders\Orders\Dealer\PackingSlipBuilder;
use Tests\Builders\Orders\Dealer\PackingSlipItemBuilder;
use Tests\TestCase;

class AddPackingSlipTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    protected OrderBuilder $orderBuilder;
    protected PackingSlipBuilder $packingSlipBuilder;
    protected DimensionsBuilder $dimensionsBuilder;
    protected ProductBuilder $productBuilder;
    protected ItemBuilder $itemBuilder;
    protected PackingSlipItemBuilder $packingSlipItemBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->packingSlipBuilder = resolve(PackingSlipBuilder::class);
        $this->dimensionsBuilder = resolve(DimensionsBuilder::class);
        $this->productBuilder = resolve(ProductBuilder::class);
        $this->itemBuilder = resolve(ItemBuilder::class);
        $this->packingSlipItemBuilder = resolve(PackingSlipItemBuilder::class);
    }

    /** @test */
    public function add_packing_slip(): void
    {
        $this->loginAsModerator();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $this->assertEmpty($model->packingSlips);

        $product_1 = $this->productBuilder->create();
        $product_2 = $this->productBuilder->create();
        $product_3 = $this->productBuilder->create();

        $item_1 = $this->itemBuilder->setProduct($product_1)->setOrder($model)->create();
        $item_2 = $this->itemBuilder->setProduct($product_2)->setOrder($model)->create();
        $item_3 = $this->itemBuilder->setProduct($product_3)->setOrder($model)->create();

        $data = $this->data();
        $data['data'][0]['products'] = [
            [
                'guid' => $product_1->guid,
                'qty' => 5,
                'description' => $this->faker->sentence,
            ],
            [
                'guid' => $product_2->guid,
                'qty' => 8,
                'description' => $this->faker->sentence,
            ],
        ];
        $data['data'][1]['products'] = [
            [
                'guid' => $product_3->guid,
                'qty' => 5,
                'description' => $this->faker->sentence,
            ],
        ];

        $this->postJson(
            route('1c.dealer-order.add-or-update-packing-slip', ['guid' => $model->guid]), $data
        )
            ->assertOk()
            ->assertJson([
                'data' => 'Done',
                'success' => true
            ]);

        $model->refresh();

        $this->assertCount(2, $model->packingSlips);

        $this->assertEquals($model->packingSlips[0]->guid, data_get($data, 'data.0.guid'));
        $this->assertEquals($model->packingSlips[0]->status, data_get($data, 'data.0.status'));
        $this->assertEquals($model->packingSlips[0]->number, data_get($data, 'data.0.number'));
        $this->assertEquals($model->packingSlips[0]->tracking_number, data_get($data, 'data.0.tracking_number'));
        $this->assertEquals($model->packingSlips[0]->tracking_company, data_get($data, 'data.0.tracking_company'));
        $this->assertEquals($model->packingSlips[0]->shipped_at->format('Y-m-d'), data_get($data, 'data.0.shipped_date'));

        $this->assertEquals($model->packingSlips[0]->items[0]->product->id, $product_1->id);
        $this->assertEquals($model->packingSlips[0]->items[0]->qty, data_get($data, 'data.0.products.0.qty'));
        $this->assertEquals($model->packingSlips[0]->items[0]->description, data_get($data, 'data.0.products.0.description'));
        $this->assertEquals($model->packingSlips[0]->items[0]->orderItem->id, $item_1->id);
        $this->assertEquals($model->packingSlips[0]->items[1]->product->id, $product_2->id);
        $this->assertEquals($model->packingSlips[0]->items[1]->qty, data_get($data, 'data.0.products.1.qty'));
        $this->assertEquals($model->packingSlips[0]->items[1]->description, data_get($data, 'data.0.products.1.description'));
        $this->assertEquals($model->packingSlips[0]->items[1]->orderItem->id, $item_2->id);

        $this->assertEquals($model->packingSlips[0]->dimensions[0]->pallet, data_get($data, 'data.0.dimensions.0.pallet'));
        $this->assertEquals($model->packingSlips[0]->dimensions[0]->box_qty, data_get($data, 'data.0.dimensions.0.box_qty'));
        $this->assertEquals($model->packingSlips[0]->dimensions[0]->type, data_get($data, 'data.0.dimensions.0.type'));
        $this->assertEquals($model->packingSlips[0]->dimensions[0]->weight, data_get($data, 'data.0.dimensions.0.weight'));
        $this->assertEquals($model->packingSlips[0]->dimensions[0]->width, data_get($data, 'data.0.dimensions.0.width'));
        $this->assertEquals($model->packingSlips[0]->dimensions[0]->depth, data_get($data, 'data.0.dimensions.0.depth'));
        $this->assertEquals($model->packingSlips[0]->dimensions[0]->height, data_get($data, 'data.0.dimensions.0.height'));
        $this->assertEquals($model->packingSlips[0]->dimensions[0]->class_freight, data_get($data, 'data.0.dimensions.0.class_freight'));
        $this->assertEquals($model->packingSlips[0]->dimensions[1]->pallet, data_get($data, 'data.0.dimensions.1.pallet'));
        $this->assertEquals($model->packingSlips[0]->dimensions[1]->box_qty, data_get($data, 'data.0.dimensions.1.box_qty'));
        $this->assertEquals($model->packingSlips[0]->dimensions[1]->type, data_get($data, 'data.0.dimensions.1.type'));
        $this->assertEquals($model->packingSlips[0]->dimensions[1]->weight, data_get($data, 'data.0.dimensions.1.weight'));
        $this->assertEquals($model->packingSlips[0]->dimensions[1]->width, data_get($data, 'data.0.dimensions.1.width'));
        $this->assertEquals($model->packingSlips[0]->dimensions[1]->depth, data_get($data, 'data.0.dimensions.1.depth'));
        $this->assertEquals($model->packingSlips[0]->dimensions[1]->height, data_get($data, 'data.0.dimensions.1.height'));
        $this->assertEquals($model->packingSlips[0]->dimensions[1]->class_freight, data_get($data, 'data.0.dimensions.1.class_freight'));

        $this->assertEquals($model->packingSlips[1]->guid, data_get($data, 'data.1.guid'));
        $this->assertEquals($model->packingSlips[1]->status, data_get($data, 'data.1.status'));
        $this->assertEquals($model->packingSlips[1]->number, data_get($data, 'data.1.number'));
        $this->assertEquals($model->packingSlips[1]->tracking_number, data_get($data, 'data.1.tracking_number'));
        $this->assertEquals($model->packingSlips[1]->tracking_company, data_get($data, 'data.1.tracking_company'));
        $this->assertEquals($model->packingSlips[1]->shipped_at->format('Y-m-d'), data_get($data, 'data.1.shipped_date'));

        $this->assertEquals($model->packingSlips[1]->items[0]->product->id, $product_3->id);
        $this->assertEquals($model->packingSlips[1]->items[0]->qty, data_get($data, 'data.1.products.0.qty'));
        $this->assertEquals($model->packingSlips[1]->items[0]->description, data_get($data, 'data.1.products.0.description'));
        $this->assertEquals($model->packingSlips[1]->items[0]->orderItem->id, $item_3->id);

        $this->assertEquals($model->packingSlips[1]->dimensions[0]->pallet, data_get($data, 'data.1.dimensions.0.pallet'));
        $this->assertEquals($model->packingSlips[1]->dimensions[0]->box_qty, data_get($data, 'data.1.dimensions.0.box_qty'));
        $this->assertEquals($model->packingSlips[1]->dimensions[0]->type, data_get($data, 'data.1.dimensions.0.type'));
        $this->assertEquals($model->packingSlips[1]->dimensions[0]->weight, data_get($data, 'data.1.dimensions.0.weight'));
        $this->assertEquals($model->packingSlips[1]->dimensions[0]->width, data_get($data, 'data.1.dimensions.0.width'));
        $this->assertEquals($model->packingSlips[1]->dimensions[0]->depth, data_get($data, 'data.1.dimensions.0.depth'));
        $this->assertEquals($model->packingSlips[1]->dimensions[0]->height, data_get($data, 'data.1.dimensions.0.height'));
        $this->assertEquals($model->packingSlips[1]->dimensions[0]->class_freight, data_get($data, 'data.1.dimensions.0.class_freight'));

    }

    /** @test */
    public function add_packing_slip_but_not_generate_invoice(): void
    {
        $this->loginAsModerator();

        /** @var $model Order */
        $model = $this->orderBuilder->setData([
            'has_invoice' => true
        ])->create();

        $this->assertEmpty($model->packingSlips);

        $product_1 = $this->productBuilder->create();

        $item_1 = $this->itemBuilder->setProduct($product_1)->setOrder($model)->create();

        $data = $this->data();
        unset($data['data'][1]);
        $data['data'][0]['products'] = [
            [
                'guid' => $product_1->guid,
                'qty' => 5,
                'description' => $this->faker->sentence,
            ],
        ];

        $this->postJson(
            route('1c.dealer-order.add-or-update-packing-slip', ['guid' => $model->guid]), $data
        )
            ->assertOk()
            ->assertJson([
                'data' => 'Done',
                'success' => true
            ]);

        $model->refresh();

        $this->assertCount(1, $model->packingSlips);

        $this->assertEquals($model->packingSlips[0]->guid, data_get($data, 'data.0.guid'));
        $this->assertNull($model->packingSlips[0]->getInvoiceFileStorageUrl());
    }

    /** @test */
    public function add_new_packing_slip(): void
    {
        $this->loginAsModerator();

        /** @var $order Order */
        $order = $this->orderBuilder->create();

        $product_1 = $this->productBuilder->create();
        $product_2 = $this->productBuilder->create();
        $product_3 = $this->productBuilder->create();

        $item_1 = $this->itemBuilder->setProduct($product_1)->setOrder($order)->create();
        $item_2 = $this->itemBuilder->setProduct($product_2)->setOrder($order)->create();

        $packing_slip_1 = $this->packingSlipBuilder->setOrder($order)->create();


        $this->assertCount(1, $order->packingSlips);

        $data = $this->data();
        unset($data['data'][1]);
        $data['data'][0]['products'] = [
            [
                'guid' => $product_1->guid,
                'qty' => 5,
                'description' => null,
            ],
        ];

        $this->postJson(
            route('1c.dealer-order.add-or-update-packing-slip', ['guid' => $order->guid]), $data
        )
            ->assertOk()
            ->assertJson([
                'data' => 'Done',
                'success' => true
            ]);

        $order->refresh();

        if(file_exists($order->packingSlips[0]->getInvoiceFileStoragePath())){
            unlink($order->packingSlips[0]->getInvoiceFileStoragePath());
        }
        if(file_exists($order->packingSlips[1]->getInvoiceFileStoragePath())){
            unlink($order->packingSlips[1]->getInvoiceFileStoragePath());
        }
    }

    /** @test */
    public function add_only_required_fields(): void
    {
        $this->loginAsModerator();

        /** @var $order Order */
        $order = $this->orderBuilder->create();

        $product_1 = $this->productBuilder->create();

        $item_1 = $this->itemBuilder->setProduct($product_1)->setOrder($order)->create();

        $data = $this->data();
        unset(
            $data['data'][0]['tracking_number'],
            $data['data'][0]['tracking_company'],
            $data['data'][0]['shipped_date'],
            $data['data'][1]
        );
        $data['data'][0]['products'] = [
            [
                'guid' => $product_1->guid,
                'qty' => 5,
                'description' => null,
            ],
        ];
        $data['data'][0]['dimensions'] = [];

        $this->postJson(
            route('1c.dealer-order.add-or-update-packing-slip', ['guid' => $order->guid]), $data
        )
            ->assertOk()
            ->assertJson([
                'data' => 'Done',
                'success' => true
            ]);

        $order->refresh();

        $this->assertEquals($order->packingSlips[0]->guid, data_get($data, 'data.0.guid'));
        $this->assertNull($order->packingSlips[0]->tracking_number);
        $this->assertNull($order->packingSlips[0]->tracking_company);
        $this->assertNull($order->packingSlips[0]->shipped_date);
        $this->assertNull($order->packingSlips[0]->getInvoiceFileStorageUrl());
        $this->assertEmpty($order->packingSlips[0]->dimensions);
    }

    /** @test */
    public function update_packing_slip(): void
    {
        $this->loginAsModerator();

        /** @var $order Order */
        $order = $this->orderBuilder->create();

        $product_1 = $this->productBuilder->create();
        $product_2 = $this->productBuilder->create();

        $item_1 = $this->itemBuilder->setProduct($product_1)->setOrder($order)->create();
        $item_2 = $this->itemBuilder->setProduct($product_2)->setOrder($order)->create();

        /** @var $packing_slip_1 PackingSlip */
        $packing_slip_1 = $this->packingSlipBuilder->setOrder($order)->create();

        $dimension_1 = $this->dimensionsBuilder->setPackingSlip($packing_slip_1)->create();
        $dimension_2 = $this->dimensionsBuilder->setPackingSlip($packing_slip_1)->create();

        $packing_slip_item_1 = $this->packingSlipItemBuilder->setPackingSlip($packing_slip_1)
            ->setProduct($product_1)->create();

        $this->assertCount(1, $order->packingSlips);
        $this->assertCount(1, $order->packingSlips[0]->items);

        $data = $this->data();
        unset($data['data'][1]);
        $data['data'][0]['guid'] = $packing_slip_1->guid;
        $data['data'][0]['status'] = OrderStatus::SHIPPED;
        $data['data'][0]['products'] = [
            [
                'guid' => $product_1->guid,
                'qty' => 5,
                'description' => null,
            ],
            [
                'guid' => $product_2->guid,
                'qty' => 5,
                'description' => null,
            ],
        ];

        $this->assertEquals($packing_slip_1->order_id, $order->id);
        $this->assertEquals($packing_slip_1->guid, data_get($data, 'data.0.guid'));
        $this->assertFalse($packing_slip_1->status->isShipped());
        $this->assertNotEquals($packing_slip_1->number, data_get($data, 'data.0.number'));
        $this->assertNotEquals($packing_slip_1->tracking_number, data_get($data, 'data.0.tracking_number'));
        $this->assertNotEquals($packing_slip_1->tracking_company, data_get($data, 'data.0.tracking_company'));
        $this->assertNotEquals($packing_slip_1->shipped_at->format('Y-m-d'), data_get($data, 'data.0.shipped_date'));

        $this->assertNotEquals($packing_slip_1->dimensions[0]->pallet, data_get($data, 'data.0.dimensions.0.pallet'));
        $this->assertNotEquals($packing_slip_1->dimensions[0]->box_qty, data_get($data, 'data.0.dimensions.0.box_qty'));
        $this->assertNotEquals($packing_slip_1->dimensions[0]->weight, data_get($data, 'data.0.dimensions.0.weight'));
        $this->assertNotEquals($packing_slip_1->dimensions[0]->width, data_get($data, 'data.0.dimensions.0.width'));
        $this->assertNotEquals($packing_slip_1->dimensions[0]->depth, data_get($data, 'data.0.dimensions.0.depth'));
        $this->assertNotEquals($packing_slip_1->dimensions[0]->height, data_get($data, 'data.0.dimensions.0.height'));
        $this->assertNotEquals($packing_slip_1->dimensions[0]->class_freight, data_get($data, 'data.0.dimensions.0.class_freight'));

        $this->assertNotEquals($packing_slip_1->dimensions[1]->pallet, data_get($data, 'data.0.dimensions.1.pallet'));
        $this->assertNotEquals($packing_slip_1->dimensions[1]->box_qty, data_get($data, 'data.0.dimensions.1.box_qty'));
        $this->assertNotEquals($packing_slip_1->dimensions[1]->weight, data_get($data, 'data.0.dimensions.1.weight'));
        $this->assertNotEquals($packing_slip_1->dimensions[1]->width, data_get($data, 'data.0.dimensions.1.width'));
        $this->assertNotEquals($packing_slip_1->dimensions[1]->depth, data_get($data, 'data.0.dimensions.1.depth'));
        $this->assertNotEquals($packing_slip_1->dimensions[1]->height, data_get($data, 'data.0.dimensions.1.height'));
        $this->assertNotEquals($packing_slip_1->dimensions[1]->class_freight, data_get($data, 'data.0.dimensions.1.class_freight'));

        $this->assertEquals($packing_slip_1->items[0]->product->guid, data_get($data, 'data.0.products.0.guid'));
        $this->assertNotEquals($packing_slip_1->items[0]->qty, data_get($data, 'data.0.products.0.qty'));
        $this->assertNotEquals($packing_slip_1->items[0]->description, data_get($data, 'data.0.products.0.description'));

        $this->postJson(
            route('1c.dealer-order.add-or-update-packing-slip', ['guid' => $order->guid]), $data
        )
            ->assertOk()
            ->assertJson([
                'data' => 'Done',
                'success' => true
            ]);

        $packing_slip_1->refresh();
        $order->refresh();

        $this->assertCount(1, $order->packingSlips);
        $this->assertCount(2, $order->packingSlips[0]->items);

        $this->assertEquals($packing_slip_1->order_id, $order->id);
        $this->assertEquals($packing_slip_1->guid, data_get($data, 'data.0.guid'));
        $this->assertTrue($packing_slip_1->status->isShipped());
        $this->assertEquals($packing_slip_1->number, data_get($data, 'data.0.number'));
        $this->assertEquals($packing_slip_1->tracking_number, data_get($data, 'data.0.tracking_number'));
        $this->assertEquals($packing_slip_1->tracking_company, data_get($data, 'data.0.tracking_company'));
        $this->assertEquals($packing_slip_1->shipped_at->format('Y-m-d'), data_get($data, 'data.0.shipped_date'));

        $this->assertEquals($packing_slip_1->dimensions[0]->pallet, data_get($data, 'data.0.dimensions.0.pallet'));
        $this->assertEquals($packing_slip_1->dimensions[0]->box_qty, data_get($data, 'data.0.dimensions.0.box_qty'));
        $this->assertEquals($packing_slip_1->dimensions[0]->weight, data_get($data, 'data.0.dimensions.0.weight'));
        $this->assertEquals($packing_slip_1->dimensions[0]->width, data_get($data, 'data.0.dimensions.0.width'));
        $this->assertEquals($packing_slip_1->dimensions[0]->depth, data_get($data, 'data.0.dimensions.0.depth'));
        $this->assertEquals($packing_slip_1->dimensions[0]->height, data_get($data, 'data.0.dimensions.0.height'));
        $this->assertEquals($packing_slip_1->dimensions[0]->class_freight, data_get($data, 'data.0.dimensions.0.class_freight'));

        $this->assertEquals($packing_slip_1->dimensions[1]->pallet, data_get($data, 'data.0.dimensions.1.pallet'));
        $this->assertEquals($packing_slip_1->dimensions[1]->box_qty, data_get($data, 'data.0.dimensions.1.box_qty'));
        $this->assertEquals($packing_slip_1->dimensions[1]->weight, data_get($data, 'data.0.dimensions.1.weight'));
        $this->assertEquals($packing_slip_1->dimensions[1]->width, data_get($data, 'data.0.dimensions.1.width'));
        $this->assertEquals($packing_slip_1->dimensions[1]->depth, data_get($data, 'data.0.dimensions.1.depth'));
        $this->assertEquals($packing_slip_1->dimensions[1]->height, data_get($data, 'data.0.dimensions.1.height'));
        $this->assertEquals($packing_slip_1->dimensions[1]->class_freight, data_get($data, 'data.0.dimensions.1.class_freight'));

        $this->assertEquals($packing_slip_1->items[0]->product->guid, data_get($data, 'data.0.products.0.guid'));
        $this->assertEquals($packing_slip_1->items[0]->qty, data_get($data, 'data.0.products.0.qty'));
        $this->assertEquals($packing_slip_1->items[0]->description, data_get($data, 'data.0.products.0.description'));

        $this->assertEquals($packing_slip_1->items[1]->product->guid, data_get($data, 'data.0.products.1.guid'));
        $this->assertEquals($packing_slip_1->items[1]->qty, data_get($data, 'data.0.products.1.qty'));
        $this->assertEquals($packing_slip_1->items[1]->description, data_get($data, 'data.0.products.1.description'));

        if(file_exists($packing_slip_1->getInvoiceFileStoragePath())){
            unlink($packing_slip_1->getInvoiceFileStoragePath());
        }
    }

    /** @test */
    public function update_dimension(): void
    {
        $this->loginAsModerator();

        /** @var $order Order */
        $order = $this->orderBuilder->create();

        $product_1 = $this->productBuilder->create();
        $product_2 = $this->productBuilder->create();

        $item_1 = $this->itemBuilder->setProduct($product_1)->setOrder($order)->create();
        $item_2 = $this->itemBuilder->setProduct($product_2)->setOrder($order)->create();

        /** @var $packing_slip_1 PackingSlip */
        $packing_slip_1 = $this->packingSlipBuilder->setOrder($order)->create();

        $dimension_1 = $this->dimensionsBuilder->setPackingSlip($packing_slip_1)->create();
        $dimension_2 = $this->dimensionsBuilder->setPackingSlip($packing_slip_1)->create();

        $packing_slip_item_1 = $this->packingSlipItemBuilder->setPackingSlip($packing_slip_1)
            ->setProduct($product_1)->create();

        $this->assertCount(1, $order->packingSlips);
        $this->assertCount(1, $order->packingSlips[0]->items);

        $data = $this->data();
        unset(
            $data['data'][1],
            $data['data'][0]['dimensions'][1]
        );
        $data['data'][0]['guid'] = $packing_slip_1->guid;
        $data['data'][0]['status'] = OrderStatus::SHIPPED;
        $data['data'][0]['products'] = [
            [
                'guid' => $product_1->guid,
                'qty' => 5,
                'description' => null,
            ],
        ];

        $this->assertCount(2, $packing_slip_1->dimensions);

        $this->assertNotEquals($packing_slip_1->dimensions[0]->pallet, data_get($data, 'data.0.dimensions.0.pallet'));
        $this->assertNotEquals($packing_slip_1->dimensions[0]->box_qty, data_get($data, 'data.0.dimensions.0.box_qty'));
        $this->assertNotEquals($packing_slip_1->dimensions[0]->weight, data_get($data, 'data.0.dimensions.0.weight'));
        $this->assertNotEquals($packing_slip_1->dimensions[0]->width, data_get($data, 'data.0.dimensions.0.width'));
        $this->assertNotEquals($packing_slip_1->dimensions[0]->depth, data_get($data, 'data.0.dimensions.0.depth'));
        $this->assertNotEquals($packing_slip_1->dimensions[0]->height, data_get($data, 'data.0.dimensions.0.height'));
        $this->assertNotEquals($packing_slip_1->dimensions[0]->class_freight, data_get($data, 'data.0.dimensions.0.class_freight'));

        $this->postJson(
            route('1c.dealer-order.add-or-update-packing-slip', ['guid' => $order->guid]), $data
        )
            ->assertOk()
            ->assertJson([
                'data' => 'Done',
                'success' => true
            ]);

        $packing_slip_1->refresh();
        $order->refresh();

        $this->assertCount(1, $packing_slip_1->dimensions);

        $this->assertEquals($packing_slip_1->dimensions[0]->pallet, data_get($data, 'data.0.dimensions.0.pallet'));
        $this->assertEquals($packing_slip_1->dimensions[0]->box_qty, data_get($data, 'data.0.dimensions.0.box_qty'));
        $this->assertEquals($packing_slip_1->dimensions[0]->weight, data_get($data, 'data.0.dimensions.0.weight'));
        $this->assertEquals($packing_slip_1->dimensions[0]->width, data_get($data, 'data.0.dimensions.0.width'));
        $this->assertEquals($packing_slip_1->dimensions[0]->depth, data_get($data, 'data.0.dimensions.0.depth'));
        $this->assertEquals($packing_slip_1->dimensions[0]->height, data_get($data, 'data.0.dimensions.0.height'));
        $this->assertEquals($packing_slip_1->dimensions[0]->class_freight, data_get($data, 'data.0.dimensions.0.class_freight'));

        if(file_exists($packing_slip_1->getInvoiceFileStoragePath())){
            unlink($packing_slip_1->getInvoiceFileStoragePath());
        }
    }

    /** @test */
    public function fail_not_item_into_order(): void
    {
        $this->loginAsModerator();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $this->assertEmpty($model->packingSlips);

        $product_1 = $this->productBuilder->create();

        $data = $this->data();
        unset($data['data'][1]);
        $data['data'][0]['products'] = [
            [
                'guid' => $product_1->guid,
                'qty' => 5,
                'description' => $this->faker->sentence,
            ],
        ];

        $this->postJson(
            route('1c.dealer-order.add-or-update-packing-slip', ['guid' => $model->guid]), $data
        )
            ->assertJson([
                'data' => "There is no product [guid - {$product_1->guid}] in the order [guid - {$model->guid}]",
                'success' => false
            ]);
    }

    /** @test */
    public function fail_empty_post_data(): void
    {
        $this->loginAsModerator();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $data = [];

        $this->postJson(
            route('1c.dealer-order.add-or-update-packing-slip', ['guid' => $model->guid]), $data
        )
            ->assertStatus(422)
            ->assertJson([
                'errors' => [
                    [
                        'messages' => [__('validation.required' , ['attribute' => 'data'])]
                    ]
                ]
            ]);
    }

    /** @test */
    public function fail_not_product_data(): void
    {
        $this->loginAsModerator();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $data = $this->data();
        unset($data['data'][1]);

        $this->postJson(
            route('1c.dealer-order.add-or-update-packing-slip', ['guid' => $model->guid]), $data
        )
            ->assertStatus(422)
            ->assertJson([
                'errors' => [
                    [
                        'messages' => ['The data.0.products field is required.']
                    ]
                ]
            ]);
    }

    /** @test */
    public function fail_not_order(): void
    {
        $this->loginAsModerator();

        /** @var $order Order */
        $order = $this->orderBuilder->create();

        $product_1 = $this->productBuilder->create();
        $item_1 = $this->itemBuilder->setProduct($product_1)->setOrder($order)->create();

        $data = $this->data();
        unset($data['data'][1]);
        $data['data'][0]['products'] = [
            [
                'guid' => $product_1->guid,
                'qty' => 5,
                'description' => null,
            ],
        ];

        $guid = '342342423';
        $this->postJson(
            route('1c.dealer-order.add-or-update-packing-slip', ['guid' => $guid]), $data
        )
            ->assertStatus(404)
            ->assertJson([
                'data' => __('exceptions.dealer.order.not found by guid' , ['guid' => $guid]),
                'success' => false
            ]);
    }

    /** @test */
    public function fail_something_wrong_to_service(): void
    {
        $this->loginAsModerator();

        /** @var $order Order */
        $order = $this->orderBuilder->create();

        $product_1 = $this->productBuilder->create();
        $item_1 = $this->itemBuilder->setProduct($product_1)->setOrder($order)->create();

        $data = $this->data();
        unset($data['data'][1]);
        $data['data'][0]['products'] = [
            [
                'guid' => $product_1->guid,
                'qty' => 5,
                'description' => null,
            ],
        ];

        $this->mock(PackingSlipService::class, function(MockInterface $mock){
            $mock->shouldReceive("addOrUpdatePackingSlips")
                ->andThrows(\Exception::class, "some exception message");
        });

        $this->postJson(
            route('1c.dealer-order.add-or-update-packing-slip', ['guid' => $order->guid]), $data
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
            'data' => [
                [
                    'guid' => $this->faker->uuid,
                    'status' => OrderStatus::APPROVED,
                    'number' => $this->faker->postcode,
                    'tracking_number' => $this->faker->creditCardNumber,
                    'tracking_company' => $this->faker->company,
                    'shipped_date' => CarbonImmutable::now()->addDay()->format('Y-m-d'),
                    'dimensions' => [
                        [
                            'pallet' => $this->faker->numberBetween(1,5),
                            'box_qty' => $this->faker->numberBetween(1,9),
                            'type' => 'box',
                            'weight' => 33.9,
                            'width' => 43.5,
                            'depth' => 54,
                            'height' => 28.9,
                            'class_freight' => $this->faker->numberBetween(1,4),
                        ],
                        [
                            'pallet' => $this->faker->numberBetween(1,5),
                            'box_qty' => $this->faker->numberBetween(1,9),
                            'type' => 'box',
                            'weight' => 133.9,
                            'width' => 143.5,
                            'depth' => 154,
                            'height' => 128.9,
                            'class_freight' => $this->faker->numberBetween(1,4),
                        ]
                    ]
                ],
                [
                    'guid' => $this->faker->uuid,
                    'status' => OrderStatus::SHIPPED,
                    'number' => $this->faker->postcode,
                    'tracking_number' => $this->faker->creditCardNumber,
                    'tracking_company' => $this->faker->company,
                    'shipped_date' => CarbonImmutable::now()->addDay()->format('Y-m-d'),
                    'dimensions' => [
                        [
                            'pallet' => $this->faker->numberBetween(1,5),
                            'box_qty' => $this->faker->numberBetween(1,9),
                            'type' => 'box',
                            'weight' => 45,
                            'width' => 55.9,
                            'depth' => 89,
                            'height' => 59.6,
                            'class_freight' => $this->faker->numberBetween(1,4),
                        ]
                    ]
                ],
            ]
        ];
    }
}
