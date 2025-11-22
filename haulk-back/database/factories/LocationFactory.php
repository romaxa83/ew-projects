<?php

use App\Models\Locations\City;
use App\Models\Locations\State;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

/** @var Factory $factory */

$factory->define(
    State::class,
    function (Faker $faker) {
        return [
            'name' => $faker->unique()->state,
            'status' => 1,
            'state_short_name' => $faker->unique()->state,
            'country_code' => $faker->unique()->countryCode,
            'country_name' => $faker->unique()->country,
        ];
    }
);

$factory->define(
    City::class,
    function (Faker $faker) {
        return [
            'name' => $faker->unique()->city,
            'zip' => (string)$faker->numberBetween(10000, 99999),
            'status' => 1,
            'state_id' => $faker->unique()->numberBetween(),
            'timezone' => $faker->unique()->timezone,
            'country_code' => $faker->unique()->countryCode,
            'country_name' => $faker->unique()->country,
        ];
    }
);
