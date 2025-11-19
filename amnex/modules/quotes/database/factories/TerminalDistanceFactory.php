<?php

namespace Wezom\Quotes\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Wezom\Quotes\Models\TerminalDistance;

/**
 * @extends Factory<TerminalDistance>
 */
class TerminalDistanceFactory extends Factory
{
    protected $model = TerminalDistance::class;

    public function definition(): array
    {
        return [
            'pickup_terminal_id' => TerminalFactory::new(),
            'delivery_address' => 'Google DC, 25 Massachusetts Ave NW, Washington, DC 20001, USA',
            'distance_as_mile' => 2661.93,
            'distance_as_meters' => 4283968.00,
            'distance_text' => '2,662 mi',
            'start_location' => [
                "address" => "Google DC, 25 Massachusetts Ave NW, Washington, DC 20001, USA",
                "location" => ["lat" => 38.8980948, "lng" => -77.0105859]
            ],
            'end_location' => [
                "address" => "Google Building 6420, 6420 Sequence Dr, San Diego, CA 92121, USA",
                "location" => ["lat" => 32.9091707, "lng" => -117.1822388]
            ],
        ];
    }
}
