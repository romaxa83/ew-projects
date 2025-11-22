<?php

namespace Tests\Feature\Http\Api\OneC\Dealers\Order;

use App\Enums\Orders\Dealer\OrderStatus;
use App\Enums\Orders\OrderArrivedFormEnum;
use App\Models\Companies\Company;
use App\Models\Dealers\Dealer;
use App\Models\Orders\Dealer\Order;
use App\Models\Orders\Dealer\PackingSlipSerialNumber;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Builders\Catalog\ProductBuilder;
use Tests\Builders\Company\CompanyBuilder;
use Tests\Builders\Dealers\DealerBuilder;
use Tests\Builders\Orders\Dealer\DimensionsBuilder;
use Tests\Builders\Orders\Dealer\ItemBuilder;
use Tests\Builders\Orders\Dealer\OrderBuilder;
use Tests\Builders\Orders\Dealer\PackingSlipBuilder;
use Tests\Builders\Orders\Dealer\PackingSlipItemBuilder;
use Tests\Builders\Orders\Dealer\PackingSlipSerialNumberBuilder;
use Tests\Builders\Orders\Dealer\SerialNumberBuilder;
use Tests\TestCase;

class ListTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    protected OrderBuilder $orderBuilder;
    protected CompanyBuilder $companyBuilder;
    protected DealerBuilder $dealerBuilder;
    protected ItemBuilder $itemBuilder;
    protected SerialNumberBuilder $serialNumberBuilder;
    protected ProductBuilder $productBuilder;
    protected PackingSlipBuilder $packingSlipBuilder;
    protected PackingSlipItemBuilder $packingSlipItemBuilder;
    protected PackingSlipSerialNumberBuilder $packingSlipSerialNumberBuilder;
    protected DimensionsBuilder $dimensionBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->companyBuilder = resolve(CompanyBuilder::class);
        $this->dealerBuilder = resolve(DealerBuilder::class);
        $this->itemBuilder = resolve(ItemBuilder::class);
        $this->serialNumberBuilder = resolve(SerialNumberBuilder::class);
        $this->productBuilder = resolve(ProductBuilder::class);
        $this->packingSlipBuilder = resolve(PackingSlipBuilder::class);
        $this->packingSlipItemBuilder = resolve(PackingSlipItemBuilder::class);
        $this->packingSlipSerialNumberBuilder = resolve(PackingSlipSerialNumberBuilder::class);
        $this->dimensionBuilder = resolve(DimensionsBuilder::class);
    }

    /** @test */
    public function paginator(): void
    {
        $this->loginAsModerator();
        /** @var $company Company */
        $company = $this->companyBuilder->create();
        /** @var $dealer Dealer */
        $dealer = $this->dealerBuilder->setCompany($company)->create();
        /** @var $model Order */
        $model = $this->orderBuilder
            ->setDealer($dealer)
            ->withAllPrices()
            ->setData([
                'terms' => [
                    [
                        'name' => $this->faker->word,
                        'guid' => $this->faker->uuid,
                    ],
                    [
                        'name' => $this->faker->word,
                        'guid' => $this->faker->uuid,
                    ]
                ],
                'created_at' => CarbonImmutable::now()->addDays(3),
                'invoice' => $this->faker->creditCardNumber,
                'invoice_at' => CarbonImmutable::now()->addDays(4),
                'approved_at' => CarbonImmutable::now()->addDays(1),
                'files' => [
                    [
                        'name' => 'test_1',
                        'url' => 'test_1',
                    ],
                    [
                        'name' => 'test_2',
                        'url' => 'test_2',
                    ]
                ]
            ])
            ->create();

        $product_1 = $this->productBuilder->create();
        $product_2 = $this->productBuilder->create();
        $product_3 = $this->productBuilder->create();

        $item_1 = $this->itemBuilder->setProduct($product_1)->setOrder($model)->create();
        $item_2 = $this->itemBuilder->setProduct($product_2)->setOrder($model)->create();

        $serial_number_1 = $this->serialNumberBuilder->setProduct($product_1)->setOrder($model)->create();
        $serial_number_2 = $this->serialNumberBuilder->setProduct($product_1)->setOrder($model)->create();
        $serial_number_3 = $this->serialNumberBuilder->setProduct($product_2)->setOrder($model)->create();

        $packing_slip_1 = $this->packingSlipBuilder->setOrder($model)->create();
        $packing_slip_2 = $this->packingSlipBuilder->setOrder($model)->create();

        $dimension_1 = $this->dimensionBuilder->setPackingSlip($packing_slip_1)->create();
        $dimension_2 = $this->dimensionBuilder->setPackingSlip($packing_slip_1)->create();
        $dimension_3 = $this->dimensionBuilder->setPackingSlip($packing_slip_2)->create();

        $packing_slip_item_1 = $this->packingSlipItemBuilder->setPackingSlip($packing_slip_1)
            ->setOrderItem($item_1)->setProduct($product_1)->create();
        $packing_slip_item_2 = $this->packingSlipItemBuilder->setPackingSlip($packing_slip_2)
            ->setOrderItem($item_2)->setProduct($product_2)->create();

        $packing_slip_serial_number_1 = $this->packingSlipSerialNumberBuilder
            ->setPackingSlip($packing_slip_1)->setProduct($product_1)->create();
        $packing_slip_serial_number_2 = $this->packingSlipSerialNumberBuilder
            ->setPackingSlip($packing_slip_1)->setProduct($product_1)->create();
        $packing_slip_serial_number_3 = $this->packingSlipSerialNumberBuilder
            ->setPackingSlip($packing_slip_2)->setProduct($product_2)->create();

        $this->getJson(route('1c.dealer-order.list'))
            ->assertOk()
            ->assertJson([
                'data' => [
                    [
                        'id' => $model->id,
                        'guid' => $model->guid,
                        'arrived_from' => OrderArrivedFormEnum::DEALER,
                        'status' => $model->status,
                        'delivery_type' => $model->delivery_type,
                        'payment_type' => $model->payment_type,
                        'po' => $model->po,
                        'terms' => $model->terms,
                        'comment' => $model->comment,
                        'created_at' => $model->created_at->format('Y-m-d'),
                        'files' => $model->files,
                        'tax' => $model->tax,
                        'shipping_price' => $model->shipping_price,
                        'total' => $model->total,
                        'total_discount' => $model->total_discount,
                        'total_with_discount' => $model->total_with_discount,
                        'invoice' => $model->invoice,
                        'has_invoice' => $model->has_invoice,
                        'error' => $model->error,
                        'invoice_at' => $model->invoice_at->format('Y-m-d'),
                        'approved_at' => $model->approved_at->format('Y-m-d'),
                        'serial_numbers' => [
                            [
                                'serial_number' => $serial_number_1->serial_number,
                                'product_guid' => $product_1->guid,
                                'product_title' => $product_1->title,
                            ],
                            [
                                'serial_number' => $serial_number_2->serial_number,
                                'product_guid' => $product_1->guid,
                                'product_title' => $product_1->title,
                            ],
                            [
                                'serial_number' => $serial_number_3->serial_number,
                                'product_guid' => $product_2->guid,
                                'product_title' => $product_2->title,
                            ]
                        ],
                        'items' => [
                            [
                                'product_guid' => $product_1->guid,
                                'product_title' => $product_1->title,
                                'price' => $item_1->price,
                                'qty' => $item_1->qty,
                                'discount' => $item_1->discount,
                                'total' => $item_1->total,
                                'description' => $item_1->description,
                            ],
                            [
                                'product_guid' => $product_2->guid,
                                'product_title' => $product_2->title,
                                'price' => $item_2->price,
                                'qty' => $item_2->qty,
                                'discount' => $item_2->discount,
                                'total' => $item_2->total,
                                'description' => $item_2->description,
                            ]
                        ],
                        'packing_slips' => [
                            [
                                'id' => $packing_slip_1->id,
                                'guid' => $packing_slip_1->guid,
                                'status' => $packing_slip_1->status,
                                'number' => $packing_slip_1->number,
                                'tracking_number' => $packing_slip_1->tracking_number,
                                'tracking_company' => $packing_slip_1->tracking_company,
                                'tax' => $packing_slip_1->tax,
                                'shipping_price' => $packing_slip_1->shipping_price,
                                'total' => $packing_slip_1->total,
                                'total_discount' => $packing_slip_1->total_discount,
                                'total_with_discount' => $packing_slip_1->total_with_discount,
                                'invoice' => $packing_slip_1->invoice,
                                'invoice_at' => $packing_slip_1->invoice_at->format('Y-m-d'),
                                'shipped_at' => $packing_slip_1->shipped_at->format('Y-m-d'),
                                'files' => $packing_slip_1->files,
                                'dimensions' => [
                                    [
                                        'pallet' => $dimension_1->pallet,
                                        'box_qty' => $dimension_1->box_qty,
                                        'type' => $dimension_1->type,
                                        'weight' => $dimension_1->weight,
                                        'width' => $dimension_1->width,
                                        'depth' => $dimension_1->depth,
                                        'height' => $dimension_1->height,
                                        'class_freight' => $dimension_1->class_freight,
                                    ],
                                    [
                                        'pallet' => $dimension_2->pallet,
                                        'box_qty' => $dimension_2->box_qty,
                                        'type' => $dimension_2->type,
                                        'weight' => $dimension_2->weight,
                                        'width' => $dimension_2->width,
                                        'depth' => $dimension_2->depth,
                                        'height' => $dimension_2->height,
                                        'class_freight' => $dimension_2->class_freight,
                                    ]
                                ],
                                'serial_numbers' => [
                                    [
                                        'serial_number' => $packing_slip_serial_number_1->serial_number,
                                        'product_guid' => $product_1->guid,
                                        'product_title' => $product_1->title,
                                    ],
                                    [
                                        'serial_number' => $packing_slip_serial_number_2->serial_number,
                                        'product_guid' => $product_1->guid,
                                        'product_title' => $product_1->title,
                                    ]
                                ],
                                'items' => [
                                    [
                                        'product_guid' => $product_1->guid,
                                        'product_title' => $product_1->title,
                                        'qty' => $packing_slip_item_1->qty,
                                        'description' => $packing_slip_item_1->description,
                                    ]
                                ]
                            ],
                            [
                                'id' => $packing_slip_2->id,
                                'guid' => $packing_slip_2->guid,
                                'status' => $packing_slip_2->status,
                                'number' => $packing_slip_2->number,
                                'tracking_number' => $packing_slip_2->tracking_number,
                                'tracking_company' => $packing_slip_2->tracking_company,
                                'tax' => $packing_slip_2->tax,
                                'shipping_price' => $packing_slip_2->shipping_price,
                                'total' => $packing_slip_2->total,
                                'total_discount' => $packing_slip_2->total_discount,
                                'total_with_discount' => $packing_slip_2->total_with_discount,
                                'invoice' => $packing_slip_2->invoice,
                                'invoice_at' => $packing_slip_2->invoice_at->format('Y-m-d'),
                                'shipped_at' => $packing_slip_2->shipped_at->format('Y-m-d'),
                                'files' => $packing_slip_2->files,
                                'dimensions' => [
                                   [
                                       'pallet' => $dimension_3->pallet,
                                       'box_qty' => $dimension_3->box_qty,
                                       'type' => $dimension_3->type,
                                       'weight' => $dimension_3->weight,
                                       'width' => $dimension_3->width,
                                       'depth' => $dimension_3->depth,
                                       'height' => $dimension_3->height,
                                       'class_freight' => $dimension_3->class_freight,
                                   ]
                                ],
                                'serial_numbers' => [
                                    [
                                        'serial_number' => $packing_slip_serial_number_3->serial_number,
                                        'product_guid' => $product_2->guid,
                                        'product_title' => $product_2->title,
                                    ]
                                ],
                                'items' => [
                                    [
                                        'product_guid' => $product_2->guid,
                                        'product_title' => $product_2->title,
                                        'qty' => $packing_slip_item_2->qty,
                                        'description' => $packing_slip_item_2->description,
                                    ]
                                ]
                            ]
                        ],
                        'shipping_address' => [
                            'name' => $model->shippingAddress->name,
                            'phone' => $model->shippingAddress->phone->getValue(),
                            'fax' => $model->shippingAddress->fax->getValue(),
                            'country' => $model->shippingAddress->country->country_code,
                            'state' => $model->shippingAddress->state->short_name,
                            'city' => $model->shippingAddress->city,
                            'address_line_1' => $model->shippingAddress->address_line_1,
                            'address_line_2' => $model->shippingAddress->address_line_2,
                            'zip' => $model->shippingAddress->zip,
                            'email' => $model->shippingAddress->email->getValue(),
                            'receiving_persona' => $model->shippingAddress->receiving_persona,
                        ],
                        'dealer' => [
                            'guid' => $dealer->guid,
                            'email' => $dealer->email->getValue(),
                            'phone' => $dealer->phone?->getValue(),
                            'first_name' => $dealer->first_name,
                            'last_name' => $dealer->last_name,
                            'company' => [
                                'guid' => $company->guid,
                                'status' => $company->status,
                                'type' => $company->type,
                                'business_name' => $company->business_name,
                                'email' => $company->email?->getValue(),
                                'phone' => $company->phone?->getValue(),
                                'fax' => $company->fax?->getValue(),
                                'country' => $company->country->country_code,
                                'state' => $company->state->short_name,
                                'city' => $company->city,
                                'address_line_1' => $company->address_line_1,
                                'address_line_2' => $company->address_line_2,
                                'po_box' => $company->po_box,
                                'zip' => $company->zip,
                                'taxpayer_id' => $company->taxpayer_id,
                                'tax' => $company->tax,
                                'websites' => $company->websites,
                                'marketplaces' => $company->marketplaces,
                                'trade_names' => $company->trade_names,
                            ]
                        ]
                    ]
                ],
                'meta' => [
                    'current_page' => 1,
                    'per_page' => 15,
                    'total' => 1
                ]
            ]);
    }

    /** @test */
    public function paginator_without_packing_slip(): void
    {
        $this->loginAsModerator();
        /** @var $company Company */
        $company = $this->companyBuilder->create();
        /** @var $dealer Dealer */
        $dealer = $this->dealerBuilder->setCompany($company)->create();
        /** @var $model Order */
        $model = $this->orderBuilder
            ->setDealer($dealer)
            ->withAllPrices()
            ->setData([
                'terms' => [
                    [
                        'name' => $this->faker->word,
                        'guid' => $this->faker->uuid,
                    ],
                    [
                        'name' => $this->faker->word,
                        'guid' => $this->faker->uuid,
                    ]
                ],
                'created_at' => CarbonImmutable::now()->addDays(3),
                'invoice' => $this->faker->creditCardNumber,
                'invoice_at' => CarbonImmutable::now()->addDays(4),
                'approved_at' => CarbonImmutable::now()->addDays(1),
                'files' => [
                    [
                        'name' => 'test_1',
                        'url' => 'test_1',
                    ],
                    [
                        'name' => 'test_2',
                        'url' => 'test_2',
                    ]
                ]
            ])
            ->create();

        $product_1 = $this->productBuilder->create();
        $product_2 = $this->productBuilder->create();
        $product_3 = $this->productBuilder->create();

        $item_1 = $this->itemBuilder->setProduct($product_1)->setOrder($model)->create();
        $item_2 = $this->itemBuilder->setProduct($product_2)->setOrder($model)->create();

        $serial_number_1 = $this->serialNumberBuilder->setProduct($product_1)->setOrder($model)->create();
        $serial_number_2 = $this->serialNumberBuilder->setProduct($product_1)->setOrder($model)->create();
        $serial_number_3 = $this->serialNumberBuilder->setProduct($product_2)->setOrder($model)->create();

        $this->getJson(route('1c.dealer-order.list'))

            ->assertOk()
            ->assertJson([
                'data' => [
                    [
                        'packing_slips' => [],
                    ]
                ],
                'meta' => [
                    'current_page' => 1,
                    'per_page' => 15,
                    'total' => 1
                ]
            ])
            ->assertJsonCount(0, 'data.0.packing_slips')
        ;
    }

    /** @test */
    public function paginator_empty_order(): void
    {
        $this->loginAsModerator();
        /** @var $company Company */
        $company = $this->companyBuilder->create();
        /** @var $dealer Dealer */
        $dealer = $this->dealerBuilder->setCompany($company)->create();
        /** @var $model Order */
        $model = $this->orderBuilder->setDealer($dealer)->setData([
            'shipping_address_id' => null
        ])->create();

        $this->getJson(route('1c.dealer-order.list'))
            ->assertOk()
            ->assertJson([
                'data' => [
                    [
                        'id' => $model->id,
                        'serial_numbers' => [],
                        'items' => [],
                        'shipping_address' => null,
                        'dealer' => [
                            'guid' => $dealer->guid,
                        ]
                    ]
                ],
                'meta' => [
                    'current_page' => 1,
                    'per_page' => 15,
                    'total' => 1
                ]
            ]);

    }

    /** @test */
    public function paginator_page(): void
    {
        $this->loginAsModerator();

        /** @var $model Order */
        $this->orderBuilder->create();
        $this->orderBuilder->create();
        $this->orderBuilder->create();
        $this->orderBuilder->create();

        $this->getJson(route('1c.dealer-order.list', ['page' => 3]))
            ->assertOk()
            ->assertJson([
                'meta' => [
                    'current_page' => 3,
                    'per_page' => 15,
                    'total' => 4
                ]
            ]);

    }

    /** @test */
    public function paginator_per_page(): void
    {
        $this->loginAsModerator();

        /** @var $model Order */
        $this->orderBuilder->create();
        $this->orderBuilder->create();
        $this->orderBuilder->create();
        $this->orderBuilder->create();

        $this->getJson(route('1c.dealer-order.list', ['per_page' => 2]))
            ->assertOk()
            ->assertJson([
                'meta' => [
                    'current_page' => 1,
                    'per_page' => 2,
                    'total' => 4
                ]
            ]);
    }

    /** @test */
    public function filter_status(): void
    {
        $this->loginAsModerator();

        /** @var $model Order */
        $this->orderBuilder->setStatus(OrderStatus::SENT)->create();
        $this->orderBuilder->setStatus(OrderStatus::SENT)->create();
        $this->orderBuilder->setStatus(OrderStatus::SENT)->create();
        $this->orderBuilder->setStatus(OrderStatus::SHIPPED)->create();

        $this->getJson(route('1c.dealer-order.list', ['status' => OrderStatus::SHIPPED]))
            ->assertOk()
            ->assertJson([
                'meta' => [
                    'current_page' => 1,
                    'per_page' => 15,
                    'total' => 1
                ]
            ]);
    }
}

