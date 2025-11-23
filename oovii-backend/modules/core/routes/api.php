<?php

use WezomCms\Core\Http\Controllers\Api\V1\SettingsController;

Route::middleware('set.locale')->group(function(){
    Route::get('mobile/settings', [SettingsController::class, 'index'])
        ->name('api.v1.mobile.settings');
});

