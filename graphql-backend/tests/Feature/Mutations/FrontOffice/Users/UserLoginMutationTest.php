<?php

namespace Tests\Feature\Mutations\FrontOffice\Users;

use App\GraphQL\Mutations\FrontOffice\Users\UserLoginMutation;
use App\Models\Users\User;
use App\ValueObjects\Email;
use Core\Enums\Messages\AuthorizationMessageEnum;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class UserLoginMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = UserLoginMutation::NAME;

    public function test_it_login_success(): void
    {
        $email = new Email('user@example.com');
        $password = 'password';

        User::factory()->create(['email' => $email]);

        $this->assertDatabaseHas(User::TABLE, ['email' => $email]);

        $query = sprintf(
            'mutation { %s
                (username: "%s", password: "%s")
                {refresh_token access_expires_in refresh_expires_in token_type access_token }
            }',
            self::MUTATION,
            $email,
            $password
        );

        $result = $this->postGraphQL(['query' => $query])
            ->assertOk();

        [self::MUTATION => $data] = $result->json('data');

        self::assertArrayHasKey('refresh_token', $data);
        self::assertArrayHasKey('access_expires_in', $data);
        self::assertArrayHasKey('refresh_expires_in', $data);
        self::assertArrayHasKey('token_type', $data);
        self::assertArrayHasKey('access_token', $data);
    }

    public function test_it_try_to_login_with_non_exists_credentials(): void
    {
        $query = sprintf(
            'mutation { %s
                (username: "%s", password: "%s")
                {refresh_token access_expires_in refresh_expires_in token_type access_token }
             }',
            self::MUTATION,
            'notexists_email@example.com',
            'not_exists_password'
        );

        $result = $this->postGraphQL(['query' => $query])
            ->assertOk();

        self::assertArrayHasKey('errors', $result);

        $errors = $result->json('errors');
        $error = array_shift($errors);
        self::assertEquals('validation', $error['message']);

        self::assertEquals(
            'These credentials do not match our records.',
            array_shift($error['extensions']['validation']['password'])
        );
    }

    public function test_try_to_login_for_auth_user(): void
    {
        $email = new Email('user@example.com');
        $password = 'password';

        User::factory()->new(['email' => $email])->create();

        $query = sprintf(
            'mutation { %s
                (username: "%s", password: "%s")
                {refresh_token access_expires_in refresh_expires_in token_type access_token }
            }',
            self::MUTATION,
            $email,
            $password
        );

        $result = $this->postGraphQL(['query' => $query])
            ->assertOk();

        [self::MUTATION => ['access_token' => $token]] = $result->json('data');

        $this->postGraphQL(['query' => $query], ['Authorization' => 'Bearer ' . $token])
            ->assertOk()
            ->assertJson(['errors' => [['message' => AuthorizationMessageEnum::AUTHORIZED]]]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->passportInit();
    }
}
