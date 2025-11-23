<?php

namespace Database\Factories\Communications;

use App\Models\Client;
use App\Models\Communications\ConversationFavorites;
use App\User;
use Database\Factories\BaseFactory;

class ConversationFavoritesFactory extends BaseFactory
{
    protected $model = ConversationFavorites::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'starred' => false,
            'client_id' => Client::factory(),
            'user_id' => User::factory(),
            'contact_type' => null,
            'contact_value' => null,
            'communication_rec_id' => null,
        ];
    }
}
