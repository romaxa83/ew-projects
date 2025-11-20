<?php

namespace Tests\Feature\Mutations\BackOffice\Auth\Admin;

use App\GraphQL\Mutations\BackOffice\Auth\Admin\AdminLogoutMutation;
use App\GraphQL\Mutations\BackOffice\Auth\LoginMutation;
use App\Models\Admins\Admin;
use App\ValueObjects\Email;
use Tests\TestCase;

class AdminLogoutMutationTest extends TestCase
{
    public const MUTATION = AdminLogoutMutation::NAME;
    protected function setUp(): void
    {
        parent::setUp();

        $this->passportInit();
    }

    /** @test */
    public function success_logout_as_admin(): void
    {
        $email = new Email('admin@example.com');
        $password = 'Password123';

        Admin::factory()->new(['email' => $email])->create();

        $query = sprintf(
            'mutation { %s(username: "%s", password: "%s") {refresh_token access_expires_in token_type access_token } }',
            LoginMutation::NAME,
            $email,
            $password
        );

        $result = $this->postGraphQLBackOffice(compact('query'))
            ->assertOk();

        [LoginMutation::NAME => $data] = $result->json('data');

        $query = sprintf(
            'mutation { %s }',
            self::MUTATION
        );

        $this->postGraphQLBackOffice(compact('query'), ['Authorization' => 'Bearer ' . $data['access_token']])
            ->assertOk()
            ->assertJson(['data' => [self::MUTATION => true,]]);

        $this->postGraphQLBackOffice(compact('query'), ['Authorization' => 'Bearer ' . $data['access_token']])
            ->assertOk()
            ->assertJson(['data' => [self::MUTATION => false,]]);
    }
}
