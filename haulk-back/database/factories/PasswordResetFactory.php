<?php

/** @var Factory $factory */

use App\Models\PasswordReset;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(
    PasswordReset::class,
    function (Faker $faker) {
        return [
            'email' => $faker->email,
            'token' => Hash::make('token'), //hashed word - token
            'created_at' => now()->addHour(),
        ];
    }
);
