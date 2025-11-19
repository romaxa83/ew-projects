<?php

namespace Wezom\Users\Tests\Feature\Mutations;

use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Wezom\Core\Models\Auth\PersonalAccessToken;
use Wezom\Core\Models\Auth\PersonalRefreshToken;
use Wezom\Core\Models\Auth\PersonalSession;
use Wezom\Core\Testing\Projections\AuthProjection;
use Wezom\Core\Testing\TestCase;
use Wezom\Users\Database\Factories\UserFactory;
use Wezom\Users\GraphQL\Mutations\Site\SiteUserLogin;

class SiteUserLogoutTest extends TestCase
{
    #[Test]
    public function logoutSuccess(): void
    {
        $email = 'user@example.com';
        $password = 'password';

        UserFactory::new()->create(['email' => $email]);

        $this->assertDatabaseCount(PersonalSession::class, 0);
        $this->assertDatabaseCount(PersonalAccessToken::class, 0);
        $this->assertDatabaseCount(PersonalRefreshToken::class, 0);

        $accessToken = $this->executeLoginQueryAndGetToken($email, $password);

        $this->assertDatabaseCount(PersonalSession::class, 1);
        $this->assertDatabaseCount(PersonalAccessToken::class, 1);
        $this->assertDatabaseCount(PersonalRefreshToken::class, 1);

        $this->executeLogoutQuery($accessToken)
            ->assertNoErrors()
            ->assertJson(['data' => [$this->operationName() => true]]);

        $this->assertDatabaseCount(PersonalSession::class, 0);
        $this->assertDatabaseCount(PersonalAccessToken::class, 0);
        $this->assertDatabaseCount(PersonalRefreshToken::class, 0);
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
            ->json('data.' . SiteUserLogin::NAME . '.accessToken');
    }

    private function executeLogoutQuery(string $accessToken): TestResponse
    {
        return $this->mutation()
            ->header('Authorization', 'Bearer ' . $accessToken)
            ->executeAndReturnResponse();
    }
}
