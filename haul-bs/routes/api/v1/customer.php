<?php

use App\Foundations\Modules\Permission\Models\Role;
use App\Http\Controllers\Api;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth:'.Role::GUARD_USER,
])->group(function () {
    // customer crud
    Route::get('customers', [Api\V1\Customers\CustomerCrudController::class, 'index'])
        ->name('');
    Route::get('customers/shortlist', [Api\V1\Customers\CustomerCrudController::class, 'shortlist'])
        ->name('.shortlist');
    Route::get('customers/{id}', [Api\V1\Customers\CustomerCrudController::class, 'show'])
        ->name('.show');
    Route::post('customers', [Api\V1\Customers\CustomerCrudController::class, 'store'])
        ->name('.store');
    Route::post('customers/{id}', [Api\V1\Customers\CustomerCrudController::class, 'update'])
        ->name('.update');
    Route::delete('customers/{id}', [Api\V1\Customers\CustomerCrudController::class, 'delete'])
        ->name('.delete');

    Route::post('customers/{customer}/tax-exemption', [Api\V1\Customers\CustomerTaxExemptionController::class, 'store'])
        ->name('.tax-exemption.store');
    Route::post('customers/{customer}/tax-exemption/accepted', [Api\V1\Customers\CustomerTaxExemptionController::class, 'accepted'])
        ->name('.tax-exemption.accepted');
    Route::post('customers/{customer}/tax-exemption/decline', [Api\V1\Customers\CustomerTaxExemptionController::class, 'decline'])
        ->name('.tax-exemption.decline');
    Route::delete('customers/{customer}/tax-exemption/delete', [Api\V1\Customers\CustomerTaxExemptionController::class, 'delete'])
        ->name('.tax-exemption.delete');


    // customer address crud
    Route::post('customers/{id}/addresses', [Api\V1\Customers\AddressCrudController::class, 'store'])
        ->name('.address.store');
    Route::post('customers/{id}/addresses/{addressId}', [Api\V1\Customers\AddressCrudController::class, 'update'])
        ->name('.address.update');
    Route::delete('customers/{id}/addresses/{addressId}', [Api\V1\Customers\AddressCrudController::class, 'delete'])
        ->name('.address.delete');

    // customer upload
    Route::post('customers/{id}/attachments', [Api\V1\Customers\CustomerUploadController::class, 'upload'])
        ->name('.upload-file');
    Route::delete('customers/{id}/attachments/{attachmentId}', [Api\V1\Customers\CustomerUploadController::class, 'delete'])
        ->name('.delete-file');

    // customer comment
    Route::get('customers/{id}/comments', [Api\V1\Customers\CustomerCommentController::class, 'index'])
        ->name('.list-comment');
    Route::post('customers/{id}/comments', [Api\V1\Customers\CustomerCommentController::class, 'store'])
        ->name('.add-comment');
    Route::delete('customers/{id}/comments/{commentId}', [Api\V1\Customers\CustomerCommentController::class, 'delete'])
        ->name('.delete-comment');
});

