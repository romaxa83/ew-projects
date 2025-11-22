<?php

namespace Tests\Feature\Saas;

use App\Models\Admins\Admin;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Tests\TestCase;

class AuthRefreshTokenTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function test_cant_get_auth_data_for_incorrect_refresh_token()
    {
        $response = $this->postJson(route('v1.saas.refreshToken'), ['refresh_token' => '123456798'])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $error = $response->json('errors.0');

        $this->assertEquals('The refresh token is invalid.', $error['title']);
    }

    public function test_can_get_correct_auth_data_by_correct_refresh_token()
    {
        $attr = [
            'email' => $this->faker->email,
            'password' => 'password',
        ];

        factory(Admin::class)->create($attr);

        $tokens = $this->postJson(route('v1.saas.login'), $attr)->json('data');

        $response = $this->postJson(route('v1.saas.refreshToken'), ['refresh_token' => $tokens['refresh_token']])
            ->assertOk();

        $response->assertJsonStructure(
            ['data' => ['token_type', 'expires_in', 'access_token', 'refresh_token', 'expires_at']]
        );
    }
}
