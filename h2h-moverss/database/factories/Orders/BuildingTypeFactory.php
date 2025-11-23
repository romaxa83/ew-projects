<?php

namespace Database\Factories\Orders;

use App\Models\BuildingType;
use Database\Factories\BaseFactory;

class BuildingTypeFactory extends BaseFactory
{
    protected $model = BuildingType::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'title' => $this->faker->word,
            'deleted_at' => null,
        ];
    }
}
