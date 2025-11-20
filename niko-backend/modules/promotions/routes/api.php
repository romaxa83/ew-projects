<?php

Route::middleware('set.locale')->group(function () {

    Route::namespace('WezomCms\\Promotions\\Http\\Controllers\\Api\\V1')
        ->group(function () {

            Route::middleware('niko.auth')->group(function(){
                Route::post('promotions/1c/users', 'PromotionsController@setUsers')->name('api.promotions-set-users');

                //@todo роут для тестирования, на проде убрать
                Route::post('promotions/1c-test/users', 'PromotionsController@setUsersTestMode')->name('api.promotions-set-users-test');
            });

            Route::get('offers', 'PromotionsController@list')->name('api.promotions');
            Route::get('offers/{id}', 'PromotionsController@one')->name('api.promotion');
        });
});

