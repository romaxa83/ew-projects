<?php

namespace Tests\Unit\Models\Users;

use App\Models\Users\Admin;
use App\Models\Users\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserRoleTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function test_it_set_role_for_new_user()
    {
        $user = User::factory()->create();

        $this->assertDatabaseMissing(
            config('permission.table_names.model_has_roles'),
            [
                'model_id' => $user->id,
                'model_type' => User::class,
            ]
        );

        $user->assignRole(User::ADMIN_ROLE);

        $this->assertDatabaseHas(
            config('permission.table_names.model_has_roles'),
            [
                'model_id' => $user->id,
                'model_type' => User::class,
            ]
        );
    }
}
