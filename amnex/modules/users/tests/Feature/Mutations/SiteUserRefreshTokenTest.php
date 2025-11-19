<?php

namespace Wezom\Users\Tests\Feature\Mutations;

use Illuminate\Testing\TestResponse;
use Wezom\Core\Models\Auth\PersonalAccessToken;
use Wezom\Core\Models\Auth\PersonalRefreshToken;
use Wezom\Core\Models\Auth\PersonalSession;
use Wezom\Core\Testing\Projections\AuthProjection;
use Wezom\Core\Testing\TestCase;
use Wezom\Users\Database\Factories\UserFactory;
use Wezom\Users\GraphQL\Mutations\Site\SiteUserLogin;
use Wezom\Users\Models\User;

class SiteUserRefreshTokenTest extends TestCase
{
    public function testUserCanExchangeRefreshToken(): void
    {
        $email = 'user@example.com';
        $password = 'password';

        UserFactory::new()->create(['email' => $email]);

        $this->assertDatabaseHas(User::TABLE, ['email' => $email]);

        $this->assertDatabaseCount(PersonalSession::class, 0);
        $this->assertDatabaseCount(PersonalAccessToken::class, 0);
        $this->assertDatabaseCount(PersonalRefreshToken::class, 0);

        $refreshToken = $this->executeLoginQueryAndGetToken($email, $password);

        $this->assertDatabaseCount(PersonalSession::class, 1);
        $this->assertDatabaseCount(PersonalAccessToken::class, 1);
        $this->assertDatabaseCount(PersonalRefreshToken::class, 1);

        $this->executeRefreshQuery($refreshToken)
            ->assertNoErrors();

        $this->assertDatabaseCount(PersonalSession::class, 1);
        $this->assertDatabaseCount(PersonalAccessToken::class, 1);
        $this->assertDatabaseCount(PersonalRefreshToken::class, 1);
    }

    private function executeLoginQueryAndGetToken(string $email, string $password): string
    {
        return $this->mutation(SiteUserLogin::NAME)
            ->args(
                [
                    'email' => $email,
                    'password' => $password,
                ]
            )
            ->select(AuthProjection::root())
            ->executeAndReturnResponse()
            ->assertNoErrors()
            ->json('data.' . SiteUserLogin::NAME . '.refreshToken');
    }

    private function executeRefreshQuery(string $refreshToken): TestResponse
    {
        return $this->mutation()
            ->args(compact('refreshToken'))
            ->select(AuthProjection::root())
            ->executeAndReturnResponse();
    }
}
