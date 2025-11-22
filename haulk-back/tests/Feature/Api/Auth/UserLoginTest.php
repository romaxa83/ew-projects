<?php

namespace Tests\Feature\Api\Auth;

use App\Models\Users\User;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Tests\TestCase;

class UserLoginTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    /**
     * @throws Exception
     */
    public function test_it_login_success()
    {
        $attributes = [
            'email' => $this->faker->unique()->email,
            'password' => $this->faker->password(10),
        ];

        /** @var User $user */
        $user = User::factory()->create($attributes + ['status' => User::STATUS_ACTIVE]);
        $user->assignRole(User::SUPERADMIN_ROLE);

        $this->postJson(route('auth.login'), $attributes)
            ->assertOk()
            ->assertJsonStructure(['data' => ['token_type', 'expires_in', 'access_token', 'refresh_token']]);
    }

    /**
     * @param $attributes
     * @param $response
     * @dataProvider loginDataProvider
     */
    public function test_it_see_login_validation_only($attributes, $response)
    {
        $this->postJson(route('auth.login'), $attributes, ['validate_only' => true])
            ->assertOk()
            ->assertJson($response);
    }

    public function loginDataProvider()
    {
        return [
            [
                [
                    'email' => 'email@example.com',
                ],
                [
                    'data' => []
                ]
            ],
            [
                [
                    'password' => 'password123456',
                ],
                [
                    'data' => []
                ]
            ],
            [
                [
                    'password' => null,
                ],
                [
                    'data' => [
                        [
                            'source' => ['parameter' => 'password'],
                            'title' => 'The password field is required.',
                            'status' =>  Response::HTTP_OK
                        ],
                    ]
                ]
            ],
            [
                [
                    'email' => null,
                ],
                [
                    'data' => [
                        [
                            'source' => ['parameter' => 'email'],
                            'title' => 'The Email field is required.',
                            'status' => Response::HTTP_OK
                        ],
                    ]
                ]
            ],
        ];
    }

    public function test_it_cant_login_with_bad_data()
    {
        $this->withoutExceptionHandling();
        $attributes = [
            'email' => 'email@example.com',
            'password' => 'password123',
        ];

        $this->postJson(route('auth.login'), $attributes)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson(
                [
                    'errors' => [
                        [
                            'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                            'title' => 'User not found.',
                        ]
                    ]
                ]
            );
    }

    public function test_it_cant_login_for_not_active_user()
    {
        $attributes = [
            'email' => $this->faker->email,
            'password' => $this->faker->password(7),
        ];

        /** @var User $user */
        $user = User::factory()->create($attributes + ['status' => User::STATUS_INACTIVE]);
        $user->assignRole(User::DISPATCHER_ROLE);

        $this->postJson(route('auth.login'), $attributes)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson(
                [
                    'errors' => [
                        [
                            'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                            'title' => 'User deactivated.'
                        ]
                    ]
                ]
            );
    }

    /**
     * @throws Exception
     */
    public function test_it_login_for_bs_users()
    {
        $attributes = [
            'email' => $this->faker->unique()->email,
            'password' => $this->faker->password(10),
            'carrier_id' => null,
        ];

        /** @var User $user */
        $user = User::factory()->create($attributes + ['status' => User::STATUS_ACTIVE]);
        $user->assignRole(User::BSSUPERADMIN_ROLE);

        $this->postJson(route('auth.login'), $attributes)
            ->assertOk()
            ->assertJsonStructure(['data' => ['token_type', 'expires_in', 'access_token', 'refresh_token']]);
    }

    public function test_it_login_v2_success()
    {
        $attributes = [
            'email' => $this->faker->unique()->email,
            'password' => $this->faker->password(10),
        ];

        /** @var User $user */
        $user = User::factory()->create($attributes + ['status' => User::STATUS_ACTIVE]);
        $user->assignRole(User::SUPERADMIN_ROLE);

        $response = $this->postJson(route('v1.authorize.login'), $attributes)
            ->assertOk()
            ->assertJsonStructure(['data' => ['redirect_url', 'token', 'is_body_shop_user']]);

        $this->assertFalse($response['data']['is_body_shop_user']);
    }

    public function test_it_login_v2_success_bs()
    {
        $attributes = [
            'email' => $this->faker->unique()->email,
            'password' => $this->faker->password(10),
        ];

        /** @var User $user */
        $user = User::factory()->create($attributes + ['status' => User::STATUS_ACTIVE]);
        $user->assignRole(User::BSSUPERADMIN_ROLE);

        $response = $this->postJson(route('v1.authorize.login'), $attributes)
            ->assertOk()
            ->assertJsonStructure(['data' => ['redirect_url', 'token', 'is_body_shop_user']]);

        $this->assertTrue($response['data']['is_body_shop_user']);
    }

    public function test_it_login_success_owner_driver()
    {
        $attributes = [
            'email' => $this->faker->unique()->email,
            'password' => $this->faker->password(10),
        ];

        /** @var User $user */
        $user = User::factory()->create($attributes + ['status' => User::STATUS_ACTIVE]);
        $user->assignRole(User::OWNER_DRIVER_ROLE);

        $response = $this->postJson(route('v1.authorize.login'), $attributes, ['Admin-Panel' => 'true'])
            ->assertOk()
            ->assertJsonStructure(['data' => ['redirect_url', 'token', 'is_body_shop_user']]);

        $this->assertFalse($response['data']['is_body_shop_user']);
    }

    public function test_it_cant_login_driver()
    {
        $attributes = [
            'email' => $this->faker->unique()->email,
            'password' => $this->faker->password(10),
        ];

        /** @var User $user */
        $user = User::factory()->create($attributes + ['status' => User::STATUS_ACTIVE]);
        $user->assignRole(User::DRIVER_ROLE);

        $this->postJson(route('v1.authorize.login'), $attributes, ['Admin-Panel' => 'true'])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
