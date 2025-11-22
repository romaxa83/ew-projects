<?php

namespace Tests\Feature\Mutations\FrontOffice\Users;

use App\Enums\Users\AuthorizationExpirationPeriodEnum;
use App\GraphQL\Mutations\FrontOffice\Users\UserLoginMutation;
use App\GraphQL\Mutations\FrontOffice\Users\UserLogoutMutation;
use App\GraphQL\Mutations\FrontOffice\Users\UserTokenRefreshMutation;
use App\Models\Users\User;
use Core\Enums\Messages\AuthorizationMessageEnum;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class UserAuthorizationMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function test_login_success(?User $user = null): TestResponse
    {
        $user = $user ?? User::factory()
                ->create();

        return $this->postGraphQL(
            GraphQLQuery::mutation(UserLoginMutation::NAME)
                ->args(
                    [
                        'username' => $user->email,
                        'password' => User::factory()::DEFAULT_PASSWORD
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
                        UserLoginMutation::NAME => [
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

    public function test_login_success_with_everyday_token()
    {
        $user = User::factory()
            ->create(['authorization_expiration_period' => AuthorizationExpirationPeriodEnum::EVERYDAY]);

        $this->postGraphQL(
            GraphQLQuery::mutation(UserLoginMutation::NAME)
                ->args(
                    [
                        'username' => $user->email,
                        'password' => User::factory()::DEFAULT_PASSWORD
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
                        UserLoginMutation::NAME => [
                            'refresh_token',
                            'access_token_expires_in',
                            'refresh_token_expires_in',
                            'token_type',
                            'access_token'
                        ]
                    ]
                ]
            );

        $this->assertDatabaseHas(
            'oauth_refresh_tokens',
            [
                'expires_at' => now()->endOfDay()->format('Y-m-d H:i:s'),
            ],
        );
    }

    public function test_login_success_with_unlimited_token()
    {
        $user = User::factory()
            ->create(['authorization_expiration_period' => AuthorizationExpirationPeriodEnum::UNLIMITED()]);

        $this->postGraphQL(
            GraphQLQuery::mutation(UserLoginMutation::NAME)
                ->args(
                    [
                        'username' => $user->email,
                        'password' => User::factory()::DEFAULT_PASSWORD
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
                        UserLoginMutation::NAME => [
                            'refresh_token',
                            'access_token_expires_in',
                            'refresh_token_expires_in',
                            'token_type',
                            'access_token'
                        ]
                    ]
                ]
            );

        $this->assertDatabaseMissing(
            'oauth_refresh_tokens',
            [
                'expires_at' => now()->endOfDay()->format('Y-m-d H:i:s'),
            ],
        );
    }

    public function test_it_try_to_login_with_non_exists_credentials(): void
    {
        $this->postGraphQL(
            GraphQLQuery::mutation(UserLoginMutation::NAME)
                ->args(
                    [
                        'username' => $this->faker->email,
                        'password' => $this->faker->bothify('##???##???')
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
        $user = User::factory()
            ->create();

        $token = $this->test_login_success($user)
            ->json('data.' . UserLoginMutation::NAME . '.access_token');

        $this->postGraphQL(
            GraphQLQuery::mutation(UserLoginMutation::NAME)
                ->args(
                    [
                        'username' => $user->email,
                        'password' => User::factory()::DEFAULT_PASSWORD
                    ]
                )
                ->select(
                    [
                        'access_token'
                    ]
                )
                ->make(),
            [
                'Authorization' => 'Bearer ' . $token
            ]
        )
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

    public function test_refresh_token_success(): void
    {
        $token = $this->test_login_success()
            ->json('data.' . UserLoginMutation::NAME . '.refresh_token');

        $this->postGraphQL(
            GraphQLQuery::mutation(UserTokenRefreshMutation::NAME)
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
                        UserTokenRefreshMutation::NAME => [
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

    public function test_logout_success(): void
    {
        $token = $this->test_login_success()
            ->json('data.' . UserLoginMutation::NAME . '.access_token');

        $this->postGraphQL(
            GraphQLQuery::mutation(UserLogoutMutation::NAME)
                ->make(),
            [
                'Authorization' => 'Bearer ' . $token
            ]
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        UserLogoutMutation::NAME => true
                    ]
                ]
            );

        $this->postGraphQL(
            GraphQLQuery::mutation(UserLogoutMutation::NAME)
                ->make(),
            [
                'Authorization' => 'Bearer ' . $token
            ]
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        UserLogoutMutation::NAME => false
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
