<?php

use Faker\Generator as Faker;
use WezomCms\Services\Models\Service;

$factory->define(\WezomCms\ServicesOrders\Models\ServicesOrder::class, function (Faker $faker) {
    $service = Service::inRandomOrder()->first();

    return [
        'service_id' => $service ? $service->id : null,
        'read' => $faker->boolean(80),
        'name' => $faker->name,
        'phone' => $faker->phoneNumber,
        'email' => $faker->email,
        'city' => $faker->city,
        'message' => $faker->text(),
    ];
});
