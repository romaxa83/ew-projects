<?php

use App\Http\Controllers\Api\GPS\DataController;

Route::middleware(
    []
)->namespace('Api\GPS')
    ->prefix('gps')
    ->name('gps.')
    ->middleware('flespi.auth')
    ->group(
        function () {
            Route::post('data', [DataController::class, 'receiveData'])
                ->name('receive-data');
        }
    );
