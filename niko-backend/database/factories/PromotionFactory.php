<?php

use Faker\Generator as Faker;

$factory->define(\WezomCms\Promotions\Models\Promotions::class, function (Faker $faker) {
    return [
        'published' => true,
    ];
});

$factory->define(\WezomCms\Promotions\Models\PromotionsTranslation::class, function (Faker $faker) {
    return [
        'name' => $faker->sentence,
        'text' => $faker->realText($faker->numberBetween(200, 250)),
    ];
});

