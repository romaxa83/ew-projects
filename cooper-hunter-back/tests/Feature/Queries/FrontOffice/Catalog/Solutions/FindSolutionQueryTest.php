<?php

namespace Tests\Feature\Queries\FrontOffice\Catalog\Solutions;

use App\Dto\Catalog\Solutions\FindSolutionDto;
use App\Enums\Solutions\SolutionClimateZoneEnum;
use App\Enums\Solutions\SolutionIndoorEnum;
use App\Enums\Solutions\SolutionSeriesEnum;
use App\Enums\Solutions\SolutionTypeEnum;
use App\Enums\Solutions\SolutionZoneEnum;
use App\GraphQL\Queries\FrontOffice\Catalog\Solutions\FindSolutionQuery;
use App\Models\Catalog\Solutions\Series\SolutionSeries;
use App\Models\Catalog\Solutions\Series\SolutionSeriesTranslation;
use App\Models\Catalog\Solutions\Solution;
use App\Services\Catalog\Solutions\SolutionService;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Database\Seeders\Catalog\Solutions\SolutionDemoSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class FindSolutionQueryTest extends TestCase
{
    use DatabaseTransactions;

    private SolutionSeries $sophia;

    private SolutionService $service;

    public function test_find_single_zone(): void
    {
        $query = GraphQLQuery::query(FindSolutionQuery::NAME)
            ->args(
                [
                    'zone' => SolutionZoneEnum::SINGLE(),
                    'climate_zones' => [
                        SolutionClimateZoneEnum::HOT(),
                        SolutionClimateZoneEnum::MODERATE(),
                    ],
                    'series_id' => $this->sophia->id,
                    'btu' => 9000,
                ]
            )
            ->select(
                [
                    'id',
                    'climate_zones',
                    'series' => [
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
                    ],
                    'zone',
                    'btu',
                    'voltage',
                    'product' => [
                        'id',
                        'title',
                    ],
                    'is_correct_btu',
                    'indoors' => [
                        'id',
                        'series' => [
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
                        ],
                        'type',
                        'btu',
                        'product' => [
                            'id',
                            'title',
                        ],
                        'line_sets' => [
                            'id',
                            'product' => [
                                'id',
                                'title',
                            ],
                            'default'
                        ]
                    ]
                ]
            );

        $result = $this->postGraphQL($query->make())
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        FindSolutionQuery::NAME => [
                            'id',
                            'climate_zones',
                            'series' => [
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
                            ],
                            'zone',
                            'btu',
                            'voltage',
                            'product' => [
                                'id',
                                'title',
                            ],
                            'is_correct_btu',
                            'indoors' => [
                                '*' => [
                                    'id',
                                    'series' => [
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
                                    ],
                                    'type',
                                    'btu',
                                    'product' => [
                                        'id',
                                        'title',
                                    ],
                                    'line_sets' => [
                                        '*' => [
                                            'id',
                                            'product' => [
                                                'id',
                                                'title',
                                            ],
                                            'default'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            );

        $outdoor = $this->service->getOutdoor(
            FindSolutionDto::byArgs(
                [
                    'type' => SolutionTypeEnum::OUTDOOR,
                    'zone' => SolutionZoneEnum::SINGLE,
                    'climate_zones' => [
                        SolutionClimateZoneEnum::HOT,
                        SolutionClimateZoneEnum::MODERATE,
                    ],
                    'series_id' => $this->sophia->id,
                    'btu' => 9000,
                ]
            )
        );

        $this->assertNotNull($outdoor);

        /**@var Solution $indoor */
        $indoor = $outdoor->children()
            ->with(
                [
                    'children',
                    'defaultLineSets'
                ]
            )
            ->first();

        $this->assertNotNull($indoor);

        $result->assertJson(
            [
                'data' => [
                    FindSolutionQuery::NAME => [
                        'id' => $outdoor->id,
                        'climate_zones' => [
                            SolutionClimateZoneEnum::HOT,
                            SolutionClimateZoneEnum::MODERATE,
                        ],
                        'series' => [
                            'id' => $this->sophia->id,
                            'translation' => [
                                'id' => $this->sophia->translation->id,
                                'title' => $this->sophia->translation->title,
                                'language' => $this->sophia->translation->language,
                            ],
                            'translations' => $this->sophia
                                ->translations
                                ->map(
                                    fn(SolutionSeriesTranslation $translation) => [
                                        'id' => $translation->id,
                                        'title' => $translation->title,
                                        'language' => $translation->language,
                                    ]
                                )
                                ->values()
                                ->toArray()
                        ],
                        'zone' => SolutionZoneEnum::SINGLE,
                        'btu' => 9000,
                        'voltage' => config('catalog.solutions.voltage.default'),
                        'product' => [
                            'id' => $outdoor->product->id,
                            'title' => $outdoor->product->title,
                        ],
                        'is_correct_btu' => true,
                        'indoors' => [
                            [
                                'id' => $indoor->id,
                                'series' => [
                                    'id' => $indoor->series->id,
                                    'translation' => [
                                        'id' => $indoor->series->translation->id,
                                        'title' => $indoor->series->translation->title,
                                        'language' => $indoor->series->translation->language,
                                    ],
                                    'translations' => $indoor->series
                                        ->translations
                                        ->map(
                                            fn(SolutionSeriesTranslation $translation) => [
                                                'id' => $translation->id,
                                                'title' => $translation->title,
                                                'language' => $translation->language,
                                            ]
                                        )
                                        ->values()
                                        ->toArray(),
                                ],
                                'type' => SolutionIndoorEnum::WALL_MOUNT,
                                'btu' => 9000,
                                'product' => [
                                    'id' => $indoor->product->id,
                                    'title' => $indoor->product->title,
                                ],
                                'line_sets' => $indoor
                                    ->children
                                    ->map(
                                        fn(Solution $lineSet) => [
                                            'id' => $lineSet->id,
                                            'product' => [
                                                'id' => $lineSet->product->id,
                                                'title' => $lineSet->product->title,
                                            ],
                                            'default' => $indoor->defaultLineSets->where('line_set_id', $lineSet->id)
                                                ->where('zone', $outdoor->zone->value)
                                                ->isNotEmpty()
                                        ]
                                    )
                                    ->values()
                                    ->toArray()
                            ]
                        ]
                    ]
                ]
            ]
        );
    }

    public function test_find_two_zones(): void
    {
        $query = GraphQLQuery::query(FindSolutionQuery::NAME)
            ->args(
                [
                    'zone' => SolutionZoneEnum::MULTI(),
                    'climate_zones' => [
                        SolutionClimateZoneEnum::HOT(),
                        SolutionClimateZoneEnum::MODERATE(),
                    ],
                    'count_zones' => 2,
                    'series_id' => $this->sophia->id,
                    'btu' => 18000,
                ]
            )
            ->select(
                [
                    'id',
                    'btu',
                    'product' => [
                        'title',
                    ],
                    'indoors' => [
                        'id',
                        'btu',
                        'product' => [
                            'title',
                        ],
                    ]
                ]
            );

        $result = $this->postGraphQL($query->make())
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        FindSolutionQuery::NAME => [
                            'id',
                            'btu',
                            'product' => [
                                'title',
                            ],
                            'indoors' => [
                                '*' => [
                                    'id',
                                    'btu',
                                    'product' => [
                                        'title',
                                    ],
                                ]
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(2, 'data.' . FindSolutionQuery::NAME . '.indoors')
            ->json('data.' . FindSolutionQuery::NAME);

        $this->assertTrue(
            $result['btu'] + $result['btu'] * config('catalog.solutions.btu.max_percent') / 100
            >=
            array_sum(
                array_column($result['indoors'], 'btu')
            )
        );
    }

    public function test_find_five_zones(): void
    {
        $query = GraphQLQuery::query(FindSolutionQuery::NAME)
            ->args(
                [
                    'zone' => SolutionZoneEnum::MULTI(),
                    'climate_zones' => [
                        SolutionClimateZoneEnum::HOT(),
                        SolutionClimateZoneEnum::MODERATE(),
                    ],
                    'count_zones' => 5,
                    'series_id' => $this->sophia->id,
                    'btu' => 48000,
                ]
            )
            ->select(
                [
                    'id',
                    'btu',
                    'product' => [
                        'title',
                    ],
                    'indoors' => [
                        'id',
                        'btu',
                        'product' => [
                            'title',
                        ],
                    ]
                ]
            );

        $result = $this->postGraphQL($query->make())
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        FindSolutionQuery::NAME => [
                            'id',
                            'btu',
                            'product' => [
                                'title',
                            ],
                            'indoors' => [
                                '*' => [
                                    'id',
                                    'btu',
                                    'product' => [
                                        'title',
                                    ],
                                ]
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(5, 'data.' . FindSolutionQuery::NAME . '.indoors')
            ->json('data.' . FindSolutionQuery::NAME);

        $this->assertTrue(
            $result['btu'] + $result['btu'] * config('catalog.solutions.btu.max_percent') / 100
            >=
            array_sum(
                array_column($result['indoors'], 'btu')
            )
        );
    }

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('app.env', 'local');

        $this->seed(SolutionDemoSeeder::class);

        $this->sophia = SolutionSeries::whereSlug(
            SolutionSeriesEnum::SOPHIA
        )
            ->get()
            ->first();

        $this->service = resolve(SolutionService::class);
    }
}
