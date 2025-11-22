<?php

namespace Tests\Feature\Mutations\BackOffice\Admins;

use App\GraphQL\Mutations\BackOffice\Admins\AdminLoginMutation;
use App\GraphQL\Mutations\BackOffice\Admins\AdminTokenRefreshMutation;
use App\Models\Admins\Admin;
use App\ValueObjects\Email;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdminTokenRefreshMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = AdminTokenRefreshMutation::NAME;

    public function test_admin_can_exchange_refresh_token_to_auth_token_object(): void
    {
        $email = new Email('admin@example.com');
        $password = 'password';

        Admin::factory()->create(['email' => $email]);

        $this->assertDatabaseHas(Admin::TABLE, ['email' => $email]);

        $query = sprintf(
            'mutation { %s
                (username: "%s", password: "%s")
                {refresh_token access_expires_in refresh_expires_in token_type access_token }
            }',
            AdminLoginMutation::NAME,
            $email,
            $password
        );

        $result = $this->postGraphQLBackOffice(['query' => $query])
            ->assertOk();

        [AdminLoginMutation::NAME => $data] = $result->json('data');

        $refreshToken = $data['refresh_token'];

        $refreshQuery = sprintf(
            'mutation { %s
                (refresh_token: "%s")
                {refresh_token access_expires_in refresh_expires_in token_type access_token }
            }',
            self::MUTATION,
            $refreshToken
        );

        $result = $this->postGraphQLBackOffice(['query' => $refreshQuery])
            ->assertOk();

        [self::MUTATION => $data] = $result->json('data');

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
