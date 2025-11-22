<?php

/** @var Factory $factory */

use App\Models\BodyShop\Orders\TypeOfWork;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(
    TypeOfWork::class,
    function (Faker $faker, ?array $data = null) {
        return [
            'name' => $faker->name,
            'duration' => $faker->numberBetween(0, 50) . ':' . $faker->numberBetween(0, 59),
            'hourly_rate' => $faker->randomFloat(2, null, 1000),
        ];
    }
);
