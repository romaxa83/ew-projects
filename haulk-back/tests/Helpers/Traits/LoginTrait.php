<?php

namespace Tests\Helpers\Traits;

use App\Models\Users\User;
use Illuminate\Foundation\Testing\WithFaker;

trait LoginTrait
{
    use WithFaker;

    public function login(string $email = null, string $password = null, string $role = null)
    {
        $attributes = [
            'email' => $email ?? $this->faker->unique()->email,
            'password' => $password ?? $this->faker->password(10),
        ];

        /** @var User $user */
        $user = User::factory()->create($attributes + ['status' => User::STATUS_ACTIVE]);

        $this->authenticatedUser = $user;

        $user->assignRole($role ?? User::SUPERADMIN_ROLE);

        $response = $this->postJson(route('auth.login'), $attributes);

        return json_to_array($response->content())['data'];
    }

    public function loginForBSUser(string $email = null, string $password = null, string $role = null)
    {
        $attributes = [
            'email' => $email ?? $this->faker->unique()->email,
            'password' => $password ?? $this->faker->password(10),
            'carrier_id' => null,
        ];

        /** @var User $user */
        $user = User::factory()->create($attributes + ['status' => User::STATUS_ACTIVE]);

        $this->authenticatedUser = $user;

        $user->assignRole($role ?? User::BSSUPERADMIN_ROLE);

        $response = $this->postJson(route('auth.login'), $attributes);

        return json_to_array($response->content())['data'];
    }
}
