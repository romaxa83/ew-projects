<?php

Route::adminResource('articles', 'WezomCms\\Articles\\Http\\Controllers\\Admin\\ArticlesController')->settings();

if (config('cms.articles.articles.use_groups')) {
    Route::adminResource('article-groups', 'WezomCms\\Articles\\Http\\Controllers\\Admin\\ArticleGroupsController')
        ->settings();
}
