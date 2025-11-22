<?php

/** @var Factory $factory */

use App\Models\Admins\Admin;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(
    Admin::class,
    function (Faker $faker) {
        return [
            'full_name' => $faker->name,
            'email' => $faker->unique()->safeEmail,
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'status' => true,
        ];
    }
);
