<?php

use App\Foundations\Modules\Permission\Models\Role;
use App\Http\Controllers\Api;

Route::middleware([
    'auth:'.Role::GUARD_USER,
])->group(function () {

    Route::get('forms/drafts/{path}', [Api\V1\Forms\DraftCrudController::class, 'show'])
        ->name('.drafts.show');
    Route::post('forms/drafts/{path}', [Api\V1\Forms\DraftCrudController::class, 'store'])
        ->name('.drafts.store');
    Route::delete('forms/drafts/{path}', [Api\V1\Forms\DraftCrudController::class, 'delete'])
        ->name('.drafts.delete');
});
