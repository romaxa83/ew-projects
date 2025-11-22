<?php

namespace Tests\Feature\Queries\BackOffice\Permissions;

use App\GraphQL\Queries\BackOffice\Permissions\UserRolesQueryForAdmin;
use App\Models\Admins\Admin;
use App\Models\Localization\Language;
use App\Models\Permissions\Role;
use App\Models\Users\User;
use App\Permissions\Roles\RoleListPermission;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminFilterable;
use Tests\Traits\AdminSortable;
use Tests\Traits\Permissions\RoleHelperTrait;

class EmployeeRolesQueryForAdminTest extends TestCase
{
    use DatabaseTransactions;
    use RoleHelperTrait;
    use AdminSortable;
    use AdminFilterable;

    public const QUERY = UserRolesQueryForAdmin::NAME;

    private null|Collection $employeeRoles;

    public function test_cant_get_list_employee_roles_for_user(): void
    {
        $this->loginAsUser();

        $this->test_cant_get_list_employee_roles_for_guest();
    }

    public function test_cant_get_list_employee_roles_for_guest(): void
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

        $result = $this->postGraphQLBackOffice(compact('query'));

        $this->assertGraphQlUnauthorized($result);
    }

    public function test_cant_get_list_employee_roles_for_admin_without_permission(): void
    {
        $this->loginAsAdmin();
        $this->test_cant_get_list_employee_roles_for_guest();
    }

    public function test_can_get_list_of_employee_roles_for_permitted_admin(): void
    {
        Language::factory()
            ->state(
                [
                    'slug' => 'ru'
                ]
            )
            ->create();

        $this->loginAsAdmin()->assignRole(
            $this->generateRole('Role1', [RoleListPermission::KEY], Admin::GUARD)
        );

        $quantity = 2;
        $this->createEmployeeRoles($quantity);

        $query = sprintf(
            'query { %s (per_page: %s) {
                        data {
                            id
                            name
                            translation { id title language }
                            translations { id title language }
                            permissions { id name }
                            created_at
                            updated_at
                        }
                    } }',
            self::QUERY,
            50
        );

        $result = $this->postGraphQLBackOffice(compact('query'))
            ->assertOk();

        $roles = $result->json('data.'.self::QUERY.'.data');

        self::assertCount($quantity + 1, $roles);
    }

    private function createEmployeeRoles(int $quantity = 20): void
    {
        $this->employeeRoles = Role::factory()
            ->times($quantity)
            ->create(['guard_name' => User::GUARD]);
    }

    public function test_permitted_admin_can_sort_employee_roles_by_fields_and_paginate_them(): void
    {
        $this->loginAsEmployeeRoleManager();
        $this->createEmployeeRoles();

        $callback = function ($query) {
            $query->where('guard_name', User::GUARD);
        };

        $this->testSimpleSortableFields(['id', 'created_at', 'updated_at'], Role::class, $callback);
        $this->testTranslateSortableFields(['title'], Role::class, $callback);
    }

    protected function loginAsEmployeeRoleManager(): Admin
    {
        return $this->loginAsAdmin()->assignRole(
            $this->generateRole('Role1', [RoleListPermission::KEY], Admin::GUARD)
        );
    }

    public function test_permitted_admin_can_filter_employee_roles_by_fields_and_paginate_them(): void
    {
        $this->loginAsEmployeeRoleManager();
        $this->createEmployeeRoles();

        $id = Role::query()->firstOrFail()->id;
        $name = 'dire';
        $fieldsToFilter = [
            [
                'field' => 'id',
                'test' => $id,
                'callback' => function ($query) use ($id) {
                    $query->where('id', $id);
                },
            ],
            [
                'field' => 'title',
                'test' => $name,
                'callback' => function ($query) use ($name) {
                    $name = strtolower($name);
                    $query->wherehas(
                        'translation',
                        function ($query) use ($name) {
                            $query->where(
                                function ($query) use ($name) {
                                    $query->orWhereRaw('LOWER(name) LIKE ?', ["%$name%"]);
                                }
                            );
                        }
                    );
                }
            ],
        ];

        $additionalFilter = function ($query) {
            $query->where('guard_name', User::GUARD);
        };

        $this->testFilterableFields($fieldsToFilter, Role::class, $additionalFilter);
    }
}
