<?php

use App\Foundations\Modules\Permission\Models\Role;
use App\Http\Controllers\Api;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth:'.Role::GUARD_USER,
])->group(function () {
    // catalog
    Route::get('vehicles/makes', [Api\V1\Vehicles\VehicleCatalogController::class, 'makes'])
        ->name('.makes');
    Route::get('vehicles/models', [Api\V1\Vehicles\VehicleCatalogController::class, 'models'])
        ->name('.models');
    Route::get('vehicles/types', [Api\V1\Vehicles\VehicleCatalogController::class, 'types'])
        ->name('.types');
    Route::get('vehicles/decode-vin', [Api\V1\Vehicles\VehicleCatalogController::class, 'decodeVin'])
        ->name('.decode-vin');
    Route::get('vehicles', [Api\V1\Vehicles\VehicleCatalogController::class, 'getVehicles'])
        ->name('.vehicles');

    // truck action
    Route::get('trucks/same-vin', [Api\V1\Vehicles\Truck\ActionController::class, 'sameVin'])
        ->name('.trucks.same-vin');
    Route::delete('trucks/{id}/attachments/{attachmentId}', [Api\V1\Vehicles\Truck\ActionController::class, 'deleteAttachment'])
        ->name('.trucks.delete-file');

    // truck crud
    Route::get('trucks', [Api\V1\Vehicles\Truck\CrudController::class, 'index'])
        ->name('.trucks');
    Route::post('trucks', [Api\V1\Vehicles\Truck\CrudController::class, 'store'])
        ->name('.trucks.create');
    Route::post('trucks/{id}', [Api\V1\Vehicles\Truck\CrudController::class, 'update'])
        ->name('.trucks.update');
    Route::get('trucks/{id}', [Api\V1\Vehicles\Truck\CrudController::class, 'show'])
        ->name('.trucks.show');
    Route::delete('trucks/{id}', [Api\V1\Vehicles\Truck\CrudController::class, 'delete'])
        ->name('.trucks.delete');

    // truck comment
    Route::get('trucks/{id}/comments', [Api\V1\Vehicles\Truck\CommentController::class, 'index'])
        ->name('.trucks.list-comment');
    Route::post('trucks/{id}/comments', [Api\V1\Vehicles\Truck\CommentController::class, 'store'])
        ->name('.trucks.add-comment');
    Route::delete('trucks/{id}/comments/{commentId}', [Api\V1\Vehicles\Truck\CommentController::class, 'delete'])
        ->name('.trucks.delete-comment');

    // truck history
    Route::get('trucks/{id}/history', [Api\V1\Vehicles\Truck\HistoryController::class, 'history'])
        ->name('.trucks.list-history');
    Route::get('trucks/{id}/history-detailed', [Api\V1\Vehicles\Truck\HistoryController::class, 'historyDetailed'])
        ->name('.trucks.detailed-history');
    Route::get('trucks/{id}/history-users-list', [Api\V1\Vehicles\Truck\HistoryController::class, 'historyUsers'])
        ->name('.trucks.history-users-list');

    // trailer action
    Route::get('trailers/same-vin', [Api\V1\Vehicles\Trailer\ActionController::class, 'sameVin'])
        ->name('.trailers.same-vin');
    Route::delete('trailers/{id}/attachments/{attachmentId}', [Api\V1\Vehicles\Trailer\ActionController::class, 'deleteAttachment'])
        ->name('.trailers.delete-file');

    // trailer crud
    Route::get('trailers', [Api\V1\Vehicles\Trailer\CrudController::class, 'index'])
        ->name('.trailers');
    Route::post('trailers', [Api\V1\Vehicles\Trailer\CrudController::class, 'store'])
        ->name('.trailers.create');
    Route::post('trailers/{id}', [Api\V1\Vehicles\Trailer\CrudController::class, 'update'])
        ->name('.trailers.update');
    Route::get('trailers/{id}', [Api\V1\Vehicles\Trailer\CrudController::class, 'show'])
        ->name('.trailers.show');
    Route::delete('trailers/{id}', [Api\V1\Vehicles\Trailer\CrudController::class, 'delete'])
        ->name('.trailers.delete');

    // trailer comment
    Route::get('trailers/{id}/comments', [Api\V1\Vehicles\Trailer\CommentController::class, 'index'])
        ->name('.trailers.list-comment');
    Route::post('trailers/{id}/comments', [Api\V1\Vehicles\Trailer\CommentController::class, 'store'])
        ->name('.trailers.add-comment');
    Route::delete('trailers/{id}/comments/{commentId}', [Api\V1\Vehicles\Trailer\CommentController::class, 'delete'])
        ->name('.trailers.delete-comment');

    // trailer history
    Route::get('trailers/{id}/history', [Api\V1\Vehicles\Trailer\HistoryController::class, 'history'])
        ->name('.trailers.list-history');
    Route::get('trailers/{id}/history-detailed', [Api\V1\Vehicles\Trailer\HistoryController::class, 'historyDetailed'])
        ->name('.trailers.detailed-history');
    Route::get('trailers/{id}/history-users-list', [Api\V1\Vehicles\Trailer\HistoryController::class, 'historyUsers'])
        ->name('.trailers.history-users-list');
});
