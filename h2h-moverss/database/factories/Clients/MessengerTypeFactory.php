<?php

namespace Database\Factories\Clients;

use App\Models\Client;
use Database\Factories\BaseFactory;

class MessengerTypeFactory extends BaseFactory
{
    protected $model = Client\MessengerType::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'title' => $this->faker->word(),
            'icon' => 'fa-telegram',
            'sort' => 1,
            'deleted_at' => null,
        ];
    }
}
