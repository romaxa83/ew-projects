<?php

use App\Foundations\Modules\Permission\Models\Role;
use App\Http\Controllers\Api;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth:'.Role::GUARD_USER,
])->group(function () {
    // inventory crud
    Route::get('inventories', [Api\V1\Inventories\Inventory\CrudController::class, 'index'])
        ->name('');
    Route::get('inventories/shortlist', [Api\V1\Inventories\Inventory\CrudController::class, 'shortlist'])
        ->name('.shortlist');
    Route::get('inventories/shortlist-paginate', [Api\V1\Inventories\Inventory\CrudController::class, 'shortlistPaginate'])
        ->name('.shortlist-paginate');
    Route::get('inventories/list', [Api\V1\Inventories\Inventory\CrudController::class, 'list'])
        ->name('.list');
    Route::get('inventories/payment-methods', [Api\V1\Inventories\Inventory\TransactionController::class, 'paymentMethod'])
        ->name('.transactions.payment-method');
    Route::get('inventories/package-types', [Api\V1\Inventories\Inventory\CatalogController::class, 'packageType'])
        ->name('.package-types');
    Route::get('inventories/report', [Api\V1\Inventories\Inventory\TransactionController::class, 'transactionsReport'])
        ->name('.transactions.report');
    Route::get('inventories/report-total', [Api\V1\Inventories\Inventory\TransactionController::class, 'transactionsReportTotal'])
        ->name('.transactions.report-total');
    Route::get('inventories/export', [Api\V1\Inventories\Inventory\ExportController::class, 'export'])
        ->name('.export');

    Route::get('inventories/{id}', [Api\V1\Inventories\Inventory\CrudController::class, 'show'])
        ->name('.show');
    Route::post('inventories', [Api\V1\Inventories\Inventory\CrudController::class, 'store'])
        ->name('.store');
    Route::post('inventories/{id}', [Api\V1\Inventories\Inventory\CrudController::class, 'update'])
        ->name('.update');
    Route::delete('inventories/{id}', [Api\V1\Inventories\Inventory\CrudController::class, 'delete'])
        ->name('.delete');
    // inventory upload
    Route::post('inventories/{id}/gallery', [Api\V1\Inventories\Inventory\UploadController::class, 'upload'])
        ->name('.upload-image-gallery');
    Route::delete('inventories/{id}/images/{imageId}', [Api\V1\Inventories\Inventory\UploadController::class, 'delete'])
        ->name('.delete-file');
    // inventory history
    Route::get('inventories/{id}/history', [Api\V1\Inventories\Inventory\HistoryController::class, 'history'])
        ->name('.list-history');
    Route::get('inventories/{id}/history-detailed', [Api\V1\Inventories\Inventory\HistoryController::class, 'historyDetailed'])
        ->name('.detailed-history');
    Route::get('inventories/{id}/history-users-list', [Api\V1\Inventories\Inventory\HistoryController::class, 'historyUsers'])
        ->name('.history-users-list');
    // inventory transaction
    Route::get('inventories/{id}/transactions', [Api\V1\Inventories\Inventory\TransactionController::class, 'index'])
        ->name('.transactions');
    Route::get('inventories/{id}/reserve', [Api\V1\Inventories\Inventory\TransactionController::class, 'reserved'])
        ->name('.transactions.reserved');
    Route::post('inventories/{id}/purchase', [Api\V1\Inventories\Inventory\TransactionController::class, 'purchase'])
        ->name('.transactions.purchase');
    Route::post('inventories/{id}/sold', [Api\V1\Inventories\Inventory\TransactionController::class, 'sold'])
        ->name('.transactions.sold');
    Route::get('inventories/transactions/{id}/generate-payment-receipt', [Api\V1\Inventories\Inventory\TransactionController::class, 'generatePaymentReceipt'])
        ->name('.transactions.generate-payment-receipt');
    Route::get('inventories/transactions/{id}/generate-invoice', [Api\V1\Inventories\Inventory\TransactionController::class, 'generateInvoice'])
        ->name('.transactions.generate-invoice');

    // inventory unit crud
    Route::get('inventory-units', [Api\V1\Inventories\Unit\CrudController::class, 'index'])
        ->name('.unit');
    Route::get('inventory-units/{id}', [Api\V1\Inventories\Unit\CrudController::class, 'show'])
        ->name('.unit.show');
    Route::post('inventory-units', [Api\V1\Inventories\Unit\CrudController::class, 'store'])
        ->name('.unit.store');
    Route::post('inventory-units/{id}', [Api\V1\Inventories\Unit\CrudController::class, 'update'])
        ->name('.unit.update');
    Route::delete('inventory-units/{id}', [Api\V1\Inventories\Unit\CrudController::class, 'delete'])
        ->name('.unit.delete');

    // inventory category crud
    Route::get('inventory-categories', [Api\V1\Inventories\Category\CrudController::class, 'index'])
        ->name('.category');
    Route::get('inventory-categories/{id}', [Api\V1\Inventories\Category\CrudController::class, 'show'])
        ->name('.category.show');
    Route::post('inventory-categories', [Api\V1\Inventories\Category\CrudController::class, 'store'])
        ->name('.category.store');
    Route::post('inventory-categories/{id}', [Api\V1\Inventories\Category\CrudController::class, 'update'])
        ->name('.category.update');
    Route::delete('inventory-categories/{id}', [Api\V1\Inventories\Category\CrudController::class, 'delete'])
        ->name('.category.delete');
    // inventory category upload
    Route::delete('inventory-categories/{id}/images/{imageId}', [Api\V1\Inventories\Category\UploadController::class, 'delete'])
        ->name('.category.delete-file');
    // inventory category action
    Route::get('inventory-categories-tree', [Api\V1\Inventories\Category\ActionController::class, 'listAsTree'])
        ->name('.category.list-tree');
    Route::get('inventory-categories-tree-select', [Api\V1\Inventories\Category\ActionController::class, 'listAsTreeForSelect'])
        ->name('.category.list-tree-select');

    // inventory brand crud
    Route::get('inventory-brands', [Api\V1\Inventories\Brand\CrudController::class, 'index'])
        ->name('.brand');
    Route::get('inventory-brands/shortlist', [Api\V1\Inventories\Brand\CrudController::class, 'shortlist'])
        ->name('.brand.shortlist');
    Route::get('inventory-brands/{id}', [Api\V1\Inventories\Brand\CrudController::class, 'show'])
        ->name('.brand.show');
    Route::post('inventory-brands', [Api\V1\Inventories\Brand\CrudController::class, 'store'])
        ->name('.brand.store');
    Route::post('inventory-brands/{id}', [Api\V1\Inventories\Brand\CrudController::class, 'update'])
        ->name('.brand.update');
    Route::delete('inventory-brands/{id}', [Api\V1\Inventories\Brand\CrudController::class, 'delete'])
        ->name('.brand.delete');

    // inventory feature crud
    Route::get('inventory-features', [Api\V1\Inventories\Feature\CrudController::class, 'index'])
        ->name('.feature');
    Route::get('inventory-features/shortlist', [Api\V1\Inventories\Feature\CrudController::class, 'shortlist'])
        ->name('.feature.shortlist');
    Route::get('inventory-features/{id}', [Api\V1\Inventories\Feature\CrudController::class, 'show'])
        ->name('.feature.show');
    Route::post('inventory-features', [Api\V1\Inventories\Feature\CrudController::class, 'store'])
        ->name('.feature.store');
    Route::post('inventory-features/{id}', [Api\V1\Inventories\Feature\CrudController::class, 'update'])
        ->name('.feature.update');
    Route::delete('inventory-features/{id}', [Api\V1\Inventories\Feature\CrudController::class, 'delete'])
        ->name('.feature.delete');

    // inventory feature value crud
    Route::get('inventory-feature-values', [Api\V1\Inventories\FeatureValue\CrudController::class, 'index'])
        ->name('.feature.value');
    Route::get('inventory-feature-values/shortlist', [Api\V1\Inventories\FeatureValue\CrudController::class, 'shortlist'])
        ->name('.feature.value.shortlist');
    Route::get('inventory-feature-value/{id}', [Api\V1\Inventories\FeatureValue\CrudController::class, 'show'])
        ->name('.feature.value.show');
    Route::post('inventory-feature-values', [Api\V1\Inventories\FeatureValue\CrudController::class, 'store'])
        ->name('.feature.value.store');
    Route::post('inventory-feature-values/{id}', [Api\V1\Inventories\FeatureValue\CrudController::class, 'update'])
        ->name('.feature.value.update');
    Route::delete('inventory-feature-values/{id}', [Api\V1\Inventories\FeatureValue\CrudController::class, 'delete'])
        ->name('.feature.value.delete');
});
