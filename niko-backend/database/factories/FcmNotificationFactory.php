<?php

use Faker\Generator as Faker;

$factory->define(\WezomCms\Firebase\Models\FcmNotification::class, function (Faker $faker) {
    return [
        'type' => true,
        'created_at' => \Carbon\Carbon::now(),
        'updated_at' => \Carbon\Carbon::now(),
    ];
});
