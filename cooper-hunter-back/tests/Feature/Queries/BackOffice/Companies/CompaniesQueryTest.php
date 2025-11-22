<?php

namespace Tests\Feature\Queries\BackOffice\Companies;

use App\Enums\Companies\CompanyStatus;
use App\GraphQL\Queries\BackOffice\Companies\CompaniesQuery;
use App\Models\Companies\Company;
use App\Models\Companies\Corporation;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Company\CompanyBuilder;
use Tests\Builders\Company\CompanyShippingAddressBuilder;
use Tests\Builders\Dealers\DealerBuilder;
use Tests\Builders\Payment\PaymentCardBuilder;
use Tests\TestCase;

class CompaniesQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = CompaniesQuery::NAME;

    protected CompanyBuilder $companyBuilder;
    protected CompanyShippingAddressBuilder $addressBuilder;
    protected DealerBuilder $dealerBuilder;
    protected PaymentCardBuilder $paymentCardBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->addressBuilder = resolve(CompanyShippingAddressBuilder::class);
        $this->companyBuilder = resolve(CompanyBuilder::class);
        $this->dealerBuilder = resolve(DealerBuilder::class);
        $this->paymentCardBuilder = resolve(PaymentCardBuilder::class);
    }

    /** @test */
    public function success_paginator(): void
    {
        $this->loginAsSuperAdmin();

        Company::factory()->times(20)->create();

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

        Company::factory()->times(20)->create();

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

        Company::factory()->times(5)->create();

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
    public function success_different_corp(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $company Company */
        $company_1 = $this->companyBuilder->withCorporation()->create();
        $company_2 = $this->companyBuilder->withCorporation()->create();

        $this->assertNotEquals($company_1->id, $company_2->id);

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr()
        ])
            ->assertJsonCount(2, 'data.'.self::MUTATION.'.data')
        ;
    }

    /** @test */
    public function success_paginator_by_id(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $company Company */
        $company = $this->companyBuilder
            ->withCorporation()
            ->withManager()
            ->withCommercialManager()
            ->create();

        $address_1 = $this->addressBuilder->setCompany($company)
            ->setData(['active' => true])->create();
        $address_2 = $this->addressBuilder->setCompany($company)
            ->setData(['active' => false])->create();

        $dealer_1 = $this->dealerBuilder->setCompany($company)->create();
        $dealer_2 = $this->dealerBuilder->setCompany($company)->create();

        $card_1 = $this->paymentCardBuilder->setMember($company)->default()->create();
        $card_2 = $this->paymentCardBuilder->setMember($company)->create();

        $this->companyBuilder->create();
        $this->companyBuilder->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrByID($company->id)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'data' => [
                            [
                                'id' => $company->id,
                                'status' => $company->status,
                                'type' => $company->type,
                                'business_name' => $company->business_name,
                                'terms' => null,
                                'email' => $company->email->getValue(),
                                'phone' => $company->phone->getValue(),
                                'corporation' => [
                                    'name' => $company->corporation->name
                                ],
                                'country' => [
                                    'id' => $company->country->id
                                ],
                                'state' => [
                                    'id' => $company->state->id
                                ],
                                'dealers' => [
                                    ['id' => $dealer_1->id],
                                    ['id' => $dealer_2->id],
                                ],
                                'manager' => [
                                    'name' => $company->manager->name,
                                    'email' => $company->manager->email->getValue(),
                                    'phone' => $company->manager->phone->getValue(),
                                ],
                                'commercial_manager' => [
                                    'name' => $company->commercialManager->name,
                                    'email' => $company->commercialManager->email->getValue(),
                                    'phone' => $company->commercialManager->phone->getValue(),
                                ],
                                'shipping_addresses' => [
                                    ['id' => $address_1->id],
                                    ['id' => $address_2->id],
                                ],
                                'shipping_addresses_active' => [
                                    ['id' => $address_1->id]
                                ],
                                'payment_cards' => [
                                    [
                                        'id' => $card_1->id,
                                        'type' => $card_1->type,
                                        'code' => $card_1->code,
                                        'expiration_date' => $card_1->expiration_date,
                                        'default' => true
                                    ],
                                    [
                                        'id' => $card_2->id,
                                        'type' => $card_2->type,
                                        'code' => $card_2->code,
                                        'expiration_date' => $card_2->expiration_date,
                                        'default' => false
                                    ]
                                ],
                                'city' => $company->city,
                                'address_line_1' => $company->address_line_1,
                                'address_line_2' => $company->address_line_2,
                                'po_box' => $company->po_box,
                                'zip' => $company->zip,
                                'fax' => $company->fax->getValue(),
                                'taxpayer_id' => $company->taxpayer_id,
                                'tax' => $company->tax,
                                'websites' => $company->websites,
                                'marketplaces' => $company->marketplaces,
                                'trade_names' => $company->trade_names,
                            ]
                        ],
                    ]
                ]
            ])
            ->assertJsonCount(1, 'data.'.self::MUTATION.'.data')
            ->assertJsonCount(2, 'data.'.self::MUTATION.'.data.0.dealers')
            ->assertJsonCount(2, 'data.'.self::MUTATION.'.data.0.shipping_addresses')
            ->assertJsonCount(1, 'data.'.self::MUTATION.'.data.0.shipping_addresses_active')
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
                        type
                        business_name
                        terms
                        email
                        phone
                        country {
                            id
                        }
                        state {
                            id
                        }
                        corporation {
                            name
                        }
                        dealers {
                            id
                        }
                        manager {
                            name
                            email
                            phone
                        }
                        commercial_manager {
                            name
                            email
                            phone
                        }
                        payment_cards {
                            id
                            type
                            default
                            code
                            expiration_date
                        }
                        shipping_addresses {
                            id
                        }
                        shipping_addresses_active {
                            id
                        }
                        city
                        address_line_1
                        address_line_2
                        po_box
                        zip
                        fax
                        taxpayer_id
                        tax
                        websites
                        marketplaces
                        trade_names
                    }
                }
            }',
            self::MUTATION,
            $value
        );
    }

    /** @test */
    public function success_paginator_by_status(): void
    {
        $this->loginAsSuperAdmin();

        $this->companyBuilder->setStatus(CompanyStatus::DRAFT())->create();
        $this->companyBuilder->setStatus(CompanyStatus::APPROVE())->create();
        $this->companyBuilder->setStatus(CompanyStatus::APPROVE())->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrByStatus(CompanyStatus::DRAFT())
        ])
            ->assertJsonCount(1, 'data.'.self::MUTATION.'.data')
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
    public function filter_by_corporation_id(): void
    {
        $this->loginAsSuperAdmin();

        $corp = Corporation::factory()->create();

        $company_1 = $this->companyBuilder->setCorporation($corp)->create();
        $company_2 = $this->companyBuilder->setCorporation($corp)->create();
        $this->companyBuilder->withCorporation()->create();
        $this->companyBuilder->withCorporation()->create();
        $this->companyBuilder->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrByCorporationId($corp->id)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'data' => [
                            ['id' => $company_2->id],
                            ['id' => $company_1->id]
                        ],
                    ]
                ]
            ])
            ->assertJsonCount(2, 'data.'.self::MUTATION.'.data')
        ;
    }

    protected function getQueryStrByCorporationId($value): string
    {
        return sprintf(
            '
            {
                %s (corporation_id: %s) {
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
        Company::factory()->times(2)->create();

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

        Company::factory()->times(2)->create();

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
