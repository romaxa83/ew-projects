<?php

namespace Core\Chat\Database\Factories;

use Core\Chat\Contracts\Messageable;
use Core\Chat\Models\Conversation;
use Core\Chat\Models\Participation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method Collection|Participation[]|Participation create(array $attributes = [])
 */
class ParticipationFactory extends Factory
{
    protected $model = Participation::class;

    public function definition(): array
    {
        return [
            'conversation_id' => Conversation::factory(),
        ];
    }

    public function forUser(Messageable $user): self
    {
        return $this->state(
            [
                'messageable_id' => $user->getKey(),
                'messageable_type' => $user->getMorphClass(),
            ]
        );
    }
}
