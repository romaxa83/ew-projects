<?php

use WezomCms\Orders\Http\Controllers\Admin\DeliveriesController;
use WezomCms\Orders\Http\Controllers\Admin\DeliveryAndPaymentController;
use WezomCms\Orders\Http\Controllers\Admin\DeliveryVariantsController;
use WezomCms\Orders\Http\Controllers\Admin\NovaPoshtaController;
use WezomCms\Orders\Http\Controllers\Admin\OrdersController;
use WezomCms\Orders\Http\Controllers\Admin\OrderStatusesController;
use WezomCms\Orders\Http\Controllers\Admin\PaymentsController;
use WezomCms\Orders\Http\Controllers\Admin\PaymentVariantsController;
use WezomCms\Orders\Http\Controllers\Admin\SdekController;

// Orders
Route::adminResource('orders', OrdersController::class)->softDeletes()->show()->settings();

Route::prefix('orders')->name('orders.')->group(function () {
    Route::get('/{order}/add-item/{provider}', [OrdersController::class, 'addItem'])->name('add-item');
    Route::post('/{id}/store-item', [OrdersController::class, 'storeItem'])->name('store-item');
    Route::get('/{id}/delete-item/{item_id}', [OrdersController::class, 'deleteItem'])->name('delete-item');
    Route::post('/render-delivery-form', [OrdersController::class, 'renderDeliveryForm'])
        ->name('render-delivery-form');
});

// Nova Poshta
Route::prefix('nova-poshta')->name('nova-poshta.')->group(function () {
    Route::get('/search-cities', [NovaPoshtaController::class, 'searchCities'])
        ->name('search-cities');

    Route::get('/get-city-warehouses', [NovaPoshtaController::class, 'getCityWarehouses'])
        ->name('get-city-warehouses');
});

// SDEK
Route::prefix('sdek')->name('sdek.')->group(function () {
    Route::get('/search-cities/{region?}', [SdekController::class, 'searchCities'])
        ->name('search-cities')
        ->withoutMiddleware('auth:admin');
});

// Statuses
Route::adminResource('order-statuses', OrderStatusesController::class);

//Deliveries
Route::adminResource('deliveries', DeliveriesController::class)->settings();

//Payments
Route::adminResource('payments', PaymentsController::class)->settings();

// Delivery and payment
Route::settings('delivery-and-payment', DeliveryAndPaymentController::class);

Route::adminResource('delivery-variants', DeliveryVariantsController::class);

Route::adminResource('payment-variants', PaymentVariantsController::class);
