<?php

namespace Tests\Feature\Mutations\BackOffice\Admins;

use App\GraphQL\Mutations\BackOffice\Admins\AdminLoginMutation;
use App\Models\Admins\Admin;
use App\ValueObjects\Email;
use Core\Enums\Messages\AuthorizationMessageEnum;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdminLoginMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = AdminLoginMutation::NAME;

    public function test_it_login_success(): void
    {
        $email = new Email('admin@example.com');
        $password = 'password';

        Admin::factory()->new(['email' => $email])->create();

        $query = sprintf(
            'mutation { %s (username: "%s", password: "%s") {refresh_token access_expires_in refresh_expires_in token_type access_token } }',
            self::MUTATION,
            $email,
            $password
        );

        $result = $this->postGraphQLBackOffice(compact('query'))
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
            'mutation { %s(username: "%s", password: "%s") {refresh_token access_expires_in refresh_expires_in token_type access_token } }',
            self::MUTATION,
            'notexists_email@example.com',
            'not_exists_password'
        );

        $result = $this->postGraphQLBackOffice(compact('query'))
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
        $email = new Email('admin@example.com');
        $password = 'password';

        Admin::factory()->new(['email' => $email])->create();

        $query = sprintf(
            'mutation { %s(username: "%s", password: "%s") {refresh_token access_expires_in refresh_expires_in token_type access_token } }',
            self::MUTATION,
            $email,
            $password
        );

        $result = $this->postGraphQLBackOffice(compact('query'))
            ->assertOk();

        [self::MUTATION => ['access_token' => $token]] = $result->json('data');

        $this->postGraphQLBackOffice(compact('query'), ['Authorization' => 'Bearer ' . $token])
            ->assertOk()
            ->assertJson(['errors' => [['message' => AuthorizationMessageEnum::AUTHORIZED]]]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->passportInit();
    }
}
