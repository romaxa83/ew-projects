<?php

use WezomCms\Firebase\Http\Controllers\Api\V1\FirebaseController;

Route::middleware('set.locale')->group(function(){
    Route::middleware('auth:api')->group(function(){

        Route::get('mobile/notifications', [FirebaseController::class, 'list'])
            ->name('api.v1.mobile.notification.list');
    });
});
