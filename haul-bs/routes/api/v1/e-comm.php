<?php

use App\Http\Controllers\Api;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth.e_comm',
])->group(function () {

    // inventory categories
    Route::get('e-comm/categories', [Api\V1\Inventories\Category\EComController::class, 'index'])
        ->name('.categories');
    // inventory brands
    Route::get('e-comm/brands', [Api\V1\Inventories\Brand\EComController::class, 'index'])
        ->name('.brands');
    // inventory features
    Route::get('e-comm/features', [Api\V1\Inventories\Feature\EComController::class, 'index'])
        ->name('.features');
    // inventory
    Route::get('e-comm/inventories', [Api\V1\Inventories\Inventory\EComController::class, 'index'])
        ->name('.inventories');

    // customers
    Route::get('e-comm/customers', [Api\V1\Customers\EComController::class, 'index'])
        ->name('.customers.index');
    Route::post('e-comm/customers', [Api\V1\Customers\EComController::class, 'store'])
        ->name('.customers.store');
    Route::put('e-comm/customers/{id}', [Api\V1\Customers\EComController::class, 'update'])
        ->name('.customers.update');
    Route::post('e-comm/customers/{id}/addresses', [Api\V1\Customers\EComController::class, 'addAddress'])
        ->name('.customers.address.store');
    Route::put('e-comm/customers/{id}/addresses/{addressId}', [Api\V1\Customers\EComController::class, 'updateAddress'])
        ->name('.customers.address.update');
    Route::delete('e-comm/customers/{id}/addresses/{addressId}', [Api\V1\Customers\EComController::class, 'deleteAddress'])
        ->name('.customers.address.delete');
    Route::put('e-comm/customers/{id}/set-ecomm-tag', [Api\V1\Customers\EComController::class, 'setECommTag'])
        ->name('.customers.set-ecomm-tag');

    Route::post('e-comm/customers/{email}/upload-tax-exemption', [Api\V1\Customers\EComController::class, 'uploadTaxExemption'])
        ->name('.customers.upload-tax-exemption');

    Route::delete('e-comm/customers/{email}/delete-tax-exemption', [Api\V1\Customers\EComController::class, 'deleteTaxExemption'])
        ->name('.customers.delete-tax-exemption');

    Route::get('e-comm/customers/{email}/get-tax-exemption', [Api\V1\Customers\EComController::class, 'getTaxExemption'])
        ->name('.customers.get-tax-exemption');

    Route::post('e-comm/delivery/get-rate', [Api\V1\Delivery\EcomController::class, 'rate'])
        ->name('.delivery.rate');

    Route::post('e-comm/delivery/address-validate', [Api\V1\Delivery\EcomController::class, 'addressValidate'])
        ->name('.delivery.address-validate');

    // orders
    Route::post('e-comm/orders', [Api\V1\Orders\Parts\EComController::class, 'store'])
        ->name('.orders.store');

    // settings
    Route::get('e-comm/settings', [Api\V1\Settings\EComController::class, 'index'])
        ->name('.settings.index');
});
