<?php

use App\Models\Translates\Translate;
use App\Models\Translates\TranslateTranslates;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

/** @var Factory $factory */
$factory->define(
    Translate::class,
    function (Faker $faker) {
        return [
            'key' => $faker->unique()->slug,
        ];
    }
);

$factory->define(
    TranslateTranslates::class,
    function (Faker $faker) {
        return [
            'language' => 'en',
            'text' => $faker->unique()->text,
            'row_id' => function () {
                return factory(Translate::class)->create()->id;
            },
        ];
    }
);
