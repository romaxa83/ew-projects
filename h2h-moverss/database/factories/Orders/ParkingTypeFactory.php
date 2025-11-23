<?php

namespace Database\Factories\Orders;

use App\Models\ParkingType;
use Database\Factories\BaseFactory;

class ParkingTypeFactory extends BaseFactory
{
    protected $model = ParkingType::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'title' => $this->faker->word(),
            'deleted_at' => null,
        ];
    }
}
