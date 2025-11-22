<?php

namespace Tests\Feature\Saas;

use App\Models\Admins\Admin;
use App\Models\Users\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Tests\TestCase;

class AuthLoginTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function test_a_company_user_cant_auth()
    {
        $attr = [
            'email' => $this->faker->email,
            'password' => 'password',
        ];

        User::factory()->create($attr);

        $response = $this->postJson(route('v1.saas.login'), $attr)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJson(
            [
                'errors' => [
                    ['title' => 'User not found.']
                ]
            ]
        );
    }

    public function test_admin_can_login()
    {
        $attr = [
            'email' => $this->faker->email,
            'password' => 'password',
        ];

        factory(Admin::class)->create($attr);

        $response = $this->postJson(route('v1.saas.login'), $attr)
            ->assertOk();

        $response->assertJsonStructure(
            ['data' => ['token_type', 'expires_in', 'access_token', 'refresh_token', 'expires_at']]
        );
    }
}
