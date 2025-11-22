<?php


namespace Tests\Feature\Mutations\BackOffice\Branches;


use App\GraphQL\Mutations\BackOffice\Branches\BranchCreateMutation;
use App\Models\Branches\Branch;
use App\Models\Locations\Region;
use App\Models\Locations\RegionTranslate;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BranchCreateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function test_create_branch(): void
    {
        $this->loginAsAdminWithRole();

        $region = Region::inRandomOrder()
            ->first();

        $branch = [
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
            GraphQLQuery::mutation(BranchCreateMutation::NAME)
                ->args(
                    [
                        'branch' => $branch
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
                        'active',
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
                        BranchCreateMutation::NAME => [
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
                            'active',
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
                        BranchCreateMutation::NAME => [
                            'name' => $branch['name'],
                            'city' => $branch['city'],
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
                            'address' => $branch['address'],
                            'active' => true,
                            'phone' => $branch['phones'][0]['phone'],
                            'phones' => [
                                [
                                    'phone' => $branch['phones'][0]['phone'],
                                    'is_default' => true,
                                ],
                                [
                                    'phone' => $branch['phones'][1]['phone'],
                                    'is_default' => false,
                                ],
                            ]
                        ]
                    ]
                ]
            );
        $this->assertDatabaseCount(Branch::class, 1);
    }

    public function test_try_to_create_similar_branch(): void
    {
        $this->loginAsAdminWithRole();

        $branch = Branch::factory()
            ->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(BranchCreateMutation::NAME)
                ->args(
                    [
                        'branch' => [
                            'name' => $this->faker->company,
                            'city' => $branch->city,
                            'region_id' => $branch->region_id,
                            'address' => $branch->address,
                            'phones' => [
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
                                ['branch_name' => $branch->name]
                            )
                        ]
                    ]
                ]
            );
    }
}
