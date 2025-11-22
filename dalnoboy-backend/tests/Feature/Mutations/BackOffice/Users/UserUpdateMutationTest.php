<?php

namespace Tests\Feature\Mutations\BackOffice\Users;

use App\GraphQL\Mutations\BackOffice\Users\UserUpdateMutation;
use App\Models\Branches\Branch;
use App\Models\Users\User;
use App\Models\Users\UserBranch;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserUpdateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function test_update_user(): void
    {
        $this->loginAsAdminWithRole();

        $user = User::factory()
            ->inspector()
            ->create();

        $oldBranchId = $user->branch->id;

        $branch = Branch::factory()
            ->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(UserUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $user->id,
                        'user' => [
                            'first_name' => $user->first_name,
                            'last_name' => $lastName = $this->faker->lastName,
                            'second_name' => $user->second_name,
                            'phones' => [
                                [
                                    'phone' => $user->phone->phone
                                ]
                            ],
                            'email' => $user->email,
                            'branch_id' => $branch->id,
                            'role_id' => $user->role->id
                        ]
                    ]
                )
                ->select(
                    [
                        'id',
                        'last_name',
                        'branch' => [
                            'id'
                        ]
                    ]
                )
                ->make()
        )
            ->assertJson(
                [
                    'data' => [
                        UserUpdateMutation::NAME => [
                            'id' => $user->id,
                            'last_name' => $lastName,
                            'branch' => [
                                'id' => $branch->id
                            ]
                        ]
                    ]
                ]
            );

        $this->assertDatabaseMissing(
            UserBranch::class,
            [
                'user_id' => $user->id,
                'branch_id' => $oldBranchId
            ]
        );

        $this->assertDatabaseHas(
            UserBranch::class,
            [
                'user_id' => $user->id,
                'branch_id' => $branch->id
            ]
        );
    }

    public function test_try_update_user_with_not_unique_email(): void
    {
        $this->loginAsAdminWithRole();

        $users = User::factory()
            ->count(2)
            ->inspector()
            ->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(UserUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $users[0]->id,
                        'user' => [
                            'first_name' => $users[0]->first_name,
                            'last_name' => $users[0]->last_name,
                            'second_name' => $users[0]->second_name,
                            'phones' => [
                                [
                                    'phone' => $users[0]->phone->phone
                                ]
                            ],
                            'email' => $users[1]->email,
                            'branch_id' => $users[0]->branch->id,
                            'role_id' => $users[0]->role->id
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
                            'message' => trans('validation.custom.users.uniq_email')
                        ]
                    ]
                ]
            );
    }
}
