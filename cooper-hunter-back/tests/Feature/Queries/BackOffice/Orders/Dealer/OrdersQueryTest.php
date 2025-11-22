<?php

namespace Tests\Feature\Queries\BackOffice\Orders\Dealer;

use App\Enums\Orders\Dealer\OrderStatus;
use App\GraphQL\Queries\BackOffice\Orders\Dealer\OrdersQuery;
use App\Models\Orders\Dealer\Order;
use App\Models\Orders\Dealer\PackingSlip;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Tests\Builders\Company\CompanyBuilder;
use Tests\Builders\Company\CompanyShippingAddressBuilder;
use Tests\Builders\Dealers\DealerBuilder;
use Tests\Builders\Orders\Dealer\DimensionsBuilder;
use Tests\Builders\Orders\Dealer\ItemBuilder;
use Tests\Builders\Orders\Dealer\OrderBuilder;
use Tests\Builders\Orders\Dealer\PackingSlipBuilder;
use Tests\Builders\Orders\Dealer\PackingSlipItemBuilder;
use Tests\Builders\Orders\Dealer\PackingSlipSerialNumberBuilder;
use Tests\Builders\Orders\Dealer\SerialNumberBuilder;
use Tests\TestCase;

class OrdersQueryTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public const MUTATION = OrdersQuery::NAME;

    protected DealerBuilder $dealerBuilder;
    protected CompanyShippingAddressBuilder $companyShippingAddressBuilder;
    protected OrderBuilder $orderBuilder;
    protected ItemBuilder $itemBuilder;
    protected SerialNumberBuilder $serialNumberBuilder;
    protected CompanyBuilder $companyBuilder;
    protected PackingSlipBuilder $packingSlipBuilder;
    protected PackingSlipItemBuilder $packingSlipItemBuilder;
    protected DimensionsBuilder $dimensionsBuilder;
    protected PackingSlipSerialNumberBuilder $packingSlipSerialNumberBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->companyBuilder = resolve(CompanyBuilder::class);
        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->itemBuilder = resolve(ItemBuilder::class);
        $this->serialNumberBuilder = resolve(SerialNumberBuilder::class);
        $this->packingSlipSerialNumberBuilder = resolve(PackingSlipSerialNumberBuilder::class);
        $this->dealerBuilder = resolve(DealerBuilder::class);
        $this->companyShippingAddressBuilder = resolve(CompanyShippingAddressBuilder::class);
        $this->packingSlipBuilder = resolve(PackingSlipBuilder::class);
        $this->packingSlipItemBuilder = resolve(PackingSlipItemBuilder::class);
        $this->dimensionsBuilder = resolve(DimensionsBuilder::class);
    }

    /** @test */
    public function success_paginator(): void
    {
        $this->loginAsSuperAdmin();

        Order::factory()->times(20)->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr()
        ])
            ->assertJsonStructure([
                'data' => [
                    self::MUTATION => [
                        'data' => [
                            [
                                'id',
                            ]
                        ],
                    ],
                ]
            ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'meta' => [
                            'total' => 20,
                            'per_page' => 15,
                            'current_page' => 1,
                            'from' => 1,
                            'to' => 15,
                            'last_page' => 2,
                            'has_more_pages' => true,
                        ],
                    ]
                ]
            ])
        ;
    }

    protected function getQueryStr(): string
    {
        return sprintf(
            '
            {
                %s {
                    data {
                        id
                    }
                    meta {
                        total
                        per_page
                        current_page
                        from
                        to
                        last_page
                        has_more_pages
                    }
                }
            }',
            self::MUTATION
        );
    }

    /** @test */
    public function success_paginator_by_page(): void
    {
        $this->loginAsSuperAdmin();

        Order::factory()->times(20)->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrByPage(2)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'meta' => [
                            'total' => 20,
                            'per_page' => 15,
                            'current_page' => 2,
                            'from' => 16,
                            'to' => 20,
                            'last_page' => 2,
                            'has_more_pages' => false,
                        ],
                    ]
                ]
            ])
        ;
    }

    protected function getQueryStrByPage($value): string
    {
        return sprintf(
            '
            {
                %s (page: %s) {
                    data {
                        id
                    }
                    meta {
                        total
                        per_page
                        current_page
                        from
                        to
                        last_page
                        has_more_pages
                    }
                }
            }',
            self::MUTATION,
            $value
        );
    }

    /** @test */
    public function success_paginator_by_per_page(): void
    {
        $this->loginAsSuperAdmin();

        Order::factory()->times(5)->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrByPerPage(2)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'meta' => [
                            'total' => 5,
                            'per_page' => 2,
                            'current_page' => 1,
                            'from' => 1,
                            'to' => 2,
                            'last_page' => 3,
                            'has_more_pages' => true,
                        ],
                    ]
                ]
            ])
        ;
    }

    protected function getQueryStrByPerPage($value): string
    {
        return sprintf(
            '
            {
                %s (per_page: %s) {
                    data {
                        id
                    }
                    meta {
                        total
                        per_page
                        current_page
                        from
                        to
                        last_page
                        has_more_pages
                    }
                }
            }',
            self::MUTATION,
            $value
        );
    }

    /** @test */
    public function filter_by_id(): void
    {
        $this->loginAsSuperAdmin();

        $dealer = $this->dealerBuilder->create();
        $address = $this->companyShippingAddressBuilder->create();

        $term = [
            'guid' => $this->faker->uuid,
            'name' => $this->faker->word,
        ];
        $invoice = '65675fjh6';
        $error = 'some error';
        $files = [
            [
                'name' => $this->faker->name,
                'url' => $this->faker->url,
            ],
            [
                'name' => $this->faker->name,
                'url' => $this->faker->url,
            ]
        ];

        /** @var $order Order */
        $order = $this->orderBuilder
            ->setDealer($dealer)
            ->setShippingAddress($address)
            ->setData([
                'terms' => $term,
                'invoice' => $invoice,
                'files' => $files,
                'error' => $error,
                'tracking_number' => $this->faker->creditCardNumber,
                'tracking_company' => $this->faker->company,
            ])
            ->withAllPrices()
            ->create();

        $serial_number_1 = $this->serialNumberBuilder->setOrder($order)->create();
        $serial_number_2 = $this->serialNumberBuilder->setOrder($order)->create();

        $filesPackingSlip = [
            [
                'name' => $this->faker->name,
                'url' => $this->faker->url,
            ],
            [
                'name' => $this->faker->name,
                'url' => $this->faker->url,
            ]
        ];
        $packing_slip_1 = $this->packingSlipBuilder->setData([
            'files' => $filesPackingSlip,
        ])->setOrder($order)->create();
        $packing_slip_2 = $this->packingSlipBuilder->setOrder($order)->create();
        $packing_slip_3 = $this->packingSlipBuilder->setOrder($order)->create();

        $packing_slip_1->addMedia(
            UploadedFile::fake()->image('test1.png')
        )
            ->toMediaCollection(PackingSlip::MEDIA_COLLECTION_NAME);
        $packing_slip_1->addMedia(
            UploadedFile::fake()->image('test2.png')
        )
            ->toMediaCollection(PackingSlip::MEDIA_COLLECTION_NAME);

        $packing_slip_serial_number_1 = $this->packingSlipSerialNumberBuilder
            ->setPackingSlip($packing_slip_1)->create();
        $packing_slip_serial_number_2 = $this->packingSlipSerialNumberBuilder
            ->setPackingSlip($packing_slip_1)->create();

        $dimensions_1 = $this->dimensionsBuilder->setPackingSlip($packing_slip_1)->create();
        $dimensions_2 = $this->dimensionsBuilder->setPackingSlip($packing_slip_1)->create();

        $order->addMedia(
            UploadedFile::fake()->image('test1.png')
        )
            ->toMediaCollection(Order::MEDIA_COLLECTION_NAME);
        $order->addMedia(
            UploadedFile::fake()->image('test2.png')
        )
            ->toMediaCollection(Order::MEDIA_COLLECTION_NAME);

        $total_item_1 = 200.8;
        $item_1 = $this->itemBuilder->setData([
            'total' => $total_item_1
        ])->setOrder($order)->create();
        $total_item_2 = 1200.8;
        $item_2 = $this->itemBuilder->setData([
            'total' => $total_item_2
        ])->setOrder($order)->create();

        $packing_slip_item_1_1 = $this->packingSlipItemBuilder->setPackingSlip($packing_slip_1)
            ->setOrderItem($item_1)->create();
        $packing_slip_item_1_2 = $this->packingSlipItemBuilder->setPackingSlip($packing_slip_1)
            ->setOrderItem($item_2)->create();
        $packing_slip_item_2 = $this->packingSlipItemBuilder->setPackingSlip($packing_slip_2)->create();
        $packing_slip_item_3 = $this->packingSlipItemBuilder->setPackingSlip($packing_slip_3)->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrByID($order->id)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'data' => [
                            [
                                'id' => $order->id,
                                'status' => $order->status,
                                'delivery_type' => $order->delivery_type,
                                'payment_type' => $order->payment_type,
                                'po' => $order->po,
                                'invoice' => $invoice,
                                'has_invoice' => $order->has_invoice,
                                'term' => data_get($term, 'name'),
                                'comment' => $order->comment,
                                'tax' => $order->tax,
                                'shipping_price' => $order->shipping_price,
                                'total' => $order->total,
                                'total_discount' => $order->total_discount,
                                'total_with_discount' => $order->total_with_discount,
                                'error' => $error,
                                'files' => [
                                    [
                                        'name' => data_get($files, '0.name'),
                                        'url' => data_get($files, '0.url'),
                                    ],
                                    [
                                        'name' => data_get($files, '1.name'),
                                        'url' => data_get($files, '1.url'),
                                    ]
                                ],
                                'shipping_address' => [
                                    'id' => $address->id
                                ],
                                'dealer' => [
                                    'id' => $dealer->id
                                ],
                                'items' => [
                                    [
                                        'id' => $item_1->id,
                                        'total' => $total_item_1,
                                        'description' => $item_1->description,
                                        'discount' => $item_1->discount,
                                        'discount_total' => $item_1->discount_total,
                                    ],
                                    [
                                        'id' => $item_2->id,
                                        'total' => $total_item_2,
                                        'description' => $item_2->description,
                                        'discount' => $item_2->discount,
                                        'discount_total' => $item_2->discount_total,
                                    ],
                                ],
                                'media' => [
                                    ['id' => $order->media[0]->id],
                                    ['id' => $order->media[1]->id],
                                ],
                                'serial_numbers' => [
                                    [
                                        'id' => $serial_number_1->id,
                                        'serial_number' => $serial_number_1->serial_number,
                                        'product' => [
                                            'id' => $serial_number_1->product_id
                                        ]
                                    ],
                                    [
                                        'id' => $serial_number_2->id,
                                        'serial_number' => $serial_number_2->serial_number,
                                        'product' => [
                                            'id' => $serial_number_2->product_id
                                        ]
                                    ]
                                ],
                                'packing_slips' => [
                                    [
                                        'id' => $packing_slip_1->id,
                                        'status' => $packing_slip_1->status,
                                        'guid' => $packing_slip_1->guid,
                                        'number' => $packing_slip_1->number,
                                        'tracking_number' => $packing_slip_1->tracking_number,
                                        'tracking_company' => $packing_slip_1->tracking_company,
                                        'shipped_at' => $packing_slip_1->shipped_at,
                                        'files' => [
                                            [
                                                'name' => data_get($filesPackingSlip, '0.name'),
                                                'url' => data_get($filesPackingSlip, '0.url'),
                                            ],
                                            [
                                                'name' => data_get($filesPackingSlip, '1.name'),
                                                'url' => data_get($filesPackingSlip, '1.url'),
                                            ]
                                        ],
                                        'dimensions' => [
                                            [
                                                'id' => $dimensions_1->id,
                                                'pallet' => $dimensions_1->pallet,
                                                'box_qty' => $dimensions_1->box_qty,
                                                'type' => $dimensions_1->type,
                                                'weight' => $dimensions_1->weight,
                                                'width' => $dimensions_1->width,
                                                'depth' => $dimensions_1->depth,
                                                'height' => $dimensions_1->height,
                                                'class_freight' => $dimensions_1->class_freight,
                                            ],
                                            [
                                                'id' => $dimensions_2->id,
                                                'pallet' => $dimensions_2->pallet,
                                                'box_qty' => $dimensions_2->box_qty,
                                                'type' => $dimensions_2->type,
                                                'weight' => $dimensions_2->weight,
                                                'width' => $dimensions_2->width,
                                                'depth' => $dimensions_2->depth,
                                                'height' => $dimensions_2->height,
                                                'class_freight' => $dimensions_2->class_freight,
                                            ]
                                        ],
                                        'items' => [
                                            [
                                                'id' => $packing_slip_item_1_1->id,
                                                'price' => $item_1->price,
                                                'discount' => $item_1->discount,
                                                'discount_total' => $item_1->discount_total,
                                                'qty' => $packing_slip_item_1_1->qty,
                                                'description' => $packing_slip_item_1_1->description,
                                                'amount' => $packing_slip_item_1_1->qty * $item_1->price,
                                                'total' => ($item_1->price - $item_1->discount) * $packing_slip_item_1_1->qty,
                                            ],
                                            [
                                                'id' => $packing_slip_item_1_2->id,
                                                'price' => $item_2->price,
                                                'discount' => $item_2->discount,
                                                'discount_total' => $item_2->discount_total,
                                                'qty' => $packing_slip_item_1_2->qty,
                                                'description' => $packing_slip_item_1_2->description,
                                                'amount' => $packing_slip_item_1_2->qty * $item_2->price,
                                                'total' => ($item_2->price - $item_2->discount) * $packing_slip_item_1_2->qty,
                                            ],
                                        ],
                                        'media' => [
                                            ['id' => $packing_slip_1->media[0]->id],
                                            ['id' => $packing_slip_1->media[1]->id],
                                        ],
                                        'serial_numbers' => [
                                            [
                                                'id' => $packing_slip_serial_number_1->id,
                                                'serial_number' => $packing_slip_serial_number_1->serial_number,
                                                'product' => [
                                                    'id' => $packing_slip_serial_number_1->product_id
                                                ]
                                            ],
                                            [
                                                'id' => $packing_slip_serial_number_2->id,
                                                'serial_number' => $packing_slip_serial_number_2->serial_number,
                                                'product' => [
                                                    'id' => $packing_slip_serial_number_2->product_id
                                                ]
                                            ]
                                        ],
                                        'invoice' => $packing_slip_1->invoice,
                                        'invoice_at' => $packing_slip_1->invoice_at,
                                        'invoice_file_link' => null,
                                        'tax' => $packing_slip_1->tax,
                                        'shipping_price' => $packing_slip_1->shipping_price,
                                        'total' => $packing_slip_1->total,
                                        'total_discount' => $packing_slip_1->total_discount,
                                        'total_with_discount' => $packing_slip_1->total_with_discount,
                                    ],
                                    [
                                        'id' => $packing_slip_2->id,
                                        'status' => $packing_slip_1->status,
                                        'guid' => $packing_slip_2->guid,
                                        'number' => $packing_slip_2->number,
                                        'tracking_number' => $packing_slip_2->tracking_number,
                                        'tracking_company' => $packing_slip_2->tracking_company,
                                        'shipped_at' => $packing_slip_2->shipped_at,
                                        'dimensions' => [],
                                        "files" => null,
                                        'items' => [
                                            ['id' => $packing_slip_item_2->id]
                                        ]
                                    ],
                                    [
                                        'id' => $packing_slip_3->id,
                                        'status' => $packing_slip_1->status,
                                        'guid' => $packing_slip_3->guid,
                                        'number' => $packing_slip_3->number,
                                        'tracking_number' => $packing_slip_3->tracking_number,
                                        'tracking_company' => $packing_slip_3->tracking_company,
                                        'shipped_at' => $packing_slip_3->shipped_at,
                                        'dimensions' => [],
                                        "files" => null,
                                        'items' => [
                                            ['id' => $packing_slip_item_3->id]
                                        ]
                                    ]
                                ]
                            ]
                        ],
                    ]
                ]
            ])
        ;
    }

    protected function getQueryStrByID($value): string
    {
        return sprintf(
            '
            {
                %s (id: %s) {
                    data {
                        id
                        status
                        delivery_type
                        payment_type
                        po
                        invoice
                        has_invoice
                        term
                        comment
                        tax
                        shipping_price
                        total
                        total_discount
                        total_with_discount
                        error
                        files {
                            name
                            url
                        }
                        shipping_address {
                            id
                        }
                        dealer {
                            id
                        }
                        items {
                            id
                            total
                            description
                            discount
                            discount_total
                        }
                        media {
                            id
                        }
                        serial_numbers {
                            id
                            serial_number
                            product {
                                id
                            }
                        }
                        packing_slips {
                            id
                            guid
                            status
                            number
                            tracking_number
                            tracking_company
                            shipped_at
                            files {
                                name
                                url
                            }
                            dimensions {
                                id
                                pallet
                                box_qty
                                type
                                weight
                                width
                                depth
                                height
                                class_freight
                            }
                            items {
                                id
                                price
                                discount
                                discount_total
                                qty
                                amount
                                total
                                description
                            }
                            media {
                                id
                            }
                            serial_numbers {
                                id
                                serial_number
                                product {
                                    id
                                }
                            }
                            invoice
                            invoice_at
                            invoice_file_link
                            tax
                            shipping_price
                            total
                            total_discount
                            total_with_discount
                        }
                    }
                }
            }',
            self::MUTATION,
            $value
        );
    }

    /** @test */
    public function filter_by_status(): void
    {
        $this->loginAsSuperAdmin();

        $order_1 = $this->orderBuilder->setStatus(OrderStatus::APPROVED)->create();
        $order_2 = $this->orderBuilder->setStatus(OrderStatus::APPROVED)->create();
        $order_3 = $this->orderBuilder->setStatus(OrderStatus::DRAFT)->create();
        $order_4 = $this->orderBuilder->setStatus(OrderStatus::CANCELED)->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrByStatus(OrderStatus::APPROVED)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'data' => [
                            ['id' => $order_2->id],
                            ['id' => $order_1->id],
                        ],
                    ]
                ]
            ])
            ->assertJsonCount(2, 'data.'.self::MUTATION.'.data')
        ;
    }

    protected function getQueryStrByStatus($value): string
    {
        return sprintf(
            '
            {
                %s (status: %s) {
                    data {
                        id
                    }
                }
            }',
            self::MUTATION,
            $value
        );
    }

    /** @test */
    public function filter_by_po(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $order Order */
        $order_1 = $this->orderBuilder->setData(['po' => 'term'])->create();
        $order_2 = $this->orderBuilder->setData(['po' => 'terminator'])->create();
        $order_3 = $this->orderBuilder->setData(['po' => 'rembo'])->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrByPO('ter')
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'data' => [
                            ['id' => $order_2->id],
                            ['id' => $order_1->id]
                        ],
                    ]
                ]
            ])
            ->assertJsonCount(2, 'data.'.self::MUTATION.'.data')
        ;
    }

    protected function getQueryStrByPO($value): string
    {
        return sprintf(
            '
            {
                %s (po: "%s") {
                    data {
                        id
                    }
                }
            }',
            self::MUTATION,
            $value
        );
    }

    /** @test */
    public function filter_by_company(): void
    {
        $this->loginAsSuperAdmin();

        $company_1 = $this->companyBuilder->create();
        $company_2 = $this->companyBuilder->create();

        $dealer_1 = $this->dealerBuilder->setCompany($company_1)->create();
        $dealer_2 = $this->dealerBuilder->setCompany($company_1)->create();
        $dealer_3 = $this->dealerBuilder->setCompany($company_2)->create();

        /** @var $order Order */
        $order_1 = $this->orderBuilder->setDealer($dealer_1)->create();
        $order_2 = $this->orderBuilder->setDealer($dealer_1)->create();
        $order_3 = $this->orderBuilder->setDealer($dealer_2)->create();
        // no check
        $order_4 = $this->orderBuilder->setDealer($dealer_3)->create();
        $order_5 = $this->orderBuilder->setDealer($dealer_3)->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrByCompany($company_1->id)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'data' => [
                            ['id' => $order_3->id],
                            ['id' => $order_2->id],
                            ['id' => $order_1->id]
                        ],
                    ]
                ]
            ])
            ->assertJsonCount(3, 'data.'.self::MUTATION.'.data')
        ;
    }

    protected function getQueryStrByCompany($value): string
    {
        return sprintf(
            '
            {
                %s (company_id: "%s") {
                    data {
                        id
                    }
                }
            }',
            self::MUTATION,
            $value
        );
    }

    /** @test */
    public function filter_by_address(): void
    {
        $this->loginAsSuperAdmin();

        $address_1 = $this->companyShippingAddressBuilder->create();
        $address_2 = $this->companyShippingAddressBuilder->create();

        /** @var $order Order */
        $order_1 = $this->orderBuilder->setShippingAddress($address_1)->create();
        $order_2 = $this->orderBuilder->setShippingAddress($address_1)->create();

        $order_3 = $this->orderBuilder->setShippingAddress($address_2)->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrByAddress($address_1->id)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'data' => [
                            ['id' => $order_2->id],
                            ['id' => $order_1->id]
                        ],
                    ]
                ]
            ])
            ->assertJsonCount(2, 'data.'.self::MUTATION.'.data')
        ;
    }

    protected function getQueryStrByAddress($value): string
    {
        return sprintf(
            '
            {
                %s (location_id: "%s") {
                    data {
                        id
                    }
                }
            }',
            self::MUTATION,
            $value
        );
    }

    /** @test */
    public function not_auth(): void
    {
        Order::factory()->times(2)->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr()
        ])
            ->assertJson([
                'errors' => [
                    ['message' => "Unauthorized"]
                ]
            ])
        ;
    }

    /** @test */
    public function not_perm(): void
    {
        $this->loginAsAdmin();

        Order::factory()->times(2)->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr()
        ])
            ->assertJson([
                'errors' => [
                    ['message' => "No permission"]
                ]
            ])
        ;
    }
}
