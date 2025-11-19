<?php

namespace Wezom\Users\Tests\Feature\Mutations;

use Illuminate\Support\Facades\Notification;
use Illuminate\Testing\TestResponse;
use Wezom\Core\Testing\TestCase;
use Wezom\Users\Database\Factories\UserFactory;
use Wezom\Users\Notifications\Site\UserEmailVerificationNotification;
use Wezom\Users\Traits\UserTestTrait;

class SiteUserResendEmailVerificationTest extends TestCase
{
    use UserTestTrait;

    public function testGuestCantResendNotification(): void
    {
        $response = $this->executeQuery();

        $this->assertGraphQlUnauthorized($response);
    }

    public function testNotificationSuccessfullySent(): void
    {
        Notification::fake();

        $user = UserFactory::new()->unverified()->create();

        $this->loginAsUser($user);

        $this->executeQuery()
            ->assertNoErrors()
            ->assertJsonPath('data.' . $this->operationName(), true);

        Notification::assertSentTo($user, UserEmailVerificationNotification::class);
    }

    public function testItNotResendNotificationForAlreadyVerifiedEmail(): void
    {
        $user = UserFactory::new()->verified()->create();

        $this->loginAsUser($user);

        $response = $this->executeQuery();

        $this->assertGraphQlInternal($response, __('users::exceptions.email_already_verified'));
    }

    private function executeQuery(): TestResponse
    {
        return $this->mutation()
            ->executeAndReturnResponse();
    }
}
