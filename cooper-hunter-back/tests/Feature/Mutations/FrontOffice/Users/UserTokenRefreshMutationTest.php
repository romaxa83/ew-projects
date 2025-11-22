<?php

namespace Tests\Feature\Mutations\FrontOffice\Users;

use App\GraphQL\Mutations\FrontOffice\Members\MemberLoginMutation;
use App\GraphQL\Mutations\FrontOffice\Users\UserTokenRefreshMutation;
use App\Models\Users\User;
use App\ValueObjects\Email;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class UserTokenRefreshMutationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_user_can_exchange_refresh_token_to_auth_token_object(): void
    {
        $email = new Email('user@example.com');
        $password = 'password';

        User::factory()->create(['email' => $email]);

        $this->assertDatabaseHas(User::TABLE, ['email' => $email]);

        $query = sprintf(
            'mutation { %s (username: "%s", password: "%s") {refresh_token access_expires_in refresh_expires_in token_type access_token } }',
            MemberLoginMutation::NAME,
            $email,
            $password
        );

        $result = $this->postGraphQL(compact('query'))
            ->assertOk();

        [MemberLoginMutation::NAME => $data] = $result->json('data');

        $refreshToken = $data['refresh_token'];

        $refreshQuery = sprintf(
            'mutation { %s (refresh_token: "%s") {refresh_token access_expires_in refresh_expires_in token_type access_token } }',
            UserTokenRefreshMutation::NAME,
            $refreshToken
        );

        $result = $this->postGraphQL(['query' => $refreshQuery])
            ->assertOk();

        [UserTokenRefreshMutation::NAME => $data] = $result->json('data');

        self::assertArrayHasKey('refresh_token', $data);
        self::assertArrayHasKey('access_expires_in', $data);
        self::assertArrayHasKey('refresh_expires_in', $data);
        self::assertArrayHasKey('token_type', $data);
        self::assertArrayHasKey('access_token', $data);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->passportInit();
    }
}
