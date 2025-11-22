<?php

use App\Http\Controllers\Api\CKFinder\CKFinderController;
use Illuminate\Support\Facades\Route;

Route::get(
    '/',
    static function () {
        return view('index');
    }
)->name('home');

Route::any('/ckfinder/connector', [CKFinderController::class, 'requestAction'])
    ->name('ckfinder_connector');

Route::any('/ckfinder/browser', [CKFinderController::class, 'browserAction'])
    ->name('ckfinder_browser');
