<?php

/** @var Factory $factory */

use App\Models\Settings\Setting;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(
    Setting::class,
    function (Faker $faker) {
        return [
            'group' => $faker->word,
            'alias' => $faker->sentence,
            'value' => $faker->word,
        ];
    }
);
