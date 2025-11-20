<?php

namespace Tests\Feature\Queries\BackOffice\Permissions;

use App\GraphQL\Queries\BackOffice\Permissions\AdminRolesQuery;
use App\Models\Admins\Admin;
use App\Models\Permissions\Role;
use App\Permissions\Admins\AdminListPermission;
use App\Permissions\Roles\RoleListPermission;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;
use Tests\Traits\AdminFilterable;
use Tests\Traits\AdminSortable;
use Tests\Traits\Permissions\RoleHelperHelperTrait;

class AdminRolesQueryTest extends TestCase
{
    use RoleHelperHelperTrait;
    use AdminSortable;
    use AdminFilterable;

    public const QUERY = AdminRolesQuery::NAME;

    private null|Collection $adminRoles;

    public function test_cant_get_list_roles_for_guest(): void
    {
        $query = sprintf(
            'query { %s {
                        data {
                            id
                            name
                            translation {id title language}
                            translations {id title language}
                            permissions {id name}
                            created_at
                            updated_at
                        }
                } }',
            self::QUERY
        );

        $result = $this->postGraphQLBackOffice(['query' => $query]);

        $this->assertGraphQlUnauthorized($result);
    }

    public function test_cant_get_list_roles_for_admin_without_permission(): void
    {
        $this->loginAsAdmin();
        $this->test_cant_get_list_roles_for_guest();
    }

    public function test_can_get_list_of_roles_for_permitted_admin(): void
    {
        $admin = Admin::factory()->create();
        $role = $this->generateRole('Role1', [RoleListPermission::KEY], Admin::GUARD);
        $admin->assignRole($role);
        $this->loginAsAdmin($admin);

        $this->createAdminRoles();

        $query = sprintf(
            'query { %s (per_page: %s) {
                        data{
                            id
                            name
                            translation {id title language}
                            translations {id title language}
                            permissions {id name}
                            created_at
                            updated_at
                        }
                    } }',
            self::QUERY,
            50
        );

        $result = $this->postGraphQLBackOffice(['query' => $query]);

        $roles = $result->json('data.' . self::QUERY . '.data');

        $countDefaultRoles = count(config('permission.roles'));

        self::assertCount(
            20 + 1,
            $roles
        );

        $query = sprintf(
            'query { %s (per_page: %s) {
                        data{
                            id
                            name
                            translation {id title language}
                            translations {id title language}
                            permissions {id name}
                            created_at
                            updated_at
                        }
                    } }',
            self::QUERY,
            10
        );
        $result = $this->postGraphQLBackOffice(['query' => $query]);

        $users = $result->json('data.' . self::QUERY . '.data');

        self::assertCount(10, $users);
    }

    private function createAdminRoles(): void
    {
        $this->adminRoles = Role::factory()->times(20)->create(['guard_name' => Admin::GUARD]);
    }


    public function test_admin_can_get_find_role_by_id_or_name(): void
    {
        $admin = Admin::factory()->create();
        $role1 = $this->generateRole('Role1', [RoleListPermission::KEY], Admin::GUARD);
        $role2 = $this->generateRole('Test-Role2', [AdminListPermission::KEY], Admin::GUARD);
        $admin->assignRole($role1);
        $this->loginAsAdmin($admin);

        $query = sprintf(
            'query { %s (id: %s) {
                        data{
                            id
                            name
                            translation {id title language}
                            translations {id title language}
                            permissions {id name}
                            created_at
                            updated_at
                        }
                    } }',
            self::QUERY,
            $role2->id
        );
        $result = $this->postGraphQLBackOffice(['query' => $query]);
        $roles = $result->json('data.' . self::QUERY . '.data');
        self::assertCount(1, $roles);

        $role = array_shift($roles);
        self::assertEquals($role['translation']['title'], $role2->translation->title);

        $query = sprintf(
            'query { %s (
                    title: "%s"
                ) {
                    data{
                            id
                            name
                            translation {id title language}
                            translations {id title language}
                            permissions {id name}
                            created_at
                            updated_at
                        }
                } }',
            self::QUERY,
            $role1->translation->title
        );

        $result = $this->postGraphQLBackOffice(['query' => $query]);

        $roles = $result->json('data.' . self::QUERY . '.data');
        self::assertCount(1, $roles);
    }

    public function test_admin_can_sort_admin_roles_by_fields_and_paginate_them(): void
    {
        $admin = Admin::factory()->create();
        $role = $this->generateRole('Role1', [RoleListPermission::KEY], Admin::GUARD);
        $admin->assignRole($role);
        $this->loginAsAdmin($admin);
        $this->createAdminRoles();

        $additionalFilter = function ($query) {
            $query->withoutSuperAdmin()->where('guard_name', Admin::GUARD);
        };

        $this->testSimpleSortableFields(['id', 'created_at', 'updated_at'], Role::class, $additionalFilter);
    }
}
