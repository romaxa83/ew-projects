<?php

use App\Http\Controllers\Api;

Route::get('timezone', [Api\V1\Locations\TimezoneController::class, 'list'])
    ->name('.timezone');

Route::get('state-list', [Api\V1\Locations\StateController::class, 'list'])
    ->name('.state.list');

Route::get('city-autocomplete', [Api\V1\Locations\CityController::class, 'list'])
    ->name('.city-autocomplete');
