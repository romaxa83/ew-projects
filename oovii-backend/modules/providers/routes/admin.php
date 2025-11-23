<?php

use WezomCms\Providers\Http\Controllers\Admin;

Route::adminResource('providers', Admin\ProviderController::class)->settings();

Route::prefix('providers')->name('providers.')->group(function () {
    Route::get('/export', [Admin\ProviderController::class, 'export'])->name('export');
});
