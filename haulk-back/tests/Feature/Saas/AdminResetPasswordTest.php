<?php

namespace Tests\Feature\Saas;

use App\Models\PasswordReset;
use Hash;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Helpers\Traits\AdminFactory;
use Tests\Helpers\Traits\PasswordResetFactoryTrait;
use Tests\TestCase;

class AdminResetPasswordTest extends TestCase
{
    use AdminFactory;
    use DatabaseTransactions;
    use PasswordResetFactoryTrait;

    public function test_admin_can_reset_password(): void
    {
        $admin = $this->createAdmin();

        $this->createToken(['email' => $admin->email]);

        $token = 'token';
        $this->assertDatabaseHas(
            PasswordReset::TABLE,
            [
                'email' => $admin->email,
            ]
        );

        $newPassword = 'newPassword1';
        $attrs = [
            'token' => $token,
            'email' => $admin->email,
            'password' => $newPassword,
            'password_confirmation' => $newPassword,
        ];

        $this->postJson(route('v1.saas.password.reset'), $attrs)
            ->assertOk();

        $admin->refresh();

        self::assertTrue(Hash::check($newPassword, $admin->password));
    }
}
