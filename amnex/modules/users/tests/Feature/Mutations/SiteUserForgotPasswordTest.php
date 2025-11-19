<?php

namespace Wezom\Users\Tests\Feature\Mutations;

use Illuminate\Support\Facades\Notification;
use Illuminate\Testing\TestResponse;
use Wezom\Core\Testing\TestCase;
use Wezom\Users\Database\Factories\UserFactory;
use Wezom\Users\Notifications\Site\UserForgotPasswordNotification;

class SiteUserForgotPasswordTest extends TestCase
{
    public function testNotificationWithLinkSent(): void
    {
        Notification::fake();

        $user = UserFactory::new()->create();

        $this->executeQuery(['email' => $user->getEmail()])
            ->assertNoErrors()
            ->assertJson(['data' => [$this->operationName() => true]]);

        $user = $user->refresh();

        static::assertNotEmpty($user->getPasswordResetCode());

        Notification::assertSentTo($user, UserForgotPasswordNotification::class);
    }

    private function executeQuery(array $args): TestResponse
    {
        return $this->mutation()
            ->args($args)
            ->executeAndReturnResponse();
    }
}
