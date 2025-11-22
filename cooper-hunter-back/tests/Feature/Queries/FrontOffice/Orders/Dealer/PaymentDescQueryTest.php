<?php

namespace Tests\Feature\Queries\FrontOffice\Orders\Dealer;

use App\Enums\Orders\Dealer\PaymentType;
use App\GraphQL\Queries\FrontOffice\Orders\Dealer\PaymentDescQuery;
use App\Models\About\Page;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PaymentDescQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = PaymentDescQuery::NAME;

    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function success_paginator(): void
    {
        $this->loginAsDealerWithRole();

        $this->postGraphQL([
            'query' => $this->getQueryStr()
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'data' => [
                            [
                                'type' => PaymentType::CARD,
                                'translation' => [
                                    'description' => Page::query()->where('slug', PaymentType::CARD)->first()->translation->description
                                ]
                            ],
                            [
                                'type' => PaymentType::PAYPAL,
                                'translation' => [
                                    'description' => Page::query()->where('slug', PaymentType::PAYPAL)->first()->translation->description
                                ]
                            ],
                            [
                                'type' => PaymentType::BANK,
                                'translation' => [
                                    'description' => Page::query()->where('slug', PaymentType::BANK)->first()->translation->description
                                ]
                            ],
                            [
                                'type' => PaymentType::CHECK,
                                'translation' => [
                                    'description' => Page::query()->where('slug', PaymentType::CHECK)->first()->translation->description
                                ]
                            ],
                            [
                                'type' => PaymentType::FLOORING,
                                'translation' => [
                                    'description' => Page::query()->where('slug', PaymentType::FLOORING)->first()->translation->description
                                ]
                            ],
                        ],
                        'meta' => [
                            'total' => 5,
                        ],
                    ]
                ]
            ])
        ;
    }

    /** @test */
    public function filter_by_type(): void
    {
        $this->loginAsDealerWithRole();

        $this->postGraphQL([
            'query' => $this->getQueryStrByType(PaymentType::CARD)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'data' => [
                            [
                                'type' => PaymentType::CARD,
                                'translation' => [
                                    'description' => Page::query()->where('slug', PaymentType::CARD)->first()->translation->description
                                ]
                            ],
                        ],
                        'meta' => [
                            'total' => 1,
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
                        type
                        translation {
                            description
                        }
                    }
                    meta {
                        total
                    }
                }
            }',
            self::MUTATION
        );
    }

    protected function getQueryStrByType($type): string
    {
        return sprintf(
            '
            {
                %s (type: %s) {
                    data {
                        type
                        translation {
                            description
                        }
                    }
                    meta {
                        total
                    }
                }
            }',
            self::MUTATION,
            $type
        );
    }

    /** @test */
    public function not_auth(): void
    {
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
}
