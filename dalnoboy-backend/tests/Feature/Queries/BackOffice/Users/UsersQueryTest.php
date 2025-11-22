<?php

namespace Tests\Feature\Queries\BackOffice\Users;

use App\GraphQL\Queries\BackOffice\Users\UsersQuery;
use App\Models\Inspections\Inspection;
use App\Models\Users\User;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UsersQueryTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    private iterable $users;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();

        $this->users = User::factory()
            ->count(5)
            ->inspector()
            ->create();
    }

    public function test_get_all_users(): void
    {
        $this->postGraphQLBackOffice(
            GraphQLQuery::query(UsersQuery::NAME)
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
                        UsersQuery::NAME => [
                            'data' => $this
                                ->users
                                ->sortBy('last_name')
                                ->map(
                                    fn(User $user) => [
                                        'id' => $user->id,
                                    ]
                                )
                                ->values()
                                ->toArray()
                        ]
                    ]
                ]
            );
    }

    public function test_get_users_sort_name_desc(): void
    {
        $this->postGraphQLBackOffice(
            GraphQLQuery::query(UsersQuery::NAME)
                ->args(
                    [
                        'sort' => [
                            'full_name-desc'
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
                        UsersQuery::NAME => [
                            'data' => $this
                                ->users
                                ->sortByDesc('last_name')
                                ->map(
                                    fn(User $user) => [
                                        'id' => $user->id,
                                    ]
                                )
                                ->values()
                                ->toArray()
                        ]
                    ]
                ]
            );
    }

    public function test_get_users_sort_branch_name_asc(): void
    {
        $this->postGraphQLBackOffice(
            GraphQLQuery::query(UsersQuery::NAME)
                ->args(
                    [
                        'sort' => [
                            'branch_name-asc'
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
                        UsersQuery::NAME => [
                            'data' => User::query()
                                ->with('branch')
                                ->get()
                                ->sortBy('branch.name')
                                ->map(
                                    fn(User $user) => [
                                        'id' => $user->id,
                                    ]
                                )
                                ->values()
                                ->toArray()
                        ]
                    ]
                ]
            );
    }

    public function test_get_user_by_email(): void
    {
        $this->postGraphQLBackOffice(
            GraphQLQuery::query(UsersQuery::NAME)
                ->args(
                    [
                        'query' => $this->users[0]->email
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
                        UsersQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $this->users[0]->id
                                ]
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(1, 'data.' . UsersQuery::NAME . '.data');
    }

    public function test_get_user_by_phone(): void
    {
        $this->postGraphQLBackOffice(
            GraphQLQuery::query(UsersQuery::NAME)
                ->args(
                    [
                        'query' => $this->users[2]->phone->phone
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
                        UsersQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $this->users[2]->id
                                ]
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(1, 'data.' . UsersQuery::NAME . '.data');
    }

    public function test_get_user_by_branch_name(): void
    {
        $this->postGraphQLBackOffice(
            GraphQLQuery::query(UsersQuery::NAME)
                ->args(
                    [
                        'query' => $this->users[4]->branch->name
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
                        UsersQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $this->users[4]->id
                                ]
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(1, 'data.' . UsersQuery::NAME . '.data');
    }

    public function test_get_user_with_count_inspections(): void
    {
        $user = Inspection::factory()
            ->create()->inspector;

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(UsersQuery::NAME)
                ->args(
                    [
                        'id' => $user->id
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
                        UsersQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $user->id,
                                    'inspections_count' => 1
                                ]
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(1, 'data.' . UsersQuery::NAME . '.data');
    }
}
