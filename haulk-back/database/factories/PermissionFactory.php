<?php

use App\Models\Permissions\Permission;
use App\Models\Permissions\Role;
use App\Models\Users\User;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

/** @var Factory $factory */
$factory->define(
    Role::class,
    function (Faker $faker) {
        return [
            'name' => $faker->unique()->word,
            'guard_name' => User::GUARD,
        ];
    }
);

$factory->define(
    Permission::class,
    function (Faker $faker) {
        return [
            'name' => $faker->unique()->word,
            'guard_name' => User::GUARD,
        ];
    }
);

