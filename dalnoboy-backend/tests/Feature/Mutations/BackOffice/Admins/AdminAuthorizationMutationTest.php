<?php

namespace Tests\Feature\Mutations\BackOffice\Admins;

use App\GraphQL\Mutations\BackOffice\Admins\AdminLoginMutation;
use App\GraphQL\Mutations\BackOffice\Admins\AdminLogoutMutation;
use App\GraphQL\Mutations\BackOffice\Admins\AdminTokenRefreshMutation;
use App\Models\Admins\Admin;
use Core\Enums\Messages\AuthorizationMessageEnum;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class AdminAuthorizationMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function test_logout_success(): void
    {
        $token = $this->test_login_success()
            ->json('data.' . AdminLoginMutation::NAME . '.access_token');

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(AdminLogoutMutation::NAME)
                ->make(),
            [
                'Authorization' => 'Bearer ' . $token
            ]
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        AdminLogoutMutation::NAME => true
                    ]
                ]
            );

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(AdminLogoutMutation::NAME)
                ->make(),
            [
                'Authorization' => 'Bearer ' . $token
            ]
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        AdminLogoutMutation::NAME => false
                    ]
                ]
            );
    }

    public function test_login_success(): TestResponse
    {
        $admin = Admin::factory()
            ->withRole()
            ->create();

        return $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(AdminLoginMutation::NAME)
                ->args(
                    [
                        'username' => $admin->email,
                        'password' => Admin::factory()::DEFAULT_PASSWORD
                    ]
                )
                ->select(
                    [
                        'refresh_token',
                        'access_token_expires_in',
                        'refresh_token_expires_in',
                        'token_type',
                        'access_token'
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        AdminLoginMutation::NAME => [
                            'refresh_token',
                            'access_token_expires_in',
                            'refresh_token_expires_in',
                            'token_type',
                            'access_token'
                        ]
                    ]
                ]
            );
    }

    public function test_refresh_token_success(): void
    {
        $token = $this->test_login_success()
            ->json('data.' . AdminLoginMutation::NAME . '.refresh_token');

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(AdminTokenRefreshMutation::NAME)
                ->args(
                    [
                        'refresh_token' => $token
                    ]
                )
                ->select(
                    [
                        'refresh_token',
                        'access_token_expires_in',
                        'refresh_token_expires_in',
                        'token_type',
                        'access_token'
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        AdminTokenRefreshMutation::NAME => [
                            'refresh_token',
                            'access_token_expires_in',
                            'refresh_token_expires_in',
                            'token_type',
                            'access_token'
                        ]
                    ]
                ]
            );
    }

    public function test_login_fail(): void
    {
        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(AdminLoginMutation::NAME)
                ->args(
                    [
                        'username' => $this->faker->safeEmail,
                        'password' => $this->faker->bothify('##??##??')
                    ]
                )
                ->select(
                    [
                        'refresh_token',
                        'access_token_expires_in',
                        'refresh_token_expires_in',
                        'token_type',
                        'access_token'
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'errors' => [
                        [
                            'message' => 'validation'
                        ]
                    ]
                ]
            );
    }

    public function test_try_to_login_for_auth_user(): void
    {
        $admin = Admin::factory()
            ->withRole()
            ->create();

        $query = GraphQLQuery::mutation(AdminLoginMutation::NAME)
            ->args(
                [
                    'username' => $admin->email,
                    'password' => Admin::factory()::DEFAULT_PASSWORD
                ]
            )
            ->select(
                [
                    'access_token'
                ]
            )
            ->make();

        $token = $this->postGraphQLBackOffice($query)
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        AdminLoginMutation::NAME => [
                            'access_token'
                        ]
                    ]
                ]
            )
            ->json('data.' . AdminLoginMutation::NAME . '.access_token');

        $this->postGraphQLBackOffice($query, ['Authorization' => 'Bearer ' . $token])
            ->assertOk()
            ->assertJson(
                [
                    'errors' => [
                        [
                            'message' => AuthorizationMessageEnum::AUTHORIZED
                        ]
                    ]
                ]
            );
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->passportInit();
    }
}
