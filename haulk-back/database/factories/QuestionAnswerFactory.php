<?php

/** @var Factory $factory */

use App\Models\QuestionAnswer\QuestionAnswer;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(
    QuestionAnswer::class,
    function (Faker $faker) {
        return [
            'question_en' => $faker->title,
            'answer_en' => $faker->title,
            'carrier_id' => 1,
        ];
    }
);
