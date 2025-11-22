<?php

/** @var Factory $factory */

use App\Models\BodyShop\Inventories\Category;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(
    Category::class,
    function (Faker $faker, ?array $data = null) {
        return [
            'name' => $faker->name,
        ];
    }
);
