<?php

namespace Tests\Feature\Saas\Admins;

use App\Models\Admins\Admin;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdminProfileChangePasswordTest extends TestCase
{
    use DatabaseTransactions;

    public function test_not_auth_admin_cant_change_password(): void
    {
        $attrs = [
            'current_password' => 'password',
            'password' => 'new_password',
            'password_confirmation' => 'new_password',
        ];

        $this->putJson(route('v1.saas.profile.change-password'), $attrs)
            ->assertUnauthorized();
    }

    public function test_auth_admin_can_change_password(): void
    {
        $admin = factory(Admin::class)->create(['password' => 'password']);

        $this->loginAsSaasAdmin($admin);

        $newPassword = 'password123';

        $attrs = [
            'current_password' => 'password',
            'password' => $newPassword,
            'password_confirmation' => $newPassword,
        ];

        $this->putJson(route('v1.saas.profile.change-password'), $attrs)
            ->assertOk();

        $admin->fresh();

        self::assertTrue($admin->passwordCompare($newPassword));
    }
}
