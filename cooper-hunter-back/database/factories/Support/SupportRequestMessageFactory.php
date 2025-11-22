<?php

namespace Database\Factories\Support;

use App\Models\Support\SupportRequestMessage;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method Collection|SupportRequestMessage[]|SupportRequestMessage create(array $attributes = [])
 */
class SupportRequestMessageFactory extends Factory
{
    protected $model = SupportRequestMessage::class;

    public function definition(): array
    {
        return [
            'message' => $this->faker->text
        ];
    }
}
