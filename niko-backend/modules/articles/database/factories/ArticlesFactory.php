<?php

use Faker\Generator as Faker;
use WezomCms\Articles\Models\Article;
use WezomCms\Articles\Models\ArticleGroup;

$factory->define(Article::class, function (Faker $faker) {
    $name = $faker->realText(50);

    $data = [
        'published' => $faker->boolean(80),
        'name' => $name,
        'h1' => $name,
        'title' => $name,
        'text' => $faker->realText(1000),
        'published_at' => $faker->dateTimeBetween('-10 days', 'now'),
    ];

    if (config('cms.articles.articles.use_groups')) {
        $group = ArticleGroup::inRandomOrder()->first();

        $data['article_group_id'] = $group ? $group->id : null;
    }

    return $data;
});
