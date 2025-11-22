<?php

use App\Foundations\Modules\Permission\Models\Role;
use App\Http\Controllers\Api;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth:'.Role::GUARD_USER,
])->group(function () {
    // bs order report
    Route::get('orders/bs/report', [Api\V1\Orders\BS\ReportController::class, 'index'])
        ->name('.bs.report');
    Route::get('orders/bs/report-total', [Api\V1\Orders\BS\ReportController::class, 'total'])
        ->name('.bs.report-total');

    // bs order crud
    Route::get('orders/bs', [Api\V1\Orders\BS\CrudController::class, 'index'])
        ->name('.bs');
    Route::get('orders/bs/{id}', [Api\V1\Orders\BS\CrudController::class, 'show'])
        ->name('.bs.show');
    Route::post('orders/bs', [Api\V1\Orders\BS\CrudController::class, 'store'])
        ->name('.bs.store');
    Route::post('orders/bs/{id}', [Api\V1\Orders\BS\CrudController::class, 'update'])
        ->name('.bs.update');
    Route::delete('orders/bs/{id}', [Api\V1\Orders\BS\CrudController::class, 'delete'])
        ->name('.bs.delete');

    // bs order upload
    Route::post('orders/bs/{id}/attachments', [Api\V1\Orders\BS\UploadController::class, 'upload'])
        ->name('.bs.upload-file');
    Route::delete('orders/bs/{id}/attachments/{attachmentId}', [Api\V1\Orders\BS\UploadController::class, 'delete'])
        ->name('.bs.delete-file');

    // bs order action
    Route::post('orders/bs/{id}/reassign-mechanic', [Api\V1\Orders\BS\ActionController::class, 'reassignMechanic'])
        ->name('.bs.reassign-mechanic');
    Route::post('orders/bs/{id}/change-status', [Api\V1\Orders\BS\ActionController::class, 'changeStatus'])
        ->name('.bs.change-status');
    Route::get('orders/bs/{id}/view-deleted', [Api\V1\Orders\BS\ActionController::class, 'viewDeleted'])
        ->name('.bs.view-deleted');
    Route::post('orders/bs/{id}/restore', [Api\V1\Orders\BS\ActionController::class, 'restore'])
        ->name('.bs.restore');
    Route::post('orders/bs/{id}/restore-with-editing', [Api\V1\Orders\BS\ActionController::class, 'restoreWithEditing'])
        ->name('.bs.restore-with-editing');
    Route::delete('orders/bs/{id}/permanently', [Api\V1\Orders\BS\ActionController::class, 'forceDelete'])
        ->name('.bs.delete-permanently');

    // bs order invoice
    Route::get('orders/bs/{id}/generate-invoice', [Api\V1\Orders\BS\InvoiceController::class, 'generate'])
        ->name('.bs.generate-invoice');
    Route::post('orders/bs/{id}/send-docs', [Api\V1\Orders\BS\InvoiceController::class, 'send'])
        ->name('.bs.send-docs');

    // bs order comment
    Route::get('orders/bs/{id}/comments', [Api\V1\Orders\BS\CommentController::class, 'index'])
        ->name('.bs.list-comment');
    Route::post('orders/bs/{id}/comments', [Api\V1\Orders\BS\CommentController::class, 'store'])
        ->name('.bs.add-comment');
    Route::delete('orders/bs/{id}/comments/{commentId}', [Api\V1\Orders\BS\CommentController::class, 'delete'])
        ->name('.bs.delete-comment');

    // bs order history
    Route::get('orders/bs/{id}/history', [Api\V1\Orders\BS\HistoryController::class, 'history'])
        ->name('.bs.list-history');
    Route::get('orders/bs/{id}/history-detailed', [Api\V1\Orders\BS\HistoryController::class, 'historyDetailed'])
        ->name('.bs.detailed-history');
    Route::get('orders/bs/{id}/history-users-list', [Api\V1\Orders\BS\HistoryController::class, 'historyUsers'])
        ->name('.bs.history-users-list');

    // bs order payment
    Route::post('orders/bs/{id}/payment', [Api\V1\Orders\BS\PaymentController::class, 'add'])
        ->name('.bs.payment.add');
    Route::delete('orders/bs/{id}/payment/{paymentId}', [Api\V1\Orders\BS\PaymentController::class, 'delete'])
        ->name('.bs.payment.delete');

    // common payment method
    Route::get('orders/payment-methods', [Api\V1\Orders\PaymentMethodController::class, 'index'])
        ->name('.payment-method');

    // parts order shipping
    Route::get('orders/parts/{id}/shipping-methods', [Api\V1\Orders\Parts\ShippingController::class, 'getMethods'])
        ->name('.parts.shipping-methods');

    // parts order catalog
    Route::get('orders/parts/catalog/payment-terms', [Api\V1\Orders\Parts\CatalogController::class, 'paymentTerms'])
        ->name('.parts.catalog.payment-terms');
    Route::get('orders/parts/catalog/payment-methods', [Api\V1\Orders\Parts\CatalogController::class, 'paymentMethods'])
        ->name('.parts.catalog.payment-methods');
    Route::get('orders/parts/catalog/payment-statuses', [Api\V1\Orders\Parts\CatalogController::class, 'paymentStatuses'])
        ->name('.parts.catalog.payment-statuses');
    Route::get('orders/parts/catalog/order-statuses', [Api\V1\Orders\Parts\CatalogController::class, 'orderStatuses'])
        ->name('.parts.catalog.order-statuses');
    Route::get('orders/parts/catalog/sources', [Api\V1\Orders\Parts\CatalogController::class, 'orderSource'])
        ->name('.parts.catalog.source');
    Route::get('orders/parts/catalog/delivery-types', [Api\V1\Orders\Parts\CatalogController::class, 'deliveryType'])
        ->name('.parts.catalog.delivery-types');
    Route::get('orders/parts/catalog/delivery-methods', [Api\V1\Orders\Parts\CatalogController::class, 'deliveryMethod'])
        ->name('.parts.catalog.delivery-methods');
    Route::get('orders/parts/catalog/statuses-to-switch/{id}', [Api\V1\Orders\Parts\CatalogController::class, 'statusesToSwitch'])
        ->name('.parts.catalog.status-to-switch');

    // parts order crud
    Route::get('orders/parts', [Api\V1\Orders\Parts\CrudController::class, 'index'])
        ->name('.parts');
    Route::get('orders/parts/{id}', [Api\V1\Orders\Parts\CrudController::class, 'show'])
        ->name('.parts.show');
    Route::post('orders/parts', [Api\V1\Orders\Parts\CrudController::class, 'store'])
        ->name('.parts.store');
    Route::post('orders/parts/{id}', [Api\V1\Orders\Parts\CrudController::class, 'update'])
        ->name('.parts.update');
    Route::delete('orders/parts/{id}', [Api\V1\Orders\Parts\CrudController::class, 'delete'])
        ->name('.parts.delete');

    // parts order item
    Route::post('orders/parts/{id}/item', [Api\V1\Orders\Parts\ItemController::class, 'add'])
        ->name('.parts.add-item');
    Route::post('orders/parts/{id}/item/{itemId}', [Api\V1\Orders\Parts\ItemController::class, 'update'])
        ->name('.parts.update-item');
    Route::delete('orders/parts/{id}/item/{itemId}', [Api\V1\Orders\Parts\ItemController::class, 'delete'])
        ->name('.parts.delete-item');

    // parts order actions
    Route::post('orders/parts/{id}/checkout', [Api\V1\Orders\Parts\ActionController::class, 'checkout'])
        ->name('.parts.checkout');
    Route::post('orders/parts/{id}/change-status', [Api\V1\Orders\Parts\ActionController::class, 'changeStatus'])
        ->name('.parts.change-status');
    Route::post('orders/parts/{id}/cancel', [Api\V1\Orders\Parts\ActionController::class, 'cancelOrder'])
        ->name('.parts.cancel');
    Route::post('orders/parts/{id}/assign-sales-manager', [Api\V1\Orders\Parts\ActionController::class, 'assignSalesManager'])
        ->name('.parts.assign-sales-manager');
    Route::post('orders/parts/{id}/refunded', [Api\V1\Orders\Parts\ActionController::class, 'refunded'])
        ->name('.parts.refunded');
    Route::post('orders/parts/{id}/delivery/{deliveryId}', [Api\V1\Orders\Parts\ActionController::class, 'editDelivery'])
        ->name('.parts.delivery-update');

    // parts order comment
    Route::get('orders/parts/{id}/comments', [Api\V1\Orders\Parts\CommentController::class, 'index'])
        ->name('.parts.list-comment');
    Route::post('orders/parts/{id}/comments', [Api\V1\Orders\Parts\CommentController::class, 'store'])
        ->name('.parts.add-comment');
    Route::delete('orders/parts/{id}/comments/{commentId}', [Api\V1\Orders\Parts\CommentController::class, 'delete'])
        ->name('.parts.delete-comment');

    // parts order invoice
    Route::get('orders/parts/{id}/generate-invoice', [Api\V1\Orders\Parts\InvoiceController::class, 'generate'])
        ->name('.parts.generate-invoice');
    Route::post('orders/parts/{id}/send-docs', [Api\V1\Orders\Parts\InvoiceController::class, 'send'])
        ->name('.parts.send-docs');

    // parts order payment
    Route::post('orders/parts/{id}/payment', [Api\V1\Orders\Parts\PaymentController::class, 'add'])
        ->name('.parts.payment.add');
    Route::delete('orders/parts/{id}/payment/{paymentId}', [Api\V1\Orders\Parts\PaymentController::class, 'delete'])
        ->name('.parts.payment.delete');
    Route::post('orders/parts/{id}/payment-send-link', [Api\V1\Orders\Parts\PaymentController::class, 'sendLink'])
        ->name('.parts.payment.send-link');

    // bs order history
    Route::get('orders/parts/{id}/history', [Api\V1\Orders\Parts\HistoryController::class, 'history'])
        ->name('.parts.list-history');
    Route::get('orders/parts/{id}/history-detailed', [Api\V1\Orders\Parts\HistoryController::class, 'historyDetailed'])
        ->name('.parts.detailed-history');
    Route::get('orders/parts/{id}/history-users-list', [Api\V1\Orders\Parts\HistoryController::class, 'historyUsers'])
        ->name('.parts.history-users-list');
});

Route::post('orders/parts/payment-callback/paypal', [Api\V1\Payments\PaymentController::class, 'callbackPaypal'])
    ->name('.payment-callback-paypal');
Route::post('orders/parts/payment-callback/stripe', [Api\V1\Payments\PaymentController::class, 'callbackStripe'])
    ->name('.payment-callback-stripe');
