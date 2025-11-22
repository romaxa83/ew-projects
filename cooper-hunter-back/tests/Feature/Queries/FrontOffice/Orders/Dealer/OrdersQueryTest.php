<?php

namespace Tests\Feature\Queries\FrontOffice\Orders\Dealer;

use App\Enums\Orders\Dealer\OrderStatus;
use App\GraphQL\Queries\FrontOffice\Orders\Dealer\OrdersQuery;
use App\Models\Companies\Corporation;
use App\Models\Orders\Dealer\Order;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Company\CompanyBuilder;
use Tests\Builders\Company\CompanyShippingAddressBuilder;
use Tests\Builders\Dealers\DealerBuilder;
use Tests\Builders\Orders\Dealer\OrderBuilder;
use Tests\TestCase;

class OrdersQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = OrdersQuery::NAME;

    protected DealerBuilder $dealerBuilder;
    protected CompanyShippingAddressBuilder $addressBuilder;
    protected OrderBuilder $orderBuilder;
    protected CompanyBuilder $companyBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->companyBuilder = resolve(CompanyBuilder::class);
        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->dealerBuilder = resolve(DealerBuilder::class);
        $this->addressBuilder = resolve(CompanyShippingAddressBuilder::class);
    }

    /** @test */
    public function success_paginator(): void
    {
        $dealer = $this->loginAsDealerWithRole();

        $order_1 = $this->orderBuilder->setDealer($dealer)->create();
        $order_2 = $this->orderBuilder->setDealer($dealer)->create();

        Order::factory()->times(5)->create();

        $this->postGraphQL([
            'query' => $this->getQueryStr()
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'data' => [
                            ['id' => $order_2->id],
                            ['id' => $order_1->id],
                        ],
                        'meta' => [
                            'total' => 2,
                            'per_page' => 15,
                            'current_page' => 1,
                            'from' => 1,
                            'to' => 2,
                            'last_page' => 1,
                            'has_more_pages' => false,
                        ],
                    ]
                ]
            ])
        ;
    }

    /** @test */
    public function success_paginator_for_main_dealer(): void
    {
        $corp = Corporation::factory()->create();
        $company_1 = $this->companyBuilder->setCorporation($corp)->create();
        $company_2 = $this->companyBuilder->setCorporation($corp)->create();
        $company_3 = $this->companyBuilder->create();

        $dealer_1 = $this->dealerBuilder->setData([
            'is_main' => true
        ])->setCompany($company_1)->create();
        $dealer_2 = $this->dealerBuilder->setCompany($company_2)->create();
        $dealer_3 = $this->dealerBuilder->setCompany($company_2)->create();
        $dealer_4 = $this->dealerBuilder->setCompany($company_3)->create();

        $this->loginAsDealerWithRole($dealer_1);
        // check
        $order_1 = $this->orderBuilder->setDealer($dealer_2)->create();
        $order_2 = $this->orderBuilder->setDealer($dealer_2)->create();
        $order_3 = $this->orderBuilder->setDealer($dealer_3)->create();
        $order_4 = $this->orderBuilder->setDealer($dealer_3)->create();
        // not
        $order_5 = $this->orderBuilder->setDealer($dealer_4)->create();
        $order_6 = $this->orderBuilder->setDealer($dealer_4)->create();

        Order::factory()->times(5)->create();

        $this->postGraphQL([
            'query' => $this->getQueryStr()
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'data' => [
                            ['id' => $order_4->id, 'is_owner' => false],
                            ['id' => $order_3->id, 'is_owner' => false],
                            ['id' => $order_2->id, 'is_owner' => false],
                            ['id' => $order_1->id, 'is_owner' => false],
                        ],
                        'meta' => [
                            'total' => 4,
                            'per_page' => 15,
                            'current_page' => 1,
                            'from' => 1,
                            'to' => 4,
                            'last_page' => 1,
                            'has_more_pages' => false,
                        ],
                    ]
                ]
            ])
        ;
    }

    /** @test */
    public function success_paginator_for_main_dealer_and_company(): void
    {
        $corp = Corporation::factory()->create();
        $company_1 = $this->companyBuilder->setCorporation($corp)->create();
        $company_2 = $this->companyBuilder->setCorporation($corp)->create();
        $company_3 = $this->companyBuilder->create();

        $dealer_1 = $this->dealerBuilder->setData([
            'is_main' => true,
            'is_main_company' => true,
        ])->setCompany($company_1)->create();
        $dealer_2 = $this->dealerBuilder->setCompany($company_2)->create();
        $dealer_3 = $this->dealerBuilder->setCompany($company_2)->create();
        $dealer_4 = $this->dealerBuilder->setCompany($company_3)->create();

        $this->loginAsDealerWithRole($dealer_1);
        // check
        $order_1 = $this->orderBuilder->setDealer($dealer_2)->create();
        $order_2 = $this->orderBuilder->setDealer($dealer_2)->create();
        $order_3 = $this->orderBuilder->setDealer($dealer_3)->create();
        $order_4 = $this->orderBuilder->setDealer($dealer_3)->create();
        // not
        $order_5 = $this->orderBuilder->setDealer($dealer_4)->create();
        $order_6 = $this->orderBuilder->setDealer($dealer_4)->create();

        Order::factory()->times(5)->create();

        $this->postGraphQL([
            'query' => $this->getQueryStr()
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'data' => [
                            ['id' => $order_4->id],
                            ['id' => $order_3->id],
                            ['id' => $order_2->id],
                            ['id' => $order_1->id],
                        ],
                        'meta' => [
                            'total' => 4,
                            'per_page' => 15,
                            'current_page' => 1,
                            'from' => 1,
                            'to' => 4,
                            'last_page' => 1,
                            'has_more_pages' => false,
                        ],
                    ]
                ]
            ])
        ;
    }

    /** @test */
    public function paginator_for_main_company_dealer(): void
    {
        $corp = Corporation::factory()->create();
        $company_1 = $this->companyBuilder->setCorporation($corp)->create();
        $company_2 = $this->companyBuilder->setCorporation($corp)->create();
        $company_3 = $this->companyBuilder->create();

        $dealer_1 = $this->dealerBuilder->setData([
            'is_main_company' => true
        ])->setCompany($company_1)->create();
        $dealer_2 = $this->dealerBuilder->setCompany($company_1)->create();
        $dealer_3 = $this->dealerBuilder->setCompany($company_2)->create();
        $dealer_4 = $this->dealerBuilder->setCompany($company_3)->create();

        $this->loginAsDealerWithRole($dealer_1);

        $order_1 = $this->orderBuilder->setDealer($dealer_1)->create();
        $order_2 = $this->orderBuilder->setDealer($dealer_1)->create();
        $order_3 = $this->orderBuilder->setDealer($dealer_2)->create();
        // not
        $order_4 = $this->orderBuilder->setDealer($dealer_3)->create();
        $order_5 = $this->orderBuilder->setDealer($dealer_4)->create();
        $order_6 = $this->orderBuilder->setDealer($dealer_4)->create();

        Order::factory()->times(5)->create();

        $this->postGraphQL([
            'query' => $this->getQueryStr()
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'data' => [
                            ['id' => $order_3->id, 'is_owner' => false],
                            ['id' => $order_2->id, 'is_owner' => true],
                            ['id' => $order_1->id, 'is_owner' => true],
                        ],
                        'meta' => [
                            'total' => 3,
                        ],
                    ]
                ]
            ])
        ;
    }

    /** @test */
    public function paginator_for_simple_dealer(): void
    {
        $corp = Corporation::factory()->create();
        $company_1 = $this->companyBuilder->setCorporation($corp)->create();
        $company_2 = $this->companyBuilder->setCorporation($corp)->create();

        $address_1 = $this->addressBuilder->setCompany($company_1)->create();
        $address_2 = $this->addressBuilder->setCompany($company_1)->create();
        $address_3 = $this->addressBuilder->setCompany($company_1)->create();
        $address_4 = $this->addressBuilder->setCompany($company_2)->create();
        $address_5 = $this->addressBuilder->setCompany($company_2)->create();

        $dealer_1 = $this->dealerBuilder->setAddresses($address_1, $address_2)->setData([
            'is_main' => false,
            'is_main_company' => false
        ])->setCompany($company_1)->create();
        $dealer_2 = $this->dealerBuilder->setCompany($company_1)->setAddresses($address_2)->create();
        $dealer_3 = $this->dealerBuilder->setCompany($company_1)->setAddresses($address_3)->create();

        $this->loginAsDealerWithRole($dealer_1);

        $order_1 = $this->orderBuilder->setDealer($dealer_1)->setShippingAddress($address_1)->create();
        $order_2 = $this->orderBuilder->setDealer($dealer_1)->create();
        $order_3 = $this->orderBuilder->setDealer($dealer_2)->setShippingAddress($address_2)->create();
        // not
        $order_4 = $this->orderBuilder->setDealer($dealer_2)->create();

        $order_5 = $this->orderBuilder->setDealer($dealer_2)->create();
        $order_6 = $this->orderBuilder->setDealer($dealer_3)->create();
        $order_7 = $this->orderBuilder->setDealer($dealer_3)->create();

        Order::factory()->times(5)->create();

        $this->postGraphQL([
            'query' => $this->getQueryStr()
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'data' => [
                            ['id' => $order_3->id, 'is_owner' => false],
                            ['id' => $order_2->id, 'is_owner' => true],
                            ['id' => $order_1->id, 'is_owner' => true],
                        ],
                        'meta' => [
                            'total' => 3,
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
                        is_owner
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

    protected function getQueryStrDealerId($dealerId): string
    {
        return sprintf(
            '
            {
                %s (dealer_id: %s){
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
            $dealerId
        );
    }

    /** @test */
    public function success_paginator_by_page(): void
    {
        $dealer = $this->loginAsDealerWithRole();

        $order_1 = $this->orderBuilder->setDealer($dealer)->create();
        $order_2 = $this->orderBuilder->setDealer($dealer)->create();

        Order::factory()->times(5)->create();

        $this->postGraphQL([
            'query' => $this->getQueryStrByPage(2)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'meta' => [
                            'total' => 2,
                            'per_page' => 15,
                            'current_page' => 2,
                            'from' => null,
                            'to' => null,
                            'last_page' => 1,
                            'has_more_pages' => false,
                        ],
                    ]
                ]
            ])
            ->assertJsonCount(0, 'data.'.self::MUTATION.'.data')
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
        $dealer = $this->loginAsDealerWithRole();

        $this->orderBuilder->setDealer($dealer)->create();
        $this->orderBuilder->setDealer($dealer)->create();
        $this->orderBuilder->setDealer($dealer)->create();

        Order::factory()->times(5)->create();

        $this->postGraphQL([
            'query' => $this->getQueryStrByPerPage(2)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'meta' => [
                            'total' => 3,
                            'per_page' => 2,
                            'current_page' => 1,
                            'from' => 1,
                            'to' => 2,
                            'last_page' => 2,
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
        $dealer = $this->loginAsDealerWithRole();

        $order_1 = $this->orderBuilder->setDealer($dealer)->create();
        $order_2 = $this->orderBuilder->setDealer($dealer)->create();

        $this->postGraphQL([
            'query' => $this->getQueryStrByID($order_1->id)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'data' => [
                            [
                                'id' => $order_1->id,
                            ]
                        ],
                    ]
                ]
            ])
            ->assertJsonCount(1, 'data.'.self::MUTATION.'.data')
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
                        comment
                        shipping_address {
                            id
                        }
                        dealer {
                            id
                        }
                    }
                }
            }',
            self::MUTATION,
            $value
        );
    }

    /** @test */
    public function search_by_po(): void
    {
        $dealer = $this->loginAsDealerWithRole();

        $order_1 = $this->orderBuilder->setDealer($dealer)->setData([
            'po' => 'wermen'
        ])->create();
        $order_2 = $this->orderBuilder->setDealer($dealer)
            ->setData([
                'po' => 'werwomen'
            ])->create();
        $order_3 = $this->orderBuilder->setDealer($dealer)->setData([
            'po' => 'test'
        ])->create();

        $this->postGraphQL([
            'query' => $this->getQueryStrByPo('wer')
        ])
            ->assertJsonCount(2, 'data.'.self::MUTATION.'.data')
        ;
    }

    protected function getQueryStrByPo($value): string
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
    public function filter_by_status(): void
    {
        $dealer = $this->loginAsDealerWithRole();

        $order_1 = $this->orderBuilder->setDealer($dealer)
            ->setStatus(OrderStatus::APPROVED)->create();
        $order_2 = $this->orderBuilder->setDealer($dealer)
            ->setStatus(OrderStatus::APPROVED)->create();
        $order_3 = $this->orderBuilder->setDealer($dealer)
            ->setStatus(OrderStatus::DRAFT)->create();
        $order_4 = $this->orderBuilder->setDealer($dealer)
            ->setStatus(OrderStatus::CANCELED)->create();

        $this->postGraphQL([
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
    public function filter_by_statuses(): void
    {
        $dealer = $this->loginAsDealerWithRole();

        $order_1 = $this->orderBuilder->setDealer($dealer)
            ->setStatus(OrderStatus::APPROVED)->create();
        $order_2 = $this->orderBuilder->setDealer($dealer)
            ->setStatus(OrderStatus::APPROVED)->create();
        $order_3 = $this->orderBuilder->setDealer($dealer)
            ->setStatus(OrderStatus::DRAFT)->create();
        $order_4 = $this->orderBuilder->setDealer($dealer)
            ->setStatus(OrderStatus::CANCELED)->create();

        $this->postGraphQL([
            'query' => $this->getQueryStrByStatuses([OrderStatus::APPROVED, OrderStatus::CANCELED])
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'data' => [
                            ['id' => $order_4->id],
                            ['id' => $order_2->id],
                            ['id' => $order_1->id],
                        ],
                    ]
                ]
            ])
            ->assertJsonCount(3, 'data.'.self::MUTATION.'.data')
        ;
    }

    protected function getQueryStrByStatuses($value): string
    {
        return sprintf(
            '
            {
                %s (statuses: [%s, %s]) {
                    data {
                        id
                    }
                }
            }',
            self::MUTATION,
            $value[0],
            $value[1],
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
