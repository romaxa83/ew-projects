<?php

Route::middleware('set.locale')->group(function () {

    Route::namespace('WezomCms\\Users\\Http\\Controllers\\Api\\V1')
        ->group(function () {
            Route::post('registration', 'AuthController@register')->name('api.registration');
            Route::post('sms-request', 'AuthController@smsRequest')->name('api.sms-request');
            Route::post('sms-verify', 'AuthController@smsVerify')->name('api.sms-verify');
            Route::post('refresh', 'AuthController@refreshToken')->name('api.refresh-token');

            Route::middleware('niko.auth')->group(function(){
                Route::post('user/1c/change-status', 'UserController@changeStatus')->name('api.user-change-status');
                Route::post('car/1c/change-status', 'CarController@changeStatus')->name('api.car-change-status');

                //@todo роут для тестирования, на проде убрать
                Route::post('user/1c-test/change-status', 'UserController@changeStatusTestMode')->name('api.user-change-status-test');
                Route::post('car/1c-test/change-status', 'CarController@changeStatusTestMode')->name('api.car-change-status-test');
            });

            Route::middleware('auth:api')->group(function(){

                Route::post('quit', 'AuthController@logout')->name('api.logout');
                Route::get('user', 'UserController@user')->name('api.user');
                Route::post('user', 'UserController@edit')->name('api.user-edit');
                Route::post('user/phone', 'UserController@changePhone')->name('api.user-change-phone');
                // cars
                Route::get('vehicles', 'CarController@list')->name('api.user-car.list');
                Route::post('vehicles', 'CarController@add')->name('api.user-car.add');
                Route::delete('vehicles/{id}', 'CarController@remove')->name('api.user-car.remove');
                // loyalty
                Route::get('loyalty', 'LoyaltyController@loyalty')->name('api.user.loyalty');
            });
        });
});
