<?php

namespace Wezom\Admins\Tests\Feature\Queries\Back;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use JsonException;
use Wezom\Admins\GraphQL\Queries\Back\BackAdminRoles;
use Wezom\Admins\Models\Admin;
use Wezom\Admins\Testing\TestCase;
use Wezom\Admins\Traits\AdminFilterable;
use Wezom\Admins\Traits\AdminSortableTrait;
use Wezom\Core\Enums\OrderDirectionEnum;
use Wezom\Core\Enums\RoleEnum;
use Wezom\Core\Enums\RoleOrderColumnEnum;
use Wezom\Core\Models\Permission\Role;

class AdminRolesQueryTest extends TestCase
{
    use AdminFilterable;
    use AdminSortableTrait;
    use DatabaseTransactions;

    public function testCantGetListRolesForGuest(): void
    {
        $query = sprintf(
            'query { %s {
                        data {
                            id
                            name
                            permissions
                            createdAt
                            updatedAt
                        }
                } }',
            BackAdminRoles::getName()
        );

        $result = $this->postGraphQL(['query' => $query]);

        $this->assertGraphQlUnauthorized($result);
    }

    public function testCantGetListRolesForAdminWithoutPermission(): void
    {
        $this->loginAsAdmin();

        $query = sprintf(
            'query { %s {
                        data {
                            id
                            name
                            permissions
                            createdAt
                            updatedAt
                        }
                } }',
            BackAdminRoles::getName()
        );

        $result = $this->postGraphQL(['query' => $query]);

        $this->assertGraphQlForbidden($result);
    }

    public function testCanGetListOfRolesForPermittedAdmin(): void
    {
        $this->loginAsAdminWithPermissions(['roles.view']);

        $query = sprintf(
            'query { %s (first: %s) {
                        data{
                            id
                            name
                            permissions
                            createdAt
                            updatedAt
                        }
                    } }',
            BackAdminRoles::getName(),
            50
        );

        $result = $this->postGraphQL(['query' => $query])->assertNoErrors();

        $roles = $result->json('data.' . BackAdminRoles::getName() . '.data');

        self::assertCount(
            1,
            $roles
        );
    }

    private function createAdminRoles(): void
    {
        Role::factory()->times(20)->create(['guard_name' => Admin::GUARD]);
    }

    public function testAdminCanGetFindRoleByIdOrName(): void
    {
        $role = $this->generateRole(
            'Admin',
            [
                'roles.view',
            ],
            Admin::GUARD
        );

        Admin::factory()->create()->assignRole($role);
        Admin::factory()->create()->assignRole($role);
        $admin = Admin::factory()->create()->assignRole($role);
        $this->loginAsAdmin($admin);

        $query = sprintf(
            'query { %s (ids: %s) {
                        data{
                            id
                            name
                            permissions
                            createdAt
                            updatedAt
                        }
                    } }',
            BackAdminRoles::getName(),
            $admin->roles->first()->id
        );
        $result = $this->postGraphQL(['query' => $query]);
        $roles = $result->json('data.' . BackAdminRoles::getName() . '.data');
        self::assertCount(1, $roles);

        $role = array_shift($roles);
        self::assertEquals($role['name'], $admin->roles->first()->name);
    }

    public function testAdminCanSortAdminRolesByFieldsAndPaginateThem(): void
    {
        $admin = Admin::factory()->create();
        $role = $this->generateRole('Role1', ['roles.view'], Admin::GUARD);
        $admin->assignRole($role);
        $this->loginAsAdmin($admin);
        $this->createAdminRoles();

        $additionalFilter = function ($query) {
            $query->where('guard_name', Admin::GUARD)
                ->where('system_type', '<>', RoleEnum::SUPER_ADMIN);
        };

        $this->testSimpleSortableFields(['id'], Role::class, $additionalFilter);
    }

    public function testAdminCanFilterAdminRolesByFieldsAndPaginateThem(): void
    {
        $admin = Admin::factory()->create();
        $role = $this->generateRole('Role1', ['roles.view'], Admin::GUARD);
        $admin->assignRole($role);
        $this->loginAsAdmin($admin);
        $this->createAdminRoles();

        $id = Role::query()
            ->where('guard_name', Admin::GUARD)
            ->whereNull('system_type')
            ->first()->id;

        $name = 'dire';
        $fieldsToFilter = [
            [
                'field' => 'ids',
                'test' => $id,
                'callback' => function ($query) use ($id) {
                    $query->where('id', $id);
                },
            ],
            [
                'field' => 'name',
                'test' => $name,
                'callback' => function ($query) use ($name) {
                    $name = strtolower($name);
                    $query->orWhereRaw('LOWER(name) LIKE ?', ["%$name%"]);
                },
            ],
        ];
        $additionalFilter = function ($query) {
            $query->where('guard_name', Admin::GUARD)
                ->whereNull('system_type');
        };

        $this->testFilterableFields($fieldsToFilter, Role::class, $additionalFilter);
    }

    /**
     * @throws JsonException
     */
    public function testOrderByDefault(): void
    {
        $this->loginAsSuperAdmin();

        $model1 = Role::factory()->create();
        $model2 = Role::factory()->create();
        $model3 = Role::factory()->create();
        $model4 = Role::factory()->create();

        $model2->update(['name' => 'testing 22']);

        $ids = $this->queryPaginate(BackAdminRoles::getName())
            ->select(['id'])
            ->execute()
            ->pluck('id')
            ->filter(fn ($id) => in_array($id, [$model1->id, $model2->id, $model3->id, $model4->id]))
            ->values();

        $this->assertCount(4, $ids);

        $this->assertTrue($ids->get(0) == $model4->id);
        $this->assertTrue($ids->get(1) == $model3->id);
        $this->assertTrue($ids->get(2) == $model2->id);
        $this->assertTrue($ids->get(3) == $model1->id);
    }

    /**
     * @throws JsonException
     */
    public function testOrderByCreatedAt(): void
    {
        $this->loginAsSuperAdmin();

        $model1 = Role::factory()->create(['created_at' => now()->subHour()]);
        $model2 = Role::factory()->create(['created_at' => now()]);
        $model3 = Role::factory()->create(['created_at' => now()->subHours(3)]);
        $model4 = Role::factory()->create(['created_at' => now()->addHours(2)]);

        $model2->update(['name' => 'testing 22']);

        $ids = $this->queryPaginate(BackAdminRoles::getName())
            ->select(['id'])
            ->ordering(RoleOrderColumnEnum::CREATED_AT, OrderDirectionEnum::DESC)
            ->execute()
            ->pluck('id')
            ->filter(fn ($id) => in_array($id, [$model1->id, $model2->id, $model3->id, $model4->id]))
            ->values();

        $this->assertCount(4, $ids);

        $this->assertTrue($ids->get(0) == $model4->id);
        $this->assertTrue($ids->get(1) == $model2->id);
        $this->assertTrue($ids->get(2) == $model1->id);
        $this->assertTrue($ids->get(3) == $model3->id);
    }
}
