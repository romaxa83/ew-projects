<?php

use App\Http\Controllers\Api;
use Illuminate\Support\Facades\Route;

Route::get('info', [Api\ApiController::class, 'info'])
    ->name('api.info');
