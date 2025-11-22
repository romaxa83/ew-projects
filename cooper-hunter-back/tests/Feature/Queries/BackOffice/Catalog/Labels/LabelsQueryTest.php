<?php

namespace Tests\Feature\Queries\BackOffice\Catalog\Labels;

use App\GraphQL\Queries\BackOffice\Catalog\Labels\LabelsQuery;
use App\Models\Catalog\Labels\Label;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Catalog\LabelBuilder;
use Tests\TestCase;

class LabelsQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = LabelsQuery::NAME;

    protected LabelBuilder $labelBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->labelBuilder = resolve(LabelBuilder::class);
    }

    /** @test */
    public function success_paginator(): void
    {
        $this->loginAsSuperAdmin();

        Label::factory()->times(20)->create();

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

        Label::factory()->times(20)->create();

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

        Label::factory()->times(5)->create();

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

        /** @var $model Label */
        $model = $this->labelBuilder->withTranslation()->create();

        $this->assertEquals($model->color_type->getTextColor(), '006EC3');
        $this->assertEquals($model->color_type->getBackgroundColor(), 'F0F4FD');

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrByID($model->id)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'data' => [
                            [
                                'id' => $model->id,
                                'color_type' => $model->color_type->value,
                                'sort' => $model->sort,
                                'translation' => [
                                    'title' => $model->translation->title
                                ],
                                'text_color' => $model->color_type->getTextColor(),
                                'background_color' => $model->color_type->getBackgroundColor(),
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
                        color_type
                        sort
                        text_color
                        background_color
                        translation {
                            title
                        }
                    }
                }
            }',
            self::MUTATION,
            $value
        );
    }
}
