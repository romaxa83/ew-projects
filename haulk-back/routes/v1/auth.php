<?php

use App\Http\Controllers\V1\Auth\AuthController;
use App\Http\Controllers\V1\Auth\ForgotPasswordController;
use App\Http\Controllers\V1\Auth\PermissionController;
use App\Http\Controllers\V1\Auth\ResetPasswordController;
use App\Http\Controllers\V1\Carrier\Users\ChangeEmailController;

Route::post('login', [AuthController::class,'login'])->name('login');
Route::post('driver-login', [AuthController::class,'driverLogin'])->name('driver-login');
Route::post('refresh-token', [AuthController::class,'refreshToken'])->name('refresh-token');
Route::post('password-forgot', [ForgotPasswordController::class,'sendResetLinkEmail'])->name('password.forgot');
Route::post('password-set', [ResetPasswordController::class,'reset'])->name('password.reset');

Route::post('change-email/confirm-email', [ChangeEmailController::class,'confirmEmail']);
Route::post('change-email/cancel-request', [ChangeEmailController::class,'cancelRequest']);

Route::group(
    ['middleware' => 'auth:api'],
    function () {
        Route::post('logout', [AuthController::class,'logout'])->name('logout');
        Route::get('permissions/{roleName?}', [PermissionController::class,'show'])->name('permissions.show');
    }
);
