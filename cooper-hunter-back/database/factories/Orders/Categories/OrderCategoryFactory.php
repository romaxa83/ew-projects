<?php

namespace Database\Factories\Orders\Categories;

use App\Models\Orders\Categories\OrderCategory;
use Database\Factories\BaseDictionaryFactory;

class OrderCategoryFactory extends BaseDictionaryFactory
{
    protected $model = OrderCategory::class;

    public function definition(): array
    {
        return array_merge(
            [
                'guid' => $this->faker->unique->uuid,
            ],
            parent::definition(),
        );
    }

    public function needsDescription(): self
    {
        return $this->state(
            [
                'need_description' => true,
            ]
        );
    }
}
