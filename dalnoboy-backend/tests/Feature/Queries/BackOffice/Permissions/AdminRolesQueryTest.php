<?php

namespace Tests\Feature\Queries\BackOffice\Permissions;

use App\Enums\Permissions\AdminRolesEnum;
use App\Enums\Permissions\UserRolesEnum;
use App\GraphQL\Queries\BackOffice\Permissions\AdminRolesQuery;
use App\GraphQL\Queries\BackOffice\Permissions\UserRolesQuery;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdminRolesQueryTest extends TestCase
{
    use DatabaseTransactions;

    public function test_get_admin_roles(): void
    {
        $this->loginAsAdminWithRole();

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(AdminRolesQuery::NAME)
                ->select(
                    [
                        'id',
                        'name',
                        'translate' => [
                            'title'
                        ],
                        'permissions'
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        AdminRolesQuery::NAME => [
                            '*' => [
                                'id',
                                'name',
                                'translate' => [
                                    'title'
                                ],
                                'permissions'
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(count(AdminRolesEnum::getValues()), 'data.' . AdminRolesQuery::NAME);
    }

    public function test_get_user_roles(): void
    {
        $this->loginAsAdminWithRole();

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(UserRolesQuery::NAME)
                ->select(
                    [
                        'id',
                        'name',
                        'translate' => [
                            'title'
                        ],
                        'permissions'
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        UserRolesQuery::NAME => [
                            '*' => [
                                'id',
                                'name',
                                'translate' => [
                                    'title'
                                ],
                                'permissions'
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(count(UserRolesEnum::getValues()), 'data.' . UserRolesQuery::NAME);
    }
}
