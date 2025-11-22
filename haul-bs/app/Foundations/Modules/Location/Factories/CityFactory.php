<?php

namespace App\Foundations\Modules\Location\Factories;

use App\Foundations\Modules\Location\Models\City;
use Database\Factories\BaseFactory;

class CityFactory extends BaseFactory
{
    protected $model = City::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->name,
            'zip' => $this->faker->postcode,
            'country_code' => 'US',
            'country_name' => 'United States',
            'active' => true,
            'state_id' => StateFactory::new(),
            'timezone' => $this->faker->timezone,
        ];
    }
}
