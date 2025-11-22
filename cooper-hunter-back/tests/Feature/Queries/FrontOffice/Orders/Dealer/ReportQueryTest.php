<?php

namespace Tests\Feature\Queries\FrontOffice\Orders\Dealer;

use App\Enums\Formats\DatetimeEnum;
use App\Enums\Orders\Dealer\OrderStatus;
use App\GraphQL\Queries\FrontOffice\Orders\Dealer\ReportQuery;
use App\Models\Companies\Corporation;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Catalog\ProductBuilder;
use Tests\Builders\Company\CompanyBuilder;
use Tests\Builders\Company\CompanyShippingAddressBuilder;
use Tests\Builders\Dealers\DealerBuilder;
use Tests\Builders\Orders\Dealer\ItemBuilder;
use Tests\Builders\Orders\Dealer\OrderBuilder;
use Tests\TestCase;

class ReportQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = ReportQuery::NAME;

    protected DealerBuilder $dealerBuilder;
    protected CompanyShippingAddressBuilder $addressBuilder;
    protected OrderBuilder $orderBuilder;
    protected CompanyBuilder $companyBuilder;
    protected ProductBuilder $productBuilder;
    protected ItemBuilder $itemBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->companyBuilder = resolve(CompanyBuilder::class);
        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->dealerBuilder = resolve(DealerBuilder::class);
        $this->addressBuilder = resolve(CompanyShippingAddressBuilder::class);
        $this->productBuilder = resolve(ProductBuilder::class);
        $this->itemBuilder = resolve(ItemBuilder::class);
    }

    /** @test */
    public function get_report_data_for_main_dealer(): void
    {
        $corp = Corporation::factory()->create();

        $company_1 = $this->companyBuilder->setCorporation($corp)->create();
        $company_2 = $this->companyBuilder->setCorporation($corp)->create();
        $company_3 = $this->companyBuilder->create();

        $address_1_1 = $this->addressBuilder->setCompany($company_1)->create();
        $address_1_2 = $this->addressBuilder->setCompany($company_1)->create();
        $address_2_1 = $this->addressBuilder->setCompany($company_2)->create();
        $address_3_1 = $this->addressBuilder->setCompany($company_3)->create();

        $dealer_1 = $this->dealerBuilder->setCompany($company_1)
            ->setData(['is_main' => true])->create();
        $dealer_2 = $this->dealerBuilder->setCompany($company_2)->create();
        $dealer_3 = $this->dealerBuilder->setCompany($company_3)->create();

        $this->loginAsDealerWithRole($dealer_1);

        $product_1 = $this->productBuilder->create();
        $product_2 = $this->productBuilder->create();

        $order_1 = $this->orderBuilder->setStatus(OrderStatus::SHIPPED)
            ->setDealer($dealer_1)->setData(['approved_at' => CarbonImmutable::now()])
            ->setShippingAddress($address_1_1)->create();
        $order_2 = $this->orderBuilder->setStatus(OrderStatus::SHIPPED)
            ->setDealer($dealer_1)->setData(['approved_at' => CarbonImmutable::now()->addDay()])
            ->setShippingAddress($address_1_1)->create();
        $order_3 = $this->orderBuilder->setStatus(OrderStatus::SHIPPED)
            ->setDealer($dealer_1)->setData(['approved_at' => CarbonImmutable::now()->addHour()])
            ->setShippingAddress($address_1_2)->create();
        $order_4 = $this->orderBuilder->setStatus(OrderStatus::SHIPPED)
            ->setDealer($dealer_2)->setData(['approved_at' => CarbonImmutable::now()->subHour()])
            ->setShippingAddress($address_2_1)->create();
        $order_5 = $this->orderBuilder->setStatus(OrderStatus::SHIPPED)
            ->setDealer($dealer_3)->setData(['approved_at' => CarbonImmutable::now()->subHour()])
            ->setShippingAddress($address_3_1)->create();

        $item_1_1 = $this->itemBuilder->setOrder($order_1)->setProduct($product_1)->create();
        $item_1_2 = $this->itemBuilder->setOrder($order_1)->setProduct($product_1)->create();
        $item_1_3 = $this->itemBuilder->setOrder($order_1)->setProduct($product_1)->create();

        $item_2_1 = $this->itemBuilder->setOrder($order_2)->setProduct($product_1)->create();
        $item_2_2 = $this->itemBuilder->setOrder($order_2)->setProduct($product_2)->create();

        $item_3_1 = $this->itemBuilder->setOrder($order_3)->setProduct($product_2)->create();

        $item_4_1 = $this->itemBuilder->setOrder($order_4)->setProduct($product_1)->create();
        $item_4_2 = $this->itemBuilder->setOrder($order_4)->setProduct($product_2)->create();
        $item_4_3 = $this->itemBuilder->setOrder($order_4)->setProduct($product_1)->create();

        $this->postGraphQL([
            'query' => $this->getQueryStr()
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        [
                            'company_name' => $company_2->business_name,
                            'total' => $item_4_1->total + $item_4_2->total + $item_4_3->total,
                            'locations' => [
                                [
                                    'location_name' => $address_2_1->name,
                                    'total' => $item_4_1->total + $item_4_2->total + $item_4_3->total,
                                    'items' => [
                                        [
                                            'po' => $order_4->po,
                                            'qty' => $item_4_1->qty,
                                            'price' => $item_4_1->price,
                                            'total' => $item_4_1->total,
                                            'desc' => $item_4_1->description,
                                            'product_title' => $item_4_1->product->title,
                                            'date' => $order_4->approved_at->format(DatetimeEnum::US_DATE_VIEW),
                                        ],
                                        [
                                            'po' => $order_4->po,
                                            'qty' => $item_4_2->qty,
                                            'price' => (float)$item_4_2->price,
                                            'total' => (float)$item_4_2->total,
                                            'desc' => $item_4_2->description,
                                            'product_title' => $item_4_2->product->title,
                                            'date' => $order_4->approved_at->format(DatetimeEnum::US_DATE_VIEW),
                                        ],
                                        [
                                            'po' => $order_4->po,
                                            'qty' => $item_4_3->qty,
                                            'price' => $item_4_3->price,
                                            'total' => $item_4_3->total,
                                            'desc' => $item_4_3->description,
                                            'product_title' => $item_4_3->product->title,
                                            'date' => $order_4->approved_at->format(DatetimeEnum::US_DATE_VIEW),
                                        ],
                                    ]
                                ]
                            ]
                        ],
                        [
                            'company_name' => $company_1->business_name,
                            'total' => $item_3_1->total + $item_2_1->total + $item_2_2->total + $item_1_1->total + $item_1_2->total + $item_1_3->total,
                            'locations' => [
                                [
                                    'location_name' => $address_1_2->name,
                                    'total' => $item_3_1->total,
                                    'items' => [
                                        [
                                            'po' => $order_3->po,
                                            'qty' => $item_3_1->qty,
                                            'price' => $item_3_1->price,
                                            'total' => $item_3_1->total,
                                            'desc' => $item_3_1->description,
                                            'product_title' => $item_3_1->product->title,
                                            'date' => $order_3->approved_at->format(DatetimeEnum::US_DATE_VIEW),
                                        ]
                                    ]
                                ],
                                [
                                    'location_name' => $address_1_1->name,
                                    'total' => $item_2_1->total + $item_2_2->total + $item_1_1->total + $item_1_2->total + $item_1_3->total,
                                    'items' => [
                                        [
                                            'po' => $order_2->po,
                                            'qty' => $item_2_1->qty,
                                            'price' => $item_2_1->price,
                                            'total' => $item_2_1->total,
                                            'desc' => $item_2_1->description,
                                            'product_title' => $item_2_1->product->title,
                                            'date' => $order_2->approved_at->format(DatetimeEnum::US_DATE_VIEW),
                                        ],
                                        [
                                            'po' => $order_2->po,
                                            'qty' => $item_2_2->qty,
                                            'price' => $item_2_2->price,
                                            'total' => $item_2_2->total,
                                            'desc' => $item_2_2->description,
                                            'product_title' => $item_2_2->product->title,
                                            'date' => $order_2->approved_at->format(DatetimeEnum::US_DATE_VIEW),
                                        ],
                                        [
                                            'po' => $order_1->po,
                                            'qty' => $item_1_1->qty,
                                            'price' => $item_1_1->price,
                                            'total' => $item_1_1->total,
                                            'desc' => $item_1_1->description,
                                            'product_title' => $item_1_1->product->title,
                                            'date' => $order_1->approved_at->format(DatetimeEnum::US_DATE_VIEW),
                                        ],
                                        [
                                            'po' => $order_1->po,
                                            'qty' => $item_1_2->qty,
                                            'price' => $item_1_2->price,
                                            'total' => $item_1_2->total,
                                            'desc' => $item_1_2->description,
                                            'product_title' => $item_1_2->product->title,
                                            'date' => $order_1->approved_at->format(DatetimeEnum::US_DATE_VIEW),
                                        ],
                                        [
                                            'po' => $order_1->po,
                                            'qty' => $item_1_3->qty,
                                            'price' => $item_1_3->price,
                                            'total' => $item_1_3->total,
                                            'desc' => $item_1_3->description,
                                            'product_title' => $item_1_3->product->title,
                                            'date' => $order_1->approved_at->format(DatetimeEnum::US_DATE_VIEW),
                                        ],
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ])
            ->assertJsonCount(2, 'data.'.self::MUTATION)
            ->assertJsonCount(1, 'data.'.self::MUTATION.'.0.locations')
            ->assertJsonCount(3, 'data.'.self::MUTATION.'.0.locations.0.items')
            ->assertJsonCount(2, 'data.'.self::MUTATION.'.1.locations')
            ->assertJsonCount(1, 'data.'.self::MUTATION.'.1.locations.0.items')
            ->assertJsonCount(5, 'data.'.self::MUTATION.'.1.locations.1.items')
        ;
    }

    /** @test */
    public function get_report_data_for_main_company_dealer(): void
    {
        $corp = Corporation::factory()->create();

        $company_1 = $this->companyBuilder->setCorporation($corp)->create();
        $company_2 = $this->companyBuilder->setCorporation($corp)->create();
        $company_3 = $this->companyBuilder->create();

        $address_1_1 = $this->addressBuilder->setCompany($company_1)->create();
        $address_1_2 = $this->addressBuilder->setCompany($company_1)->create();
        $address_2_1 = $this->addressBuilder->setCompany($company_2)->create();
        $address_3_1 = $this->addressBuilder->setCompany($company_3)->create();

        $dealer_1 = $this->dealerBuilder->setCompany($company_1)
            ->setData(['is_main_company' => true])->create();
        $dealer_2 = $this->dealerBuilder->setCompany($company_2)->create();
        $dealer_3 = $this->dealerBuilder->setCompany($company_3)->create();

        $this->loginAsDealerWithRole($dealer_1);

        $product_1 = $this->productBuilder->create();
        $product_2 = $this->productBuilder->create();

        $order_1 = $this->orderBuilder->setStatus(OrderStatus::SHIPPED)
            ->setDealer($dealer_1)->setData(['approved_at' => CarbonImmutable::now()])
            ->setShippingAddress($address_1_1)->create();
        $order_2 = $this->orderBuilder->setStatus(OrderStatus::SHIPPED)
            ->setDealer($dealer_1)->setData(['approved_at' => CarbonImmutable::now()->addDay()])
            ->setShippingAddress($address_1_1)->create();
        $order_3 = $this->orderBuilder->setStatus(OrderStatus::SHIPPED)
            ->setDealer($dealer_1)->setData(['approved_at' => CarbonImmutable::now()->addHour()])
            ->setShippingAddress($address_1_2)->create();
        $order_4 = $this->orderBuilder->setStatus(OrderStatus::SHIPPED)
            ->setDealer($dealer_2)->setData(['approved_at' => CarbonImmutable::now()->subHour()])
            ->setShippingAddress($address_2_1)->create();
        $order_5 = $this->orderBuilder->setStatus(OrderStatus::SHIPPED)
            ->setDealer($dealer_3)->setData(['approved_at' => CarbonImmutable::now()->subHour()])
            ->setShippingAddress($address_3_1)->create();

        $item_1_1 = $this->itemBuilder->setOrder($order_1)->setProduct($product_1)->create();
        $item_1_2 = $this->itemBuilder->setOrder($order_1)->setProduct($product_1)->create();
        $item_1_3 = $this->itemBuilder->setOrder($order_1)->setProduct($product_1)->create();

        $item_2_1 = $this->itemBuilder->setOrder($order_2)->setProduct($product_1)->create();
        $item_2_2 = $this->itemBuilder->setOrder($order_2)->setProduct($product_2)->create();

        $item_3_1 = $this->itemBuilder->setOrder($order_3)->setProduct($product_2)->create();

        $item_4_1 = $this->itemBuilder->setOrder($order_4)->setProduct($product_1)->create();
        $item_4_2 = $this->itemBuilder->setOrder($order_4)->setProduct($product_2)->create();
        $item_4_3 = $this->itemBuilder->setOrder($order_4)->setProduct($product_1)->create();

        $this->postGraphQL([
            'query' => $this->getQueryStr()
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        [
                            'company_name' => $company_1->business_name,
                            'total' => $item_3_1->total + $item_2_1->total + $item_2_2->total + $item_1_1->total + $item_1_2->total + $item_1_3->total,
                            'locations' => [
                                [
                                    'location_name' => $address_1_2->name,
                                    'total' => $item_3_1->total,
                                    'items' => [
                                        [
                                            'po' => $order_3->po,
                                            'qty' => $item_3_1->qty,
                                            'price' => $item_3_1->price,
                                            'total' => $item_3_1->total,
                                            'desc' => $item_3_1->description,
                                            'product_title' => $item_3_1->product->title,
                                            'date' => $order_3->approved_at->format(DatetimeEnum::US_DATE_VIEW),
                                        ]
                                    ]
                                ],
                                [
                                    'location_name' => $address_1_1->name,
                                    'total' => $item_2_1->total + $item_2_2->total + $item_1_1->total + $item_1_2->total + $item_1_3->total,
                                    'items' => [
                                        [
                                            'po' => $order_2->po,
                                            'qty' => $item_2_1->qty,
                                            'price' => $item_2_1->price,
                                            'total' => $item_2_1->total,
                                            'desc' => $item_2_1->description,
                                            'product_title' => $item_2_1->product->title,
                                            'date' => $order_2->approved_at->format(DatetimeEnum::US_DATE_VIEW),
                                        ],
                                        [
                                            'po' => $order_2->po,
                                            'qty' => $item_2_2->qty,
                                            'price' => $item_2_2->price,
                                            'total' => $item_2_2->total,
                                            'desc' => $item_2_2->description,
                                            'product_title' => $item_2_2->product->title,
                                            'date' => $order_2->approved_at->format(DatetimeEnum::US_DATE_VIEW),
                                        ],
                                        [
                                            'po' => $order_1->po,
                                            'qty' => $item_1_1->qty,
                                            'price' => $item_1_1->price,
                                            'total' => $item_1_1->total,
                                            'desc' => $item_1_1->description,
                                            'product_title' => $item_1_1->product->title,
                                            'date' => $order_1->approved_at->format(DatetimeEnum::US_DATE_VIEW),
                                        ],
                                        [
                                            'po' => $order_1->po,
                                            'qty' => $item_1_2->qty,
                                            'price' => $item_1_2->price,
                                            'total' => $item_1_2->total,
                                            'desc' => $item_1_2->description,
                                            'product_title' => $item_1_2->product->title,
                                            'date' => $order_1->approved_at->format(DatetimeEnum::US_DATE_VIEW),
                                        ],
                                        [
                                            'po' => $order_1->po,
                                            'qty' => $item_1_3->qty,
                                            'price' => $item_1_3->price,
                                            'total' => $item_1_3->total,
                                            'desc' => $item_1_3->description,
                                            'product_title' => $item_1_3->product->title,
                                            'date' => $order_1->approved_at->format(DatetimeEnum::US_DATE_VIEW),
                                        ],
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ])
            ->assertJsonCount(1, 'data.'.self::MUTATION.'')
            ->assertJsonCount(2, 'data.'.self::MUTATION.'.0.locations')
            ->assertJsonCount(1, 'data.'.self::MUTATION.'.0.locations.0.items')
            ->assertJsonCount(5, 'data.'.self::MUTATION.'.0.locations.1.items')
        ;
    }

    /** @test */
    public function get_report_data_for_simple_dealer(): void
    {
        $corp = Corporation::factory()->create();

        $company_1 = $this->companyBuilder->setCorporation($corp)->create();
        $company_2 = $this->companyBuilder->setCorporation($corp)->create();
        $company_3 = $this->companyBuilder->create();

        $address_1_1 = $this->addressBuilder->setCompany($company_1)->create();
        $address_1_2 = $this->addressBuilder->setCompany($company_1)->create();
        $address_1_3 = $this->addressBuilder->setCompany($company_1)->create();
        $address_2_1 = $this->addressBuilder->setCompany($company_2)->create();
        $address_3_1 = $this->addressBuilder->setCompany($company_3)->create();

        $dealer_1 = $this->dealerBuilder->setAddresses($address_1_2, $address_1_3)->setData([
            'is_main' => false,
            'is_main_company' => false])->setCompany($company_1)->create();
        $dealer_2 = $this->dealerBuilder->setCompany($company_1)->create();
        $dealer_3 = $this->dealerBuilder->setCompany($company_3)->create();

        $this->loginAsDealerWithRole($dealer_1);

        $product_1 = $this->productBuilder->create();
        $product_2 = $this->productBuilder->create();

        $order_1 = $this->orderBuilder->setStatus(OrderStatus::SHIPPED)
            ->setDealer($dealer_2)->setData(['approved_at' => CarbonImmutable::now()])
            ->setShippingAddress($address_1_1)->create();

        $order_2 = $this->orderBuilder->setStatus(OrderStatus::SHIPPED)
            ->setDealer($dealer_2)->setData(['approved_at' => CarbonImmutable::now()->addDay()])
            ->setShippingAddress($address_1_2)->create();

        $order_3 = $this->orderBuilder->setStatus(OrderStatus::SHIPPED)
            ->setDealer($dealer_1)->setData(['approved_at' => CarbonImmutable::now()->addHour()])
            ->setShippingAddress($address_1_2)->create();
        $order_3_2 = $this->orderBuilder->setStatus(OrderStatus::SHIPPED)
            ->setDealer($dealer_1)->setData(['approved_at' => CarbonImmutable::now()->addHour()])
            ->setShippingAddress($address_1_3)->create();

        $order_4 = $this->orderBuilder->setStatus(OrderStatus::SHIPPED)
            ->setDealer($dealer_2)->setData(['approved_at' => CarbonImmutable::now()->subHour()])
            ->setShippingAddress($address_2_1)->create();
        $order_5 = $this->orderBuilder->setStatus(OrderStatus::SHIPPED)
            ->setDealer($dealer_3)->setData(['approved_at' => CarbonImmutable::now()->subHour()])
            ->setShippingAddress($address_3_1)->create();

        $item_1_1 = $this->itemBuilder->setOrder($order_1)->setProduct($product_1)->create();
        $item_1_2 = $this->itemBuilder->setOrder($order_1)->setProduct($product_1)->create();
        $item_1_3 = $this->itemBuilder->setOrder($order_1)->setProduct($product_1)->create();

        $item_2_1 = $this->itemBuilder->setOrder($order_2)->setProduct($product_1)->create();

        $item_3_1 = $this->itemBuilder->setOrder($order_3)->setProduct($product_2)->create();

        $item_3_2_1 = $this->itemBuilder->setOrder($order_3_2)->setProduct($product_2)->create();
        $item_3_2_2 = $this->itemBuilder->setOrder($order_3_2)->setProduct($product_2)->create();

        $item_4_1 = $this->itemBuilder->setOrder($order_4)->setProduct($product_1)->create();
        $item_4_2 = $this->itemBuilder->setOrder($order_4)->setProduct($product_2)->create();
        $item_4_3 = $this->itemBuilder->setOrder($order_4)->setProduct($product_1)->create();

        $this->postGraphQL([
            'query' => $this->getQueryStr()
        ])
//            ->dump()
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        [
                            'company_name' => $company_1->business_name,
                            'total' => $item_3_1->total + $item_3_2_1->total + $item_3_2_2->total + $item_2_1->total,
                            'locations' => [
                                [
                                    'location_name' => $address_1_3->name,
                                    'total' => $item_3_2_1->total + $item_3_2_2->total,
                                    'items' => [
                                        [
                                            'po' => $order_3_2->po,
                                            'qty' => $item_3_2_1->qty,
                                            'price' => $item_3_2_1->price,
                                            'total' => $item_3_2_1->total,
                                            'desc' => $item_3_2_1->description,
                                            'product_title' => $item_3_2_1->product->title,
                                            'date' => $order_3_2->approved_at->format(DatetimeEnum::US_DATE_VIEW),
                                        ],
                                        [
                                            'po' => $order_3_2->po,
                                            'qty' => $item_3_2_2->qty,
                                            'price' => $item_3_2_2->price,
                                            'total' => $item_3_2_2->total,
                                            'desc' => $item_3_2_2->description,
                                            'product_title' => $item_3_2_2->product->title,
                                            'date' => $order_3_2->approved_at->format(DatetimeEnum::US_DATE_VIEW),
                                        ]
                                    ]
                                ],
                                [
                                    'location_name' => $address_1_2->name,
                                    'total' => $item_3_1->total + $item_2_1->total,
                                    'items' => [
                                        [
                                            'po' => $order_3->po,
                                            'qty' => $item_3_1->qty,
                                            'price' => $item_3_1->price,
                                            'total' => $item_3_1->total,
                                            'desc' => $item_3_1->description,
                                            'product_title' => $item_3_1->product->title,
                                            'date' => $order_3->approved_at->format(DatetimeEnum::US_DATE_VIEW),
                                        ],
                                        [
                                            'po' => $order_2->po,
                                            'qty' => $item_2_1->qty,
                                            'price' => $item_2_1->price,
                                            'total' => $item_2_1->total,
                                            'desc' => $item_2_1->description,
                                            'product_title' => $item_2_1->product->title,
                                            'date' => $order_2->approved_at->format(DatetimeEnum::US_DATE_VIEW),
                                        ]
                                    ]
                                ],
                            ]
                        ]
                    ]
                ]
            ])
            ->assertJsonCount(1, 'data.'.self::MUTATION)
            ->assertJsonCount(2, 'data.'.self::MUTATION.'.0.locations')
            ->assertJsonCount(2, 'data.'.self::MUTATION.'.0.locations.0.items')
            ->assertJsonCount(2, 'data.'.self::MUTATION.'.0.locations.1.items')
        ;
    }

    /** @test */
    public function get_report_data_only_shipped_status(): void
    {
        $company_1 = $this->companyBuilder->create();

        $address_1_1 = $this->addressBuilder->setCompany($company_1)->create();

        $dealer_1 = $this->dealerBuilder->setCompany($company_1)->create();

        $this->loginAsDealerWithRole($dealer_1);

        $order_1 = $this->orderBuilder->setStatus(OrderStatus::SHIPPED)
            ->setDealer($dealer_1)->setData(['approved_at' => CarbonImmutable::now()])
            ->setShippingAddress($address_1_1)->create();
        $order_2 = $this->orderBuilder->setStatus(OrderStatus::DRAFT)
            ->setDealer($dealer_1)->setData(['approved_at' => CarbonImmutable::now()])
            ->setShippingAddress($address_1_1)->create();
        $order_3 = $this->orderBuilder->setStatus(OrderStatus::SENT)
            ->setDealer($dealer_1)->setData(['approved_at' => CarbonImmutable::now()])
            ->setShippingAddress($address_1_1)->create();
        $order_4 = $this->orderBuilder->setStatus(OrderStatus::APPROVED)
            ->setDealer($dealer_1)->setData(['approved_at' => CarbonImmutable::now()])
            ->setShippingAddress($address_1_1)->create();
        $order_5 = $this->orderBuilder->setStatus(OrderStatus::CANCELED)
            ->setDealer($dealer_1)->setData(['approved_at' => CarbonImmutable::now()])
            ->setShippingAddress($address_1_1)->create();
        $order_6 = $this->orderBuilder->setStatus(OrderStatus::BACKORDER)
            ->setDealer($dealer_1)->setData(['approved_at' => CarbonImmutable::now()])
            ->setShippingAddress($address_1_1)->create();

        $item_1 = $this->itemBuilder->setOrder($order_1)->create();
        $item_2 = $this->itemBuilder->setOrder($order_2)->create();
        $item_3 = $this->itemBuilder->setOrder($order_3)->create();
        $item_4 = $this->itemBuilder->setOrder($order_4)->create();
        $item_5 = $this->itemBuilder->setOrder($order_5)->create();
        $item_6 = $this->itemBuilder->setOrder($order_6)->create();

        $this->postGraphQL([
            'query' => $this->getQueryStr()
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        [
                            'company_name' => $company_1->business_name,
                            'total' => $item_1->total,
                            'locations' => [
                                [
                                    'location_name' => $address_1_1->name,
                                    'total' => $item_1->total,
                                    'items' => [
                                        [
                                            'po' => $order_1->po,
                                            'qty' => $item_1->qty,
                                            'price' => $item_1->price,
                                            'total' => $item_1->total,
                                            'desc' => $item_1->description,
                                            'product_title' => $item_1->product->title,
                                            'date' => $order_1->approved_at->format(DatetimeEnum::US_DATE_VIEW),
                                        ],
                                    ]
                                ]
                            ]
                        ],
                    ]
                ]
            ])
            ->assertJsonCount(1, 'data.'.self::MUTATION)
            ->assertJsonCount(1, 'data.'.self::MUTATION.'.0.locations')
            ->assertJsonCount(1, 'data.'.self::MUTATION.'.0.locations.0.items')
        ;
    }

    /** @test */
    public function get_report_data_empty(): void
    {
        $this->loginAsDealerWithRole();

        $this->postGraphQL([
            'query' => $this->getQueryStr()
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => []
                ]
            ])
            ->assertJsonCount(0, 'data.'.self::MUTATION)
        ;
    }

    protected function getQueryStr(): string
    {
        return sprintf(
            '
            {
                %s {
                    company_name
                    total
                    locations {
                        location_name
                        total
                        items {
                            po
                            qty
                            price
                            desc
                            product_title
                            date
                            total
                        }
                    }
                }
            }',
            self::MUTATION
        );
    }

    /** @test */
    public function filter_by_location(): void
    {
        $corp = Corporation::factory()->create();

        $company_1 = $this->companyBuilder->setCorporation($corp)->create();
        $company_2 = $this->companyBuilder->setCorporation($corp)->create();
        $company_3 = $this->companyBuilder->create();

        $address_1_1 = $this->addressBuilder->setCompany($company_1)->create();
        $address_1_2 = $this->addressBuilder->setCompany($company_1)->create();
        $address_2_1 = $this->addressBuilder->setCompany($company_2)->create();
        $address_3_1 = $this->addressBuilder->setCompany($company_3)->create();

        $dealer_1 = $this->dealerBuilder->setCompany($company_1)
            ->setData(['is_main' => true])->create();
        $dealer_2 = $this->dealerBuilder->setCompany($company_2)->create();
        $dealer_3 = $this->dealerBuilder->setCompany($company_3)->create();

        $this->loginAsDealerWithRole($dealer_1);

        $product_1 = $this->productBuilder->create();
        $product_2 = $this->productBuilder->create();

        $order_1 = $this->orderBuilder->setStatus(OrderStatus::SHIPPED)
            ->setDealer($dealer_1)->setData(['approved_at' => CarbonImmutable::now()])
            ->setShippingAddress($address_1_1)->create();
        $order_2 = $this->orderBuilder->setStatus(OrderStatus::SHIPPED)
            ->setDealer($dealer_1)->setData(['approved_at' => CarbonImmutable::now()->addDay()])
            ->setShippingAddress($address_1_1)->create();
        $order_3 = $this->orderBuilder->setStatus(OrderStatus::SHIPPED)
            ->setDealer($dealer_1)->setData(['approved_at' => CarbonImmutable::now()->addHour()])
            ->setShippingAddress($address_1_2)->create();
        $order_4 = $this->orderBuilder->setStatus(OrderStatus::SHIPPED)
            ->setDealer($dealer_2)->setData(['approved_at' => CarbonImmutable::now()->subHour()])
            ->setShippingAddress($address_2_1)->create();
        $order_5 = $this->orderBuilder->setStatus(OrderStatus::SHIPPED)
            ->setDealer($dealer_3)->setData(['approved_at' => CarbonImmutable::now()->subHour()])
            ->setShippingAddress($address_3_1)->create();

        $item_1_1 = $this->itemBuilder->setOrder($order_1)->setProduct($product_1)->create();
        $item_1_2 = $this->itemBuilder->setOrder($order_1)->setProduct($product_1)->create();
        $item_1_3 = $this->itemBuilder->setOrder($order_1)->setProduct($product_1)->create();

        $item_2_1 = $this->itemBuilder->setOrder($order_2)->setProduct($product_1)->create();
        $item_2_2 = $this->itemBuilder->setOrder($order_2)->setProduct($product_2)->create();

        $item_3_1 = $this->itemBuilder->setOrder($order_3)->setProduct($product_2)->create();

        $item_4_1 = $this->itemBuilder->setOrder($order_4)->setProduct($product_1)->create();
        $item_4_2 = $this->itemBuilder->setOrder($order_4)->setProduct($product_2)->create();
        $item_4_3 = $this->itemBuilder->setOrder($order_4)->setProduct($product_1)->create();

        $this->postGraphQL([
            'query' => $this->getQueryStrByLocation($address_1_1->id)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        [
                            'company_name' => $company_1->business_name,
                            'total' => $item_2_1->total + $item_2_2->total + $item_1_1->total + $item_1_2->total + $item_1_3->total,
                            'locations' => [
                                [
                                    'location_name' => $address_1_1->name,
                                    'total' => $item_2_1->total + $item_2_2->total + $item_1_1->total + $item_1_2->total + $item_1_3->total,
                                    'items' => [
                                        [
                                            'po' => $order_2->po,
                                            'qty' => $item_2_1->qty,
                                            'price' => $item_2_1->price,
                                            'total' => $item_2_1->total,
                                            'desc' => $item_2_1->description,
                                            'product_title' => $item_2_1->product->title,
                                            'date' => $order_2->approved_at->format(DatetimeEnum::US_DATE_VIEW),
                                        ],
                                        [
                                            'po' => $order_2->po,
                                            'qty' => $item_2_2->qty,
                                            'price' => $item_2_2->price,
                                            'total' => $item_2_2->total,
                                            'desc' => $item_2_2->description,
                                            'product_title' => $item_2_2->product->title,
                                            'date' => $order_2->approved_at->format(DatetimeEnum::US_DATE_VIEW),
                                        ],
                                        [
                                            'po' => $order_1->po,
                                            'qty' => $item_1_1->qty,
                                            'price' => $item_1_1->price,
                                            'total' => $item_1_1->total,
                                            'desc' => $item_1_1->description,
                                            'product_title' => $item_1_1->product->title,
                                            'date' => $order_1->approved_at->format(DatetimeEnum::US_DATE_VIEW),
                                        ],
                                        [
                                            'po' => $order_1->po,
                                            'qty' => $item_1_2->qty,
                                            'price' => $item_1_2->price,
                                            'total' => $item_1_2->total,
                                            'desc' => $item_1_2->description,
                                            'product_title' => $item_1_2->product->title,
                                            'date' => $order_1->approved_at->format(DatetimeEnum::US_DATE_VIEW),
                                        ],
                                        [
                                            'po' => $order_1->po,
                                            'qty' => $item_1_3->qty,
                                            'price' => $item_1_3->price,
                                            'total' => $item_1_3->total,
                                            'desc' => $item_1_3->description,
                                            'product_title' => $item_1_3->product->title,
                                            'date' => $order_1->approved_at->format(DatetimeEnum::US_DATE_VIEW),
                                        ],
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ])
            ->assertJsonCount(1, 'data.'.self::MUTATION)
            ->assertJsonCount(1, 'data.'.self::MUTATION.'.0.locations')
            ->assertJsonCount(5, 'data.'.self::MUTATION.'.0.locations.0.items')
        ;
    }

    protected function getQueryStrByLocation($id): string
    {
        return sprintf(
            '
            {
                %s (location_id: %s) {
                    company_name
                    total
                    locations {
                        location_name
                        total
                        items {
                            po
                            qty
                            price
                            desc
                            product_title
                            date
                            total
                        }
                    }
                }
            }',
            self::MUTATION,
            $id
        );
    }

    /** @test */
    public function filter_by_company(): void
    {
        $corp = Corporation::factory()->create();

        $company_1 = $this->companyBuilder->setCorporation($corp)->create();
        $company_2 = $this->companyBuilder->setCorporation($corp)->create();
        $company_3 = $this->companyBuilder->create();

        $address_1_1 = $this->addressBuilder->setCompany($company_1)->create();
        $address_1_2 = $this->addressBuilder->setCompany($company_1)->create();
        $address_2_1 = $this->addressBuilder->setCompany($company_2)->create();
        $address_3_1 = $this->addressBuilder->setCompany($company_3)->create();

        $dealer_1 = $this->dealerBuilder->setCompany($company_1)
            ->setData(['is_main' => true])->create();
        $dealer_2 = $this->dealerBuilder->setCompany($company_2)->create();
        $dealer_3 = $this->dealerBuilder->setCompany($company_3)->create();

        $this->loginAsDealerWithRole($dealer_1);

        $product_1 = $this->productBuilder->create();
        $product_2 = $this->productBuilder->create();

        $order_1 = $this->orderBuilder->setStatus(OrderStatus::SHIPPED)
            ->setDealer($dealer_1)->setData(['approved_at' => CarbonImmutable::now()])
            ->setShippingAddress($address_1_1)->create();
        $order_2 = $this->orderBuilder->setStatus(OrderStatus::SHIPPED)
            ->setDealer($dealer_1)->setData(['approved_at' => CarbonImmutable::now()->addDay()])
            ->setShippingAddress($address_1_1)->create();
        $order_3 = $this->orderBuilder->setStatus(OrderStatus::SHIPPED)
            ->setDealer($dealer_1)->setData(['approved_at' => CarbonImmutable::now()->addHour()])
            ->setShippingAddress($address_1_2)->create();
        $order_4 = $this->orderBuilder->setStatus(OrderStatus::SHIPPED)
            ->setDealer($dealer_2)->setData(['approved_at' => CarbonImmutable::now()->subHour()])
            ->setShippingAddress($address_2_1)->create();
        $order_5 = $this->orderBuilder->setStatus(OrderStatus::SHIPPED)
            ->setDealer($dealer_3)->setData(['approved_at' => CarbonImmutable::now()->subHour()])
            ->setShippingAddress($address_3_1)->create();

        $item_1_1 = $this->itemBuilder->setOrder($order_1)->setProduct($product_1)->create();
        $item_1_2 = $this->itemBuilder->setOrder($order_1)->setProduct($product_1)->create();
        $item_1_3 = $this->itemBuilder->setOrder($order_1)->setProduct($product_1)->create();

        $item_2_1 = $this->itemBuilder->setOrder($order_2)->setProduct($product_1)->create();
        $item_2_2 = $this->itemBuilder->setOrder($order_2)->setProduct($product_2)->create();

        $item_3_1 = $this->itemBuilder->setOrder($order_3)->setProduct($product_2)->create();

        $item_4_1 = $this->itemBuilder->setOrder($order_4)->setProduct($product_1)->create();
        $item_4_2 = $this->itemBuilder->setOrder($order_4)->setProduct($product_2)->create();
        $item_4_3 = $this->itemBuilder->setOrder($order_4)->setProduct($product_1)->create();

        $this->postGraphQL([
            'query' => $this->getQueryStrByCompany($company_2->id)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        [
                            'company_name' => $company_2->business_name,
                            'total' => $item_4_1->total + $item_4_2->total + $item_4_3->total,
                            'locations' => [
                                [
                                    'location_name' => $address_2_1->name,
                                    'total' => $item_4_1->total + $item_4_2->total + $item_4_3->total,
                                    'items' => [
                                        [
                                            'po' => $order_4->po,
                                            'qty' => $item_4_1->qty,
                                            'price' => $item_4_1->price,
                                            'total' => $item_4_1->total,
                                            'desc' => $item_4_1->description,
                                            'product_title' => $item_4_1->product->title,
                                            'date' => $order_4->approved_at->format(DatetimeEnum::US_DATE_VIEW),
                                        ],
                                        [
                                            'po' => $order_4->po,
                                            'qty' => $item_4_2->qty,
                                            'price' => (float)$item_4_2->price,
                                            'total' => (float)$item_4_2->total,
                                            'desc' => $item_4_2->description,
                                            'product_title' => $item_4_2->product->title,
                                            'date' => $order_4->approved_at->format(DatetimeEnum::US_DATE_VIEW),
                                        ],
                                        [
                                            'po' => $order_4->po,
                                            'qty' => $item_4_3->qty,
                                            'price' => $item_4_3->price,
                                            'total' => $item_4_3->total,
                                            'desc' => $item_4_3->description,
                                            'product_title' => $item_4_3->product->title,
                                            'date' => $order_4->approved_at->format(DatetimeEnum::US_DATE_VIEW),
                                        ],
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ])
            ->assertJsonCount(1, 'data.'.self::MUTATION)
            ->assertJsonCount(1, 'data.'.self::MUTATION.'.0.locations')
            ->assertJsonCount(3, 'data.'.self::MUTATION.'.0.locations.0.items')
        ;
    }

    protected function getQueryStrByCompany($id): string
    {
        return sprintf(
            '
            {
                %s (company_id: %s) {
                    company_name
                    total
                    locations {
                        location_name
                        total
                        items {
                            po
                            qty
                            price
                            desc
                            product_title
                            date
                            total
                        }
                    }
                }
            }',
            self::MUTATION,
            $id
        );
    }

    /** @test */
    public function filter_by_date(): void
    {
        $corp = Corporation::factory()->create();

        $company_1 = $this->companyBuilder->setCorporation($corp)->create();
        $company_2 = $this->companyBuilder->setCorporation($corp)->create();
        $company_3 = $this->companyBuilder->create();

        $address_1_1 = $this->addressBuilder->setCompany($company_1)->create();
        $address_1_2 = $this->addressBuilder->setCompany($company_1)->create();
        $address_2_1 = $this->addressBuilder->setCompany($company_2)->create();
        $address_3_1 = $this->addressBuilder->setCompany($company_3)->create();

        $dealer_1 = $this->dealerBuilder->setCompany($company_1)
            ->setData(['is_main' => true])->create();
        $dealer_2 = $this->dealerBuilder->setCompany($company_2)->create();
        $dealer_3 = $this->dealerBuilder->setCompany($company_3)->create();

        $this->loginAsDealerWithRole($dealer_1);

        $product_1 = $this->productBuilder->create();
        $product_2 = $this->productBuilder->create();

        $order_1 = $this->orderBuilder->setStatus(OrderStatus::SHIPPED)
            ->setDealer($dealer_1)->setData(['approved_at' => CarbonImmutable::now()->addDays(3)])
            ->setShippingAddress($address_1_1)->create();
        $order_2 = $this->orderBuilder->setStatus(OrderStatus::SHIPPED)
            ->setDealer($dealer_1)->setData(['approved_at' => CarbonImmutable::now()->addDays(3)])
            ->setShippingAddress($address_1_1)->create();
        $order_3 = $this->orderBuilder->setStatus(OrderStatus::SHIPPED)
            ->setDealer($dealer_1)->setData(['approved_at' => CarbonImmutable::now()->addDays(1)])
            ->setShippingAddress($address_1_2)->create();
        $order_4 = $this->orderBuilder->setStatus(OrderStatus::SHIPPED)
            ->setDealer($dealer_2)->setData(['approved_at' => CarbonImmutable::now()->subDay()])
            ->setShippingAddress($address_2_1)->create();
        $order_5 = $this->orderBuilder->setStatus(OrderStatus::SHIPPED)
            ->setDealer($dealer_3)->setData(['approved_at' => CarbonImmutable::now()->subDay()])
            ->setShippingAddress($address_3_1)->create();

        $item_1_1 = $this->itemBuilder->setOrder($order_1)->setProduct($product_1)->create();
        $item_1_2 = $this->itemBuilder->setOrder($order_1)->setProduct($product_1)->create();
        $item_1_3 = $this->itemBuilder->setOrder($order_1)->setProduct($product_1)->create();

        $item_2_1 = $this->itemBuilder->setOrder($order_2)->setProduct($product_1)->create();
        $item_2_2 = $this->itemBuilder->setOrder($order_2)->setProduct($product_2)->create();

        $item_3_1 = $this->itemBuilder->setOrder($order_3)->setProduct($product_2)->create();

        $item_4_1 = $this->itemBuilder->setOrder($order_4)->setProduct($product_1)->create();
        $item_4_2 = $this->itemBuilder->setOrder($order_4)->setProduct($product_2)->create();
        $item_4_3 = $this->itemBuilder->setOrder($order_4)->setProduct($product_1)->create();

        $this->postGraphQL([
            'query' => $this->getQueryStrByDate(
                CarbonImmutable::now()->format('Y-m-d'),
                CarbonImmutable::now()->addDays(2)->format('Y-m-d'),
            )
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        [
                            'company_name' => $company_1->business_name,
                            'total' => $item_3_1->total,
                            'locations' => [
                                [
                                    'location_name' => $address_1_2->name,
                                    'total' => $item_3_1->total,
                                    'items' => [
                                        [
                                            'po' => $order_3->po,
                                            'qty' => $item_3_1->qty,
                                            'price' => $item_3_1->price,
                                            'total' => $item_3_1->total,
                                            'desc' => $item_3_1->description,
                                            'product_title' => $item_3_1->product->title,
                                            'date' => $order_3->approved_at->format(DatetimeEnum::US_DATE_VIEW),
                                        ]
                                    ]
                                ],
                            ]
                        ]
                    ]
                ]
            ])
            ->assertJsonCount(1, 'data.'.self::MUTATION)
            ->assertJsonCount(1, 'data.'.self::MUTATION.'.0.locations')
            ->assertJsonCount(1, 'data.'.self::MUTATION.'.0.locations.0.items')
        ;
    }

    protected function getQueryStrByDate($from, $to): string
    {
        return sprintf(
            '
            {
                %s (date_from: "%s", date_to: "%s") {
                    company_name
                    total
                    locations {
                        location_name
                        total
                        items {
                            po
                            qty
                            price
                            desc
                            product_title
                            date
                            total
                        }
                    }
                }
            }',
            self::MUTATION,
            $from,
            $to
        );
    }

    /** @test */
    public function not_auth(): void
    {
        $this->orderBuilder->create();

        $this->postGraphQL([
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
        $dealer = $this->loginAsDealer();

        $this->orderBuilder->setDealer($dealer)->create();

        $this->postGraphQL([
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

