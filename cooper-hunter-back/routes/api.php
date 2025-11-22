<?php

use App\Http\Controllers\Api\Statistics\FindSolutionStatisticsController;
use App\Http\Controllers\Pdf\FileController;
use Illuminate\Support\Facades\Route;

Route::get('statistics/solutions/{token}', FindSolutionStatisticsController::class)
    ->name('statistics.solutions');

//
//Route::get('pdf-files/{id}', FileController::class)
//    ->name('file.pdf');
