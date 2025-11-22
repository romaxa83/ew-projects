<?php

use App\Http\Controllers\ApiController;

Route::get('', [ApiController::class, 'api'])
    ->name('broker');
