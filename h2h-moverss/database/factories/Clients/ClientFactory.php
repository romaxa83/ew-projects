<?php

namespace Database\Factories\Clients;

use App\Models\Client;
use Database\Factories\BaseFactory;

class ClientFactory extends BaseFactory
{
    protected $model = Client::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'name' => 'John',
            'lname' => 'Doe',
            'ext_id' => '470842',
            'deleted_at' => null,
        ];
    }
}
