<?php

/** @var Factory $factory */

use App\Models\Vehicles\Truck;
use App\Models\Vehicles\Vehicle;
use App\Models\Users\User;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(
    Truck::class,
    function (Faker $faker, ?array $data = null) {
        return [
            'carrier_id' => array_key_exists('carrier_id', $data) ? $data['carrier_id'] : 1,
            'broker_id' => null,
            'vin' => $faker->bothify('#####????###'),
            'unit_number' => $faker->bothify('##??'),
            'make' => 'Audi',
            'model' => 'A3',
            'year' => $faker->numberBetween(2000, 2023),
            'type' => Vehicle::VEHICLE_TYPE_COUPE_2,
            'license_plate' => $faker->bothify('###-???'),
            'temporary_plate' => $faker->bothify('###-???'),
            'notes' => $faker->text,
        ];
    }
);
