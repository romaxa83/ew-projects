<?php

use Illuminate\Support\Facades\Route;

Route::get('/',
    function () {
        return view('index');
    }
)->name('home');

Route::get('/info',
    function () {
        return phpinfo();
    }
)->name('info');
