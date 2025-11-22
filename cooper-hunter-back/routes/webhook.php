<?php

use App\Http\Controllers\Webhook\PayPalController;
use App\Http\Middleware\PayPalWebhookSignature;
use Illuminate\Support\Facades\Route;

Route::post('paypal', PayPalController::class)
    ->middleware(PayPalWebhookSignature::class)
    ->name('paypal');
