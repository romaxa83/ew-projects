<?php

use Illuminate\Support\Facades\Route;

Route::get(
    '/',
    function () {
        \App\Services\Telegram\TelegramDev::info('welcome to arma');
        return redirect('graphql');
    }
)->name('home');


//Route::get(
//    '/info',
//    function () {
//        return phpinfo();
//    }
//)->name('info');
