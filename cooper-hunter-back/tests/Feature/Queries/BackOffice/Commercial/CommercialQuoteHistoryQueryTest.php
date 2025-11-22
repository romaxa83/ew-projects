<?php

namespace Tests\Feature\Queries\BackOffice\Commercial;

use App\GraphQL\Queries\BackOffice\Commercial\CommercialQuoteHistoriesQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Commercial\QuoteBuilder;
use Tests\Builders\Commercial\QuoteHistoryBuilder;
use Tests\TestCase;

class CommercialQuoteHistoryQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = CommercialQuoteHistoriesQuery::NAME;

    protected $quoteBuilder;
    protected $quoteHistoryBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->quoteBuilder = resolve(QuoteBuilder::class);
        $this->quoteHistoryBuilder = resolve(QuoteHistoryBuilder::class);
    }

    /** @test */
    public function success_paginator(): void
    {
        $admin = $this->loginAsSuperAdmin();

        $model = $this->quoteBuilder->create();

        $this->quoteHistoryBuilder->setAdminId($admin->id)
            ->setQuoteId($model->id)->setPosition(1)->create();
        $this->quoteHistoryBuilder->setAdminId($admin->id)
            ->setQuoteId($model->id)->setPosition(2)->create();

        $model_2 = $this->quoteBuilder->create();

        $this->quoteHistoryBuilder->setAdminId($admin->id)
            ->setQuoteId($model_2->id)->setPosition(1)->create();
        $this->quoteHistoryBuilder->setAdminId($admin->id)
            ->setQuoteId($model_2->id)->setPosition(2)->create();
        $this->quoteHistoryBuilder->setAdminId($admin->id)
            ->setQuoteId($model_2->id)->setPosition(3)->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($model->id)
        ])
            ->assertJson(
                [
                    'data' => [
                        self::MUTATION => [
                            'meta' => [
                                'total' => 2,
                            ],
                        ],
                    ]
                ]
            )
        ;

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($model_2->id)
        ])
            ->assertJson(
                [
                    'data' => [
                        self::MUTATION => [
                            'meta' => [
                                'total' => 3,
                            ],
                        ],
                    ]
                ]
            )
        ;
    }

    protected function getQueryStr($quoteId): string
    {
        return sprintf(
            '
            {
                %s (quote_id: %s) {
                       data {
                            id
                       }
                       meta {
                            total
                       }
                }
            }',
            self::MUTATION,
            $quoteId
        );
    }

    /** @test */
    public function success_paginator_get_one(): void
    {
        $admin = $this->loginAsSuperAdmin();

        $model = $this->quoteBuilder->create();

        $history_1 = $this->quoteHistoryBuilder->setAdminId($admin->id)
            ->setQuoteId($model->id)->setPosition(1)->create();
        $this->quoteHistoryBuilder->setAdminId($admin->id)
            ->setQuoteId($model->id)->setPosition(2)->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrOne($model->id, $history_1->id)
        ])
            ->assertJson(
                [
                    'data' => [
                        self::MUTATION => [
                            'data' => [
                                ['id' => $history_1->id]
                            ],
                            'meta' => [
                                'total' => 1,
                            ],
                        ],
                    ]
                ]
            )
        ;
    }

    protected function getQueryStrOne($quoteId, $id): string
    {
        return sprintf(
            '
            {
                %s (quote_id: %s, id: %s) {
                       data {
                            id
                       }
                       meta {
                            total
                       }
                }
            }',
            self::MUTATION,
            $quoteId,
            $id
        );
    }

    /** @test */
    public function success_paginator_by_per_page(): void
    {
        $admin = $this->loginAsSuperAdmin();

        $model = $this->quoteBuilder->create();

        $this->quoteHistoryBuilder->setAdminId($admin->id)
            ->setQuoteId($model->id)->setPosition(1)->create();
        $this->quoteHistoryBuilder->setAdminId($admin->id)
            ->setQuoteId($model->id)->setPosition(2)->create();
        $this->quoteHistoryBuilder->setAdminId($admin->id)
            ->setQuoteId($model->id)->setPosition(2)->create();
        $this->quoteHistoryBuilder->setAdminId($admin->id)
            ->setQuoteId($model->id)->setPosition(2)->create();
        $this->quoteHistoryBuilder->setAdminId($admin->id)
            ->setQuoteId($model->id)->setPosition(2)->create();
        $this->quoteHistoryBuilder->setAdminId($admin->id)
            ->setQuoteId($model->id)->setPosition(2)->create();
        $this->quoteHistoryBuilder->setAdminId($admin->id)
            ->setQuoteId($model->id)->setPosition(2)->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrPerPage($model->id, 2, 2)
        ])
            ->assertJson(
                [
                    'data' => [
                        self::MUTATION => [
                            'meta' => [
                                'total' => 7,
                                'per_page' => 2,
                                'current_page' => 2,
                                'from' => 3,
                                'to' => 4,
                                'last_page' => 4,
                                'has_more_pages' => true,
                            ],
                        ],
                    ]
                ]
            )
        ;
    }

    protected function getQueryStrPerPage($quoteId, $perPage, $page): string
    {
        return sprintf(
            '
            {
                %s (quote_id: %s, per_page: %s, page: %s) {
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
            $quoteId,
            $perPage,
            $page
        );
    }
}
