<?php

namespace Database\Factories\Settings;

use App\Models\Settings\WaypointFlights;
use Database\Factories\BaseFactory;

class WaypointFlightsFactory extends BaseFactory
{
    protected $model = WaypointFlights::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'title' => $this->faker->city(),
            'value' => 11.8,
            'sort' => 1,
        ];
    }
}
