<?php

namespace Wezom\Users\Tests\Feature\Mutations;

use Illuminate\Support\Facades\Event;
use Illuminate\Testing\TestResponse;
use Wezom\Core\Models\Auth\PersonalAccessToken;
use Wezom\Core\Models\Auth\PersonalRefreshToken;
use Wezom\Core\Models\Auth\PersonalSession;
use Wezom\Core\Testing\Projections\AuthProjection;
use Wezom\Core\Testing\TestCase;
use Wezom\Users\Database\Factories\UserFactory;
use Wezom\Users\Events\Auth\UserLoggedInEvent;
use Wezom\Users\Models\User;

class SiteUserLoginTest extends TestCase
{
    public function testGuestCanLogin(): void
    {
        Event::fake();

        $email = 'user@example.com';
        $password = 'password';

        UserFactory::new()->create(['email' => $email]);

        $this->assertDatabaseHas(User::TABLE, ['email' => $email]);

        $this->assertDatabaseCount(PersonalSession::class, 0);
        $this->assertDatabaseCount(PersonalAccessToken::class, 0);
        $this->assertDatabaseCount(PersonalRefreshToken::class, 0);

        $response = $this->executeQuery([
            'email' => $email,
            'password' => $password,
        ])
            ->assertNoErrors();

        [$this->operationName() => $data] = $response->json('data');

        self::assertArrayHasKey('refreshToken', $data);
        self::assertArrayHasKey('accessExpiresIn', $data);
        self::assertArrayHasKey('refreshExpiresIn', $data);
        self::assertArrayHasKey('tokenType', $data);
        self::assertArrayHasKey('accessToken', $data);

        $this->assertDatabaseCount(PersonalSession::class, 1);
        $this->assertDatabaseCount(PersonalAccessToken::class, 1);
        $this->assertDatabaseCount(PersonalRefreshToken::class, 1);

        Event::assertDispatched(UserLoggedInEvent::class);
    }

    public function testNonExistingCredentials(): void
    {
        $response = $this->executeQuery([
            'email' => 'notexists_email@example.com',
            'password' => 'not_exists_password',
        ]);

        $this->assertResponseHasValidationMessage(
            $response,
            'password',
            [__('users::validation.site.custom.credentials.invalid')]
        );
    }

    public function testNotValidEmail(): void
    {
        $response = $this->executeQuery([
            'email' => 'not valid email',
            'password' => 'password',
        ])->dump();

        $this->assertResponseHasValidationMessage(
            $response,
            'email',
            [
                __('users::validation.site.custom.email.must_be_email'),
            ]
        );
    }

    public function testNotCorrectPassword(): void
    {
        $user = UserFactory::new()->create();

        $response = $this->executeQuery([
            'email' => $user->email,
            'password' => 'not_correct_password',
        ]);

        $this->assertResponseHasValidationMessage(
            $response,
            'password',
            [__('users::validation.site.custom.credentials.invalid')]
        );
    }

    private function executeQuery(array $args): TestResponse
    {
        return $this->mutation()
            ->args($args)
            ->select(AuthProjection::root())
            ->executeAndReturnResponse();
    }
}
