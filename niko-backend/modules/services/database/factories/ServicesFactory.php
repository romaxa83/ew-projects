<?php

use Faker\Generator as Faker;
use WezomCms\Services\Models\Service;
use WezomCms\Services\Models\ServiceGroup;

$factory->define(Service::class, function (Faker $faker) {
    $name = $faker->realText(50);

    $data = [
        'published' => $faker->boolean(80),
        'name' => $name,
        'h1' => $name,
        'title' => $name,
        'text' => $faker->realText(1000),
    ];

    if (config('cms.services.services.use_groups')) {
        $group = ServiceGroup::inRandomOrder()->first();

        $data['service_group_id'] = $group ? $group->id : null;
    }

    return $data;
});
