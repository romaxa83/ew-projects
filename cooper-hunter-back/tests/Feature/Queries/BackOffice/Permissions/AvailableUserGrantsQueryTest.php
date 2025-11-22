<?php

namespace Tests\Feature\Queries\BackOffice\Permissions;

use App\GraphQL\Queries\BackOffice\Permissions\AvailableUserGrantsQuery;
use App\Models\Users\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AvailableUserGrantsQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const QUERY = AvailableUserGrantsQuery::NAME;

    public function test_cant_get_available_user_permissions_for_user(): void
    {
        $this->loginAsUser();

        $this->test_cant_get_available_user_permissions_for_not_auth();
    }

    public function test_cant_get_available_user_permissions_for_not_auth(): void
    {
        $query = sprintf(
            'query { %s {
                    key
                    name
                    position
                    permissions {
                        key
                        name
                        position
                    }
                } }',
            self::QUERY
        );
        $result = $this->postGraphQLBackOffice(compact('query'));

        $this->assertGraphQlUnauthorized($result);
    }

    public function test_it_get_list_of_user_permissions_groups_for_admin(): void
    {
        $this->loginAsAdmin();

        $query = sprintf(
            'query { %s {
                    key
                    name
                    position
                    permissions {
                        key
                        name
                        position
                    }
                } }',
            self::QUERY
        );
        $result = $this->postGraphQLBackOffice(compact('query'));

        $grants = $result->json('data.'.self::QUERY);

        self::assertCount(
            count(config('grants.matrix.'.User::GUARD.'.groups')),
            $grants
        );

//        self::assertEquals(
//            [
//                'key' => 'role',
//                'name' => 'Roles',
//                'position' => 0,
//                'permissions' => [
//                    [
//                        'key' => 'role.list',
//                        'name' => 'List',
//                        'position' => 1,
//                    ],
//                    [
//                        'key' => 'role.create',
//                        'name' => 'Create',
//                        'position' => 2,
//                    ],
//                    [
//                        'key' => 'role.update',
//                        'name' => 'Update',
//                        'position' => 3,
//                    ],
//                    [
//                        'key' => 'role.delete',
//                        'name' => 'Delete',
//                        'position' => 4,
//                    ],
//                ],
//            ],
//            array_shift($grants)
//        );
    }
}
