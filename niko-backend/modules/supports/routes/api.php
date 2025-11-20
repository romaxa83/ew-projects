<?php

Route::middleware('set.locale')->group(function () {

    Route::namespace('WezomCms\\Supports\\Http\\Controllers\\Api\\V1')
        ->group(function () {
            Route::post('support', 'SupportController@create')->name('api.support.create');
        });
});

