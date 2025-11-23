<?php

namespace Database\Factories\Items;

use App\Models\Item;
use App\Models\Item\Group;
use Database\Factories\BaseFactory;

class ItemFactory extends BaseFactory
{
    protected $model = Item::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'title' => $this->faker->word(),
            'group_id' => Group::factory(),
            'weight' => $this->faker->randomFloat(2, 0, 100),
            'cuft' => $this->faker->randomFloat(2, 0, 100),
            'price' => $this->faker->randomFloat(2, 0, 1000),
            'division_ids' => [$this->faker->numberBetween(1, 10)],
            'deleted_at' => null,
        ];
    }
}
