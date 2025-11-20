<?php

use Faker\Generator as Faker;

$factory->define(\WezomCms\Core\Models\Administrator::class, function (Faker $faker) {
    return [
        'name' => $faker->userName,
        'email' => $faker->email,
        'active' => $faker->boolean,
        'password' => bcrypt($faker->password(8)),
        'super_admin' => $faker->boolean,
    ];
});

$factory->state(\WezomCms\Core\Models\Administrator::class, 'active', function () {
    return [
        'active' => true,
    ];
});
$factory->state(\WezomCms\Core\Models\Administrator::class, 'super_admin', function () {
    return [
        'super_admin' => true,
    ];
});
