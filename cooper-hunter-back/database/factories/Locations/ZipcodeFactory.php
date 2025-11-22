<?php

namespace Database\Factories\Locations;

use App\Models\Locations\State;
use App\Models\Locations\Zipcode;
use App\ValueObjects\Point;
use Database\Factories\BaseFactory;

class ZipcodeFactory extends BaseFactory
{
    protected $model = Zipcode::class;

    public function definition(): array
    {
        return [
            'state_id' => State::factory(),
            'zip' => $this->faker->postcode,
            'coordinates' => new Point($this->faker->longitude, $this->faker->latitude),
            'name' => $this->faker->country,
            'timezone' => $this->faker->timezone,
        ];
    }
}
