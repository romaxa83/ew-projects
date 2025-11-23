<?php

namespace Database\Factories\Users;

use App\User;
use Database\Factories\BaseFactory;
use Faker\Factory;

class UserFactory extends BaseFactory
{
    protected $model = User::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {

        $faker = Factory::create();
//        dd($faker->name);
        return [
            'name' => $faker->name,
            'active' => 1,
            'tmp_is_admin' => 1,
            'division_ids' => [1,2],
            'email' => $faker->unique()->safeEmail,
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password,
            'miscs' => [],
            'remember_token' => \Str::random(10),
        ];
    }
}

