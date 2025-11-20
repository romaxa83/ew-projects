<?php

use WezomCms\Cars\Http\Controllers\Admin;

Route::adminResource('car-brands', Admin\BrandController::class);
Route::adminResource('car-models', Admin\ModelController::class);
Route::adminResource('car-transmissions', Admin\TransmissionController::class);
Route::adminResource('car-engine-types', Admin\EngineTypeController::class);
