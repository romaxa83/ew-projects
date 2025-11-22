<?php

use App\Foundations\Modules\Permission\Models\Role;
use App\Http\Controllers\Api;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth:'.Role::GUARD_USER,
])->group(function () {
    // tags crud
    Route::get('tags', [Api\V1\Tags\TagCrudController::class, 'index'])
        ->name('');
    Route::get('tags/{id}', [Api\V1\Tags\TagCrudController::class, 'show'])
        ->name('.show');
    Route::post('tags', [Api\V1\Tags\TagCrudController::class, 'store'])
        ->name('.store');
    Route::put('tags/{id}', [Api\V1\Tags\TagCrudController::class, 'update'])
        ->name('.update');
    Route::delete('tags/{id}', [Api\V1\Tags\TagCrudController::class, 'delete'])
        ->name('.delete');
});
