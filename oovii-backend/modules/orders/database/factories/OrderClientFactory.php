<?php

namespace WezomCms\Orders\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use WezomCms\Orders\Models\OrderClient;

class OrderClientFactory extends Factory
{
    protected $model = OrderClient::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->firstName,
            'surname' => $this->faker->lastName,
            'email' => $this->faker->email,
            'phone' => $this->faker->e164PhoneNumber,
        ];
    }
}
