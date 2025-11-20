<?php

Route::namespace('WezomCms\\Articles\\Http\\Controllers\\Site')
    ->group(function () {
        if (config('cms.articles.articles.use_groups')) {
            Route::get('article-groups', 'ArticleGroupsController@index')->name('article-groups');
            Route::get('articles-group/{slug}', 'ArticleGroupsController@inner')->name('article-groups.inner');
        } else {
            Route::get('articles', 'ArticlesController@index')->name('articles');
        }

        Route::get('articles/{slug}', 'ArticlesController@inner')->name('articles.inner');
    });
