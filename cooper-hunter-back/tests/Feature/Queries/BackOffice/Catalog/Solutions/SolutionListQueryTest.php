<?php


namespace Tests\Feature\Queries\BackOffice\Catalog\Solutions;


use App\Enums\Solutions\SolutionTypeEnum;
use App\GraphQL\Queries\BackOffice\Catalog\Solutions\SolutionListQuery;
use App\Models\Catalog\Solutions\Solution;
use App\Permissions\Catalog\Solutions\SolutionReadPermission;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Permissions\AdminManagerHelperTrait;

class SolutionListQueryTest extends TestCase
{
    use AdminManagerHelperTrait;
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginByAdminManager([SolutionReadPermission::KEY]);

        Solution::query()
            ->delete();

        Solution::factory()
            ->children(
                Solution::factory()
                    ->children(
                        Solution::factory()
                            ->lineSet()
                            ->count(15)
                            ->create()
                    )
                    ->indoor()
                    ->count(10)
                    ->create()
            )
            ->outdoor()
            ->count(3)
            ->create();
    }

    public function test_get_solutions(): void
    {
        $this->postGraphQLBackOffice(
            GraphQLQuery::query(SolutionListQuery::NAME)
                ->select(
                    [
                        '__typename',
                        'id',
                        'type',
                        'product' => [
                            'id'
                        ],
                        'series' => [
                            '__typename',
                            'id'
                        ],
                        'zone',
                        'climate_zones',
                        'indoor_type',
                        'btu',
                        'voltage',
                        'default_schemas' => [
                            'count_zones',
                            'indoors' => [
                                'id'
                            ]
                        ],
                        'line_sets' => [
                            'line_set' => [
                                'id',
                                'type'
                            ],
                            'default_for_zones'
                        ],
                        'indoors' => [
                            'id',
                            'type',
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonCount(28, 'data.' . SolutionListQuery::NAME);
    }

    public function test_get_line_set_solutions(): void
    {
        $this->postGraphQLBackOffice(
            GraphQLQuery::query(SolutionListQuery::NAME)
                ->args(
                    [
                        'type' => SolutionTypeEnum::LINE_SET()
                    ]
                )
                ->select(
                    [
                        '__typename'
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonCount(15, 'data.' . SolutionListQuery::NAME);
    }

    public function test_get_indoor_solutions(): void
    {
        $this->postGraphQLBackOffice(
            GraphQLQuery::query(SolutionListQuery::NAME)
                ->args(
                    [
                        'type' => SolutionTypeEnum::INDOOR()
                    ]
                )
                ->select(
                    [
                        '__typename'
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonCount(10, 'data.' . SolutionListQuery::NAME);
    }

    public function test_get_outdoor_solutions(): void
    {
        $this->postGraphQLBackOffice(
            GraphQLQuery::query(SolutionListQuery::NAME)
                ->args(
                    [
                        'type' => SolutionTypeEnum::OUTDOOR()
                    ]
                )
                ->select(
                    [
                        '__typename',
                        'default_schemas' => [
                            'count_zones',
                            'indoors' => [
                                'id'
                            ]
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        SolutionListQuery::NAME => [
                            '*' => [
                                '__typename',
                                'default_schemas' => [
                                    '*' => [
                                        'count_zones',
                                        'indoors' => [
                                            '*' => [
                                                'id'
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(3, 'data.' . SolutionListQuery::NAME);
    }
}
