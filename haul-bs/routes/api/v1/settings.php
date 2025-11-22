<?php

use App\Foundations\Modules\Permission\Models\Role;
use App\Http\Controllers\Api;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth:'.Role::GUARD_USER,
])->group(function () {
    // settings crud
    Route::get('settings/info', [Api\V1\Settings\SettingCrudController::class, 'information'])
        ->name('.info');
    Route::put('settings/info', [Api\V1\Settings\SettingCrudController::class, 'update'])
        ->name('.update');

    // settings upload logo
    Route::post('settings/info/upload-logo', [Api\V1\Settings\SettingUploadController::class, 'upload'])
        ->name('.upload-logo');
    Route::delete('settings/info/delete-logo', [Api\V1\Settings\SettingUploadController::class, 'delete'])
        ->name('.delete-logo');
    Route::post('settings/info/upload-ecommerce-logo', [Api\V1\Settings\SettingUploadController::class, 'uploadEcommLogo'])
        ->name('.upload-ecommerce-logo');
    Route::delete('settings/info/delete-ecommerce-logo', [Api\V1\Settings\SettingUploadController::class, 'deleteEcomm'])
        ->name('.delete-ecommerce-logo');
});
