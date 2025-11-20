<?php

namespace Database\Factories\Report;

use App\Models\Report\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

class LocationFactory extends Factory
{
    protected $model = Location::class;

    public function definition(): array
    {
        return [
            'city' => $this->faker->city,
            'lat' => $this->faker->latitude,
            'long' => $this->faker->longitude,
            'region' => $this->faker->city,
            'zipcode' => $this->faker->countryCode,
            'street' => $this->faker->streetName,
        ];
    }
}
