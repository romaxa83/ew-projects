<?php

use WezomCms\Users\Http\Controllers\Admin\ReferralsController;
use WezomCms\Users\Http\Controllers\Admin\UsersController;

Route::adminResource('users', UsersController::class)
    ->settings()
    ->softDeletes();
Route::adminResource('referrals', ReferralsController::class)
    ->except([ 'store', 'create', 'destroy' ])
    ->settings();
Route::get('referrals/{referral}/detach', [ReferralsController::class, 'detach'])
    ->name('referrals.detach');
Route::get('referrals/export', [ReferralsController::class, 'export'])
    ->name('referrals.export');

Route::prefix('users')->name('users.')->group(function () {
    Route::get('/{id}/auth', [UsersController::class, 'auth'])->name('auth');

    Route::get('/search', [UsersController::class, 'search'])->name('search');
    Route::get('/export', [UsersController::class, 'export'])->name('export');
});

