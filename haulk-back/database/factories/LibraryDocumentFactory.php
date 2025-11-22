<?php

/** @var Factory $factory */

use App\Models\Library\LibraryDocument;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(
    LibraryDocument::class,
    function (Faker $faker) {
        return [
            'carrier_id' => 1,
            'name' => $faker->name,
        ];
    }
);
