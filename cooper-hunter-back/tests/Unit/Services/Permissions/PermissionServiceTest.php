<?php

namespace Tests\Unit\Services\Permissions;

use App\Models\Admins\Admin;
use App\Models\Permissions\Permission as PermissionModel;
use App\Permissions\Users\UserCreatePermission;
use App\Permissions\Users\UserDeletePermission;
use App\Permissions\Users\UserListPermission;
use App\Permissions\Users\UserUpdatePermission;
use Core\Permissions\Permission;
use Core\Permissions\PermissionGroup;
use Core\Services\Permissions\PermissionService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PermissionServiceTest extends TestCase
{
    use DatabaseTransactions;

    private PermissionService $service;

    public function test_it_get_groups_of_permissions(): void
    {
        $groups = $this->service->getGroupsFor(Admin::GUARD);
        $guard = Admin::GUARD;
        $count = count(config("grants.matrix.$guard.groups"));
        self::assertCount($count, $groups);

        $group = $groups->first();
        self::assertInstanceOf(PermissionGroup::class, $group);

        $permissions = $group->getPermissions();

        self::assertCount(4, $permissions);
        $permission = array_shift($permissions);

        self::assertInstanceOf(Permission::class, $permission);
    }

    public function test_it_create_new_permissions_via_upsert(): void
    {
        $guard = Admin::GUARD;

        $permissionList = [
            'list' => UserListPermission::KEY,
            'create' => UserCreatePermission::KEY,
            'update' => UserUpdatePermission::KEY,
            'delete' => UserDeletePermission::KEY,
        ];

        $permission1 = [
            'name' => $permissionList['list'],
            'guard_name' => $guard,
        ];
        $permission2 = [
            'name' => $permissionList['create'],
            'guard_name' => $guard,
        ];

        $permission3 = [
            'name' => $permissionList['update'],
            'guard_name' => $guard,
        ];

        $permission4 = [
            'name' => $permissionList['delete'],
            'guard_name' => $guard,
        ];

        $this->service->sync($permissionList, $guard);

        $this->assertDatabaseHas(PermissionModel::TABLE, $permission1);
        $this->assertDatabaseHas(PermissionModel::TABLE, $permission2);
        $this->assertDatabaseHas(PermissionModel::TABLE, $permission3);
        $this->assertDatabaseHas(PermissionModel::TABLE, $permission4);
    }

    public function test_it_get_super_admin_from_db(): void
    {
        $role = $this->service->firstOrCreateSuperAdminRole();

        self::assertEquals(config('permission.roles.super_admin'), $role->name);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = $this->app->make(PermissionService::class);
    }
}
