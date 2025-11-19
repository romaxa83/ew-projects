<?php

namespace Wezom\Users\Tests\Feature\Mutations;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Testing\TestResponse;
use Wezom\Core\Models\Auth\PersonalAccessToken;
use Wezom\Core\Models\Auth\PersonalRefreshToken;
use Wezom\Core\Models\Auth\PersonalSession;
use Wezom\Core\Testing\Projections\AuthProjection;
use Wezom\Core\Testing\TestCase;
use Wezom\Users\Database\Factories\UserFactory;
use Wezom\Users\Events\Auth\UserLoggedInEvent;
use Wezom\Users\Events\Auth\UserRegisteredEvent;
use Wezom\Users\Models\User;
use Wezom\Users\Notifications\Site\UserEmailVerificationNotification;

class SiteUserRegistrationTest extends TestCase
{
    public function testNewUserSuccessfullyRegistered(): void
    {
        Event::fake();
        Notification::fake();

        $email = $this->faker->email;

        $this->assertDatabaseMissing(User::TABLE, ['email' => $email]);

        $args = [
            'user' => [
                'firstName' => 'John',
                'lastName' => 'Doe',
                'email' => $email,
                'password' => 'somepassword1',
                'passwordConfirmation' => 'somepassword1',
            ],
        ];

        $this->assertDatabaseCount(PersonalSession::class, 0);
        $this->assertDatabaseCount(PersonalAccessToken::class, 0);
        $this->assertDatabaseCount(PersonalRefreshToken::class, 0);

        $this->executeQuery($args)
            ->assertNoErrors();

        $this->assertDatabaseCount(PersonalSession::class, 1);
        $this->assertDatabaseCount(PersonalAccessToken::class, 1);
        $this->assertDatabaseCount(PersonalRefreshToken::class, 1);

        $user = User::query()->where('email', $email)->first();

        $this->assertNull($user->email_verified_at);
        $this->assertNotEmpty($user->email_verification_code);

        Event::assertDispatched(UserRegisteredEvent::class);
        Event::assertDispatched(UserLoggedInEvent::class);

        Notification::assertSentTo($user, UserEmailVerificationNotification::class);
    }

    public function testValidationErrorOnNotValidEmail(): void
    {
        $args = [
            'user' => [
                'firstName' => 'John',
                'lastName' => 'Doe',
                'email' => 'not valid email',
                'password' => 'somepassword1',
                'passwordConfirmation' => 'somepassword1',
            ],
        ];

        $this->executeQuery($args)
            ->assertHasValidationMessage(
                'user.email',
                __('users::validation.site.custom.email.invalid'),
            );
    }

    public function testValidationErrorOnEmailDuplicate(): void
    {
        $user = UserFactory::new()->create();

        $args = [
            'user' => [
                'firstName' => 'John',
                'lastName' => 'Doe',
                'email' => $user->email,
                'password' => 'somepassword1',
                'passwordConfirmation' => 'somepassword1',
            ],
        ];

        $this->executeQuery($args)
            ->assertHasValidationMessage(
                'user.email',
                __('users::validation.site.custom.email.already_registered'),
            );
    }

    private function executeQuery(array $args = []): TestResponse
    {
        return $this->mutation()
            ->args($args)
            ->select(AuthProjection::root())
            ->executeAndReturnResponse();
    }
}
