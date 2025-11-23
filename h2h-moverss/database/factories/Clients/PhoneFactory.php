<?php

namespace Database\Factories\Clients;

use App\Models\Client;
use Database\Factories\BaseFactory;

class PhoneFactory extends BaseFactory
{
    protected $model = Client\Phone::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'type_id' => 1,
            'is_primary' => 1,
            'sort' => 1,
            'value' => '221111956545',
            'deleted_at' => null,
        ];
    }
}
