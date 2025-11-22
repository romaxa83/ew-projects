<?php


namespace Tests\Feature\Queries\FrontOffice\Branches;


use App\GraphQL\Queries\FrontOffice\Branches\BranchesQuery;
use App\Models\Branches\Branch;
use App\Models\Locations\Region;
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

        $this->branches[] = Branch::factory()
            ->create(
                [
                    'region_id' => Region::whereSlug('kyiv-city')
                        ->first()->id,
                    'name' => 'abcd',
                    'city' => 'Kiev'
                ]
            );

        $this->branches[] = Branch::factory()
            ->create(
                [
                    'region_id' => Region::whereSlug('kharkiv')
                        ->first()->id,
                    'name' => 'abcd',
                    'city' => 'Kharkiv'
                ]
            );

        $this->branches[] = Branch::factory()
            ->create(
                [
                    'region_id' => Region::whereSlug('donetsk')
                        ->first()->id,
                    'name' => 'abcd',
                    'city' => 'Donetsk'
                ]
            );

        $this->branches[] = Branch::factory()
            ->create(
                [
                    'region_id' => Region::whereSlug('donetsk')
                        ->first()->id,
                    'name' => 'bcd',
                    'city' => 'Donetsk'
                ]
            );

        $this->branches[] = Branch::factory()
            ->create(
                [
                    'region_id' => Region::whereSlug('donetsk')
                        ->first()->id,
                    'name' => 'bcd',
                    'city' => 'Makeevka'
                ]
            );

        $user = $this->loginAsUserWithRole();

        $user->branch()->delete();
    }

    public function test_get_all_list(): void
    {
        $this->postGraphQL(
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
        $this->postGraphQL(
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
        $this->postGraphQL(
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
        $this->postGraphQL(
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
        $this->postGraphQL(
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
        $this->postGraphQL(
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
        $this->postGraphQL(
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
}
