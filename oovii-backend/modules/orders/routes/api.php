<?php

use WezomCms\Orders\Http\Controllers\Api\V1\AddressController;
use WezomCms\Orders\Http\Controllers\Api\V1\CartController;
use WezomCms\Orders\Http\Controllers\Api\V1\CheckoutController;
use WezomCms\Orders\Http\Controllers\Api\V1\DeliveryController;
use WezomCms\Orders\Http\Controllers\Api\V1\PayBoxController;
use WezomCms\Orders\Http\Controllers\Api\V1\PaymentController;
use WezomCms\Orders\Http\Controllers\Api\V1\SDEKController;

Route::middleware(['set.locale', 'set.cart'])->group(function() {

    Route::middleware(['auth:api'])->group(function() {
        Route::get('mobile/payment-drivers', [PaymentController::class, 'drivers'])
            ->name('api.v1.mobile.payment-drivers');

        Route::post('mobile/checkout/create-order', [CheckoutController::class, 'createOrder'])
            ->name('api.v1.mobile.checkout.create-order');

        Route::get('mobile/checkout/cancel-order/{order}', [CheckoutController::class, 'cancelOrder'])
            ->name('api.v1.mobile.checkout.cancel-order');

        Route::post('mobile/checkout/order-payment/{paymentInfo}', [CheckoutController::class, 'orderPayment'])
            ->name('api.v1.mobile.checkout.order-payment');

        // addresses
        Route::prefix('mobile')
            ->name('api.v1.mobile.')
            ->group(function () {
                Route::apiResource('addresses', AddressController::class)
                    ->except(['show']);
            });
    });

    Route::get('mobile/delivery-drivers', [DeliveryController::class, 'drivers'])
        ->name('api.v1.mobile.delivery-drivers');

    Route::get('mobile/sdek/regions', [SDEKController::class, 'regions'])
        ->name('api.v1.mobile.sdek.regions');

    Route::get('mobile/sdek/cities', [SDEKController::class, 'cities'])
        ->name('api.v1.mobile.sdek.cities');

    Route::get('mobile/sdek/delivery-points', [SDEKController::class, 'deliveryPoints'])
        ->name('api.v1.mobile.sdek.delivery-points');

    Route::post('mobile/sdek/tariffs', [SDEKController::class, 'tariffs'])
        ->name('api.v1.mobile.sdek.tariffs');

    Route::prefix('mobile/cart')
        ->name('api.v1.mobile.cart.')
        ->group(function () {
            Route::get('', [CartController::class, 'getUserCart'])->name('get');
            Route::get('clear', [CartController::class, 'clearCart'])->name('clear');
            Route::post('add', [CartController::class, 'addProductToCart'])->name('add');
            Route::post('set-quantity', [CartController::class, 'setQuantity'])->name('set-quantity');
            Route::delete('remove/{uniqueId}', [CartController::class, 'remove'])->name('remove');

            Route::middleware('auth:api')
                ->group(function() {
                    Route::get('separated', [CartController::class, 'getSeparatedUserCart'])
                        ->name('separated');

                    Route::get('to-wishlist', [CartController::class, 'toWishlist'])
                        ->name('to-wishlist');
                });
        });
});

Route::prefix('pay-box')
    ->name('api.v1.mobile.pay-box.')
    ->group(function() {
        Route::post('check', [PayBoxController::class, 'check'])->name('check');

        Route::post('result', [PayBoxController::class, 'result'])->name('result');
    });

Route::post('sdek/webhooks', [SDEKController::class, 'webhooks'])->name('api.v1.mobile.sdek.webhooks');
