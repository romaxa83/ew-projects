<?php

namespace Core\Chat\Database\Factories;

use Core\Chat\Models\Conversation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method Collection|Conversation[]|Conversation create(array $attributes = [])
 */
class ConversationFactory extends Factory
{
    protected $model = Conversation::class;

    public function definition(): array
    {
        return [
            'direct_message' => false,
            'title' => $this->faker->word,
            'description' => $this->faker->sentence,
            'is_closed' => false,
            'can_messaging' => false,
        ];
    }

    public function canMessaging(): self
    {
        return $this->state(
            [
                'can_messaging' => true,
            ]
        );
    }

    public function direct(): self
    {
        return $this->state(
            [
                'direct_message' => true,
            ]
        );
    }
}
