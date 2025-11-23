<?php

use WezomCms\Users\Http\Controllers\Api\V1\AuthController;
use WezomCms\Users\Http\Controllers\Api\V1\UserController;

Route::middleware('set.locale')->group(function(){
    Route::post('mobile/auth/register', [AuthController::class, 'register'])
        ->name('api.v1.mobile.user.register');

    Route::post('mobile/auth/login', [AuthController::class, 'login'])
        ->name('api.v1.mobile.user.login');

    Route::post('mobile/auth/refresh-token', [AuthController::class, 'refreshToken'])
        ->name('api.v1.mobile.user.refresh-token');

    Route::post('mobile/user/exist-by-phone', [AuthController::class, 'checkByPhone'])
        ->name('api.v1.mobile.user.exist-by-phone');

    Route::middleware('auth:api')->group(function(){
        Route::get('mobile/user', [UserController::class, 'user'])
            ->name('api.v1.mobile.user');

        Route::put('mobile/user', [UserController::class, 'edit'])
            ->name('api.v1.mobile.user.edit');

        Route::put('mobile/user/change-phone', [UserController::class, 'changePhone'])
            ->name('api.v1.mobile.user.change-phone');

        Route::get('mobile/user/bonus-history', [UserController::class, 'bonusHistory'])
            ->name('api.v1.mobile.user.bonus-history');

        Route::get('mobile/user/orders', [UserController::class, 'orders'])
            ->name('api.v1.mobile.user.orders');

        Route::get('mobile/user/orders/{order}', [UserController::class, 'order'])
            ->name('api.v1.mobile.user.order');

        Route::post('mobile/auth/logout', [AuthController::class, 'logout'])
            ->name('api.v1.mobile.logout');

        Route::delete('mobile/user', [UserController::class, 'delete'])
            ->name('api.v1.mobile.user.delete');
    });
});

