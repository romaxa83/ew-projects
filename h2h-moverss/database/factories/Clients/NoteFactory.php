<?php

namespace Database\Factories\Clients;

use App\Models\Client;
use App\User;
use Database\Factories\BaseFactory;

class NoteFactory extends BaseFactory
{
    protected $model = Client\Notes::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'user_id' => User::factory(),
            'value' => $this->faker->sentence(),
            'deleted_at' => null,
        ];
    }
}
