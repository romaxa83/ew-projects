<?php

namespace Tests\Unit\Services\AlertMessages;

use App\Models\Companies\Company;
use App\Models\Users\User;
use App\Services\AlertMessages\CustomHandlers\UserEmailVerifiedAlertMessageHandler;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class CustomMessageServiceTest extends TestCase
{
    use DatabaseTransactions;

    private \Core\Services\AlertMessages\CustomAlertMessageService $service;

    public function test_it_get_message_for_not_verify_email(): void
    {
        Config::set('notifications.custom.handlers', [UserEmailVerifiedAlertMessageHandler::class]);

        $user = User::factory()
            ->withCompany(Company::factory()->create(), true)
            ->create();

        $user->email_verified_at = null;

        $messages = $this->service->getForUser($user);

        self::assertCount(1, $messages);

        self::assertEquals(__('messages.user.email-is-not-verified'), $messages->shift()->message);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = $this->app->make(\Core\Services\AlertMessages\CustomAlertMessageService::class);
    }
}
