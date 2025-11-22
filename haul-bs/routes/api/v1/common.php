<?php

use App\Foundations\Modules\Permission\Models\Role;
use App\Http\Controllers\Api;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth:'.Role::GUARD_USER,
])->group(function () {
    // seo
    Route::delete('seo/{id}/image{imageId}', [Api\V1\Common\SeoController::class, 'delete'])
        ->name('.seo.delete-file');
});

Route::middleware([])
    ->group(function () {
        Route::post('filebrowser/browse', [Api\V1\Common\FileBrowserController::class, 'browse'])
            ->name('.filebrowser.browse');
        Route::post('filebrowser/upload', [Api\V1\Common\FileBrowserController::class, 'upload'])
            ->name('.filebrowser.upload');
    });
