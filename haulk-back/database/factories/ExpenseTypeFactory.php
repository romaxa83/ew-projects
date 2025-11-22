<?php

/** @var Factory $factory */

use App\Models\Lists\ExpenseType;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(
    ExpenseType::class,
    function (Faker $faker) {
        return [
            'carrier_id' => 1,
            'title' => $faker->title,
        ];
    }
);
