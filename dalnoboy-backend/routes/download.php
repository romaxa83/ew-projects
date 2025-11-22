<?php

use App\Http\Controllers\Download\DownloadController;
use Illuminate\Support\Facades\Route;

Route::get('{hash}.xlsx', [DownloadController::class, 'xlsx'])
    ->name('download.xlsx');
