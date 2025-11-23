<?php

namespace Database\Factories\Orders;

use App\Models\MoveSize;
use Database\Factories\BaseFactory;

class MoveSizeFactory extends BaseFactory
{
    protected $model = MoveSize::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'title' => $this->faker->word,
            'deleted_at' => null,
        ];
    }
}
