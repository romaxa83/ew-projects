<?php

namespace Database\Factories\Orders;

use App\Models\Order;
use Database\Factories\BaseFactory;

class TagFactory extends BaseFactory
{
    protected $model = Order\Tag::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'color' => '#363535',
            'sort' => 1,
            'icon' => $this->faker->word(),
            'title' => $this->faker->word(),
            'deleted_at' => null,
        ];
    }
}
