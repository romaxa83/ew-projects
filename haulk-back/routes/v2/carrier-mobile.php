<?php

use App\Http\Controllers\V2\CarrierMobile\Orders\OrderMobileController;

Route::prefix('orders')
    ->middleware(['auth:api'])
    ->group(
        function () {
            Route::post('{order}/add-payment-data', [OrderMobileController::class, 'addPaymentData'])
                ->name('orders.add-payment-data');
            Route::post('{order}/complete-inspection', [OrderMobileController::class, 'completeInspection'])
                ->name('orders.complete-inspection');
        }
    );
