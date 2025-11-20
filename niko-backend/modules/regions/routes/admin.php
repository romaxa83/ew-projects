<?php

use WezomCms\Regions\Http\Controllers\Admin;

Route::adminResource('regions', Admin\RegionController::class);
Route::adminResource('cities', Admin\CityController::class);
