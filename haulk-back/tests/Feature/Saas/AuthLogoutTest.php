<?php

namespace Tests\Feature\Saas;

use App\Models\Admins\Admin;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthLogoutTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function test_not_auth_admin_cant_logout(): void
    {
        $this->postJson(route('v1.saas.logout'))
            ->assertUnauthorized();
    }

    public function test_auth_admin_can_logout(): void
    {
        $attr = [
            'email' => $this->faker->email,
            'password' => 'password',
        ];

        factory(Admin::class)->create($attr);

        $tokens = $this->postJson(route('v1.saas.login'), $attr)->json('data');

        $this->postJson(route('v1.saas.logout'), [], ['Authorization' => 'Bearer ' . $tokens['access_token']])
            ->assertOk();
    }
}
