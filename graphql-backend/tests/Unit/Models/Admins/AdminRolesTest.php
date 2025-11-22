<?php

namespace Tests\Unit\Models\Admins;

use App\Models\Admins\Admin;
use App\Models\Permissions\Role;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdminRolesTest extends TestCase
{
    use DatabaseTransactions;

    public function test_user_can_save_role()
    {
        $role = Role::factory()->create(['guard_name' => Admin::GUARD]);

        $admin = Admin::factory()->create();

        $admin->assignRole($role);

        $fetchedRole = $admin->role;

        self::assertEquals($role->name, $fetchedRole->name);
    }
}
