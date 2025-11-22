<?php

use App\Foundations\Modules\Permission\Models\Role;
use App\Http\Controllers\Api;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth:'.Role::GUARD_USER,
])->group(function () {
    // suppliers crud
    Route::get('suppliers', [Api\V1\Suppliers\SupplierCrudController::class, 'index'])
        ->name('');
    Route::get('suppliers/shortlist', [Api\V1\Suppliers\SupplierCrudController::class, 'shortlist'])
        ->name('.shortlist');
    Route::get('suppliers/{id}', [Api\V1\Suppliers\SupplierCrudController::class, 'show'])
        ->name('.show');
    Route::post('suppliers', [Api\V1\Suppliers\SupplierCrudController::class, 'store'])
        ->name('.store');
    Route::post('suppliers/{id}', [Api\V1\Suppliers\SupplierCrudController::class, 'update'])
        ->name('.update');
    Route::delete('suppliers/{id}', [Api\V1\Suppliers\SupplierCrudController::class, 'delete'])
        ->name('.delete');
});
