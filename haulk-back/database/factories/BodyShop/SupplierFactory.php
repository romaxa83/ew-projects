<?php

/** @var Factory $factory */

use App\Models\BodyShop\Suppliers\Supplier;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(
    Supplier::class,
    function (Faker $faker, ?array $data = null) {
        return [
            'name' => $faker->name,
            'url' => $faker->url,
        ];
    }
);
