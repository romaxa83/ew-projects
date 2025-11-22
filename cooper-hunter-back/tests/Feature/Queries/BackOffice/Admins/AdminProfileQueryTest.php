<?php

namespace Tests\Feature\Queries\BackOffice\Admins;

use App\GraphQL\Queries\BackOffice\Admins\AdminProfileQuery;
use App\Models\Admins\Admin;
use App\Models\Permissions\Permission;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;
use Tests\Traits\Permissions\RoleHelperTrait;

class AdminProfileQueryTest extends TestCase
{
    use DatabaseTransactions;
    use RoleHelperTrait;

    public const QUERY = AdminProfileQuery::NAME;

    public function test_it_has_error_for_not_auth_users(): void
    {
        $result = $this->query();

        $this->assertGraphQlUnauthorized($result);
    }

    protected function query(): TestResponse
    {
        $query = sprintf(
            'query {
                %s {
                    id
                    email
                    name
                    language {
                        name
                        slug
                    }
                    permissions {
                        id
                        name
                    }
                    roles {
                        name
                        translation {
                            title
                        }
                    }
                }
            }',
            self::QUERY
        );

        return $this->postGraphQLBackOffice(compact('query'));
    }

    public function test_it_has_error_for_authorized_user(): void
    {
        $this->loginAsUser();
        $result = $this->query();

        $this->assertGraphQlUnauthorized($result);
    }

    public function test_admin_can_get_his_profile_with_list_of_permissions(): void
    {
        $permissions = Permission::factory()
            ->count(5)
            ->admin()
            ->create();
        $role = $this->generateRole(
            'test-role',
            $permissions->pluck('name')->all(),
            Admin::GUARD
        );
        $admin = $this->loginAsAdmin()->assignRole($role);

        $result = $this->query()
            ->assertOk();

        $profile = $result->json('data.'.self::QUERY);
        $permissionsArray = $permissions->map(
            static fn(Permission $permission) => $permission->only(['id', 'name'])
        )->all();

        $adminArray = [
            'id' => $admin->id,
            'email' => (string)$admin->email,
            'name' => $admin->name,
            'language' => [
                'name' => $admin->language->name,
                'slug' => $admin->language->slug
            ],
            'permissions' => $permissionsArray,
            'roles' => [
                [
                    'name' => $role->name,
                    'translation' => [
                        'title' => $role->translation->title
                    ]
                ]
            ]
        ];

        self::assertEquals($adminArray, $profile);
    }
}
