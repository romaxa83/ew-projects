<?php

namespace Tests\Feature\Mutations\FrontOffice\Technicians;

use App\GraphQL\Mutations\FrontOffice\Members\MemberLoginMutation;
use App\GraphQL\Mutations\FrontOffice\Technicians\TechnicianTokenRefreshMutation;
use App\Models\Technicians\Technician;
use App\ValueObjects\Email;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TechnicianTokenRefreshMutationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_technician_can_exchange_refresh_token_to_auth_token_object(): void
    {
        $email = new Email('user@example.com');
        $password = 'password';

        Technician::factory()->create(compact('email'));

        $this->assertDatabaseHas(Technician::TABLE, compact('email'));

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
            'mutation { %s (refresh_token: "%s") {refresh_token access_expires_in refresh_expires_in token_type access_token member_guard } }',
            TechnicianTokenRefreshMutation::NAME,
            $refreshToken
        );

        $result = $this->postGraphQL(['query' => $refreshQuery])
            ->assertOk();

        [TechnicianTokenRefreshMutation::NAME => $data] = $result->json('data');

        self::assertArrayHasKey('refresh_token', $data);
        self::assertArrayHasKey('access_expires_in', $data);
        self::assertArrayHasKey('refresh_expires_in', $data);
        self::assertArrayHasKey('token_type', $data);
        self::assertArrayHasKey('access_token', $data);
        self::assertArrayHasKey('member_guard', $data);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->passportInit();
    }
}
