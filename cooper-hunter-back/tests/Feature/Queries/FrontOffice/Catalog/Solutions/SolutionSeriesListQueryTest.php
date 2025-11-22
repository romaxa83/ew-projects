<?php

namespace Tests\Feature\Queries\FrontOffice\Catalog\Solutions;

use App\Enums\Solutions\SolutionClimateZoneEnum;
use App\Enums\Solutions\SolutionZoneEnum;
use App\GraphQL\Queries\FrontOffice\Catalog\Solutions\SolutionSeriesListQuery;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Database\Seeders\Catalog\Solutions\SolutionDemoSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class SolutionSeriesListQueryTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        Config::set('app.env', 'local');

        $this->seed(SolutionDemoSeeder::class);
    }

    public function test_find_sophia(): void
    {
        $query = GraphQLQuery::query(SolutionSeriesListQuery::NAME)
            ->args(
                [
                    'zone' => SolutionZoneEnum::SINGLE(),
                    'climate_zones' => [
                        SolutionClimateZoneEnum::HOT(),
                        SolutionClimateZoneEnum::MODERATE(),
                    ],
                ]
            )
            ->select(
                [
                    'id',
                    'translation' => [
                        'id',
                        'title',
                        'language'
                    ],
                    'translations' => [
                        'id',
                        'title',
                        'language'
                    ]
                ]
            );

        $this->postGraphQL($query->make())
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        SolutionSeriesListQuery::NAME => [
                            '*' => [
                                'id',
                                'translation' => [
                                    'id',
                                    'title',
                                    'language'
                                ],
                                'translations' => [
                                    '*' => [
                                        'id',
                                        'title',
                                        'language'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            );
    }

    public function test_find_hyper(): void
    {
        $query = GraphQLQuery::query(SolutionSeriesListQuery::NAME)
            ->args(
                [
                    'zone' => SolutionZoneEnum::SINGLE(),
                    'climate_zones' => [
                        SolutionClimateZoneEnum::COLD(),
                        SolutionClimateZoneEnum::MODERATE(),
                    ],
                ]
            )
            ->select(
                [
                    'id',
                    'translation' => [
                        'id',
                        'title',
                        'language'
                    ],
                    'translations' => [
                        'id',
                        'title',
                        'language'
                    ]
                ]
            );

        $this->postGraphQL($query->make())
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        SolutionSeriesListQuery::NAME => [
                            '*' => [
                                'id',
                                'translation' => [
                                    'id',
                                    'title',
                                    'language'
                                ],
                                'translations' => [
                                    '*' => [
                                        'id',
                                        'title',
                                        'language'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            );
    }

    public function test_find_error(): void
    {
        $query = GraphQLQuery::query(SolutionSeriesListQuery::NAME)
            ->args(
                [
                    'zone' => SolutionZoneEnum::SINGLE(),
                    'climate_zones' => [
                        SolutionClimateZoneEnum::HOT(),
                        SolutionClimateZoneEnum::COLD(),
                        SolutionClimateZoneEnum::MODERATE(),
                    ],
                ]
            )
            ->select(
                [
                    'id'
                ]
            );

        $this->postGraphQL($query->make())
            ->assertOk()
            ->assertJson(
                [
                    'errors' => [
                        [
                            'message' => trans('validation.custom.catalog.solutions.series_not_found')
                        ]
                    ]
                ]
            );
    }
}
