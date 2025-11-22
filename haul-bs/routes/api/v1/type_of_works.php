<?php

use App\Foundations\Modules\Permission\Models\Role;
use App\Http\Controllers\Api;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth:'.Role::GUARD_USER,
])->group(function () {
    // type of work crud
    Route::get('types-of-work', [Api\V1\TypeOfWorks\CrudController::class, 'index'])
        ->name('');
    Route::get('types-of-work/shortlist', [Api\V1\TypeOfWorks\CrudController::class, 'shortlist'])
        ->name('.shortlist');
    Route::get('types-of-work/{id}', [Api\V1\TypeOfWorks\CrudController::class, 'show'])
        ->name('.show');
    Route::post('types-of-work', [Api\V1\TypeOfWorks\CrudController::class, 'store'])
        ->name('.store');
    Route::put('types-of-work/{id}', [Api\V1\TypeOfWorks\CrudController::class, 'update'])
        ->name('.update');
    Route::delete('types-of-work/{id}', [Api\V1\TypeOfWorks\CrudController::class, 'delete'])
        ->name('.delete');
});
