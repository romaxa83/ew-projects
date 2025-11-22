<?php

namespace Tests\Feature\Mutations\BackOffice\Admins;

use App\GraphQL\Mutations\BackOffice\Admins\AdminLoginMutation;
use App\GraphQL\Mutations\BackOffice\Admins\AdminLogoutMutation;
use App\Models\Admins\Admin;
use App\ValueObjects\Email;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdminLogoutMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = AdminLogoutMutation::NAME;

    public function test_it_logout_success(): void
    {
        $email = new Email('admin@example.com');
        $password = 'password';

        Admin::factory()->new(['email' => $email])->create();

        $query = sprintf(
            'mutation { %s(username: "%s", password: "%s") {refresh_token access_expires_in refresh_expires_in token_type access_token member_guard} }',
            AdminLoginMutation::NAME,
            $email,
            $password
        );

        $result = $this->postGraphQLBackOffice(compact('query'))
            ->assertOk();

        [AdminLoginMutation::NAME => $data] = $result->json('data');

        $query = sprintf(
            'mutation { %s }',
            self::MUTATION
        );

        $this->postGraphQLBackOffice(compact('query'), ['Authorization' => 'Bearer '.$data['access_token']])
            ->assertOk()
            ->assertJson(['data' => [self::MUTATION => true,]]);

        $this->postGraphQLBackOffice(compact('query'), ['Authorization' => 'Bearer '.$data['access_token']])
            ->assertOk()
            ->assertJson(['data' => [self::MUTATION => false,]]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->passportInit();
    }
}
