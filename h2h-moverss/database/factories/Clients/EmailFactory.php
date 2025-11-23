<?php

namespace Database\Factories\Clients;

use App\Models\Client;
use Database\Factories\BaseFactory;

class EmailFactory extends BaseFactory
{
    protected $model = Client\Email::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'is_primary' => 1,
            'sort' => 1,
            'value' => $this->faker->unique()->safeEmail(),
            'deleted_at' => null,
        ];
    }
}
