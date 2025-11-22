<?php

use App\Http\Controllers\ApiController;
use App\Http\Controllers\V1\Carrier\Intl\LanguageController;
use App\Http\Controllers\V1\Data\Locations\CityController;
use App\Http\Controllers\V1\Data\Locations\StateController;
use App\Http\Controllers\V1\Data\ReferencesController;
use App\Http\Controllers\V1\Data\TimezoneController;
use App\Http\Controllers\V1\Data\Translations\TranslationController;
use App\Http\Controllers\V1\Data\Usdot\UsdotController;
use App\Http\Controllers\V1\Data\VehicleDB\VehicleDBController;

Route::get('', [ApiController::class, 'api'])
    ->name('data');

Route::middleware('throttleIp:1200,1')
    ->group(
        function () {
            Route::get('translates-list', [TranslationController::class, 'list'])
                ->name('translates.list');
        }
    );

Route::middleware('throttleIp:600,1')
    ->group(
        function () {
            Route::get('company-info/{usdot}', [UsdotController::class, 'companyInfo'])
                ->name('usdot.show');
        }
    );

// composite list
Route::middleware('throttleIp:600,1')
    ->group(
        function () {
            Route::get('references', [ReferencesController::class, 'index']);
        }
    );

Route::middleware('auth:api,api_admin')
    ->group(
        function () {
            Route::apiResource('languages', LanguageController::class)->only('index', 'show');

            // locations
            Route::apiResource('states', StateController::class);
            Route::get('states-list', [StateController::class, 'list'])->name('states.list');
            Route::apiResource('cities', CityController::class);
            Route::get('city-autocomplete', [CityController::class, 'autocomplete']);

            // timezones
            Route::get('timezone-list', [TimezoneController::class, 'timezoneList']);

            // vehicle db
            Route::get('vehicle-db/makes', [VehicleDBController::class, 'getMakes']);
            Route::get('vehicle-db/models', [VehicleDBController::class, 'getModels']);
            Route::get('vehicle-db/decode-vin', [VehicleDBController::class, 'decodeVin']);
        }
    );
