<?php

namespace Tests\Unit\Models\Users;

use App\Models\Permissions\Role;
use App\Models\Users\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class UserRolesTest extends TestCase
{
    use DatabaseTransactions;

    public function test_user_can_save_role()
    {
        $role = Role::factory()->create(['guard_name' => User::GUARD]);

        $user = User::factory()->create();

        $user->assignRole($role);

        $fetchedRole = $user->role;

        self::assertEquals($role->name, $fetchedRole->name);
    }
}
