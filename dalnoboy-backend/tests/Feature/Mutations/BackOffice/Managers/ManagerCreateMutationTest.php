<?php

namespace Tests\Feature\Mutations\BackOffice\Managers;

use App\Enums\Utilities\MorphModelNameEnum;
use App\GraphQL\Mutations\BackOffice\Managers\ManagerCreateMutation;
use App\Models\Managers\Manager;
use App\Models\Phones\Phone;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ManagerCreateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_create_manager(): void
    {
        $manager = [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'second_name' => $this->faker->lastName,
            'region_id' => $this->faker->ukrainianRegionId,
            'city' => $this->faker->city,
            'phones' => [
                [
                    'phone' => $this->faker->ukrainianPhone,
                ]
            ],
        ];

        $managerId = $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(ManagerCreateMutation::NAME)
                ->args(
                    [
                        'manager' => $manager
                    ]
                )
                ->select(
                    [
                        'id',
                        'first_name',
                        'last_name',
                        'second_name',
                        'region' => [
                            'id',
                        ],
                        'city',
                        'phone',
                        'phones' => [
                            'is_default',
                            'phone'
                        ],
                    ]
                )
                ->make()
        )
            ->assertJson(
                [
                    'data' => [
                        ManagerCreateMutation::NAME => [
                            'first_name' => $manager['first_name'],
                            'last_name' => $manager['last_name'],
                            'second_name' => $manager['second_name'],
                            'region' => [
                                'id' => $manager['region_id']
                            ],
                            'city' => $manager['city'],
                            'phone' => $manager['phones'][0]['phone'],
                            'phones' => [
                                [
                                    'phone' => $manager['phones'][0]['phone'],
                                    'is_default' => true
                                ]
                            ],
                        ]
                    ]
                ]
            )
            ->json('data.' . ManagerCreateMutation::NAME . '.id');

        $this->assertDatabaseHas(
            Manager::class,
            [
                'id' => $managerId
            ]
        );

        $this->assertDatabaseHas(
            Phone::class,
            [
                'owner_type' => MorphModelNameEnum::manager()->key,
                'owner_id' => $managerId
            ]
        );
    }

    public function test_try_create_manager_with_not_unique_fio(): void
    {
        $manager = Manager::factory()
            ->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(ManagerCreateMutation::NAME)
                ->args(
                    [
                        'manager' => [
                            'first_name' => $manager->first_name,
                            'last_name' => $manager->last_name,
                            'second_name' => $manager->second_name,
                            'region_id' => $this->faker->ukrainianRegionId,
                            'city' => $this->faker->city,
                            'phones' => [
                                [
                                    'phone' => $this->faker->ukrainianPhone
                                ]
                            ],
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
                            'message' => trans('validation.custom.managers.uniq')
                        ]
                    ]
                ]
            );
    }
}
