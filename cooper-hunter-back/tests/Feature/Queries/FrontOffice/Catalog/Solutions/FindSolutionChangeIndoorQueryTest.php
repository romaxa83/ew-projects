<?php

namespace Tests\Feature\Queries\FrontOffice\Catalog\Solutions;

use App\Dto\Catalog\Solutions\FindSolutionDto;
use App\Enums\Solutions\SolutionIndoorEnum;
use App\Enums\Solutions\SolutionTypeEnum;
use App\Enums\Solutions\SolutionZoneEnum;
use App\GraphQL\Queries\FrontOffice\Catalog\Solutions\FindSolutionChangeIndoorQuery;
use App\Models\Catalog\Products\Product;
use App\Models\Catalog\Solutions\Solution;
use App\Services\Catalog\Solutions\SolutionService;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Database\Seeders\Catalog\Solutions\SolutionDemoSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class FindSolutionChangeIndoorQueryTest extends TestCase
{
    use DatabaseTransactions;

    protected static bool $wasRunSeed = false;

    private SolutionService $service;

    /**
     * @var Collection
     * indoors btu - 24000 18000 9000 9000
     * indoors types - UNIVERSAL_FLOOR_CEILING
     *                 WALL_MOUNT
     *                 SLIM_DUCT
     *                 SLIM_DUCT
     */
    private Collection $solution;

    public function test_change_indoor_type(): void
    {
        $indoors = $this->solution['indoors']->toArray();

        $indoors[1]['type'] = SolutionIndoorEnum::CEILING_CASSETTE();

        $query = GraphQLQuery::query(FindSolutionChangeIndoorQuery::NAME)
            ->args(
                [
                    'outdoor_id' => $this->solution['id'],
                    'count_zones' => 4,
                    'indoors' => array_map(
                        fn(array $indoor) => [
                            'btu' => $indoor['btu'],
                            'type' => $indoor['type'],
                            'series_id' => $indoor['series']->id
                        ],
                        $indoors
                    )
                ]
            )
            ->select(
                [
                    'id',
                    'is_correct_btu',
                    'indoors' => [
                        'type',
                        'btu',
                    ]
                ]
            );

        $this->postGraphQL($query->make())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        FindSolutionChangeIndoorQuery::NAME => [
                            'id' => $this->solution['id'],
                            'is_correct_btu' => true,
                            'indoors' => array_map(
                                fn(array $indoor) => [
                                    'type' => $indoor['type'],
                                    'btu' => $indoor['btu'],
                                ],
                                $indoors
                            )
                        ]
                    ]
                ]
            );
    }

    public function test_correct_change_indoor_btu(): void
    {
        $indoors = $this->solution['indoors']->toArray();

        $indoors[0]['btu'] = 18000;

        $query = GraphQLQuery::query(FindSolutionChangeIndoorQuery::NAME)
            ->args(
                [
                    'outdoor_id' => $this->solution['id'],
                    'count_zones' => 4,
                    'indoors' => array_map(
                        fn(array $indoor) => [
                            'btu' => $indoor['btu'],
                            'type' => $indoor['type'],
                            'series_id' => $indoor['series']->id
                        ],
                        $indoors
                    )
                ]
            )
            ->select(
                [
                    'id',
                    'is_correct_btu',
                    'indoors' => [
                        'type',
                        'btu',
                    ]
                ]
            );

        $this->postGraphQL($query->make())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        FindSolutionChangeIndoorQuery::NAME => [
                            'id' => $this->solution['id'],
                            'is_correct_btu' => true,
                            'indoors' => array_map(
                                fn(array $indoor) => [
                                    'type' => $indoor['type'],
                                    'btu' => $indoor['btu'],
                                ],
                                $indoors
                            )
                        ]
                    ]
                ]
            );
    }

    public function test_incorrect_change_indoor_btu(): void
    {
        $indoors = $this->solution['indoors']->toArray();

        $indoors[1]['btu'] = 24000;

        $query = GraphQLQuery::query(FindSolutionChangeIndoorQuery::NAME)
            ->args(
                [
                    'outdoor_id' => $this->solution['id'],
                    'count_zones' => 4,
                    'indoors' => array_map(
                        fn(array $indoor) => [
                            'btu' => $indoor['btu'],
                            'type' => $indoor['type'],
                            'series_id' => $indoor['series']->id
                        ],
                        $indoors
                    )
                ]
            )
            ->select(
                [
                    'id',
                    'is_correct_btu',
                    'indoors' => [
                        'type',
                        'btu',
                    ]
                ]
            );

        $this->postGraphQL($query->make())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        FindSolutionChangeIndoorQuery::NAME => [
                            'id' => $this->solution['id'],
                            'is_correct_btu' => false,
                            'indoors' => array_map(
                                fn(array $indoor) => [
                                    'type' => $indoor['type'],
                                    'btu' => $indoor['btu'],
                                ],
                                $indoors
                            )
                        ]
                    ]
                ]
            );
    }

    public function test_correct_change_three_indoors_btu(): void
    {
        $indoors = $this->solution['indoors']->toArray();

        $indoors[0]['btu'] = 18000;
        $indoors[1]['btu'] = 9000;
        $indoors[2]['btu'] = 9000;

        $query = GraphQLQuery::query(FindSolutionChangeIndoorQuery::NAME)
            ->args(
                [
                    'outdoor_id' => $this->solution['id'],
                    'count_zones' => 4,
                    'indoors' => array_map(
                        fn(array $indoor) => [
                            'btu' => $indoor['btu'],
                            'type' => $indoor['type'],
                            'series_id' => $indoor['series']->id
                        ],
                        $indoors
                    )
                ]
            )
            ->select(
                [
                    'id',
                    'is_correct_btu',
                    'indoors' => [
                        'type',
                        'btu',
                    ]
                ]
            );

        $this->postGraphQL($query->make())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        FindSolutionChangeIndoorQuery::NAME => [
                            'id' => $this->solution['id'],
                            'is_correct_btu' => true,
                            'indoors' => array_map(
                                fn(array $indoor) => [
                                    'type' => $indoor['type'],
                                    'btu' => $indoor['btu'],
                                ],
                                $indoors
                            )
                        ]
                    ]
                ]
            );
    }


    protected function setUp(): void
    {
        parent::setUp();
        Config::set('app.env', 'local');

        $this->seed(SolutionDemoSeeder::class);

        $this->service = resolve(SolutionService::class);

        /**@var Solution $solution */
        $solution = Product::whereTitle('CH-48MSPH-230VO')
            ->first()
            ->solution;

        $this->solution = $this->service->find(
            FindSolutionDto::byArgs(
                [
                    'type' => SolutionTypeEnum::OUTDOOR,
                    'zone' => SolutionZoneEnum::MULTI,
                    'count_zones' => 4,
                    'climate_zones' => $solution->climateZones()
                        ->get()
                        ->pluck('climate_zone')
                        ->toArray(),
                    'series_id' => $solution->series_id,
                    'btu' => $solution->btu,
                ]
            )
        );
    }
}
