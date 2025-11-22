<?php

namespace Database\Factories\Locations;

use App\Models\Locations\City;
use App\Models\Locations\State;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method City|City[]|Collection create($attributes = [], ?Model $parent = null)
 */
class CityFactory extends Factory
{
    protected $model = City::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->city,
            'status' => true,
            'zip' => $this->faker->numerify("#####"),
            'state_id' => State::factory(),
            'timezone' => $this->faker->timezone,
            'country_code' => 'US',
            'country_name' => 'United States',
        ];
    }
}
