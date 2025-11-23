<?php

namespace Database\Factories\Clients;

use App\Models\Client;
use App\Models\Client\MessengerType;
use Database\Factories\BaseFactory;

class MessengerFactory extends BaseFactory
{
    protected $model = Client\Messenger::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'type_id' => MessengerType::factory(),
            'value' => '22244456545',
            'deleted_at' => null,
        ];
    }
}
