<?php

namespace Tests\Feature\Queries\FrontOffice\Permissions;

use App\GraphQL\Queries\FrontOffice\Permissions\UserRolesQuery;
use App\Models\Localization\Language;
use App\Models\Permissions\Role;
use App\Models\Users\User;
use App\Permissions\Roles\RoleListPermission;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Filterable;
use Tests\Traits\Permissions\RoleHelperTrait;
use Tests\Traits\Sortable;

class UserRolesQueryTest extends TestCase
{
    use DatabaseTransactions;
    use RoleHelperTrait;
    use Sortable;
    use Filterable;

    public const QUERY = UserRolesQuery::NAME;

    private null|Collection $employeeRoles;

    public function test_cant_get_list_employee_roles_for_admin(): void
    {
        $this->loginAsAdmin();

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

        $result = $this->postGraphQL(compact('query'));

        $this->assertGraphQlUnauthorized($result);
    }

    public function test_cant_get_list_employee_roles_for_user_without_permission(): void
    {
        $this->loginAsUser();

        $this->test_cant_get_list_employee_roles_for_guest();
    }

    public function test_can_get_list_of_employee_roles_for_permitted_user(): void
    {
        Language::factory()
            ->state(
                [
                    'slug' => 'ru'
                ]
            )
            ->create();

        $this->loginAsUser()->assignRole(
            $this->generateRole('Role1', [RoleListPermission::KEY])
        );

        $existingRoles = 2;
        $quantity = 2;
        $this->createEmployeeRoles($quantity);

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

        $result = $this->postGraphQL(compact('query'));

        $roles = $result->json('data.'.self::QUERY.'.data');

        self::assertCount(
            $quantity + $existingRoles,
            $roles
        );
    }

    private function createEmployeeRoles(int $quantity = 2): void
    {
        $this->employeeRoles = Role::factory()->times($quantity)->create(['guard_name' => User::GUARD]);
    }

    public function test_permitted_user_can_sort_employee_roles_by_fields_and_paginate_them(): void
    {
        $role = $this->generateRole('Role1', [RoleListPermission::KEY]);
        $user = $this->loginAsUser();
        $user->assignRole($role);
        $this->createEmployeeRoles();

        $callback = function ($query) {
            $query->where('guard_name', User::GUARD);
        };

        $this->testSimpleSortableFields(['id', 'created_at', 'updated_at'], Role::class, $callback);
        $this->testTranslateSortableFields(['title'], Role::class, $callback);
    }

    public function test_permitted_user_can_filter_employee_roles_by_fields_and_paginate_them(): void
    {
        $role = $this->generateRole('Role1', [RoleListPermission::KEY]);
        $user = $this->loginAsUser();
        $user->assignRole($role);
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
                'callback' => function (Builder $query) use ($name) {
                    $name = strtolower($name);
                    $query->wherehas(
                        'translation',
                        function (Builder $query) use ($name) {
                            $query->where(
                                function (Builder $query) use ($name) {
                                    $query->orWhereRaw('LOWER(title) LIKE ?', ["%$name%"]);
                                }
                            );
                        }
                    );
                }
            ],
        ];
        $additionalFilter = static fn(Builder $b) => $b->where('guard_name', User::GUARD);

        $this->testFilterableFields($fieldsToFilter, Role::class, $additionalFilter);
    }
}
