<?php

/** @var Factory $factory */

use App\Models\BodyShop\Inventories\Unit;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(
    Unit::class,
    function (Faker $faker, ?array $data = null) {
        return [
            'name' => $faker->name,
            'accept_decimals' => true,
        ];
    }
);
