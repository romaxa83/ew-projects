<?php

namespace Tests\Feature\Queries\FrontOffice\Users;

use App\GraphQL\Queries\FrontOffice\Users\UserQuery;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class UserQueryTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_get_profile_with_list_of_permissions(): void
    {
        $user = $this->loginAsUserWithRole();

        $this
            ->query()
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        UserQuery::NAME => [
                            'id' => $user->id,
                            'email' => $user->email,
                            'first_name' => $user->first_name,
                            'role' => [
                                'permissions' => $user->role->permissions->pluck('name')
                                    ->toArray()
                            ],
                            'language' => [
                                'name' => $user->language->name,
                                'slug' => $user->lang
                            ]
                        ]
                    ]
                ]
            );
    }

    protected function query(): TestResponse
    {
        return $this->postGraphQL(
            GraphQLQuery::query(UserQuery::NAME)
                ->select(
                    [
                        'id',
                        'first_name',
                        'last_name',
                        'email',
                        'language' => [
                            'name',
                            'slug',
                        ],
                        'role' => [
                            'permissions'
                        ]
                    ]
                )
                ->make()
        );
    }

    public function test_it_has_error_for_not_auth_users(): void
    {
        $result = $this->query()
            ->assertOk();

        $this->assertGraphQlUnauthorized($result);
    }
}
