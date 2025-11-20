<?php

Route::middleware('set.locale')->group(function () {

    Route::namespace('WezomCms\\Firebase\\Http\\Controllers\\Api\\V1')
        ->group(function () {

            Route::get('notifications/test-send/{userId}', 'FirebaseController@testSend')->name('api.fcm-notification.test-send');

            Route::middleware('auth:api')->group(function(){
                Route::post('firebase/fcm', 'FirebaseController@setToken')->name('api.set-fcm-token');

                Route::get('notifications', 'FirebaseController@listNotification')->name('api.list-notification');
                Route::get('notifications/number', 'FirebaseController@countNotification')->name('api.count-notification');
            });
        });
});
