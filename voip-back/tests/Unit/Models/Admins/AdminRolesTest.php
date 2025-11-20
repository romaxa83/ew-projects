<?php

namespace Tests\Unit\Models\Admins;

use App\Models\Admins\Admin;
use App\Models\Permissions\Role;
use Tests\TestCase;

class AdminRolesTest extends TestCase
{
    public function test_user_can_save_role(): void
    {
        $role = Role::factory()->create(['guard_name' => Admin::GUARD]);

        $admin = Admin::factory()->create();

        $admin->assignRole($role);

        $fetchedRole = $admin->role;

        self::assertEquals($role->name, $fetchedRole->name);
    }
}
