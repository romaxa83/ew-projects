<?php


namespace Tests\Feature\Mutations\BackOffice\Branches;


use App\GraphQL\Mutations\BackOffice\Branches\BranchUpdateMutation;
use App\Models\Branches\Branch;
use App\Models\Locations\Region;
use App\Models\Locations\RegionTranslate;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BranchUpdateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function test_update_branch(): void
    {
        $this->loginAsAdminWithRole();

        $branch = Branch::factory()
            ->create();

        $region = Region::inRandomOrder()
            ->first();

        $newBranchData = [
            'name' => $this->faker->company,
            'city' => $this->faker->city,
            'region_id' => $region->id,
            'address' => $this->faker->streetAddress,
            'phones' => [
                [
                    'phone' => $this->faker->ukrainianPhone,
                    'is_default' => true
                ],
                [
                    'phone' => $this->faker->ukrainianPhone,
                ]
            ]
        ];

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(BranchUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $branch->id,
                        'branch' => $newBranchData
                    ]
                )
                ->select(
                    [
                        'id',
                        'name',
                        'city',
                        'region' => [
                            'id',
                            'slug',
                            'translate' => [
                                'title',
                                'language'
                            ],
                            'translates' => [
                                'title',
                                'language'
                            ]
                        ],
                        'address',
                        'phone',
                        'phones' => [
                            'phone',
                            'is_default'
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        BranchUpdateMutation::NAME => [
                            'id',
                            'name',
                            'city',
                            'region' => [
                                'id',
                                'slug',
                                'translate' => [
                                    'title',
                                    'language'
                                ],
                                'translates' => [
                                    '*' => [
                                        'title',
                                        'language'
                                    ]
                                ],
                            ],
                            'address',
                            'phone',
                            'phones' => [
                                '*' => [
                                    'phone',
                                    'is_default'
                                ]
                            ]
                        ]
                    ]
                ]
            )
            ->assertJson(
                [
                    'data' => [
                        BranchUpdateMutation::NAME => [
                            'id' => $branch->id,
                            'name' => $newBranchData['name'],
                            'city' => $newBranchData['city'],
                            'region' => [
                                'id' => $region->id,
                                'slug' => $region->slug,
                                'translate' => [
                                    'title' => $region->translate->title,
                                    'language' => $region->translate->language,
                                ],
                                'translates' => $region
                                    ->translates
                                    ->map(
                                        fn(RegionTranslate $translate) => [
                                            'title' => $translate->title,
                                            'language' => $translate->language,
                                        ]
                                    )
                                    ->toArray()
                            ],
                            'address' => $newBranchData['address'],
                            'phone'  => $newBranchData['phones'][0]['phone'],
                            'phones' => [
                                [
                                    'phone' => $newBranchData['phones'][0]['phone'],
                                    'is_default' => true,
                                ],
                                [
                                    'phone' => $newBranchData['phones'][1]['phone'],
                                    'is_default' => false,
                                ],
                            ]
                        ]
                    ]
                ]
            );
    }

    public function test_try_to_update_similar_branch(): void
    {
        $this->loginAsAdminWithRole();

        $branch = Branch::factory()
            ->create();
        $sameBranch = Branch::factory()
            ->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(BranchUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $branch->id,
                        'branch' => [
                            'name' => $this->faker->company,
                            'city' => $sameBranch->city,
                            'region_id' => $sameBranch->region_id,
                            'address' => $sameBranch->address,
                            'phones' => [
                                [
                                    'phone' => $this->faker->ukrainianPhone,
                                    'is_default' => true
                                ],
                                [
                                    'phone' => $this->faker->ukrainianPhone,
                                ]
                            ]
                        ]
                    ]
                )
                ->select(
                    [
                        'id',
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'errors' => [
                        [
                            'message' => trans(
                                'validation.custom.branches.similar_branch',
                                ['branch_name' => $sameBranch->name]
                            )
                        ]
                    ]
                ]
            );
    }

    public function test_update_current_branch_to_similar_data(): void
    {
        $this->loginAsAdminWithRole();

        $branch = Branch::factory()
            ->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(BranchUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $branch->id,
                        'branch' => [
                            'name' => $this->faker->company,
                            'city' => $branch->city,
                            'region_id' => $branch->region_id,
                            'address' => $branch->address,
                            'phones' => [
                                [
                                    'phone' => $this->faker->ukrainianPhone,
                                    'is_default' => true
                                ],
                                [
                                    'phone' => $this->faker->ukrainianPhone,
                                ]
                            ]
                        ]
                    ]
                )
                ->select(
                    [
                        'id',
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        BranchUpdateMutation::NAME => [
                            'id' => $branch->id
                        ]
                    ]
                ]
            );
    }
}
