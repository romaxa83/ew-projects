<?php

namespace Tests\Unit\Models\Permissions;

use App\Models\Permissions\Role;
use App\Models\Users\User;
use App\Permissions\Departments\CreatePermission;
use Tests\TestCase;

class SyncRolePermissionsTest extends TestCase
{
    public function test_it_sync_permission_into_role(): void
    {
        $role = Role::factory()->create();
        $createdPermission = $role->permissions()->firstOrCreate(
            ['name' => CreatePermission::KEY, 'guard_name' => User::GUARD]
        );
        $role->refresh();
        $permissionList = $role->permissionList;
        $permission = array_shift($permissionList);

        self::assertEquals(CreatePermission::KEY, $permission);
        self::assertEquals($createdPermission->name, $permission);
    }
}
