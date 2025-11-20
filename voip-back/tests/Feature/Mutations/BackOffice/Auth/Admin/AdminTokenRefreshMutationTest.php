<?php

namespace Tests\Feature\Mutations\BackOffice\Auth\Admin;

use App\GraphQL\Mutations\BackOffice\Auth\Admin\AdminTokenRefreshMutation;
use App\GraphQL\Mutations\BackOffice\Auth\LoginMutation;
use App\Models\Admins\Admin;
use App\ValueObjects\Email;
use Tests\TestCase;

class AdminTokenRefreshMutationTest extends TestCase
{
    public const MUTATION = AdminTokenRefreshMutation::NAME;

    protected function setUp(): void
    {
        parent::setUp();

        $this->passportInit();
    }

    /** @test */
    public function admin_can_exchange_refresh_token_to_auth_token_object(): void
    {
        $email = new Email('admin@example.com');
        $password = 'Password123';

        Admin::factory()->create(['email' => $email]);

        $this->assertDatabaseHas(Admin::TABLE, ['email' => $email]);

        $query = sprintf(
            'mutation { %s(username: "%s", password: "%s") {refresh_token access_expires_in token_type access_token } }',
            LoginMutation::NAME,
            $email,
            $password
        );

        $result = $this->postGraphQLBackOffice(['query' => $query])
            ->assertOk();

        [LoginMutation::NAME => $data] = $result->json('data');

        $refreshToken = $data['refresh_token'];

        $refreshQuery = sprintf(
            'mutation { %s (refresh_token: "%s") {refresh_token access_expires_in token_type access_token guard } }',
            self::MUTATION,
            $refreshToken
        );

        $result = $this->postGraphQLBackOffice(['query' => $refreshQuery])
            ->assertOk();

        [self::MUTATION => $data] = $result->json('data');

        self::assertArrayHasKey('refresh_token', $data);
        self::assertArrayHasKey('access_expires_in', $data);
        self::assertArrayHasKey('token_type', $data);
        self::assertArrayHasKey('access_token', $data);
        self::assertEquals($data['guard'], Admin::GUARD);
    }
}
