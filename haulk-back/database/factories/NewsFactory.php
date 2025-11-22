<?php

/** @var Factory $factory */

use App\Models\News\News;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(
    News::class,
    function (Faker $faker) {
        return [
            'title_en' => $faker->title,
            'carrier_id' => 1,
        ];
    }
);
