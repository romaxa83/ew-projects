<?php

use App\Http\Controllers\V1\Carrier\Intl\LanguageController;
use App\Http\Controllers\V1\Carrier\Library\LibraryDocumentController;
use App\Http\Controllers\V1\Carrier\News\NewsController;
use App\Http\Controllers\V1\Carrier\Orders\OrderController;
use App\Http\Controllers\V1\Carrier\Orders\PaymentMethodController;
use App\Http\Controllers\V1\Carrier\QuestionAnswer\QuestionAnswerController;
use App\Http\Controllers\V1\Carrier\Users\ChangeEmailController;
use App\Http\Controllers\V1\Carrier\Users\ProfileController;
use App\Http\Controllers\V1\CarrierMobile\Billing\SubscriptionController;
use App\Http\Controllers\V1\CarrierMobile\Library\LibraryDocumentMobileController;
use App\Http\Controllers\V1\CarrierMobile\Orders\OrderMobileController;
use App\Http\Controllers\ApiController;

Route::get('', [ApiController::class, 'api'])
    ->name('carrier-mobile');

Route::post('change-email/confirm-email', [ChangeEmailController::class, 'confirmEmail']);
Route::post('change-email/cancel-request', [ChangeEmailController::class, 'cancelRequest']);

Route::group(
    ['middleware' => 'auth:api'],
    function () {
        Route::get('subscription-info', [SubscriptionController::class, 'subscriptionInfo'])
            ->name('subscription-info');

        Route::get('change-email/if-requested', [ChangeEmailController::class, 'ifRequested']);
        Route::post('change-email', [ChangeEmailController::class, 'store']);
        Route::delete('change-email/{change_email}', [ChangeEmailController::class, 'destroy']);

        Route::get('library', [LibraryDocumentMobileController::class, 'index']);
        Route::post('library', [LibraryDocumentMobileController::class, 'store']);
        Route::delete('library/{library}', [LibraryDocumentController::class, 'destroy']);

        Route::get('question-answer', [QuestionAnswerController::class, 'index']);

        Route::get('news', [NewsController::class, 'index']);
        Route::get('news/{news}', [NewsController::class, 'show']);

        Route::get('profile', [ProfileController::class, 'show']);
        Route::put('profile', [ProfileController::class, 'update']);
        Route::post('profile/upload-photo', [ProfileController::class, 'uploadPhoto']);
        Route::delete('profile/delete-photo', [ProfileController::class, 'deletePhoto']);
        Route::put('profile/change-password', [ProfileController::class, 'changePassword']);
        Route::put('profile/set-fcm-token', [ProfileController::class, 'setFcmToken']);
        Route::get('profile/company-info', [ProfileController::class, 'companyInfo']);
        Route::put('profile/change-language', [LanguageController::class, 'changeLanguage']);
        Route::get('profile/selected-language', [LanguageController::class, 'getSelected']);
    }
);

Route::namespace('Orders')
    ->prefix('orders')
    ->middleware(['auth:api'])
    ->group(
        function () {
            Route::get('payment-methods', [PaymentMethodController::class, 'forDriver']);

            Route::post('{order}/vehicles/{vehicle}/inspect-vin', [OrderMobileController::class, 'inspectVin'])
                ->name('mobile.orders.vehicles.inspect-vin');

            Route::post(
                '{order}/vehicles/{vehicle}/inspect-pickup-damage',
                [OrderMobileController::class, 'inspectPickupDamage']
            )
                ->name('mobile.orders.vehicles.inspect-pickup-damage');

            Route::post(
                '{order}/vehicles/{vehicle}/inspect-pickup-exterior',
                [OrderMobileController::class, 'inspectPickupExterior']
            )
                ->name('mobile.orders.vehicles.inspect-pickup-exterior');

            Route::post(
                '{order}/vehicles/{vehicle}/delete-pickup-photo',
                [OrderMobileController::class, 'deletePickupPhoto']
            );
            Route::post(
                '{order}/vehicles/{vehicle}/inspect-pickup-interior',
                [OrderMobileController::class, 'inspectPickupInterior']
            );

            Route::post(
                '{order}/vehicles/{vehicle}/inspect-delivery-damage',
                [OrderMobileController::class, 'inspectDeliveryDamage']
            )
                ->name('mobile.orders.vehicles.inspect-delivery-damage');

            Route::post(
                '{order}/vehicles/{vehicle}/inspect-delivery-exterior',
                [OrderMobileController::class, 'inspectDeliveryExterior']
            )
                ->name('mobile.orders.vehicles.inspect-delivery-exterior');

            Route::post(
                '{order}/vehicles/{vehicle}/delete-delivery-photo',
                [OrderMobileController::class, 'deleteDeliveryPhoto']
            );
            Route::post(
                '{order}/vehicles/{vehicle}/inspect-delivery-interior',
                [OrderMobileController::class, 'inspectDeliveryInterior']
            );

            Route::post('{order}/pickup-signature', [OrderMobileController::class, 'pickupSignature'])
                ->name('mobile.orders.pickup-signature');

            Route::post('{order}/delivery-signature', [OrderMobileController::class, 'deliverySignature'])
                ->name('mobile.orders.delivery-signature');

            Route::get('{order}/vehicles/{vehicle}', [OrderMobileController::class, 'getVehicle']);
            Route::put('{order}/send-in-work', [OrderMobileController::class, 'sendInWork']);

            Route::post('{order}/add-payment-data', [OrderMobileController::class, 'addPaymentData'])
                ->name('orders.add-payment-data');

            Route::post('{order}/documents', [OrderMobileController::class, 'addDocument']);
            Route::delete('{order}/documents/{id}', [OrderMobileController::class, 'deleteDocument']);
            Route::post('{order}/photos', [OrderMobileController::class, 'addPhoto']);
            Route::delete('{order}/photos/{id}', [OrderMobileController::class, 'deletePhoto']);
            Route::post('{order}/comments', [OrderMobileController::class, 'addComment']);
            Route::put('{order}/seen-by-driver', [OrderMobileController::class, 'markSeenByDriver']);

            Route::put('{order}/send-docs', [OrderMobileController::class, 'sendDocs'])
                ->name('orders.send-docs');

            Route::get('{order}/get-invoice', [OrderController::class, 'getInvoice']);
            Route::get('{order}/get-bol', [OrderController::class, 'getBol']);

            Route::post('{order}/attachments', [OrderController::class, 'addAttachment']);
            Route::delete('{order}/attachments/{id}', [OrderController::class, 'deleteAttachment']);
        }
    );

Route::group(
    ['middleware' => 'auth:api'],
    function () {
        Route::apiResource('orders', OrderMobileController::class)
            ->except(['store', 'update', 'destroy'])
            ->names(
                [
                    'index' => 'order-mobile.index',
                    'show' => 'order-mobile.show',
                ]
            );
    }
);
