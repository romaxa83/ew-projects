<?php

namespace Tests\Feature\Queries\FrontOffice\Commercial;

use App\Enums\Commercial\CommercialQuoteStatusEnum;
use App\GraphQL\Queries\FrontOffice\Commercial\CommercialQuotesQuery;
use App\Models\Technicians\Technician;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Commercial\ProjectBuilder;
use Tests\Builders\Commercial\QuoteBuilder;
use Tests\TestCase;

class CommercialQuotesQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const QUERY = CommercialQuotesQuery::NAME;

    protected $quoteBuilder;
    protected $projectBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->quoteBuilder = resolve(QuoteBuilder::class);
        $this->projectBuilder = resolve(ProjectBuilder::class);
    }

    /** @test */
    public function success_paginator_by_tech(): void
    {
        $tech = $this->loginAsTechnicianWithRole();

        $project_1 = $this->projectBuilder->setTechnician($tech)->create();
        $project_2 = $this->projectBuilder->create();

        $this->quoteBuilder->setProject($project_1)->create();
        $this->quoteBuilder->setProject($project_1)->create();
        $this->quoteBuilder->setProject($project_2)->create();
        $this->quoteBuilder->setProject($project_2)->create();
        $this->quoteBuilder->setProject($project_2)->create();


        $this->postGraphQL([
            'query' => $this->getQueryStr()
        ])
            ->assertJson(
                [
                    'data' => [
                        self::QUERY => [
                            'meta' => [
                                'total' => 2,
                                'per_page' => 15,
                                'current_page' => 1,
                                'from' => 1,
                                'to' => 2,
                                'last_page' => 1,
                                'has_more_pages' => false,
                            ],
                        ],
                    ]
                ]
            )
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
            self::QUERY
        );
    }

    /** @test */
    public function success_paginator_by_tech_per_page(): void
    {
        $tech = $this->loginAsTechnicianWithRole();

        $project_1 = $this->projectBuilder->setTechnician($tech)->create();

        $this->quoteBuilder->setProject($project_1)->create();
        $this->quoteBuilder->setProject($project_1)->create();
        $this->quoteBuilder->setProject($project_1)->create();
        $this->quoteBuilder->setProject($project_1)->create();
        $this->quoteBuilder->setProject($project_1)->create();


        $this->postGraphQL([
            'query' => $this->getQueryStrPerPage(2,2)
        ])
            ->assertJson(
                [
                    'data' => [
                        self::QUERY => [
                            'meta' => [
                                'total' => 5,
                                'per_page' => 2,
                                'current_page' => 2,
                                'from' => 3,
                                'to' => 4,
                                'last_page' => 3,
                                'has_more_pages' => true,
                            ],
                        ],
                    ]
                ]
            )
        ;
    }

    protected function getQueryStrPerPage($perPage, $page): string
    {
        return sprintf(
            '
            {
                %s (per_page: %s, page: %s) {
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
            self::QUERY,
            $perPage,
            $page
        );
    }

    /** @test */
    public function success_by_status(): void
    {
        $tech = $this->loginAsTechnicianWithRole();

        $project_1 = $this->projectBuilder->setTechnician($tech)->create();

        $this->quoteBuilder->setProject($project_1)->setStatus(CommercialQuoteStatusEnum::PENDING)->create();
        $this->quoteBuilder->setProject($project_1)->setStatus(CommercialQuoteStatusEnum::PENDING)->create();
        $this->quoteBuilder->setProject($project_1)->setStatus(CommercialQuoteStatusEnum::PENDING)->create();
        $this->quoteBuilder->setProject($project_1)->setStatus(CommercialQuoteStatusEnum::DONE)->create();
        $this->quoteBuilder->setProject($project_1)->setStatus(CommercialQuoteStatusEnum::FINAL)->create();


        $this->postGraphQL([
            'query' => $this->getQueryStrByStatus('pending')
        ])
            ->assertJson(
                [
                    'data' => [
                        self::QUERY => [
                            'meta' => [
                                'total' => 3,
                                'has_more_pages' => false,
                            ],
                        ],
                    ]
                ]
            )
        ;
    }

    protected function getQueryStrByStatus($status): string
    {
        return sprintf(
            '
            {
                %s (status: %s) {
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
            self::QUERY,
            $status,
        );
    }

    /** @test */
    public function fail_technic_not_have_certificate(): void
    {
        $this->loginAsTechnicianWithRole(
            Technician::factory()->certified()->verified()
                ->create(['is_commercial_certification' => false])
        );

        $this->postGraphQL([
            'query' => $this->getQueryStr()
        ])
            ->assertJson([
                'errors' => [
                    ['message' => __("exceptions.commercial.technician does\'n have a commercial certificate")]
                ]
            ])
        ;
    }
}


