<?php
/** @var Factory $factory */

use App\Models\VehicleDB\VehicleMake;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(
    VehicleMake::class,
    function (Faker $faker, array $data) {
        return [
            'id' => $faker->numberBetween(),
            'name' => $data['name']
        ];
    }
);
