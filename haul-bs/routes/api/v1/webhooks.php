<?php

use App\Http\Controllers\Api;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth.webhook',
])->group(function () {

    // vehicles
    Route::post('vehicles/set', [Api\V1\Webhooks\VehicleController::class, 'setVehicles'])
        ->name('.vehicles.set');
    Route::post('vehicles/unset/{companyId}', [Api\V1\Webhooks\VehicleController::class, 'unsetVehicles'])
        ->name('.vehicles.unset');
    Route::post('vehicles/sync', [Api\V1\Webhooks\VehicleController::class, 'createOrUpdate'])
        ->name('.vehicles.sync');
    Route::delete('vehicles/trailer/{id}', [Api\V1\Webhooks\VehicleController::class, 'deleteTrailer'])
        ->name('.vehicles.trailer.delete');
    Route::delete('vehicles/truck/{id}', [Api\V1\Webhooks\VehicleController::class, 'deleteTruck'])
        ->name('.vehicles.truck.delete');
});

