<?php

Route::middleware('set.locale')->group(function () {

    Route::namespace('WezomCms\\Dealerships\\Http\\Controllers\\Api\\V1')
        ->group(function () {

            // точка входа для 1с
            Route::middleware('niko.auth')->group(function(){
                Route::get('dealership/1c/list', 'DealershipsController@listFor1C')->name('api.dealership.list-1c');
            });

            Route::get('centers', 'DealershipsController@all')->name('api.dealerships');
            Route::get('centers/{id}', 'DealershipsController@one')->name('api.dealership');
        });
});


