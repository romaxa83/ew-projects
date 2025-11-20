<?php

use WezomCms\Core\Http\Controllers\Auth\ForgotPasswordController;
use WezomCms\Core\Http\Controllers\Auth\LoginController;
use WezomCms\Core\Http\Controllers\Auth\ResetPasswordController;
use WezomCms\Core\Http\Middleware\RedirectToAdminIfAuthenticated;

Route::middleware(RedirectToAdminIfAuthenticated::class)
    ->group(function () {
        Route::get('login', [LoginController::class, 'showLoginForm'])->name('login-form');
        Route::post('login', [LoginController::class, 'login'])->name('login');

        Route::prefix('password')
            ->name('password.')
            ->group(function () {
                Route::get('reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('request');
                Route::post('email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('email');

                Route::get('reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('reset-form');
                Route::post('reset', [ResetPasswordController::class, 'reset'])->name('reset');
            });
    });

Route::post('logout', [LoginController::class, 'logout'])->name('logout');
