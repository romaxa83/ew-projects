<?php

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;

Route::get('/', [Controller::class, 'root']);

Route::domain(trim(config('requests.e_com_front.host'), '/'))
    ->name('site.')->group(
        function () {
            Route::get('success-payment')->name('thanks-page');
            Route::get('error-payment')->name('error-page');
        }
    );
