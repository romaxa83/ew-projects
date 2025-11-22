<?php

/** @var Factory $factory */

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use App\Models\BodyShop\VehicleOwners\VehicleOwnerComment;

$factory->define(
    VehicleOwnerComment::class,
    function (Faker $faker, ?array $data = null) {
        return [
            'comment' => $faker->text,
        ];
    }
);
