<?php

use Faker\Generator as Faker;
use WezomCms\Articles\Models\ArticleGroup;

$factory->define(ArticleGroup::class, function (Faker $faker) {
    $name = $faker->realText(50);

    return [
        'published' => $faker->boolean(80),
        'name' => $name,
        'h1' => $name,
        'title' => $name,
    ];
});
