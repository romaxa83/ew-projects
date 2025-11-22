<?php

/** @var Factory $factory */

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use App\Models\Vehicles\Comments\TrailerComment;

$factory->define(
    TrailerComment::class,
    function (Faker $faker, ?array $data = null) {
        return [
            'comment' => $faker->text,
        ];
    }
);
