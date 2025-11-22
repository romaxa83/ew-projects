<?php

use App\Http\Controllers\Api\Reports\CompanyReportController;
use App\Http\Controllers\V1\Carrier\Broadcasting\BroadcastController;
use App\Http\Controllers\V1\Carrier\FileBrowser\FileBrowserController;
use App\Http\Controllers\V1\Carrier\Orders\OrderController;
use App\Http\Controllers\V1\Carrier\Users\UserController;
use App\Http\Controllers\ApiController;
use App\Http\Middleware\CheckSubscription;
use App\Models\Orders\Payment;

Route::get('', [ApiController::class, 'api'])
    ->name('carrier');

// vehicle types list
Route::get('orders/vehicle-types', 'Orders\OrderController@vehicleTypes');

// public BOL
Route::namespace('Orders')
    ->prefix('orders/public-bol')
    ->group(
        function () {
            Route::get('{token}/vehicle/{vehicle_id}/photos', [OrderController::class, 'publicVehiclePhotos'])
                ->name('orders.vehicle.photos');

            Route::get('{token}/photos', 'OrderController@publicOrderPhotos');
            Route::get('{token}', 'OrderController@publicBol');
        }
    );

// public PDF
Route::namespace('Orders')
    ->prefix('orders/public-pdf')
    ->group(
        function () {
            Route::get('{token}/bol.pdf', [OrderController::class, 'publicPdfBol'])
                ->name('orders.public.pdf-bol');

            Route::get('{token}/{recipient}/invoice.pdf', [OrderController::class, 'publicPdfInvoice'])
                ->where('recipient', '^' . Payment::PAYER_CUSTOMER . '|' . Payment::PAYER_BROKER . '$');
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

Route::middleware(
    [
        'auth:api',
        CheckSubscription::class
    ]
)
    ->group(
        function () {
            //profile
            Route::get('profile', 'Users\ProfileController@show')->name('profile.show');
            Route::put('profile', 'Users\ProfileController@update')->name('profile.update');
            Route::post('profile/upload-photo', 'Users\ProfileController@uploadPhoto')->name('profile.upload-photo');
            Route::delete('profile/delete-photo', 'Users\ProfileController@deletePhoto')->name('profile.delete-photo');
            Route::put('profile/change-password', 'Users\ProfileController@changePassword')->name(
                'profile.change-password'
            );
            Route::put('profile/set-fcm-token', 'Users\ProfileController@setFcmToken');

            // languages
            Route::get('languages/selected', 'Intl\LanguageController@getSelected');
            Route::apiResource('languages', 'Intl\LanguageController')->except('store', 'update', 'destroy');
            Route::put('change-language', 'Intl\LanguageController@changeLanguage')->name('languages.change-language');

            //roles
            Route::get('roles-list', 'Roles\RoleController@list')->name('roles.list');
            Route::get('roles/{role}', 'Roles\RoleController@show')->name('roles.show');

            // contacts section
            Route::get('contacts/types', 'Contacts\ContactController@contactTypes');
            Route::get('contacts/search', 'Contacts\ContactController@search');
            Route::post('contacts/import', 'Contacts\ContactController@import');
            Route::apiResource('contacts', 'Contacts\ContactController');

            // carrier profile
            Route::namespace('Carrier')
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
                    }
                );

            // library
            Route::apiResource('library', 'Library\LibraryDocumentController')->except(
                [
                    'update',
                ]
            );
            Route::get('library/documents/{policy}', 'Library\LibraryDocumentController@documentsList')
                ->where('policy', '(public|private)');
            Route::get('library/documents/search', 'Library\LibraryDocumentController@search');

            // faq
            Route::apiResource('question-answer', 'QuestionAnswer\QuestionAnswerController')->except(
                [
                    'show',
                ]
            )->parameters(
                [
                    'question-answer' => 'questionAnswer'
                ]
            );
            Route::get('question-answer/{questionAnswer}/full', 'QuestionAnswer\QuestionAnswerController@showFull');

            // payment methods
            Route::namespace('Orders')
                ->group(
                    function () {
                        Route::get('payment-methods/for-order', 'PaymentMethodController@forOrder');
                        Route::get('payment-methods/for-driver', 'PaymentMethodController@forDriver');
                    }
                );

            // orders
            Route::namespace('Orders')
                ->prefix('orders')
                ->middleware('throttleIp:1200,1')
                ->group(
                    function () {
                        Route::post('{order}/vehicles', 'OrderController@addVehicle');
                        Route::put('{order}/vehicles/{vehicle}', 'OrderController@editVehicle');
                        Route::delete('{order}/vehicles/{vehicle}', 'OrderController@deleteVehicle');
                        Route::post('{order}/expenses', 'OrderController@addExpense');
                        Route::post('{order}/expenses/{expense}', 'OrderController@editExpense');
                        Route::delete('{order}/expenses/{expense}', 'OrderController@deleteExpense');

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
                        Route::get('{order}/get-invoice', 'OrderController@getInvoice');
                        Route::get('{order}/get-bol', 'OrderController@getBol');

                        Route::post('send-docs', [OrderController::class, 'sendDocs'])
                            ->name('orders.send-docs');

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
                        Route::get('{order_id}/history', 'OrderController@orderHistory');
                        Route::put('{order}/take', 'OrderController@take')
                            ->name('orders.take');

                        Route::put('{order}/release', 'OrderController@release')
                            ->name('orders.release');

                        Route::put('{order}/change-status', 'OrderController@changeStatus')->name('orders.change-order-status');
                        Route::get('expense-types', 'OrderController@expenseTypes');
                        Route::get('contact-types', 'OrderController@contactTypes');
                        Route::get('vehicles-for-filter', 'OrderController@vehiclesForFilter');
                        Route::get('same-load-id', 'OrderController@sameLoadId')->name('orders.same-load-id');
                        Route::get('same-vin', 'OrderController@sameVin')->name('orders.same-vin');
                        Route::get('offers', 'OrderController@offers');
                    }
                );
            Route::namespace('Orders')
                ->group(
                    function () {
                        Route::apiResource('orders', 'OrderController');
                    }
                );

            // reports
            Route::namespace('Reports')
                ->group(
                    function () {
                        Route::get('report/companies-total', [CompanyReportController::class, 'reportTotal']);
                        Route::get('report/companies', [CompanyReportController::class, 'report']);
                    }
                );

            // news
            Route::namespace('News')
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
            Route::namespace('History')
                ->group(
                    function () {
                        Route::get('history', 'HistoryController@index');
                    }
                );

            // Dashboard
            Route::namespace('Dashboard')
                ->group(
                    function () {
                        Route::get('dashboard', 'DashboardController@index');
                    }
                );

            // change user email
            Route::namespace('Users')
                ->group(
                    function () {
                        Route::get('change-email/if-requested', 'ChangeEmailController@ifRequested');
                        Route::apiResource('change-email', 'ChangeEmailController')->only(['store', 'destroy']);
                    }
                );

            Route::namespace('Users')
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
                        Route::get('users/order-creators-list', 'UserController@orderCreatorsList');
                        /** @see UserController::allDriversList() */
                        Route::get('users/all-drivers-list', 'UserController@allDriversList');
                        Route::get('users/shortlist', 'UserController@shortlist')->name('users.shortlist');

                        Route::apiResource('users', 'UserController')
                            ->except('update');

                        Route::post('users/{user}', [UserController::class, 'update'])
                            ->name('users.update');

                        Route::post('users/{user}/attachments', [UserController::class, 'addAttachment'])
                            ->name('users.attachments');
                        Route::delete('users/{user}/attachments/{id}', [UserController::class, 'deleteAttachment'])
                            ->name('users.delete-attachments');

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
                        Route::get('users/{user}/history-users-list', 'UserController@historyUsers')
                            ->name('users.history-users-list');
                    }
                );

            Route::namespace('Files')
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

            Route::namespace('Forms')
                ->prefix('forms')
                ->name('draft.')
                ->group(
                    function () {
                        Route::get('drafts/{path}', 'DraftController@show')->name('show');
                        Route::post('drafts/{path}', 'DraftController@store')->name('store');
                        Route::delete('drafts/{path}', 'DraftController@delete')->name('delete');
                    }
                );

            Route::namespace('Parsers')
                ->prefix('parse')
                ->name('parse.')
                ->group(
                    function () {
                        Route::post('pdf_order', 'PdfOrderController@parse')->name('pdf_order');
                    }
                );

            Route::prefix('broadcasts')
                ->name('broadcasts.')
                ->group(
                    function () {
                        Route::get('channels', [BroadcastController::class, 'channels'])
                            ->name('channels');
                    }
                );

            Route::namespace('Vehicles')
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
                        Route::get('trucks/{truck}/owners-activity', 'TruckController@ownersHistory')
                            ->name('trucks.owners-history');

                        Route::get('trailers/available-gps-devices', 'TrailerController@availableGPSDevices')
                            ->name('trailers.available-gps-devices');
                        Route::get('trailers/same-vin', 'TrailerController@sameVin')->name('trailers.same-vin');
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

                    }
                );
        }
    );
