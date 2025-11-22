<?php

namespace Database\Factories\Admin;

use App\Models\Admin\Login;
use Illuminate\Database\Eloquent\Factories\Factory;

class LoginFactory extends Factory
{
    protected $model = Login::class;

    public function definition(): array
    {
        return [
            'ip_address' => $this->faker->ipv4,
            'created_at' => now(),
        ];
    }
}
