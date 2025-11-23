<?php

namespace Database\Factories\Communications;

use App\Models\Client;
use App\Models\Communications\ConversationMark;
use App\User;
use Database\Factories\BaseFactory;

class ConversationMarkFactory extends BaseFactory
{
    protected $model = ConversationMark::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'type' => ConversationMark::TYPE_READ,
            'client_id' => Client::factory(),
            'user_id' => User::factory(),
            'contact_type' => null,
            'contact_value' => null,
        ];
    }
}
