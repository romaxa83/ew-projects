<?php

Route::middleware('set.locale')->group(function () {

    Route::namespace('WezomCms\\Regions\\Http\\Controllers\\Api\\V1')
        ->group(function () {
            Route::get('catalogs/cities', 'CityController@list')->name('api.city-list');
        });
});



