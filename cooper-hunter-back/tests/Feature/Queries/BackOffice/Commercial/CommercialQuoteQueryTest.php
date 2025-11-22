<?php

namespace Tests\Feature\Queries\BackOffice\Commercial;

use App\Enums\Commercial\CommercialQuoteStatusEnum;
use App\GraphQL\Queries\BackOffice\Commercial\CommercialQuotesQuery;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Commercial\QuoteBuilder;
use Tests\Builders\Commercial\QuoteHistoryBuilder;
use Tests\Builders\Commercial\QuoteItemBuilder;
use Tests\TestCase;

class CommercialQuoteQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = CommercialQuotesQuery::NAME;

    protected $quoteBuilder;
    protected $quoteItemBuilder;
    protected $quoteHistoryBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->quoteBuilder = resolve(QuoteBuilder::class);
        $this->quoteItemBuilder = resolve(QuoteItemBuilder::class);
        $this->quoteHistoryBuilder = resolve(QuoteHistoryBuilder::class);
    }

    /** @test */
    public function success_paginator(): void
    {
        $this->loginAsSuperAdmin();

        $this->quoteBuilder->setStatus(CommercialQuoteStatusEnum::PENDING)->create();
        $this->quoteBuilder->setStatus(CommercialQuoteStatusEnum::PENDING)->create();
        $this->quoteBuilder->setStatus(CommercialQuoteStatusEnum::PENDING)->create();
        $this->quoteBuilder->setStatus(CommercialQuoteStatusEnum::DONE)->create();
        $this->quoteBuilder->setStatus(CommercialQuoteStatusEnum::DONE)->create();
        $this->quoteBuilder->setStatus(CommercialQuoteStatusEnum::FINAL)->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr()
        ])
            ->assertJson(
                [
                    'data' => [
                        self::MUTATION => [
                            'meta' => [
                                'total' => 6,
                                'per_page' => 15,
                                'current_page' => 1,
                                'from' => 1,
                                'to' => 6,
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
            self::MUTATION
        );
    }

    /** @test */
    public function success_query_page_and_per_page(): void
    {
        $this->loginAsSuperAdmin();

        $this->quoteBuilder->setStatus(CommercialQuoteStatusEnum::PENDING)->create();
        $this->quoteBuilder->setStatus(CommercialQuoteStatusEnum::PENDING)->create();
        $this->quoteBuilder->setStatus(CommercialQuoteStatusEnum::PENDING)->create();
        $this->quoteBuilder->setStatus(CommercialQuoteStatusEnum::DONE)->create();
        $this->quoteBuilder->setStatus(CommercialQuoteStatusEnum::DONE)->create();
        $this->quoteBuilder->setStatus(CommercialQuoteStatusEnum::FINAL)->create();
        $this->quoteBuilder->setStatus(CommercialQuoteStatusEnum::FINAL)->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrPageAndPerPage(2, 3)
        ])
            ->assertJson(
                [
                    'data' => [
                        self::MUTATION => [
                            'meta' => [
                                'total' => 7,
                                'per_page' => 2,
                                'current_page' => 3,
                                'from' => 5,
                                'to' => 6,
                                'last_page' => 4,
                                'has_more_pages' => true,
                            ],
                        ],
                    ]
                ]
            )
            ->assertJsonCount(2, "data.".self::MUTATION.".data")
        ;
    }

    protected function getQueryStrPageAndPerPage($perPage, $page): string
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
            self::MUTATION,
            $perPage,
            $page,
        );
    }

    /** @test */
    public function success_get_one(): void
    {
        $admin = $this->loginAsSuperAdmin();

        $model = $this->quoteBuilder
            ->setStatus(CommercialQuoteStatusEnum::PENDING)
            ->create();

        $item_1 = $this->quoteItemBuilder->setQuoteId($model->id)->create();
        $item_2 = $this->quoteItemBuilder->setQuoteId($model->id)->create();

        $history_1 = $this->quoteHistoryBuilder->setAdminId($admin->id)
            ->setQuoteId($model->id)->setPosition(1)->create();
        $history_2 = $this->quoteHistoryBuilder->setAdminId($admin->id)
            ->setQuoteId($model->id)->setPosition(2)->create();

        $model->refresh();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrOne($model->id)
        ])
            ->assertJson(
                [
                    'data' => [
                        self::MUTATION => [
                            'data' => [
                                [
                                    'id' => $model->id,
                                    'status' => $model->status,
                                    'created_at' => $model->created_at->format('Y-m-d H:i:s'),
                                    'shipping_address' => $model->shipping_address,
                                    'email' => $model->email,
                                    'send_detail_data' => $model->send_detail_data,
                                    'closed_at' => $model->closed_at ?? null,
                                    'shipping_price' => $model->shipping_price,
                                    'tax' => $model->tax,
                                    'sub_total' => $model->sub_total,
                                    'discount_percent' => $model->discount_percent,
                                    'discount_sum' => $model->discount_sum,
                                    'discount' => $model->discount,
                                    'tax_sum' => $model->tax_sum,
                                    'total' => $model->total,
                                    'commercial_project' => [
                                        'id' => $model->commercialProject->id
                                    ],
                                    'items' => [
                                        [
                                            "id" => $item_1->id,
                                            "price" => $item_1->price,
                                            "qty" => $item_1->qty,
                                            "total" => $item_1->total,
                                        ],
                                        [
                                            "id" => $item_2->id,
                                            "price" => $item_2->price,
                                            "qty" => $item_2->qty,
                                            "total" => $item_2->total,
                                        ]
                                    ],
                                    'histories' => [
                                        [
                                            'id' => $history_2->id,
                                            'estimate' => $history_2->estimate,
                                            'position' => $history_2->position,
                                            'created_at' => $history_2->created_at->format('Y-m-d H:i:s'),
                                            'admin' => [
                                                'id' => $admin->id
                                            ]
                                        ],
                                        [
                                            'id' => $history_1->id,
                                            'estimate' => $history_1->estimate,
                                            'position' => $history_1->position,
                                            'created_at' => $history_1->created_at->format('Y-m-d H:i:s'),
                                            'admin' => [
                                                'id' => $admin->id
                                            ]
                                        ]
                                    ]
                                ]
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

    protected function getQueryStrOne($id): string
    {
        return sprintf(
            '
            {
                %s (id: %s) {
                       data {
                            id
                            status
                            created_at
                            shipping_address
                            email
                            send_detail_data
                            closed_at
                            shipping_price
                            tax
                            sub_total
                            discount_percent
                            discount_sum
                            discount
                            tax_sum
                            total
                            commercial_project {
                                id
                            }
                            items {
                                id
                                price
                                qty
                                total
                            }
                            histories {
                                id
                                created_at
                                estimate
                                position
                                admin {
                                    id
                                }
                            }
                       }
                       meta {
                            total
                       }
                }
            }',
            self::MUTATION,
            $id
        );
    }

    /** @test */
    public function success_filter_by_status(): void
    {
        $this->loginAsSuperAdmin();

        $this->quoteBuilder->setStatus(CommercialQuoteStatusEnum::PENDING)->create();
        $this->quoteBuilder->setStatus(CommercialQuoteStatusEnum::PENDING)->create();
        $this->quoteBuilder->setStatus(CommercialQuoteStatusEnum::PENDING)->create();
        $this->quoteBuilder->setStatus(CommercialQuoteStatusEnum::DONE)->create();
        $this->quoteBuilder->setStatus(CommercialQuoteStatusEnum::DONE)->create();
        $this->quoteBuilder->setStatus(CommercialQuoteStatusEnum::FINAL)->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrByStatus(CommercialQuoteStatusEnum::DONE)
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
            ->assertJsonCount(2, "data.".self::MUTATION.".data")
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
                       }
                }
            }',
            self::MUTATION,
            $status
        );
    }

    /** @test */
    public function success_filter_by_project_name(): void
    {
        $this->loginAsSuperAdmin();

        $model = $this->quoteBuilder->setStatus(CommercialQuoteStatusEnum::PENDING)->create();
        $this->quoteBuilder->setStatus(CommercialQuoteStatusEnum::PENDING)->create();
        $this->quoteBuilder->setStatus(CommercialQuoteStatusEnum::PENDING)->create();


        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrByProjectName($model->commercialProject->name)
        ])
            ->assertJson(
                [
                    'data' => [
                        self::MUTATION => [
                            'data' => [
                                ['id' => $model->id]
                            ],
                            'meta' => [
                                'total' => 1,
                            ],
                        ],
                    ]
                ]
            )
            ->assertJsonCount(1, "data.".self::MUTATION.".data")
        ;
    }

    protected function getQueryStrByProjectName($name): string
    {
        return sprintf(
            '
            {
                %s (project_name: "%s") {
                       data {
                            id
                       }
                       meta {
                            total
                       }
                }
            }',
            self::MUTATION,
            $name
        );
    }

    /** @test */
    public function success_filter_by_technician_name(): void
    {
        $this->loginAsSuperAdmin();

        $model = $this->quoteBuilder->setStatus(CommercialQuoteStatusEnum::PENDING)->create();
        $this->quoteBuilder->setStatus(CommercialQuoteStatusEnum::PENDING)->create();
        $this->quoteBuilder->setStatus(CommercialQuoteStatusEnum::PENDING)->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrByTechnicianName($model->commercialProject->member->getName())
        ])
            ->assertJson(
                [
                    'data' => [
                        self::MUTATION => [
                            'data' => [
                                ['id' => $model->id]
                            ],
                            'meta' => [
                                'total' => 1,
                            ],
                        ],
                    ]
                ]
            )
            ->assertJsonCount(1, "data.".self::MUTATION.".data")
        ;
    }

    protected function getQueryStrByTechnicianName($name): string
    {
        return sprintf(
            '
            {
                %s (technician_name: "%s") {
                       data {
                            id
                       }
                       meta {
                            total
                       }
                }
            }',
            self::MUTATION,
            $name
        );
    }

    /** @test */
    public function success_filter_by_range_date(): void
    {
        $this->loginAsSuperAdmin();

        $date = CarbonImmutable::now();

        $model = $this->quoteBuilder->setStatus(CommercialQuoteStatusEnum::PENDING)
            ->setCreatedAt($date)->create();
        $this->quoteBuilder->setStatus(CommercialQuoteStatusEnum::PENDING)
            ->setCreatedAt($date->addDays(3))->create();
        $this->quoteBuilder->setStatus(CommercialQuoteStatusEnum::PENDING)
            ->setCreatedAt($date->addDays(7))->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrRangeDate(
                $date->format('Y-m-d'), $date->addDay()->format('Y-m-d')
            )
        ])
            ->assertJson(
                [
                    'data' => [
                        self::MUTATION => [
                            'data' => [
                                ['id' => $model->id]
                            ],
                            'meta' => [
                                'total' => 1,
                            ],
                        ],
                    ]
                ]
            )
            ->assertJsonCount(1, "data.".self::MUTATION.".data")
        ;

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrRangeDate(
                $date->format('Y-m-d'), $date->addDays(4)->format('Y-m-d')
            )
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
            ->assertJsonCount(2, "data.".self::MUTATION.".data")
        ;
    }

    protected function getQueryStrRangeDate($from, $to): string
    {
        return sprintf(
            '
            {
                %s (date_from: "%s", date_to: "%s") {
                       data {
                            id
                       }
                       meta {
                            total
                       }
                }
            }',
            self::MUTATION,
            $from,
            $to
        );
    }
}

