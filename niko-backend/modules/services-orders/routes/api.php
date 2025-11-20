<?php

Route::middleware('set.locale')->group(function () {

    Route::namespace('WezomCms\\ServicesOrders\\Http\\Controllers\\Api\\V1')
        ->group(function () {

            // точка входа для 1с
            Route::middleware('niko.auth')->group(function(){
                Route::post('application/1c/change-status', 'OrderController@changeStatus')->name('api.service-order-change-status');
                //@todo роут для тестирования, на проде убрать
                Route::post('application/1c-test/change-status', 'OrderController@changeStatusTestMode')->name('api.service-order-change-status-test');
            });

            Route::middleware('auth:api')->group(function(){

                Route::get('applications', 'OrderController@list')->name('api.services-order-completed.list');
                Route::get('applications/completed', 'OrderController@listCompletedOrder')->name('api.services-order-completed.list');
                Route::get('applications/planned', 'OrderController@listPlannedOrder')->name('api.services-order-planned.list');

                Route::get('applications/time', 'OrderController@time')->name('api.services-order.time');
                Route::post('applications', 'OrderController@create')->name('api.services-order.create');
                Route::post('applications/{id}/rate', 'OrderController@addRate')->name('api.services-order.rate');
            });
        });
});
