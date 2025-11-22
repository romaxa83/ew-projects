<?php

namespace Tests\Feature\Saas;

use App\Notifications\Saas\Admins\AdminMailResetPasswordToken;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Notification;
use Tests\Helpers\Traits\AdminFactory;
use Tests\TestCase;

class AdminForgotPasswordTest extends TestCase
{
    use AdminFactory;
    use DatabaseTransactions;

    /**
     * @throws Exception
     */
    public function test_not_aut_admin_can_send_forgot_password_email(): void
    {
        Notification::fake();

        $admin = $this->createAdmin();

        $this->postJson(route('v1.saas.password.forgot'), ['email' => $admin->email])
            ->assertOk();

        Notification::hasSent($admin, AdminMailResetPasswordToken::class);
    }
}
