<?php

namespace Tests\Feature\Queries\BackOffice\Dealers;

use App\GraphQL\Queries\BackOffice\Dealers\DealersQuery;
use App\Models\Dealers\Dealer;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Company\CompanyBuilder;
use Tests\Builders\Dealers\DealerBuilder;
use Tests\TestCase;

class DealersQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = DealersQuery::NAME;

    protected DealerBuilder $dealerBuilder;
    protected CompanyBuilder $companyBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->dealerBuilder = resolve(DealerBuilder::class);
        $this->companyBuilder = resolve(CompanyBuilder::class);
    }

    /** @test */
    public function success_paginator(): void
    {
        $this->loginAsSuperAdmin();

        Dealer::factory()->times(20)->create();

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

        Dealer::factory()->times(20)->create();

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

        Dealer::factory()->times(5)->create();

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

        /** @var $dealer Dealer */
        $dealer = $this->dealerBuilder->create();
        $this->dealerBuilder->create();
        $this->dealerBuilder->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrByID($dealer->id)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'data' => [
                            [
                                'id' => $dealer->id,
                                'email' => $dealer->email,
                                'name' => $dealer->first_name,
                                'is_main' => $dealer->is_main,
                                'is_main_company' => $dealer->is_main_company,
                                'company' => [
                                    'id' => $dealer->company_id
                                ]
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
                        email
                        name
                        is_main
                        is_main_company
                        company {
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
    public function filter_by_company(): void
    {
        $this->loginAsSuperAdmin();

        $company_1 = $this->companyBuilder->create();
        $company_2 = $this->companyBuilder->create();

        /** @var $dealer Dealer */
        $dealer_1 = $this->dealerBuilder->setCompany($company_1)->create();
        $dealer_2 = $this->dealerBuilder->setCompany($company_1)->create();
        $dealer_3 = $this->dealerBuilder->setCompany($company_2)->create();
        $this->dealerBuilder->create();
        $this->dealerBuilder->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrByCompanyID($company_1->id)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'data' => [
                            ['id' => $dealer_2->id],
                            ['id' => $dealer_1->id],
                        ],
                    ]
                ]
            ])
            ->assertJsonCount(2, 'data.'.self::MUTATION.'.data')
        ;
    }

    protected function getQueryStrByCompanyID($value): string
    {
        return sprintf(
            '
            {
                %s (company_id: %s) {
                    data {
                        id
                    }
                }
            }',
            self::MUTATION,
            $value
        );
    }
}
