<?php

namespace Tests\Feature\Mutations\BackOffice\Admins;

use App\GraphQL\Mutations\BackOffice\Admins\AdminForgotPasswordMutation;
use App\Models\Admins\Admin;
use App\Notifications\Admins\AdminForgotPasswordNotification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use Tests\Traits\Notifications\FakeNotifications;

class AdminForgotPasswordMutationTest extends TestCase
{
    use DatabaseTransactions;
    use FakeNotifications;

    public const MUTATION = AdminForgotPasswordMutation::NAME;

    public function test_admin_forgot_password(): void
    {
        Notification::fake();

        $admin = Admin::factory()->create();

        $query = sprintf(
            'mutation { %s (email: "%s", link: "%s") }',
            self::MUTATION,
            $admin->getEmailString(),
            'http://localhost'
        );

        $this->postGraphQLBackOffice(compact('query'))
            ->assertOk();

        $this->assertNotificationSentTo($admin->getEmailString(), AdminForgotPasswordNotification::class);
    }
}
