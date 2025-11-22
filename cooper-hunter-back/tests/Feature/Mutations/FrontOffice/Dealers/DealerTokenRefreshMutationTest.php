<?php

namespace Tests\Feature\Mutations\FrontOffice\Dealers;

use App\GraphQL\Mutations\FrontOffice\Dealers\DealerTokenRefreshMutation;
use App\GraphQL\Mutations\FrontOffice\Members\MemberLoginMutation;
use App\Models\Dealers\Dealer;
use App\ValueObjects\Email;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Dealers\DealerBuilder;
use Tests\TestCase;

class DealerTokenRefreshMutationTest extends TestCase
{
    use DatabaseTransactions;

    protected DealerBuilder $dealerBuilder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dealerBuilder = resolve(DealerBuilder::class);
        $this->passportInit();
    }

    public function test_technician_can_exchange_refresh_token_to_auth_token_object(): void
    {
        $email = new Email('user@example.com');
        $password = 'password';

        $this->dealerBuilder->setData([
            'email' => $email,
        ])->setPassword($password)->create();

        $this->assertDatabaseHas(Dealer::TABLE, compact('email'));

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
            DealerTokenRefreshMutation::NAME,
            $refreshToken
        );

        $result = $this->postGraphQL(['query' => $refreshQuery])
            ->assertOk();

        [DealerTokenRefreshMutation::NAME => $data] = $result->json('data');

        self::assertArrayHasKey('refresh_token', $data);
        self::assertArrayHasKey('access_expires_in', $data);
        self::assertArrayHasKey('refresh_expires_in', $data);
        self::assertArrayHasKey('token_type', $data);
        self::assertArrayHasKey('access_token', $data);
        self::assertArrayHasKey('member_guard', $data);
    }
}

