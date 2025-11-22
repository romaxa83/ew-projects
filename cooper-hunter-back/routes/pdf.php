<?php

use App\Http\Controllers\Pdf\PdfController;
use App\Http\Middleware\PdfToken;
use Illuminate\Support\Facades\Route;

//Route::get(
//    '{token}.pdf',
//    static function () {
//        logger_info("ROUTE PDF");
//        return view('index');
//    }
//)->name('stream');

Route::get('{token}.pdf', PdfController::class)
    ->middleware(PdfToken::class)
    ->name('stream');
