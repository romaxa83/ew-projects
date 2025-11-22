<?php

namespace Tests\Feature\Queries\BackOffice\Commercial\Commissioning;

use App\Enums\Commercial\Commissioning\ProtocolType;
use App\GraphQL\Queries\BackOffice\Commercial\Commissioning\ProtocolQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Commercial\Commissioning\ProtocolBuilder;
use Tests\TestCase;

class ProtocolsQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = ProtocolQuery::NAME;

    protected $protocolBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->protocolBuilder = resolve(ProtocolBuilder::class);
    }

    /** @test */
    public function success_paginator(): void
    {
        $this->loginAsSuperAdmin();

        $this->protocolBuilder->create();
        $this->protocolBuilder->create();
        $this->protocolBuilder->create();
        $this->protocolBuilder->create();
        $this->protocolBuilder->create();

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

    /** @test */
    public function success_sort_by_commissioning(): void
    {
        $this->loginAsSuperAdmin();

        $protocol_1 = $this->protocolBuilder->setType(ProtocolType::PRE_COMMISSIONING)->create();
        $protocol_2 = $this->protocolBuilder->setType(ProtocolType::COMMISSIONING)->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr()
        ])
            ->assertJson(
                [
                    'data' => [
                        self::MUTATION => [
                            'data' => [
                                ['id' => $protocol_2->id],
                                ['id' => $protocol_1->id],
                            ],
                            'meta' => [
                                'total' => 2,
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
}

