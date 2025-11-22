<?php

/** @var Factory $factory */

use App\Models\BodyShop\VehicleOwners\VehicleOwner;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(
    VehicleOwner::class,
    function (Faker $faker, ?array $data = null) {
        return [
            'first_name' => $faker->firstName,
            'last_name' => $faker->lastName,
            'email' => $faker->unique()->safeEmail,
            'phone' => $faker->phoneNumber,
        ];
    }
);
