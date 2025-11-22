<?php

use App\Http\Controllers\Api\V1;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'arma.auth',
//    'set.locale',
    'api.local',
])->group(function(){

    Route::get('dealerships', [V1\DealershipController::class, 'list'])->name('api.v1.dealerships');
    Route::get('services', [V1\ServiceController::class, 'list'])->name('api.v1.services');

    Route::post('models', [V1\ModelController::class, 'store'])->name('api.v1.store.brad-model');

    Route::post('users/{userId}/cars/{carId}/edit', [V1\User\CarController::class, 'edit'])
        ->name('api.v1.user.car.edit');

    Route::get('users/car-verify/{carId}', [V1\User\CarController::class, 'verify'])
        ->name('api.v1.user.car.verify');

    Route::post('users/{userId}/cars', [V1\User\CarController::class, 'add'])
        ->name('api.v1.user.car.add');

    Route::post('users/{id}/edit', [V1\User\UserController::class, 'edit'])
        ->name('api.v1.user.edit');

    Route::post('orders/{orderId}', [V1\Order\OrderController::class, 'edit'])
        ->name('api.v1.order.edit');

    Route::post('orders/{orderId}/invoice', [V1\Order\OrderController::class, 'bill'])
        ->name('api.v1.order.bill');

    Route::post('orders/{orderId}/act', [V1\Order\OrderController::class, 'act'])
        ->name('api.v1.order.act');

    Route::post('recommendations/', [V1\Recommendation\RecommendationController::class, 'createOrUpdate'])
        ->name('api.v1.recommendation.create');

    Route::post('agreements/', [V1\Agreement\AgreementController::class, 'createOrUpdate'])
        ->name('api.v1.agreement.create');

    Route::post('orders/set/free-slot-time', [V1\Order\OrderController::class, 'freeSlotTime'])
        ->name('api.v1.order.free-slot-time');

    Route::post('orders/set/exists', [V1\Order\OrderController::class, 'setExists'])
        ->name('api.v1.order.set.exist');

    Route::post('history/cars/{carId}', [V1\User\CarController::class, 'history'])
        ->name('api.v1.history.car');
});
