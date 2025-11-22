<?php

namespace Tests\Feature\Mutations\BackOffice\Managers;

use App\GraphQL\Mutations\BackOffice\Managers\ManagerUpdateMutation;
use App\Models\Managers\Manager;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ManagerUpdateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_update_manager(): void
    {
        $manager = Manager::factory()
            ->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(ManagerUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $manager->id,
                        'manager' => [
                            'first_name' => $manager->first_name,
                            'last_name' => $manager->last_name,
                            'second_name' => $manager->second_name,
                            'region_id' => $manager->region_id,
                            'city' => $city = $this->faker->city,
                            'phones' => [
                                [
                                    'phone' => $phone = $this->faker->ukrainianPhone,
                                ]
                            ],
                        ]
                    ]
                )
                ->select(
                    [
                        'id',
                        'first_name',
                        'last_name',
                        'second_name',
                        'region' => [
                            'id'
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
                        ManagerUpdateMutation::NAME => [
                            'first_name' => $manager->first_name,
                            'last_name' => $manager->last_name,
                            'second_name' => $manager->second_name,
                            'region' => [
                                'id' => $manager->region_id,
                            ],
                            'city' => $city,
                            'phone' => $phone,
                            'phones' => [
                                [
                                    'phone' => $phone,
                                    'is_default' => true
                                ]
                            ],
                        ]
                    ]
                ]
            );
    }

    public function test_try_update_manager_with_not_unique_fio(): void
    {
        $manager = Manager::factory()
            ->create();

        $sameManager = Manager::factory()
            ->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(ManagerUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $manager->id,
                        'manager' => [
                            'first_name' => $sameManager->first_name,
                            'last_name' => $sameManager->last_name,
                            'second_name' => $sameManager->second_name,
                            'region_id' => $manager->region_id,
                            'city' => $this->faker->city,
                            'phones' => [
                                [
                                    'phone' => $this->faker->ukrainianPhone,
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
