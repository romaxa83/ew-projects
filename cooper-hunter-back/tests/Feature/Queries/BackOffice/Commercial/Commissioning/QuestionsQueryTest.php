<?php

namespace Tests\Feature\Queries\BackOffice\Commercial\Commissioning;

use App\GraphQL\Queries\BackOffice\Commercial\Commissioning\QuestionQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Commercial\Commissioning\ProtocolBuilder;
use Tests\Builders\Commercial\Commissioning\QuestionBuilder;
use Tests\TestCase;

class QuestionsQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = QuestionQuery::NAME;

    protected $protocolBuilder;
    protected $questionBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->questionBuilder = resolve(QuestionBuilder::class);
        $this->protocolBuilder = resolve(ProtocolBuilder::class);
    }

    /** @test */
    public function success_paginator(): void
    {
        $this->loginAsSuperAdmin();

        $this->questionBuilder->create();
        $this->questionBuilder->create();
        $this->questionBuilder->create();
        $this->questionBuilder->create();
        $this->questionBuilder->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr()
        ])
            ->assertJson(
                [
                    'data' => [
                        self::MUTATION => [
                            'meta' => [
                                'total' => 5,
                                'per_page' => 15,
                                'current_page' => 1,
                                'from' => 1,
                                'to' => 5,
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
    public function success_filter_by_protocol_id(): void
    {
        $this->loginAsSuperAdmin();

        $protocol_1 = $this->protocolBuilder->create();
        $protocol_2 = $this->protocolBuilder->create();

        $this->questionBuilder->setProtocol($protocol_1)->create();
        $this->questionBuilder->setProtocol($protocol_1)->create();
        $this->questionBuilder->setProtocol($protocol_2)->create();
        $this->questionBuilder->setProtocol($protocol_2)->create();
        $this->questionBuilder->setProtocol($protocol_2)->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrByProtocolId($protocol_1->id)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'meta' => [
                            'total' => 2,
                        ],
                    ],
                ]
            ])
            ->assertJsonCount(2, 'data.'.self::MUTATION.'.data')
        ;
    }

    protected function getQueryStrByProtocolId($id): string
    {
        return sprintf(
            '
            {
                %s (protocol_id: %s) {
                       data {
                            id
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
    public function success_filter_by_id(): void
    {
        $this->loginAsSuperAdmin();

        $model = $this->questionBuilder->create();
        $this->questionBuilder->create();
        $this->questionBuilder->create();
        $this->questionBuilder->create();
        $this->questionBuilder->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrById($model->id)
        ])
            ->assertJson([
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
            ])
            ->assertJsonCount(1, 'data.'.self::MUTATION.'.data')
        ;
    }

    protected function getQueryStrById($id): string
    {
        return sprintf(
            '
            {
                %s (id: %s) {
                       data {
                            id
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
}

