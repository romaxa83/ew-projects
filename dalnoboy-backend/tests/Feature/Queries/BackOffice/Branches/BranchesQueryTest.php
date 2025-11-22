<?php


namespace Tests\Feature\Queries\BackOffice\Branches;


use App\GraphQL\Queries\BackOffice\Branches\BranchesQuery;
use App\Models\Branches\Branch;
use App\Models\Inspections\Inspection;
use App\Models\Locations\Region;
use App\Models\Users\User;
use Carbon\CarbonImmutable;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class BranchesQueryTest extends TestCase
{
    use DatabaseTransactions;

    /**@var Branch[] $branches */
    private iterable $branches;

    public function setUp(): void
    {
        parent::setUp();

        $date = CarbonImmutable::now();

        $this->branches[] = Branch::factory()
            ->create(
                [
                    'region_id' => Region::whereSlug('kyiv-city')
                        ->first()->id,
                    'name' => 'abcd',
                    'city' => 'Kiev',
                    'created_at' => $date->subSeconds(10)
                ]
            );

        $this->branches[] = Branch::factory()
            ->create(
                [
                    'region_id' => Region::whereSlug('kharkiv')
                        ->first()->id,
                    'name' => 'abcd',
                    'city' => 'Kharkiv',
                    'created_at' => $date->subSeconds(9)
                ]
            );

        $this->branches[] = Branch::factory()
            ->create(
                [
                    'region_id' => Region::whereSlug('donetsk')
                        ->first()->id,
                    'name' => 'abcd',
                    'city' => 'Donetsk',
                    'created_at' => $date->subSeconds(8)
                ]
            );

        $this->branches[] = Branch::factory()
            ->create(
                [
                    'region_id' => Region::whereSlug('donetsk')
                        ->first()->id,
                    'name' => 'bcd',
                    'city' => 'Donetsk',
                    'created_at' => $date->subSeconds(7)
                ]
            );

        $this->branches[] = Branch::factory()
            ->create(
                [
                    'region_id' => Region::whereSlug('donetsk')
                        ->first()->id,
                    'name' => 'bcd',
                    'city' => 'Makeevka',
                    'created_at' => $date->subSeconds(6)
                ]
            );

        $this->loginAsAdminWithRole();
    }

    public function test_get_all_list(): void
    {
        $this->postGraphQLBackOffice(
            GraphQLQuery::query(BranchesQuery::NAME)
                ->select(
                    [
                        'data' => [
                            'id',
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        BranchesQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $this->branches[0]->id
                                ],
                                [
                                    'id' => $this->branches[1]->id
                                ],
                                [
                                    'id' => $this->branches[2]->id
                                ],
                                [
                                    'id' => $this->branches[3]->id
                                ],
                                [
                                    'id' => $this->branches[4]->id
                                ],
                            ]
                        ]
                    ]
                ]
            );
    }

    public function test_get_all_list_sort_name_desc(): void
    {
        $this->postGraphQLBackOffice(
            GraphQLQuery::query(BranchesQuery::NAME)
                ->args(
                    [
                        'sort' => [
                            'name-desc'
                        ]
                    ]
                )
                ->select(
                    [
                        'data' => [
                            'id',
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        BranchesQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $this->branches[3]->id
                                ],
                                [
                                    'id' => $this->branches[4]->id
                                ],
                                [
                                    'id' => $this->branches[0]->id
                                ],
                                [
                                    'id' => $this->branches[1]->id
                                ],
                                [
                                    'id' => $this->branches[2]->id
                                ],
                            ]
                        ]
                    ]
                ]
            );
    }

    public function test_get_all_list_sort_region_desc(): void
    {
        $this->postGraphQLBackOffice(
            GraphQLQuery::query(BranchesQuery::NAME)
                ->args(
                    [
                        'sort' => [
                            'region-desc'
                        ]
                    ]
                )
                ->select(
                    [
                        'data' => [
                            'id',
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        BranchesQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $this->branches[0]->id
                                ],
                                [
                                    'id' => $this->branches[1]->id
                                ],
                                [
                                    'id' => $this->branches[2]->id
                                ],
                                [
                                    'id' => $this->branches[3]->id
                                ],
                                [
                                    'id' => $this->branches[4]->id
                                ],
                            ]
                        ]
                    ]
                ]
            );
    }

    public function test_filter_by_id(): void
    {
        $this->postGraphQLBackOffice(
            GraphQLQuery::query(BranchesQuery::NAME)
                ->args(
                    [
                        'id' => $this->branches[2]->id
                    ]
                )
                ->select(
                    [
                        'data' => [
                            'id',
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        BranchesQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $this->branches[2]->id
                                ],
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(1, 'data.' . BranchesQuery::NAME . '.data');
    }

    public function test_filter_by_name(): void
    {
        $this->postGraphQLBackOffice(
            GraphQLQuery::query(BranchesQuery::NAME)
                ->args(
                    [
                        'query' => 'abcd'
                    ]
                )
                ->select(
                    [
                        'data' => [
                            'id',
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        BranchesQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $this->branches[0]->id
                                ],
                                [
                                    'id' => $this->branches[1]->id
                                ],
                                [
                                    'id' => $this->branches[2]->id
                                ],
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(3, 'data.' . BranchesQuery::NAME . '.data');
    }

    public function test_filter_by_city(): void
    {
        $this->postGraphQLBackOffice(
            GraphQLQuery::query(BranchesQuery::NAME)
                ->args(
                    [
                        'query' => 'Makeevka'
                    ]
                )
                ->select(
                    [
                        'data' => [
                            'id',
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        BranchesQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $this->branches[4]->id
                                ],
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(1, 'data.' . BranchesQuery::NAME . '.data');
    }

    public function test_filter_by_region(): void
    {
        $this->postGraphQLBackOffice(
            GraphQLQuery::query(BranchesQuery::NAME)
                ->args(
                    [
                        'query' => 'Kharkiv'
                    ]
                )
                ->select(
                    [
                        'data' => [
                            'id',
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        BranchesQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $this->branches[1]->id
                                ],
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(1, 'data.' . BranchesQuery::NAME . '.data');
    }

    public function test_total_inspections(): void
    {
        $user = User::factory()
            ->create();

        Inspection::factory()
            ->forInspector($user)
            ->count(4)
            ->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(BranchesQuery::NAME)
                ->args(
                    [
                        'id' => $user->branch->id
                    ]
                )
                ->select(
                    [
                        'data' => [
                            'id',
                            'inspections_count'
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        BranchesQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $user->branch->id,
                                    'inspections_count' => 4
                                ],
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(1, 'data.' . BranchesQuery::NAME . '.data');
    }
}
