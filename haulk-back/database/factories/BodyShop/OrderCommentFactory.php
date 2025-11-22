<?php

/** @var Factory $factory */

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use App\Models\BodyShop\Orders\OrderComment;

$factory->define(
    OrderComment::class,
    function (Faker $faker, ?array $data = null) {
        return [
            'comment' => $faker->text,
        ];
    }
);
