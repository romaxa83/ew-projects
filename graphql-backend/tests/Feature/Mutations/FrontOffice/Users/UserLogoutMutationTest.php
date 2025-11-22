<?php

namespace Tests\Feature\Mutations\FrontOffice\Users;

use App\GraphQL\Mutations\FrontOffice\Users\UserLoginMutation;
use App\GraphQL\Mutations\FrontOffice\Users\UserLogoutMutation;
use App\Models\Users\User;
use App\ValueObjects\Email;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class UserLogoutMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = UserLogoutMutation::NAME;

    public function test_it_logout_success(): void
    {
        $email = new Email('user@example.com');
        $password = 'password';

        User::factory()->new(['email' => $email])->create();

        $query = sprintf(
            'mutation { %s
                (username: "%s", password: "%s")
                {refresh_token access_expires_in refresh_expires_in token_type access_token }
            }',
            UserLoginMutation::NAME,
            $email,
            $password
        );

        $result = $this->postGraphQL(['query' => $query])
            ->assertOk();

        [UserLoginMutation::NAME => $data] = $result->json('data');

        $query = sprintf(
            'mutation { %s }',
            self::MUTATION
        );

        $this->postGraphQL(['query' => $query], ['Authorization' => 'Bearer ' . $data['access_token']])
            ->assertOk()
            ->assertJson(['data' => [self::MUTATION => true,]]);

        $this->postGraphQL(['query' => $query], ['Authorization' => 'Bearer ' . $data['access_token']])
            ->assertOk()
            ->assertJson(['data' => [self::MUTATION => false,]]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->passportInit();
    }
}
