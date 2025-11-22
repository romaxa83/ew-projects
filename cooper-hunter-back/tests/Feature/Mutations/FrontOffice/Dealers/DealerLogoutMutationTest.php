<?php

namespace Tests\Feature\Mutations\FrontOffice\Dealers;

use App\GraphQL\Mutations\FrontOffice\Dealers\DealerLogoutMutation;
use App\GraphQL\Mutations\FrontOffice\Members\MemberLoginMutation;
use App\ValueObjects\Email;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Dealers\DealerBuilder;
use Tests\TestCase;

class DealerLogoutMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = DealerLogoutMutation::NAME;
    public const LOGIN_MUTATION = MemberLoginMutation::NAME;

    protected DealerBuilder $dealerBuilder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dealerBuilder = resolve(DealerBuilder::class);
        $this->passportInit();
    }

    public function test_it_logout_success(): void
    {
        $email = new Email('user@example.com');
        $password = 'password';

        $this->dealerBuilder->setData([
            'email' => $email,
        ])->setPassword($password)->create();

        $query = sprintf(
            'mutation { %s (username: "%s", password: "%s") {refresh_token access_expires_in refresh_expires_in token_type access_token } }',
            self::LOGIN_MUTATION,
            $email,
            $password
        );

        $result = $this->postGraphQL(compact('query'))
            ->assertOk();

        [self::LOGIN_MUTATION => $data] = $result->json('data');

        $query = sprintf(
            'mutation { %s }',
            self::MUTATION
        );

        $this->postGraphQL(compact('query'), ['Authorization' => 'Bearer ' . $data['access_token']])
            ->assertOk()
            ->assertJson(['data' => [self::MUTATION => true,]]);

        $this->postGraphQL(compact('query'), ['Authorization' => 'Bearer ' . $data['access_token']])
            ->assertOk()
            ->assertJson(['data' => [self::MUTATION => false,]]);
    }
}
