<?php


namespace Tests\Feature\Mutations\BackOffice\Catalog\Solutions;


use App\Enums\Solutions\SolutionClimateZoneEnum;
use App\Enums\Solutions\SolutionIndoorEnum;
use App\Enums\Solutions\SolutionTypeEnum;
use App\Enums\Solutions\SolutionZoneEnum;
use App\GraphQL\Mutations\BackOffice\Catalog\Solutions\SolutionCreateUpdateMutation;
use App\GraphQL\Types\Catalog\Products\ProductType;
use App\GraphQL\Types\Catalog\Solutions\SolutionSeriesType;
use App\GraphQL\Types\Catalog\Solutions\SolutionType;
use App\Models\Catalog\Products\Product;
use App\Models\Catalog\Solutions\Series\SolutionSeries;
use App\Models\Catalog\Solutions\Solution;
use App\Permissions\Catalog\Solutions\SolutionCreateUpdatePermission;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\Permissions\AdminManagerHelperTrait;

class SolutionCreateUpdateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use AdminManagerHelperTrait;
    use WithFaker;

    public function test_create_line_set(): void
    {
        $product = Product::factory()
            ->create();
        $query = GraphQLQuery::mutation(SolutionCreateUpdateMutation::NAME)
            ->args(
                [
                    'solution' => [
                        'product_id' => $product->id,
                        'type' => SolutionTypeEnum::LINE_SET(),
                        'short_name' => $shortName = $this->faker->lexify,
                    ]
                ]
            )
            ->select(
                [
                    '__typename',
                    'id',
                    'solution' => [
                        '__typename',
                        'type',
                        'short_name',
                        'series' => [
                            'id'
                        ],
                        'zone',
                        'climate_zones',
                        'indoor_type',
                        'btu',
                        'voltage',
                        'line_sets' => [
                            'line_set' => [
                                '__typename',
                                'type',
                                'short_name',
                                'series' => [
                                    'id'
                                ],
                                'zone',
                                'climate_zones',
                                'indoor_type',
                                'btu',
                                'voltage',
                            ],
                            'default_for_zones'
                        ],
                        'indoors' => [
                            '__typename',
                            'type',
                            'short_name',
                            'series' => [
                                'id'
                            ],
                            'zone',
                            'climate_zones',
                            'indoor_type',
                            'btu',
                            'voltage',
                        ]
                    ]
                ]
            );

        $this->postGraphQLBackOffice($query->make())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        SolutionCreateUpdateMutation::NAME => [
                            '__typename' => ProductType::NAME,
                            'id' => $product->id,
                            'solution' => [
                                '__typename' => SolutionType::NAME,
                                'type' => SolutionTypeEnum::LINE_SET,
                                'short_name' => $shortName,
                                'series' => null,
                                'zone' => null,
                                'climate_zones' => null,
                                'indoor_type' => null,
                                'btu' => null,
                                'voltage' => null,
                                'line_sets' => null,
                                'indoors' => null,
                            ]
                        ]
                    ]
                ]
            );
    }

    public function test_update_line_set_to_indoor_without_parent(): void
    {
        $solution = Solution::factory()
            ->lineSet()
            ->create();

        $this->assertTrue(
            $solution->type->is(SolutionTypeEnum::LINE_SET)
        );

        $series = SolutionSeries::query()
            ->first();
        $btu = config(
            'catalog.solutions.btu.lists.' .
            SolutionTypeEnum::INDOOR . '.' .
            SolutionZoneEnum::MULTI . '.' .
            SolutionIndoorEnum::WALL_MOUNT
        )[2];

        $query = GraphQLQuery::mutation(SolutionCreateUpdateMutation::NAME)
            ->args(
                [
                    'solution' => [
                        'product_id' => $solution->product->id,
                        'type' => SolutionTypeEnum::INDOOR(),
                        'indoor_type' => SolutionIndoorEnum::CEILING_CASSETTE(),
                        'btu' => $btu,
                        'series_id' => $series->id,
                        'line_sets' => Solution::factory()
                            ->lineSet()
                            ->count(3)
                            ->create()
                            ->map(
                                function (Solution $lineSet) use (&$notFirst)
                                {
                                    $result = [
                                        'line_set_id' => $lineSet->id,
                                    ];

                                    if (empty($notFirst)) {
                                        $notFirst = true;

                                        $result['default_for_zones'] = [
                                            SolutionZoneEnum::SINGLE(),
                                            SolutionZoneEnum::MULTI(),
                                        ];
                                    }
                                    return $result;
                                }
                            )
                            ->toArray()
                    ]
                ]
            )
            ->select(
                [
                    'solution' => [
                        'type',
                        'series' => [
                            'id'
                        ],
                        'indoor_type',
                        'btu',
                        'line_sets' => [
                            'line_set' => [
                                'type',
                            ],
                            'default_for_zones'
                        ]
                    ]
                ]
            );

        $this->postGraphQLBackOffice($query->make())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        SolutionCreateUpdateMutation::NAME => [
                            'solution' => [
                                'type' => SolutionTypeEnum::INDOOR,
                                'series' => [
                                    'id' => $series->id
                                ],
                                'indoor_type' => SolutionIndoorEnum::CEILING_CASSETTE,
                                'btu' => $btu,
                                'line_sets' => [
                                    [
                                        'line_set' => [
                                            'type' => SolutionTypeEnum::LINE_SET,
                                        ],
                                        'default_for_zones' => [
                                            SolutionZoneEnum::SINGLE,
                                            SolutionZoneEnum::MULTI,
                                        ]
                                    ],
                                    [
                                        'line_set' => [
                                            'type' => SolutionTypeEnum::LINE_SET,
                                        ],
                                        'default_for_zones' => null
                                    ],
                                    [
                                        'line_set' => [
                                            'type' => SolutionTypeEnum::LINE_SET,
                                        ],
                                        'default_for_zones' => null
                                    ],
                                ]
                            ]
                        ]
                    ]
                ]
            );
    }

    public function test_update_line_set_to_indoor_two_parent(): void
    {
        $lineSets = Solution::factory()
            ->lineSet()
            ->count(2)
            ->create();

        $solution = Solution::factory()
            ->children($lineSets)
            ->indoor()
            ->count(2)
            ->create();

        $lineSet = $lineSets[0];

        $series = SolutionSeries::query()
            ->first();

        $btu = config(
            'catalog.solutions.btu.lists.' .
            SolutionTypeEnum::INDOOR . '.' .
            SolutionZoneEnum::MULTI . '.' .
            SolutionIndoorEnum::WALL_MOUNT
        )[2];

        $query = GraphQLQuery::mutation(SolutionCreateUpdateMutation::NAME)
            ->args(
                [
                    'solution' => [
                        'product_id' => $lineSet->product->id,
                        'type' => SolutionTypeEnum::INDOOR(),
                        'indoor_type' => SolutionIndoorEnum::CEILING_CASSETTE(),
                        'btu' => $btu,
                        'series_id' => $series->id,
                        'line_sets' => Solution::factory()
                            ->lineSet()
                            ->count(3)
                            ->create()
                            ->map(
                                function (Solution $lineSet) use (&$notFirst)
                                {
                                    $result = [
                                        'line_set_id' => $lineSet->id,
                                    ];

                                    if (empty($notFirst)) {
                                        $notFirst = true;

                                        $result['default_for_zones'] = [
                                            SolutionZoneEnum::SINGLE(),
                                            SolutionZoneEnum::MULTI(),
                                        ];
                                    }
                                    return $result;
                                }
                            )
                            ->toArray()
                    ]
                ]
            )
            ->select(
                [
                    'solution' => [
                        'type'
                    ]
                ]
            );

        $this->postGraphQLBackOffice($query->make())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        SolutionCreateUpdateMutation::NAME => [
                            'solution' => [
                                'type' => SolutionTypeEnum::INDOOR
                            ]
                        ]
                    ]
                ]
            );

        $this->assertFalse(
            $solution[0]
                ->children()
                ->where('id', $lineSet->id)
                ->exists()
        );
        $this->assertFalse(
            $solution[1]
                ->children()
                ->where('id', $lineSet->id)
                ->exists()
        );
        $this->assertFalse(
            $lineSet
                ->parents()
                ->exists()
        );
    }

    public function test_update_line_set_to_indoor_one_parent(): void
    {
        $lineSet = Solution::factory()
            ->lineSet()
            ->create();

        $solution = Solution::factory()
            ->children(
                new Collection([$lineSet])
            )
            ->indoor()
            ->create();

        $series = SolutionSeries::query()
            ->first();

        $btu = config(
            'catalog.solutions.btu.lists.' .
            SolutionTypeEnum::INDOOR . '.' .
            SolutionZoneEnum::MULTI . '.' .
            SolutionIndoorEnum::WALL_MOUNT
        )[2];

        $query = GraphQLQuery::mutation(SolutionCreateUpdateMutation::NAME)
            ->args(
                [
                    'solution' => [
                        'product_id' => $lineSet->product->id,
                        'type' => SolutionTypeEnum::INDOOR(),
                        'indoor_type' => SolutionIndoorEnum::CEILING_CASSETTE(),
                        'btu' => $btu,
                        'series_id' => $series->id,
                        'line_sets' => Solution::factory()
                            ->lineSet()
                            ->count(3)
                            ->create()
                            ->map(
                                function (Solution $lineSet) use (&$notFirst)
                                {
                                    $result = [
                                        'line_set_id' => $lineSet->id,
                                    ];

                                    if (empty($notFirst)) {
                                        $notFirst = true;

                                        $result['default_for_zones'] = [
                                            SolutionZoneEnum::SINGLE(),
                                            SolutionZoneEnum::MULTI(),
                                        ];
                                    }
                                    return $result;
                                }
                            )
                            ->toArray()
                    ]
                ]
            )
            ->select(
                [
                    'solution' => [
                        'type'
                    ]
                ]
            );

        $this->postGraphQLBackOffice($query->make())
            ->assertOk()
            ->assertJson(
                [
                    'errors' => [
                        [
                            'message' => trans(
                                'validation.custom.catalog.solutions.cant_change_type_and_delete',
                                ['product' => $solution->product->title]
                            )
                        ]
                    ]
                ]
            );

        $this->assertTrue(
            $solution
                ->children()
                ->where('id', $lineSet->id)
                ->exists()
        );
        $this->assertTrue(
            $lineSet
                ->parents()
                ->exists()
        );
    }

    public function test_create_indoor(): void
    {
        $lineSets = Solution::factory()
            ->lineSet()
            ->count(2)
            ->create();

        $product = Product::factory()
            ->create();
        $series = SolutionSeries::query()
            ->first();
        $btu = config(
            'catalog.solutions.btu.lists.' .
            SolutionTypeEnum::INDOOR . '.' .
            SolutionZoneEnum::MULTI . '.' .
            SolutionIndoorEnum::WALL_MOUNT
        )[2];

        $query = GraphQLQuery::mutation(SolutionCreateUpdateMutation::NAME)
            ->args(
                [
                    'solution' => [
                        'product_id' => $product->id,
                        'type' => SolutionTypeEnum::INDOOR(),
                        'indoor_type' => SolutionIndoorEnum::CEILING_CASSETTE(),
                        'btu' => $btu,
                        'series_id' => $series->id,
                        'line_sets' => $lineSets
                            ->map(
                                function (Solution $lineSet) use (&$notFirst)
                                {
                                    $result = [
                                        'line_set_id' => $lineSet->id,
                                    ];

                                    if (empty($notFirst)) {
                                        $notFirst = true;

                                        $result['default_for_zones'] = [
                                            SolutionZoneEnum::SINGLE(),
                                            SolutionZoneEnum::MULTI(),
                                        ];
                                    }
                                    return $result;
                                }
                            )
                            ->toArray()
                    ]
                ]
            )
            ->select(
                [
                    '__typename',
                    'id',
                    'solution' => [
                        '__typename',
                        'type',
                        'series' => [
                            '__typename',
                            'id'
                        ],
                        'zone',
                        'climate_zones',
                        'indoor_type',
                        'btu',
                        'voltage',
                        'line_sets' => [
                            'line_set' => [
                                '__typename',
                                'type',
                                'series' => [
                                    '__typename',
                                    'id'
                                ],
                                'zone',
                                'climate_zones',
                                'indoor_type',
                                'btu',
                                'voltage',
                                'line_sets' => [
                                    'line_set' => [
                                        'type'
                                    ]
                                ],
                                'indoors' => [
                                    'type'
                                ],
                                'product' => [
                                    '__typename',
                                    'id'
                                ]
                            ],
                            'default_for_zones'
                        ],
                        'indoors' => [
                            '__typename',
                            'type',
                            'series' => [
                                'id'
                            ],
                            'zone',
                            'climate_zones',
                            'indoor_type',
                            'btu',
                            'voltage',
                            'line_sets' => [
                                'line_set' => [
                                    'type'
                                ]
                            ],
                            'indoors' => [
                                'type'
                            ],
                            'product' => [
                                '__typename',
                                'id'
                            ]
                        ]
                    ]
                ]
            );

        $this->postGraphQLBackOffice($query->make())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        SolutionCreateUpdateMutation::NAME => [
                            '__typename' => ProductType::NAME,
                            'id' => $product->id,
                            'solution' => [
                                '__typename' => SolutionType::NAME,
                                'type' => SolutionTypeEnum::INDOOR,
                                'series' => [
                                    '__typename' => SolutionSeriesType::NAME,
                                    'id' => $series->id
                                ],
                                'zone' => null,
                                'climate_zones' => null,
                                'indoor_type' => SolutionIndoorEnum::CEILING_CASSETTE,
                                'btu' => $btu,
                                'voltage' => null,
                                'line_sets' => [
                                    [
                                        'line_set' => [
                                            '__typename' => SolutionType::NAME,
                                            'type' => SolutionTypeEnum::LINE_SET,
                                            'series' => null,
                                            'zone' => null,
                                            'climate_zones' => null,
                                            'indoor_type' => null,
                                            'btu' => null,
                                            'voltage' => null,
                                            'line_sets' => null,
                                            'indoors' => null,
                                            'product' => [
                                                '__typename' => ProductType::NAME,
                                                'id' => $lineSets[0]->product->id
                                            ],
                                        ],
                                        'default_for_zones' => [
                                            SolutionZoneEnum::SINGLE,
                                            SolutionZoneEnum::MULTI,
                                        ]
                                    ],
                                    [
                                        'line_set' => [
                                            '__typename' => SolutionType::NAME,
                                            'type' => SolutionTypeEnum::LINE_SET,
                                            'series' => null,
                                            'zone' => null,
                                            'climate_zones' => null,
                                            'indoor_type' => null,
                                            'btu' => null,
                                            'voltage' => null,
                                            'line_sets' => null,
                                            'indoors' => null,
                                            'product' => [
                                                '__typename' => ProductType::NAME,
                                                'id' => $lineSets[1]->product->id
                                            ]
                                        ],
                                        'default_for_zones' => null,
                                    ],
                                ],
                                'indoors' => null,
                            ]
                        ]
                    ]
                ]
            );
    }

    public function test_update_indoor(): void
    {
        $lineSets = Solution::factory()
            ->lineSet()
            ->count(2)
            ->create();

        $indoor = Solution::factory()
            ->indoor()
            ->create();

        $query = GraphQLQuery::mutation(SolutionCreateUpdateMutation::NAME)
            ->args(
                [
                    'solution' => [
                        'product_id' => $indoor->product->id,
                        'type' => SolutionTypeEnum::INDOOR(),
                        'indoor_type' => SolutionIndoorEnum::WALL_MOUNT(),
                        'btu' => $indoor->btu,
                        'series_id' => $indoor->series_id,
                        'line_sets' => $lineSets
                            ->map(
                                function (Solution $lineSet) use (&$notFirst)
                                {
                                    $result = [
                                        'line_set_id' => $lineSet->id,
                                    ];

                                    if (empty($notFirst)) {
                                        $notFirst = true;

                                        $result['default_for_zones'] = [
                                            SolutionZoneEnum::SINGLE(),
                                            SolutionZoneEnum::MULTI(),
                                        ];
                                    }
                                    return $result;
                                }
                            )
                            ->toArray()
                    ]
                ]
            )
            ->select(
                [
                    'solution' => [
                        'indoor_type',
                        'line_sets' => [
                            'line_set' => [
                                '__typename',
                                'type',
                                'product' => [
                                    'id'
                                ]
                            ]
                        ]
                    ]
                ]
            );

        $this->postGraphQLBackOffice($query->make())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        SolutionCreateUpdateMutation::NAME => [
                            'solution' => [
                                'indoor_type' => SolutionIndoorEnum::WALL_MOUNT,
                                'line_sets' => [
                                    [
                                        'line_set' => [
                                            'type' => SolutionTypeEnum::LINE_SET,
                                            'product' => [
                                                'id' => $lineSets[0]->product->id
                                            ],
                                        ]
                                    ],
                                    [
                                        'line_set' => [
                                            'type' => SolutionTypeEnum::LINE_SET,
                                            'product' => [
                                                'id' => $lineSets[1]->product->id
                                            ],
                                        ]
                                    ],
                                ],
                            ]
                        ]
                    ]
                ]
            );
    }

    public function test_update_indoor_with_parent(): void
    {
        $indoors = Solution::factory()
            ->indoor()
            ->count(2)
            ->create();

        $outdoor = Solution::factory()
            ->children($indoors)
            ->create();

        $query = GraphQLQuery::mutation(SolutionCreateUpdateMutation::NAME)
            ->args(
                [
                    'solution' => [
                        'product_id' => $indoors[0]->product->id,
                        'type' => SolutionTypeEnum::LINE_SET(),
                        'short_name' => $this->faker->lexify,
                    ]
                ]
            )
            ->select(
                [
                    'solution' => [
                        'type',
                        'indoor_type',
                        'line_sets' => [
                            'line_set' => [
                                'type',
                            ]
                        ]
                    ]
                ]
            );

        $this->postGraphQLBackOffice($query->make())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        SolutionCreateUpdateMutation::NAME => [
                            'solution' => [
                                'type' => SolutionTypeEnum::LINE_SET,
                                'indoor_type' => null,
                                'line_sets' => null,
                            ]
                        ]
                    ]
                ]
            );

        $this->assertFalse(
            $indoors[0]->parents()
                ->exists()
        );
        $this->assertFalse(
            $outdoor->children()
                ->where('id', $indoors[0]->id)
                ->exists()
        );
    }

    public function test_update_indoor_with_one_parent(): void
    {
        $indoors = Solution::factory()
            ->indoor()
            ->count(1)
            ->create();

        $outdoor = Solution::factory()
            ->children($indoors)
            ->outdoor()
            ->create();

        $query = GraphQLQuery::mutation(SolutionCreateUpdateMutation::NAME)
            ->args(
                [
                    'solution' => [
                        'product_id' => $indoors[0]->product->id,
                        'type' => SolutionTypeEnum::LINE_SET(),
                        'short_name' => $this->faker->lexify,
                    ]
                ]
            )
            ->select(
                [
                    'solution' => [
                        'type',
                    ]
                ]
            );

        $this->postGraphQLBackOffice($query->make())
            ->assertOk()
            ->assertJson(
                [
                    'errors' => [
                        [
                            'message' => trans(
                                'validation.custom.catalog.solutions.cant_change_type_and_delete',
                                ['product' => $outdoor->product->title]
                            )
                        ]
                    ]
                ]
            );

        $this->assertTrue(
            $indoors[0]->parents()
                ->exists()
        );
        $this->assertTrue(
            $outdoor->children()
                ->where('id', $indoors[0]->id)
                ->exists()
        );
    }

    public function test_create_outdoor(): void
    {
        $indoors = Solution::factory()
            ->indoor()
            ->count(2)
            ->create();

        $product = Product::factory()
            ->create();
        $series = SolutionSeries::query()
            ->first();
        $btu = config(
            'catalog.solutions.btu.lists.' .
            SolutionTypeEnum::OUTDOOR . '.' .
            SolutionZoneEnum::MULTI
        )[6];

        $voltage = config('catalog.solutions.voltage.default');

        $query = GraphQLQuery::mutation(SolutionCreateUpdateMutation::NAME)
            ->args(
                [
                    'solution' => [
                        'product_id' => $product->id,
                        'type' => SolutionTypeEnum::OUTDOOR(),
                        'zone' => SolutionZoneEnum::MULTI(),
                        'climate_zones' => [
                            SolutionClimateZoneEnum::COLD(),
                            SolutionClimateZoneEnum::MODERATE(),
                        ],
                        'default_schemas' => [
                            [
                                'count_zones' => 2,
                                'indoors' => $indoors->pluck('id')
                                    ->toArray()
                            ]
                        ],
                        'btu' => $btu,
                        'max_btu_percent' => 40,
                        'voltage' => $voltage,
                        'series_id' => $series->id,
                        'indoors' => $indoors->pluck('id')
                            ->toArray()
                    ]
                ]
            )
            ->select(
                [
                    '__typename',
                    'id',
                    'solution' => [
                        '__typename',
                        'type',
                        'series' => [
                            '__typename',
                            'id'
                        ],
                        'zone',
                        'climate_zones',
                        'indoor_type',
                        'btu',
                        'max_btu_percent',
                        'voltage',
                        'default_schemas' => [
                            'count_zones',
                            'indoors' => [
                                'id'
                            ]
                        ],
                        'line_sets' => [
                            'line_set' => [
                                '__typename',
                                'type',
                                'series' => [
                                    '__typename',
                                    'id'
                                ],
                                'zone',
                                'climate_zones',
                                'indoor_type',
                                'btu',
                                'voltage',
                                'line_sets' => [
                                    'line_set' => [
                                        'type'
                                    ]
                                ],
                                'indoors' => [
                                    'type'
                                ],
                                'product' => [
                                    '__typename',
                                    'id'
                                ]
                            ]
                        ],
                        'indoors' => [
                            '__typename',
                            'type',
                            'series' => [
                                'id'
                            ],
                            'zone',
                            'climate_zones',
                            'indoor_type',
                            'btu',
                            'voltage',
                            'line_sets' => [
                                'line_set' => [
                                    'type'
                                ]
                            ],
                            'indoors' => [
                                'type'
                            ],
                            'product' => [
                                '__typename',
                                'id'
                            ]
                        ]
                    ]
                ]
            );

        $this->postGraphQLBackOffice($query->make())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        SolutionCreateUpdateMutation::NAME => [
                            '__typename' => ProductType::NAME,
                            'id' => $product->id,
                            'solution' => [
                                '__typename' => SolutionType::NAME,
                                'type' => SolutionTypeEnum::OUTDOOR,
                                'series' => [
                                    '__typename' => SolutionSeriesType::NAME,
                                    'id' => $series->id
                                ],
                                'zone' => SolutionZoneEnum::MULTI,
                                'climate_zones' => [
                                    SolutionClimateZoneEnum::COLD,
                                    SolutionClimateZoneEnum::MODERATE,
                                ],
                                'indoor_type' => null,
                                'btu' => $btu,
                                'max_btu_percent' => 40,
                                'voltage' => $voltage,
                                'default_schemas' => [
                                    [
                                        'count_zones' => 2,
                                        'indoors' => [
                                            [
                                                'id' => $indoors[0]->id
                                            ],
                                            [
                                                'id' => $indoors[1]->id
                                            ]
                                        ]
                                    ]
                                ],
                                'line_sets' => null,
                                'indoors' => [
                                    [
                                        '__typename' => SolutionType::NAME,
                                        'type' => SolutionTypeEnum::INDOOR,
                                        'series' => [
                                            'id' => $indoors[0]->series->id
                                        ],
                                        'zone' => null,
                                        'climate_zones' => null,
                                        'indoor_type' => $indoors[0]->indoor_type,
                                        'btu' => $indoors[0]->btu,
                                        'voltage' => null,
                                        'line_sets' => [
                                            [
                                                'line_set' => [
                                                    'type' => SolutionTypeEnum::LINE_SET,
                                                ]
                                            ],
                                            [
                                                'line_set' => [
                                                    'type' => SolutionTypeEnum::LINE_SET,
                                                ]
                                            ],
                                        ],
                                        'indoors' => null,
                                        'product' => [
                                            '__typename' => ProductType::NAME,
                                            'id' => $indoors[0]->product->id
                                        ],
                                    ],
                                    [
                                        '__typename' => SolutionType::NAME,
                                        'type' => SolutionTypeEnum::INDOOR,
                                        'series' => [
                                            'id' => $indoors[1]->series->id
                                        ],
                                        'zone' => null,
                                        'climate_zones' => null,
                                        'indoor_type' => $indoors[1]->indoor_type,
                                        'btu' => $indoors[1]->btu,
                                        'voltage' => null,
                                        'line_sets' => [
                                            [
                                                'line_set' => [
                                                    'type' => SolutionTypeEnum::LINE_SET,
                                                ]
                                            ],
                                            [
                                                'line_set' => [
                                                    'type' => SolutionTypeEnum::LINE_SET,
                                                ]
                                            ],
                                        ],
                                        'indoors' => null,
                                        'product' => [
                                            '__typename' => ProductType::NAME,
                                            'id' => $indoors[1]->product->id
                                        ],
                                    ],
                                ],
                            ]
                        ]
                    ]
                ]
            );
    }

    public function test_update_outdoor(): void
    {
        $outdoor = Solution::factory()
            ->outdoor()
            ->create();

        $query = GraphQLQuery::mutation(SolutionCreateUpdateMutation::NAME)
            ->args(
                [
                    'solution' => [
                        'product_id' => $outdoor->product->id,
                        'type' => SolutionTypeEnum::OUTDOOR(),
                        'zone' => SolutionZoneEnum::SINGLE(),
                        'climate_zones' => [
                            SolutionClimateZoneEnum::HOT(),
                        ],
                        'btu' => config(
                            'catalog.solutions.btu.lists.' .
                            SolutionTypeEnum::OUTDOOR . '.' .
                            SolutionZoneEnum::SINGLE
                        )[2],
                        'voltage' => $outdoor->voltage,
                        'series_id' => $outdoor->series_id,
                        'indoors' => $outdoor->children->pluck('id')
                            ->toArray()
                    ]
                ]
            )
            ->select(
                [
                    'solution' => [
                        'zone',
                        'climate_zones',
                    ]
                ]
            );

        $this->postGraphQLBackOffice($query->make())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        SolutionCreateUpdateMutation::NAME => [
                            'solution' => [
                                'zone' => SolutionZoneEnum::SINGLE,
                                'climate_zones' => [
                                    SolutionClimateZoneEnum::HOT,
                                ],
                            ]
                        ]
                    ]
                ]
            );
    }

    public function test_update_outdoor_change_type(): void
    {
        $outdoor = Solution::factory()
            ->outdoor()
            ->create();

        $query = GraphQLQuery::mutation(SolutionCreateUpdateMutation::NAME)
            ->args(
                [
                    'solution' => [
                        'product_id' => $outdoor->product->id,
                        'type' => SolutionTypeEnum::LINE_SET(),
                        'short_name' => $this->faker->lexify,
                    ]
                ]
            )
            ->select(
                [
                    'solution' => [
                        'type'
                    ]
                ]
            );

        $this->postGraphQLBackOffice($query->make())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        SolutionCreateUpdateMutation::NAME => [
                            'solution' => [
                                'type' => SolutionTypeEnum::LINE_SET
                            ]
                        ]
                    ]
                ]
            );

        $this->assertFalse(
            $outdoor->children()
                ->exists()
        );
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->loginByAdminManager([SolutionCreateUpdatePermission::KEY]);
    }
}
