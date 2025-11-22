<?php

namespace Tests\Feature\Mutations\BackOffice\Admins;

use App\GraphQL\Mutations\BackOffice\Admins\AdminResetPasswordMutation;
use App\Models\Admins\Admin;
use App\Notifications\Admins\AdminResetPasswordNotification;
use App\Services\Admins\AdminVerificationService;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use Tests\Traits\Notifications\FakeNotifications;

class AdminResetPasswordMutationTest extends TestCase
{
    use DatabaseTransactions;
    use FakeNotifications;

    public const MUTATION = AdminResetPasswordMutation::NAME;

    private AdminVerificationService $service;

    /**
     * @throws Exception
     */
    public function test_it_reset_password(): void
    {
        Notification::fake();

        $admin = Admin::factory()->create();

        $query = sprintf(
            'mutation { %s (token: "%s") }',
            self::MUTATION,
            $this->service->encryptEmailToken($admin),
        );

        $this->postGraphQLBackOffice(compact('query'))
            ->assertOk();

        $this->assertNotificationSentTo($admin->getEmailString(), AdminResetPasswordNotification::class);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = resolve(AdminVerificationService::class);
    }
}
