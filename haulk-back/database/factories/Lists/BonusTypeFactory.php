<?php

namespace Database\Factories\Lists;

use App\Models\Lists\BonusType;
use Illuminate\Database\Eloquent\Factories\Factory;

class BonusTypeFactory extends Factory
{
    protected $model = BonusType::class;

    public function definition(): array
    {
        return [
            'carrier_id' => 1,
            'title' => $this->faker->title,
        ];
    }
}
