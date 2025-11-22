<?php

namespace Database\Factories\User;

use App\Models\User\Confidant;
use App\ValueObjects\Phone;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConfidantFactory extends Factory
{
    protected $model = Confidant::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'phone' => new Phone($this->faker->phoneNumber),
            'car_id' => 1,
        ];
    }
}
