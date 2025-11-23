<?php

namespace Database\Factories\Clients;

use App\Models\Client;
use App\User;
use Database\Factories\BaseFactory;

class ActivityFactory extends BaseFactory
{
    protected $model = Client\Activity::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'user_id' => User::factory(),
            'type' => 'phone.value',
            'miscs' => [
                'from' => null,
                'to' => '3243123423',
            ],
        ];
    }
}
