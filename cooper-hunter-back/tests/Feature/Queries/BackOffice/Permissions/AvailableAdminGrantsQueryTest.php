<?php

namespace Tests\Feature\Queries\BackOffice\Permissions;

use App\GraphQL\Queries\BackOffice\Permissions\AvailableAdminGrantsQuery;
use App\Models\Admins\Admin;
use App\Permissions\Admins\AdminCreatePermission;
use App\Permissions\Admins\AdminDeletePermission;
use App\Permissions\Admins\AdminListPermission;
use App\Permissions\Admins\AdminUpdatePermission;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AvailableAdminGrantsQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const QUERY = AvailableAdminGrantsQuery::NAME;

    public function test_cant_get_available_admin_permissions_for_user(): void
    {
        $this->loginAsUser();

        $this->test_cant_get_available_admin_permissions_for_not_auth();
    }

    public function test_cant_get_available_admin_permissions_for_not_auth(): void
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
        $result = $this->postGraphQLBackOffice(compact('query'))
            ->assertOk();

        $this->assertGraphQlUnauthorized($result);
    }

    public function test_it_get_list_of_permissions_groups_for_admin(): void
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
        $result = $this->postGraphQLBackOffice(compact('query'))
            ->assertOk();

        $grants = $result->json('data.'.self::QUERY);

        $guard = Admin::GUARD;
        $count = count(config("grants.matrix.$guard.groups"));
        self::assertCount($count, $grants);
        self::assertEquals(
            [
                'key' => 'admin',
                'name' => 'Admins',
                'position' => 0,
                'permissions' => [
                    [
                        'key' => AdminListPermission::KEY,
                        'name' => 'List',
                        'position' => 1,
                    ],
                    [
                        'key' => AdminCreatePermission::KEY,
                        'name' => 'Create',
                        'position' => 2,
                    ],
                    [
                        'key' => AdminUpdatePermission::KEY,
                        'name' => 'Update',
                        'position' => 3,
                    ],
                    [
                        'key' => AdminDeletePermission::KEY,
                        'name' => 'Delete',
                        'position' => 4,
                    ],
                ],
            ],
            array_shift($grants)
        );
    }
}
