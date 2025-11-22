<?php

use App\Http\Controllers\Api\Billing\BillingController;
use App\Http\Controllers\Api\Billing\InvoiceController;
use App\Http\Controllers\Api\Broadcasting\BroadcastController;
use App\Http\Controllers\Api\FileBrowser\FileBrowserController;
use App\Http\Controllers\Api\GPS;
use App\Http\Controllers\Api\Logs\EmailDeliveryLogController;
use App\Http\Controllers\Api\Orders\OrderController;
use App\Http\Controllers\Api\Orders\OrderMobileController;
use App\Http\Controllers\Api\Reports\DriverTripReportController;
use App\Http\Controllers\Api\Users\UserController;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\V1\Carrier\Carrier\CarrierController;
use App\Http\Middleware\CheckSubscription;
use App\Http\Middleware\EmailDeliveryLog;
use App\Models\Orders\Payment;

Route::get('', [ApiController::class, 'api'])
    ->name('api');

Route::post('email-delivery-logs', [EmailDeliveryLogController::class, 'receive'])
    ->middleware(EmailDeliveryLog::class)
    ->name('email-delivery-logs');

Route::post('login', 'Api\AuthController@login')->name('auth.login');
Route::post('refresh-token', 'Api\AuthController@refreshToken')->name('auth.refresh-token');
Route::post('password-forgot', 'Api\ForgotPasswordController@sendResetLinkEmail')->name('password.forgot');
Route::post('password-set', 'Api\ResetPasswordController@reset')->name('password.reset');

Route::middleware('throttleIp:1200,1')->group(
    function () {
        Route::get('translates-list', 'Api\Translates\TranslateController@list')->name('translates.list');
    }
);

// vehicle types list
Route::get('orders/vehicle-types', 'Api\Orders\OrderController@vehicleTypes');

// public BOL
Route::namespace('Api\Orders')
    ->prefix('orders/public-bol')
    ->group(
        function () {
            Route::get('{token}/vehicle/{vehicle_id}/photos', [OrderController::class, 'publicVehiclePhotos'])
                ->name('orders.vehicle.photos');

            Route::get('{token}/photos', [OrderController::class,'publicOrderPhotos']);
            Route::get('{token}', [OrderController::class, 'publicBol'])
                ->name('orders.public-bol');

            Route::post('{token}', [OrderController::class, 'signPublicBol'])
                ->name('orders.sign-public-bol');
        }
    );

// public PDF
Route::namespace('Api\Orders')
    ->prefix('orders/public-pdf')
    ->group(
        function () {
            Route::get('{token}/bol.pdf', [OrderController::class, 'publicPdfBol'])
                ->name('orders.public.pdf-bol');

            Route::get('{token}/{recipient}/invoice.pdf', [OrderController::class, 'publicPdfInvoice'])
                ->where('recipient', '^' . Payment::PAYER_CUSTOMER . '|' . Payment::PAYER_BROKER . '$');
        }
    );

// public payroll
Route::namespace('Api\Payrolls')
    ->prefix('payrolls')
    ->group(
        function () {
            Route::get('{public_token}/payroll.pdf', 'PayrollController@getPdf');
        }
    );

// public invoice
Route::namespace('Api\Billing')
    ->prefix('billing')
    ->group(
        function () {
            Route::get('invoices/{public_token}/invoice.pdf', 'InvoiceController@getPdf');
        }
    );

// timezones
Route::namespace('Api')
    ->group(
        function () {
            Route::get('timezone-list', 'TimezoneController@timezoneList');
        }
    );

Route::prefix('filebrowser')
    ->name('filebrowser.')
    ->group(
        function () {
            Route::post('browse', [FileBrowserController::class, 'browse'])->name('browse');
            Route::post('upload', [FileBrowserController::class, 'upload'])->name('upload');
        }
    );

Route::middleware('auth:api')
    ->group(
        function () {
            Route::post('logout', 'Api\AuthController@logout')->name('auth.logout');
        }
    );

Route::middleware(
    [
        'auth:api',
        CheckSubscription::class,
    ]
)
    ->group(
        function () {
            //billing
            Route::prefix('billing')
                ->name('billing.')
                ->group(
                    function () {
                        Route::get('subscription-info', [BillingController::class, 'subscriptionInfo'])
                            ->name('subscription-info');
                        Route::get('info', [BillingController::class, 'billingInfo'])
                            ->name('billing-info');
                        Route::post('payment-method', [BillingController::class, 'updatePaymentMethod'])
                            ->name('update-payment-method');
                        Route::post('payment-contact', [BillingController::class, 'updatePaymentContact'])
                            ->name('update-payment-contact');
                        Route::delete('payment-contact', [BillingController::class, 'deletePaymentContact'])
                            ->name('delete-payment-contact');
                        Route::put('subscribe', [BillingController::class, 'subscribe'])
                            ->name('subscribe');
                        Route::put('unsubscribe', [BillingController::class, 'unsubscribe'])
                            ->name('unsubscribe');
                        Route::put('invoices/{invoice}/pay', [InvoiceController::class, 'payInvoice'])
                            ->name('pay-invoice');
                        Route::apiResource('invoices', 'Api\Billing\InvoiceController')
                            ->except('store', 'update', 'destroy');
                    }
                );

            //profile
            Route::get('profile', 'Api\Users\ProfileController@show')->name('profile.show');
            Route::put('profile', 'Api\Users\ProfileController@update')->name('profile.update');
            Route::post('profile/upload-photo', 'Api\Users\ProfileController@uploadPhoto')->name('profile.upload-photo');
            Route::delete('profile/delete-photo', 'Api\Users\ProfileController@deletePhoto')->name('profile.delete-photo');
            Route::put('profile/change-password', 'Api\Users\ProfileController@changePassword')->name(
                'profile.change-password'
            );
            Route::put('profile/set-fcm-token', 'Api\Users\ProfileController@setFcmToken');
            // Get default permissions by role name
            Route::get('permissions/{roleName?}', 'Api\Permissions\PermissionController@show')->name('permissions.show');
            // languages
            Route::get('languages/selected', 'Api\LanguageController@getSelected');
            Route::apiResource('languages', 'Api\LanguageController')->except('store', 'update', 'destroy');
            Route::put('change-language', 'Api\LanguageController@changeLanguage')->name('languages.change-language');
            // locations
            Route::apiResource('states', 'Api\Locations\StateController');
            Route::get('states-list', 'Api\Locations\StateController@list')->name('states.list');
            Route::apiResource('cities', 'Api\Locations\CityController');
            Route::get('city-autocomplete', 'Api\Locations\CityController@autocomplete');
            //roles
            Route::get('roles-list', 'Api\Roles\RoleController@list')->name('roles.list');
            Route::get('roles/{role}', 'Api\Roles\RoleController@show')->name('roles.show');
            // contacts section
            Route::get('contacts/types', 'Api\Contacts\ContactController@contactTypes');
            Route::get('contacts/search', 'Api\Contacts\ContactController@search');
            Route::post('contacts/import', 'Api\Contacts\ContactController@import');
            Route::apiResource('contacts', 'Api\Contacts\ContactController');

            // carrier profile
            Route::namespace('Api\Carrier')
                ->prefix('carrier')
                ->name('carrier.')
                ->group(
                    function () {
                        Route::get('', 'CarrierController@show')
                            ->name('show');
                        Route::put('', 'CarrierController@update')
                            ->name('update');

                        Route::post('info/upload-photo', 'CarrierController@uploadInfoPhoto')
                            ->name('upload-info-photo');
                        Route::delete('info/delete-photo', 'CarrierController@deleteInfoPhoto')
                            ->name('delete-info-photo');

                        Route::post('w9/upload-photo', 'CarrierController@uploadW9Photo')
                            ->name('upload-w9-photo');
                        Route::delete('w9/delete-photo', 'CarrierController@deleteW9Photo')
                            ->name('delete-w9-photo');
                        Route::get('w9/get-photo', 'CarrierController@getW9Photo')
                            ->name('get-w9-photo');

                        Route::post('usdot/upload-photo', 'CarrierController@uploadUsdotPhoto')
                            ->name('upload-usdot-photo');
                        Route::delete('usdot/delete-photo', 'CarrierController@deleteUsdotPhoto')
                            ->name('delete-usdot-photo');
                        Route::get('usdot/get-photo', 'CarrierController@getUsdotPhoto')
                            ->name('get-usdot-photo');

                        Route::get('insurance', 'CarrierController@getInsurance')
                            ->name('get-insurance');
                        Route::put('insurance', 'CarrierController@updateInsurance')
                            ->name('update-insurance');
                        Route::post('insurance/upload-photo', 'CarrierController@uploadInsurancePhoto')
                            ->name('upload-insurance-photo');
                        Route::delete('insurance/delete-photo', 'CarrierController@deleteInsurancePhoto')
                            ->name('delete-insurance-photo');

                        Route::get('notification', 'CarrierController@getNotificationSettings')
                            ->name('notification-get');
                        Route::put('notification', 'CarrierController@updateNotificationSettings')
                            ->name('notification-update');
                        Route::post('send-destroy-notification', [CarrierController::class, 'sendDestroyNotification'])
                            ->name('send-destroy-notification');
                        Route::post('set-destroy', [CarrierController::class, 'setDestroy'])
                            ->name('set-destroy')->withoutMiddleware(CheckSubscription::class);
                    }
                );

            // library
            Route::apiResource('library', 'Api\Library\LibraryDocumentController')
                ->except(
                    [
                        'update',
                    ]
                );
            Route::get('library/documents/{policy}', 'Api\Library\LibraryDocumentController@documentsList')
                ->where('policy', '(public|private)');
            Route::get('library/documents/search', 'Api\Library\LibraryDocumentController@search');
            Route::post('mobile/library', 'Api\Library\LibraryDocumentMobileController@store');
            Route::get('mobile/library', 'Api\Library\LibraryDocumentMobileController@index');


            Route::apiResource('question-answer', 'Api\QuestionAnswer\QuestionAnswerController')
                ->except(
                    [
                        'show',
                    ]
                )->parameters(
                    [
                        'question-answer' => 'questionAnswer'
                    ]
                );
            Route::get(
                'question-answer/{questionAnswer}/full',
                'Api\QuestionAnswer\QuestionAnswerController@showFull'
            )
                ->name('question-answer.full');
            // driver reports
            Route::namespace('Api\Reports')
                ->prefix('driver-trip-report')
                ->name('driver-trip-report.')
                ->group(
                    function () {
                        Route::post('{driver_trip_report}/update', 'DriverTripReportController@update')->name('update');
                        Route::apiResource('', 'DriverTripReportController')
                            ->parameters(['' => 'driver_trip_report'])->except(['update']);
                        Route::delete('{driver_trip_report}/file/{id}', [DriverTripReportController::class, 'deleteFile'])
                            ->name('delete-file');
                    }
                );

            // payment methods
            Route::namespace('Api\Orders')
                ->group(
                    function () {
                        Route::get('payment-methods/for-order', 'PaymentMethodController@forOrder');
                        Route::get('payment-methods/for-driver', 'PaymentMethodController@forDriver');
                    }
                );

            // payrolls
            Route::namespace('Api\Payrolls')
                ->group(
                    function () {
                        Route::post('payrolls/{payroll}/send-pdf', 'PayrollController@sendPdf');
                        Route::post('payrolls/prepare', 'PayrollController@prepare')
                            ->name('payrolls.prepare');
                        Route::put('payrolls/mark-as-paid', 'PayrollController@markAsPaid')
                            ->name('payrolls.mark-as-paid');
                        Route::delete('payrolls/delete-many', 'PayrollController@deleteMany')
                            ->name('payrolls.delete-many');
                        Route::apiResource('payrolls', 'PayrollController')
                            ->except(['destroy']);
                    }
                );

            // orders
            Route::namespace('Api\Orders')
                ->prefix('orders')
                ->middleware('throttleIp:1200,1')
                ->group(
                    function () {
                        Route::post('{order}/vehicles', 'OrderController@addVehicle');
                        Route::put('{order}/vehicles/{vehicle}', 'OrderController@editVehicle');
                        Route::delete('{order}/vehicles/{vehicle}', [OrderController::class, 'deleteVehicle'])
                            ->name('orders.delete-vehicle');
                        //Route::post('{order}/expenses', 'OrderController@addExpense');
                        //Route::post('{order}/expenses/{expense}', 'OrderController@editExpense');
                        Route::delete('{order}/expenses/{expense}', 'OrderController@deleteExpense');
                        Route::delete('{order}/bonuses/{bonus}', 'OrderController@deleteBonus');

                        Route::post('{order}/attachments', [OrderController::class, 'addAttachment'])
                            ->name('orders.attachments');
                        Route::delete('{order}/attachments/{id}', [OrderController::class, 'deleteAttachment'])
                            ->name('orders.delete-attachments');

                        Route::delete('{order}/driver-documents/{id}', 'OrderController@deleteDocument');
                        Route::delete('{order}/driver-photos/{id}', 'OrderController@deletePhoto');
                        Route::get('{order_id}/comments', 'OrderCommentController@index')->name('order-comments.index');

                        Route::get('total', [OrderController::class, 'orderTotal'])
                            ->name('orders.total');
                        Route::get('companies-list', [OrderController::class, 'orderCompanyList'])
                            ->name('orders.companies-list');

                        Route::apiResource('{order}/comments', 'OrderCommentController')->except(
                            [
                                'index',
                                'update',
                            ]
                        )->names(
                            [
                                'store' => 'order-comments.store',
                                'show' => 'order-comments.show',
                                'destroy' => 'order-comments.destroy',
                            ]
                        );
                        Route::get('{order}/print', 'OrderController@printOrder');
                        Route::get('{order}/get-invoice/{recipient}', 'OrderController@getInvoice')
                            ->where('recipient', '^(' . Payment::PAYER_CUSTOMER . '|' . Payment::PAYER_BROKER . ')$');

                        Route::get('{order}/get-bol', 'OrderController@getBol');

                        Route::post('send-docs', [OrderController::class, 'sendDocs'])
                            ->name('orders.send-docs');

                        Route::post('{order}/send-signature-link', [OrderController::class, 'sendSignatureLink'])
                            ->name('orders.send-signature-link');

                        Route::get('{order}/available-invoices', [OrderController::class, 'availableInvoices'])
                            ->name('orders.available-invoices');

                        Route::put('{order}/mark-reviewed', 'OrderController@markReviewed');
                        Route::get('{order}/get-drivers-list', 'OrderController@getDriversForOrder');
                        Route::put('{order}/assign-driver', 'OrderController@assignDriver')->name('orders.assign-driver');
                        Route::delete('{order_id}/permanently', 'OrderController@deletePermanently')
                            ->name('orders.delete-permanently');
                        Route::put('{order_id}/restore', 'OrderController@restoreOrder')
                            ->name('orders.restore');
                        Route::get('{order_id}/view-deleted', 'OrderController@viewDeletedOrder');
                        Route::get('{order}/duplicate', 'OrderController@duplicateOrder')->name('orders.duplicate-order');
                        Route::post('{order}/split', 'OrderController@splitOrder')->name('orders.split-order');
                        Route::post('{order}/update', 'OrderController@update')->name('orders.update-order');
                        Route::get('{order_id}/history', 'OrderController@orderHistory')->name('order.histories');
                        Route::get('{order_id}/history-detailed', 'OrderController@orderHistoryDetailed')->name('order.histories-detailed');

                        Route::post('{order}/payment-stage', 'OrderController@addPaymentStage')->name('order.add-payment-stage');
                        Route::delete('{order}/payment-stage/{paymentStage}', 'OrderController@deletePaymentStage')->name('order.delete-payment-stage');

                        Route::put('{order}/mark-broker-paid', 'OrderController@markBrokerPaid')->name('order.mark-broker-paid');
                        Route::put('{order}/mark-broker-unpaid', 'OrderController@markBrokerUnpaid')->name('order.mark-broker-unpaid');

                        Route::get('{order_id}/history-users-list', 'OrderController@orderHistoryUsers')->name('order.history-users-list');
                        Route::put('{order}/take', 'OrderController@take')
                            ->name('orders.take');

                        Route::put('{order}/change-deduct-from-driver', [OrderController::class, 'changeDeductFromDriver'])
                            ->name('orders.change-deduct-from-driver');

                        Route::put('{order}/release', 'OrderController@release')
                            ->name('orders.release');

                        Route::put('{order}/change-status', 'OrderController@changeStatus')->name('orders.change-order-status');
                        Route::get('expense-types', 'OrderController@expenseTypes');
                        Route::get('contact-types', 'OrderController@contactTypes');
                        Route::get('vehicles-for-filter', 'OrderController@vehiclesForFilter');
                        Route::get('same-load-id', 'OrderController@sameLoadId')->name('orders.same-load-id');
                        Route::get('same-vin', 'OrderController@sameVin')->name('orders.same-vin');
                        Route::get('offers', 'OrderController@offers');
                        Route::get('export', [OrderController::class, 'export'])
                            ->name('orders.export');
                    }
                );

            Route::namespace('Api\Orders')
                ->group(
                    function () {
                        Route::apiResource('orders', 'OrderController');
                    }
                );

            // vehicle db
            Route::namespace('Api\VehicleDB')
                ->group(
                    function () {
                        Route::get('vehicle-db/makes', 'VehicleDBController@getMakes');
                        Route::get('vehicle-db/models', 'VehicleDBController@getModels');
                        Route::get('vehicle-db/decode-vin', 'VehicleDBController@decodeVin');
                        Route::get('vehicle-db/unit-number/shortlist', 'VehicleDBController@shortlist');
                    }
                );

            // expense types
            Route::namespace('Api\Lists')
                ->name('lists.')
                ->group(
                    function () {
                        Route::get('expense-types/list', 'ExpenseTypeController@list')
                            ->name('expense-types-list');
                        Route::apiResource('expense-types', 'ExpenseTypeController');
                    }
                );

            // bonus types
            Route::namespace('Api\Lists')
                ->name('lists.')
                ->group(
                    function () {
                        Route::get('bonus-types/list', 'BonusTypeController@list')
                            ->name('bonus-types-list');
                        Route::apiResource('bonus-types', 'BonusTypeController');
                    }
                );

            // news
            Route::namespace('Api\News')
                ->group(
                    function () {
                        Route::put('news/{news}/activate', 'NewsController@activate');
                        Route::put('news/{news}/deactivate', 'NewsController@deactivate');
                        Route::post('news/{news}/update', 'NewsController@update');
                        Route::delete('news/{news}/delete-photo', 'NewsController@deletePhoto');
                        Route::get('news/{news}/full', 'NewsController@showFull');
                        Route::apiResource('news', 'NewsController')->except(['update']);
                    }
                );

            // history
            Route::namespace('Api\History')
                ->group(
                    function () {
                        Route::get('history', 'HistoryController@index');
                        Route::get('history/{history}', 'HistoryController@show')->name('history.show');
                    }
                );

            // Dashboard
            Route::namespace('Api\Dashboard')
                ->group(
                    function () {
                        Route::get('dashboard', 'DashboardController@index');
                    }
                );

            // mobile endpoints
            Route::namespace('Api\Orders')
                ->prefix('mobile/orders')
                ->name('mobile.orders.')
                ->group(
                    function () {
                        // inspection
                        Route::post(
                            '{order}/vehicles/{vehicle}/inspect-vin',
                            'OrderMobileController@inspectVin'
                        )
                            ->name('vehicles.inspect-vin');

                        Route::post(
                            '{order}/vehicles/{vehicle}/inspect-pickup-damage',
                            'OrderMobileController@inspectPickupDamage'
                        )
                            ->name('vehicles.inspect-pickup-damage');

                        Route::post(
                            '{order}/vehicles/{vehicle}/inspect-pickup-exterior',
                            [
                                OrderMobileController::class,
                                'inspectPickupExterior'
                            ]
                        )
                            ->name('vehicles.inspect-pickup-exterior');

                        Route::post(
                            '{order}/vehicles/{vehicle}/delete-pickup-photo',
                            'OrderMobileController@deletePickupPhoto'
                        );
                        Route::post(
                            '{order}/vehicles/{vehicle}/inspect-pickup-interior',
                            'OrderMobileController@inspectPickupInterior'
                        )
                            ->name('vehicles.inspect-pickup-interior');

                        Route::post(
                            '{order}/vehicles/{vehicle}/inspect-delivery-damage',
                            'OrderMobileController@inspectDeliveryDamage'
                        )
                            ->name('vehicles.inspect-delivery-damage');

                        Route::post(
                            '{order}/vehicles/{vehicle}/inspect-delivery-exterior',
                            [
                                OrderMobileController::class,
                                'inspectDeliveryExterior'
                            ]
                        )
                            ->name('vehicles.inspect-delivery-exterior');

                        Route::post(
                            '{order}/vehicles/{vehicle}/delete-delivery-photo',
                            'OrderMobileController@deleteDeliveryPhoto'
                        );
                        Route::post(
                            '{order}/vehicles/{vehicle}/inspect-delivery-interior',
                            'OrderMobileController@inspectDeliveryInterior'
                        )
                            ->name('vehicles.inspect-delivery-interior');

                        Route::post('{order}/pickup-signature', [OrderMobileController::class, 'pickupSignature'])
                            ->name('pickup-signature');

                        Route::post('{order}/delivery-signature', [OrderMobileController::class, 'deliverySignature'])
                            ->name('delivery-signature');
                        // manage order
                        Route::get('{order}/vehicles/{vehicle}', 'OrderMobileController@getVehicle')
                            ->name('get-vehicle');

                        Route::put('{order}/send-in-work', 'OrderMobileController@sendInWork');

                        Route::post('{order}/documents', 'OrderMobileController@addDocument');
                        Route::delete('{order}/documents/{id}', 'OrderMobileController@deleteDocument');
                        Route::post('{order}/photos', 'OrderMobileController@addPhoto');
                        Route::delete('{order}/photos/{id}', 'OrderMobileController@deletePhoto');
                        Route::post('{order}/comments', 'OrderMobileController@addComment');
                        Route::put('{order}/seen-by-driver', 'OrderMobileController@markSeenByDriver');

                        Route::put('{order}/send-docs', [OrderMobileController::class, 'sendDocs'])
                            ->name('send-docs');
                    }
                );

            Route::namespace('Api\Orders')
                ->group(
                    function () {
                        Route::apiResource('mobile/orders', 'OrderMobileController')
                            ->except(['store', 'update', 'destroy'])
                            ->names(
                                [
                                    'index' => 'order-mobile.index',
                                    'show' => 'order-mobile.show',
                                ]
                            );
                    }
                );

            // alerts
            Route::namespace('Api\Alerts')
                ->group(
                    function () {
                        Route::apiResource('alerts', 'AlertController')
                            ->except(['store', 'update', 'show'])
                            ->names(
                                [
                                    'index' => 'alerts.index',
                                    'destroy' => 'alerts.destroy',
                                ]
                            );
                    }
                );

            // change user email
            Route::namespace('Api\Users')
                ->group(
                    function () {
                        Route::get('change-email/if-requested', 'ChangeEmailController@ifRequested');
                        Route::apiResource('change-email', 'ChangeEmailController')->only(['store', 'destroy']);
                    }
                );

            Route::namespace('Api\Users')
                ->group(
                    function () {
                        /** @see UserController::roleList() */
                        Route::get('users/role-list', 'UserController@roleList')->name('users.role-list');
                        /** @see UserController::dispatchers() */
                        Route::get('users/dispatchers', 'UserController@dispatchers')->name('users.dispatchers');
                        /** @see UserController::changeStatus() */
                        Route::put('users/{user}/change-status', 'UserController@changeStatus')->name('users.change-status');
                        /** @see UserController::uploadPhoto() */
                        Route::post('users/{user}/upload-photo', 'UserController@uploadPhoto')->name('users.upload-photo');
                        /** @see UserController::deletePhoto() */
                        Route::delete('users/{user}/delete-photo', 'UserController@deletePhoto')->name('users.delete-photo');

                        Route::post('users/{user}/change-password', [UserController::class, 'changePassword'])
                            ->name('users.change-password');

                        /** @see UserController::orderCreatorsList() */
                        Route::get('users/order-creators-list', 'UserController@orderCreatorsList')
                            ->name('users.order-creators-list');
                        /** @see UserController::allDriversList() */
                        Route::get('users/all-drivers-list', 'UserController@allDriversList')->name('drivers-list');;
                        Route::get('users/all-drivers-for-fuel-cards', 'UserController@allDriversForFuelCard')->name('all-drivers-for-fuel-cards');;
                        Route::get('users/shortlist', 'UserController@shortlist')->name('users.shortlist');

                        Route::apiResource('users', 'UserController')
                            ->except('update');

                        Route::post('users/{user}', [UserController::class, 'update'])
                            ->name('users.update');

                        Route::post('users/{user}/attachments', [UserController::class, 'addAttachment'])
                            ->name('users.attachments');
                        Route::delete('users/{user}/attachments/{id}', [UserController::class, 'deleteAttachment'])
                            ->name('users.delete-attachments');
                        Route::put('users/{driverFrom}/reassign-driver-orders/{driverTo}', [UserController::class, 'reassignDriverOrders'])
                            ->name('reassign.driver-orders');
                        Route::put('users/{dispatcherFrom}/reassign-dispatcher-drivers/{dispatcherTo}', [UserController::class, 'reassignDispatcherDrivers'])
                            ->name('reassign.dispatcher-drivers');
                        Route::put('users/resend-invitation-link/{user}', [UserController::class, 'resendInvitationLink'])
                            ->name('users.resendInvitationLink');

                        Route::get('users/{user}/comments', 'UserCommentController@index')
                            ->name('users.comments.index');
                        Route::post('users/{user}/comments', 'UserCommentController@store')
                            ->name('users.comments.store');
                        Route::delete('users/{user}/comments/{comment}', 'UserCommentController@destroy')
                            ->name('users.comments.destroy');

                        Route::get('users/{user}/driver-trucks-history', [UserController::class, 'driverTrucksHistory'])
                            ->name('users.driver-trucks-history');
                        Route::get('users/{user}/driver-trailers-history', [UserController::class, 'driverTrailersHistory'])
                            ->name('users.driver-trailers-history');
                        Route::get('users/{user}/owner-trucks-history', [UserController::class, 'ownerTrucksHistory'])
                            ->name('users.owner-trucks-history');
                        Route::get('users/{user}/owner-trailers-history', [UserController::class, 'ownerTrailersHistory'])
                            ->name('users.owner-trailers-history');

                        Route::delete('users/{user}/delete-mvr-document', [UserController::class, 'deleteMvrDocument'])
                            ->name('users.delete-mvr-document');
                        Route::delete('users/{user}/delete-medical-card-document', [UserController::class, 'deleteMedicalCard'])
                            ->name('users.delete-medical-card-document');
                        Route::delete('users/{user}/delete-driver-license-document', [UserController::class, 'deleteDriverLicenseDocument'])
                            ->name('users.delete-driver-license-document');
                        Route::delete('users/{user}/delete-previous-driver-license-document', [UserController::class, 'deletePreviousDriverLicenseDocument'])
                            ->name('users.delete-previous-driver-license-document');

                        Route::get('users/{user}/history', 'UserController@history')
                            ->name('users.history');
                        Route::get('users/{user}/history-detailed', 'UserController@historyDetailed')
                            ->name('users.history-detailed');
                        Route::put('users/{user}/assigned-fuel-card', 'UserController@assignedFuelCard')
                            ->name('users.assigned-fuel-card');
                        Route::put('users/{user}/unassigned-fuel-card', 'UserController@unassignedFuelCard')
                            ->name('users.unassigned-fuel-card');
                        Route::get('users/{user}/history-users-list', 'UserController@historyUsers')
                            ->name('users.history-users-list');
                    }
                );

            Route::namespace('Api\Files')
                ->prefix('files')
                ->name('files.')
                ->group(
                    function () {
                        Route::get('', 'FileManageController@index')
                            ->name('list');
                        Route::delete('{file}', 'FileManageController@delete')
                            ->name('delete')
                            //TODO добавить удаленные разрешения для файлов
                            ->middleware('can:roles admin');
                    }
                );

            Route::namespace('Api\Forms')
                ->prefix('forms')
                ->name('draft.')
                ->group(
                    function () {
                        Route::get('drafts/{path}', 'DraftController@show')->name('show');
                        Route::post('drafts/{path}', 'DraftController@store')->name('store');
                        Route::delete('drafts/{path}', 'DraftController@delete')->name('delete');
                    }
                );

            Route::namespace('Api\Parsers')
                ->prefix('parse')
                ->name('parse.')
                ->group(
                    function () {
                        Route::post('pdf_order', 'PdfOrderController@parse')->name('pdf_order');
                    }
                );

            Route::apiResource('tags', 'Api\Tags\TagController');
            Route::apiResource('fuel-cards', 'Api\Fueling\FuelCardController');

            Route::get('fueling-import', 'Api\Fueling\FuelingImportController@index')->name('fueling-import.index');
            Route::put('fueling-import/{fueling}', 'Api\Fueling\FuelingImportController@update')->name('fueling-import.update');
            Route::delete('fueling-import/{fueling}', 'Api\Fueling\FuelingImportController@destroy')->name('fueling-import.destroy');

            Route::get('fueling', 'Api\Fueling\FuelingController@index')->name('fueling.index');

            Route::delete('fueling/{fueling}', 'Api\Fueling\FuelingController@destroy')->name('fueling.destroy');
            Route::put('fueling/{fueling}', 'Api\Fueling\FuelingController@update')->name('fueling.update');
            Route::get('fueling/history', 'Api\Fueling\FuelingController@history')->name('fueling.history');
            Route::post('fueling/import', 'Api\Fueling\FuelingController@import')->name('fueling.import');
            Route::get('fueling/active-import', 'Api\Fueling\FuelingController@activeImport')->name('fueling.active-import');
            Route::get('fuel-card-history/{fuelCard}', 'Api\Fueling\FuelCardController@history')->name('fuel-cards.history');
            Route::put('fuel-cards/{fuelCard}/assigned-driver', 'Api\Fueling\FuelCardController@assigned')->name('fuel-cards.assigned');
            Route::put('fuel-cards/{fuelCard}/unassigned-driver', 'Api\Fueling\FuelCardController@unassigned')->name('fuel-cards.unassigned');

            Route::namespace('Api\Vehicles')
                ->group(
                    function () {
                        Route::get('trucks/same-vin', 'TruckController@sameVin')->name('trucks.same-vin');
                        Route::get('trucks/available-gps-devices', 'TruckController@availableGPSDevices')
                            ->name('trucks.available-gps-devices');
                        Route::apiResource('trucks', 'TruckController')
                            ->except(['update'])
                            ->names('trucks');
                        Route::post('trucks/{truck}', 'TruckController@update')
                            ->name('trucks.update');
                        Route::delete('trucks/{truck}/attachments/{id}', 'TruckController@deleteAttachment')
                            ->name('trucks.delete-attachment');
                        Route::get('trucks/{truck}/comments', 'TruckCommentController@index')
                            ->name('trucks.comments.index');
                        Route::post('trucks/{truck}/comments', 'TruckCommentController@store')
                            ->name('trucks.comments.store');
                        Route::delete('trucks/{truck}/comments/{comment}', 'TruckCommentController@destroy')
                            ->name('trucks.comments.destroy');
                        Route::get('trucks/{truck}/history', 'TruckController@history')
                            ->name('trucks.history');
                        Route::get('trucks/{truck}/history-detailed', 'TruckController@historyDetailed')
                            ->name('trucks.history-detailed');
                        Route::get('trucks/{truck}/history-users-list', 'TruckController@historyUsers')
                            ->name('trucks.history-users-list');
                        Route::delete('trucks/{truck}/delete-inspection-document', 'TruckController@deleteInspectionDocument')
                            ->name('trucks.delete-inspection-document');
                        Route::delete('trucks/{truck}/delete-registration-document', 'TruckController@deleteRegistrationDocument')
                            ->name('trucks.delete-registration-document');
                        Route::get('trucks/{truck}/drivers-activity', 'TruckController@driversHistory')
                            ->name('trucks.drivers-history');
                        Route::post('trucks/{truck}/drivers-activity', 'TruckController@addDriversHistory')
                            ->name('trucks.add-drivers-history');
                        Route::get('trucks/{truck}/owners-activity', 'TruckController@ownersHistory')
                            ->name('trucks.owners-history');

                        Route::get('trailers/same-vin', 'TrailerController@sameVin')->name('trailers.same-vin');
                        Route::get('trailers/available-gps-devices', 'TrailerController@availableGPSDevices')
                            ->name('trailers.available-gps-devices');
                        Route::apiResource('trailers', 'TrailerController')
                            ->except(['update'])
                            ->names('trailers');
                        Route::post('trailers/{trailer}', 'TrailerController@update')
                            ->name('trailers.update');
                        Route::delete('trailers/{trailer}/attachments/{id}', 'TrailerController@deleteAttachment')
                            ->name('trailers.delete-attachment');
                        Route::get('trailers/{trailer}/comments', 'TrailerCommentController@index')
                            ->name('trailers.comments.index');
                        Route::post('trailers/{trailer}/comments', 'TrailerCommentController@store')
                            ->name('trailers.comments.store');
                        Route::delete('trailers/{trailer}/comments/{comment}', 'TrailerCommentController@destroy')
                            ->name('trailers.comments.destroy');
                        Route::get('trailers/{trailer}/history', 'TrailerController@history')
                            ->name('trailers.history');
                        Route::get('trailers/{trailer}/history-detailed', 'TrailerController@historyDetailed')
                            ->name('trailers.history-detailed');
                        Route::get('trailers/{trailer}/history-users-list', 'TrailerController@historyUsers')
                            ->name('trailers.history-users-list');
                        Route::delete('trailers/{trailer}/delete-inspection-document', 'TrailerController@deleteInspectionDocument')
                            ->name('trailers.delete-inspection-document');
                        Route::delete('trailers/{trailer}/delete-registration-document', 'TrailerController@deleteRegistrationDocument')
                            ->name('trailers.delete-registration-document');
                        Route::get('trailers/{trailer}/drivers-activity', 'TrailerController@driversHistory')
                            ->name('trailers.drivers-history');
                        Route::post('trailers/{trailer}/drivers-activity', 'TrailerController@addDriversHistory')
                            ->name('trailers.add-drivers-history');
                        Route::get('trailers/{trailer}/owners-activity', 'TrailerController@ownersHistory')
                            ->name('trailers.owners-history');
                    }
                );

            Route::namespace('Api\GPS')
                ->prefix('gps')
                ->name('gps.')
                ->group(
                    function () {
                        Route::put('speed-limit', 'SettingsController@updateSpeedLimit')
                            ->name('update-speed-limit');
                        Route::get('alerts', 'AlertController@index')
                            ->name('alerts-index');
                        Route::get('alerts-list', 'AlertController@list')
                            ->name('alerts-list');

                        Route::get('devices', [GPS\DeviceController::class, 'list'])
                            ->name('device-list-api');
                        Route::get('devices-free', [GPS\DeviceController::class, 'index'])
                            ->name('device-index-api');
                        Route::get('devices/has-active', [GPS\DeviceController::class, 'hasActive'])
                            ->name('device.has-active');
                        Route::get('devices/has-active-at-vehicle', [GPS\DeviceController::class, 'hasActiveAtVehicle'])
                            ->name('device.has-active-at-vehicle');
                        Route::get('devices/vehicle-without-device', [GPS\DeviceController::class, 'vehicleWithoutDevice'])
                            ->name('device.vehicle-without-device');
                        Route::put('devices/{id}', [GPS\DeviceController::class, 'update'])
                            ->name('device-update-api');
                        Route::put('devices/{id}/attach-vehicle', [GPS\DeviceController::class, 'attachVehicle'])
                            ->name('device-attach-vehicle-api');
                        Route::put('devices/{id}/toggle-activate', [GPS\DeviceController::class, 'toggleActivate'])
                            ->name('device-toggle-activate-api');

                        Route::get('devices/request/can-add', [GPS\DeviceRequestController::class, 'canCreate'])
                            ->name('device.request.can-create');
                        Route::post('devices/request', [GPS\DeviceRequestController::class, 'create'])
                            ->name('device.request.create');

                        Route::put('subscription/{id}/cancel', [GPS\SubscriptionController::class, 'cancel'])
                            ->name('subscription.cancel');
                        Route::put('subscription/{id}/restore', [GPS\SubscriptionController::class, 'restore'])
                            ->name('subscription.restore');

                        Route::get('tracking', [GPS\TrackingController::class, 'index'])
                            ->name('gps-tracking');

                        Route::get('history', [GPS\HistoryController::class, 'index'])
                            ->name('gps-history-index');
                        Route::get('history/export', [GPS\HistoryController::class, 'export'])
                            ->name('gps-history-export');
                        Route::get('history/coords-route', [GPS\HistoryController::class, 'coordsRoute'])
                            ->name('gps-history-coords-route');
                        Route::get('history/route', [GPS\HistoryController::class, 'route'])
                            ->name('gps-history-route');
                        Route::post('history/route', [GPS\HistoryController::class, 'setRoute'])
                            ->name('gps-history-set-route');
                        Route::get('history/additional', [GPS\HistoryController::class, 'additional'])
                            ->name('gps-history-additional');
                    }
                );
        }
    );
Route::prefix('broadcasts')
    ->name('broadcasts.')
    ->group(
        function () {
            Route::get('channels', [BroadcastController::class, 'channels'])
                ->name('channels')->middleware('auth:api');
            Route::get('admin/channels', [BroadcastController::class, 'adminChannels'])
                ->name('admin-channels')->middleware('auth:api_admin');
        }
    );
