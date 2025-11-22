<?php

use App\Http\Controllers\Api\BodyShop\Orders\OrderController;
use App\Http\Controllers\Api\BodyShop\Orders\PaymentMethodController;
use App\Http\Controllers\Api\BodyShop\VehicleOwners\VehicleOwnerController;
use App\Http\Middleware\CheckBodyShopAccess;
use App\Http\Controllers\Api\BodyShop\Inventories\PaymentMethodController as InventoryPaymentMethodController;

Route::middleware(
    [
        'auth:api',
        CheckBodyShopAccess::class,
    ]
)
    ->group(
        function () {
            Route::namespace('Api\BodyShop')
                ->prefix('body-shop')
                ->name('body-shop.')
                ->group(
                    function () {
                        Route::get('vehicle-owners/shortlist', [VehicleOwnerController::class, 'shortlist'])
                            ->name('vehicle-owners.shortlist');
                        Route::apiResource('vehicle-owners', 'VehicleOwners\VehicleOwnerController')
                            ->except('update')
                            ->names('vehicle-owners');
                        Route::post('vehicle-owners/{vehicleOwner}', [VehicleOwnerController::class, 'update'])
                            ->name('vehicle-owners.update');
                        Route::post('vehicle-owners/{vehicleOwner}/attachments', [VehicleOwnerController::class, 'addAttachment'])
                            ->name('vehicle-owners.attachments');
                        Route::delete('vehicle-owners/{vehicleOwner}/attachments/{id}', [VehicleOwnerController::class, 'deleteAttachment'])
                            ->name('vehicle-owners.delete-attachments');
                        Route::get('vehicle-owners/{vehicleOwner}/comments', 'VehicleOwners\VehicleOwnerCommentController@index')
                            ->name('vehicle-owners.comments.index');
                        Route::post('vehicle-owners/{vehicleOwner}/comments', 'VehicleOwners\VehicleOwnerCommentController@store')
                            ->name('vehicle-owners.comments.store');
                        Route::delete('vehicle-owners/{vehicleOwner}/comments/{comment}', 'VehicleOwners\VehicleOwnerCommentController@destroy')
                            ->name('vehicle-owners.comments.destroy');

                        Route::get('suppliers/shortlist', 'Suppliers\SupplierController@shortlist')->name('suppliers.shortlist');
                        Route::apiResource('suppliers', 'Suppliers\SupplierController')->names('suppliers');

                        Route::apiResource('inventory-categories', 'Inventories\CategoryController')->names('inventory-categories');

                        Route::get('profile', 'Users\ProfileController@showBS')->name('profile.show');
                        Route::put('profile', 'Users\ProfileController@updateBS')->name('profile.update');
                        Route::post('profile/upload-photo', 'Users\ProfileController@uploadPhoto')->name('profile.upload-photo');
                        Route::delete('profile/delete-photo', 'Users\ProfileController@deletePhoto')->name('profile.delete-photo');
                        Route::put('profile/change-password', 'Users\ProfileController@changePassword')->name(
                            'profile.change-password'
                        );

                        Route::get('change-email/if-requested', 'Users\ChangeEmailController@ifRequested')
                            ->name('change-email.if-requested');
                        Route::apiResource('change-email', 'Users\ChangeEmailController')->only(['store', 'destroy'])
                            ->names('change-email');

                        Route::get('companies', 'Companies\CompanyController@index')->name('companies.index');

                        Route::get('inventories/export', 'Inventories\InventoryController@export')
                            ->name('inventories.export');

                        Route::get('inventories/payment-methods', [InventoryPaymentMethodController::class, 'index'])
                            ->name('inventories.payment-methods');
                        Route::get('inventories/shortlist', 'Inventories\InventoryController@shortlist')->name('inventories.shortlist');
                        Route::get('inventories/report', 'Inventories\InventoryController@transactionsReport')->name('inventories.report');
                        Route::get('inventories/report-total', 'Inventories\InventoryController@transactionsReportTotal')->name('inventories.report-total');
                        Route::get('inventories/transactions/{transaction}/generate-invoice', 'Inventories\InventoryController@generateInvoice')
                            ->name('inventories.generate-invoice');
                        Route::get('inventories/transactions/{transaction}/generate-payment-receipt', 'Inventories\InventoryController@generatePaymentReceipt')
                            ->name('inventories.generate-payment-receipt');
                        Route::apiResource('inventories', 'Inventories\InventoryController')->names('inventories');
                        Route::get('inventories/{inventory_id}/history', 'Inventories\InventoryController@history')->name('inventories.histories');
                        Route::get('inventories/{inventory_id}/history-detailed', 'Inventories\InventoryController@historyDetailed')->name('inventories.histories-detailed');
                        Route::post('inventories/{inventory}/purchase', 'Inventories\InventoryController@purchase')
                            ->name('inventories.purchase');
                        Route::post('inventories/{inventory}/sold', 'Inventories\InventoryController@sold')
                            ->name('inventories.sold');
                        Route::get('inventories/{inventory}/transactions', 'Inventories\InventoryController@transactions')
                            ->name('inventories.transactions');
                        Route::get('inventories/{inventory}/reserve', 'Inventories\InventoryController@transactionsReserved')
                            ->name('inventories.reserve');

                        Route::apiResource('tags', 'Tags\TagController')->names('tags');

                        Route::get('forms/drafts/{path}', 'Forms\DraftController@show')->name('drafts.show');
                        Route::post('forms/drafts/{path}', 'Forms\DraftController@store')->name('drafts.store');
                        Route::delete('forms/drafts/{path}', 'Forms\DraftController@delete')->name('drafts.delete');

                        Route::get('types-of-work/shortlist', 'TypesOfWork\TypeOfWorkController@shortlist')
                            ->name('types-of-work.shortlist');
                        Route::apiResource('types-of-work', 'TypesOfWork\TypeOfWorkController')->names('types-of-work');

                        Route::get('users/shortlist', 'Users\UserController@shortlist')->name('users.shortlist');
                        Route::apiResource('users', 'Users\UserController')->names('users');
                        Route::put('users/{user}/change-status', 'Users\UserController@changeStatus')->name('users.change-status');
                        Route::post('users/{user}/change-password', 'Users\UserController@changePassword')
                            ->name('users.change-password');
                        Route::put('users/resend-invitation-link/{user}', 'Users\UserController@resendInvitationLink')
                            ->name('users.resendInvitationLink');

                        Route::get('trucks/same-vin', 'Vehicles\TruckController@sameVin')->name('trucks.same-vin');
                        Route::apiResource('trucks', 'Vehicles\TruckController')
                            ->except(['update'])
                            ->names('trucks');
                        Route::post('trucks/{truck}', 'Vehicles\TruckController@update')->name('trucks.update');
                        Route::delete('trucks/{truck}/attachments/{id}', 'Vehicles\TruckController@deleteAttachment')
                            ->name('trucks.delete-attachment');
                        Route::get('trucks/{truck}/comments', 'Vehicles\TruckCommentController@index')
                            ->name('trucks.comments.index');
                        Route::post('trucks/{truck}/comments', 'Vehicles\TruckCommentController@store')
                            ->name('trucks.comments.store');
                        Route::delete('trucks/{truck}/comments/{comment}', 'Vehicles\TruckCommentController@destroy')
                            ->name('trucks.comments.destroy');
                        Route::get('trucks/{truck}/history', 'Vehicles\TruckController@history')
                            ->name('trucks.history');
                        Route::get('trucks/{truck}/history-detailed', 'Vehicles\TruckController@historyDetailed')
                            ->name('trucks.history-detailed');
                        Route::get('trucks/{truck}/history-users-list', 'Vehicles\TruckController@historyUsers')
                            ->name('trucks.history-users-list');

                        Route::get('trailers/same-vin', 'Vehicles\TrailerController@sameVin')->name('trailers.same-vin');
                        Route::apiResource('trailers', 'Vehicles\TrailerController')
                            ->except(['update'])
                            ->names('trailers');
                        Route::post('trailers/{trailer}', 'Vehicles\TrailerController@update')->name('trailers.update');
                        Route::delete('trailers/{trailer}/attachments/{id}', 'Vehicles\TrailerController@deleteAttachment')
                            ->name('trailers.delete-attachment');
                        Route::get('trailers/{trailer}/comments', 'Vehicles\TrailerCommentController@index')
                            ->name('trailers.comments.index');
                        Route::post('trailers/{trailer}/comments', 'Vehicles\TrailerCommentController@store')
                            ->name('trailers.comments.store');
                        Route::delete('trailers/{trailer}/comments/{comment}', 'Vehicles\TrailerCommentController@destroy')
                            ->name('trailers.comments.destroy');
                        Route::get('trailers/{trailer}/history', 'Vehicles\TrailerController@history')
                            ->name('trailers.history');
                        Route::get('trailers/{trailer}/history-detailed', 'Vehicles\TrailerController@historyDetailed')
                            ->name('trailers.history-detailed');
                        Route::get('trailers/{trailer}/history-users-list', 'Vehicles\TrailerController@historyUsers')
                            ->name('trailers.history-users-list');

                        Route::get('vehicle-db/makes', 'Vehicles\VehicleDBController@getMakes')->name('vehicle-db.makes');
                        Route::get('vehicle-db/models', 'Vehicles\VehicleDBController@getModels')->name('vehicle-db.models');
                        Route::get('vehicle-db/decode-vin', 'Vehicles\VehicleDBController@decodeVin')->name('vehicle-db.decode-vin');
                        Route::get('vehicle-db/types', 'Vehicles\VehicleDBController@getTypes')->name('vehicle-db.types');
                        Route::get('vehicle-db/vehicles', 'Vehicles\VehicleDBController@getVehicles')->name('vehicle-db.vehicles');

                        Route::get('roles-list', 'Roles\RoleController@list')->name('roles.list');

                        Route::get('orders/payment-methods', [PaymentMethodController::class, 'index'])
                            ->name('orders.payment-methods');

                        Route::get('orders/report', [OrderController::class, 'report'])
                            ->name('orders.report');
                        Route::get('orders/report-total', [OrderController::class, 'reportTotal'])
                            ->name('orders.report-total');
                        Route::apiResource('orders', 'Orders\OrderController')->names('orders')
                            ->except(['update']);
                        Route::post('orders/{order}', [OrderController::class, 'update'])
                            ->name('orders.update');
                        Route::post('orders/{order}/attachments', [OrderController::class, 'addAttachment'])
                            ->name('orders.attachments');
                        Route::delete('orders/{order}/attachments/{id}', [OrderController::class, 'deleteAttachment'])
                            ->name('orders.delete-attachments');
                        Route::get('orders/{order_id}/history', [OrderController::class, 'history'])
                            ->name('order.histories');
                        Route::get('orders/{order_id}/history-detailed',  [OrderController::class, 'historyDetailed'])
                            ->name('order.histories-detailed');
                        Route::apiResource('orders/{order}/comments','Orders\OrderCommentController')
                            ->except(['update', 'show'])
                            ->names('order-comments');
                        Route::put('orders/{order}/change-status', [OrderController::class, 'changeStatus'])
                            ->name('orders.change-status');
                        Route::put('orders/{order}/reassign-mechanic', [OrderController::class, 'reassignMechanic'])
                            ->name('orders.reassign-mechanic');
                        Route::get('orders/{order}/generate-invoice', [OrderController::class, 'generateInvoice'])
                            ->name('orders.generate-invoice');
                        Route::get('orders/{order}/view-deleted', [OrderController::class, 'viewDeletedOrder']);
                        Route::put('orders/{order}/restore', [OrderController::class, 'restoreOrder'])
                            ->name('orders.restore');
                        Route::post('orders/{order}/restore-with-editing', [OrderController::class, 'restoreOrderWithEditing'])
                            ->name('orders.restore-with-editing');
                        Route::delete('orders/{order}/permanently', [OrderController::class, 'deletePermanently'])
                            ->name('orders.delete-permanently');
                        Route::post('orders/{order}/send-docs', [OrderController::class, 'sendDocs'])
                            ->name('orders.send-docs');
                        Route::post('orders/{order}/payment', [OrderController::class, 'addPayment'])
                            ->name('orders.add-payment');
                        Route::delete('orders/{order}/payment/{payment}', [OrderController::class, 'deletePayment'])
                            ->name('orders.delete-payment');

                        Route::apiResource('inventory-units', 'Inventories\UnitController')->names('inventory-units');

                        Route::get('settings/info', 'Settings\SettingsController@information')
                            ->name('settings.show-info');
                        Route::put('settings/info', 'Settings\SettingsController@informationUpdate')
                            ->name('settings.update-info');
                        Route::post('settings/info/upload-photo', 'Settings\SettingsController@uploadInfoPhoto')
                            ->name('settings.upload-info-photo');
                        Route::delete('settings/info/delete-photo', 'Settings\SettingsController@deleteInfoPhoto')
                            ->name('settings.delete-info-photo');

                        Route::get('states-list', 'Locations\StateController@list')->name('states.list');
                        Route::get('city-autocomplete', 'Locations\CityController@autocomplete')
                            ->name('city-autocomplete');
                    }
                );
        }
    );

Route::namespace('Api\BodyShop')
    ->prefix('body-shop')
    ->name('body-shop.')
    ->group(
        function () {
            Route::post('change-email/confirm-email', 'Users\ChangeEmailController@confirmEmail')
                ->name('change-email.confirm-email');
            Route::post('change-email/cancel-request', 'Users\ChangeEmailController@cancelRequest')
                ->name('change-email.cancel-request');

            Route::get('timezone-list', 'TimezoneController@timezoneList')->name('timezone-list');
        }
    );

Route::middleware(
    ['bs.auth']
)->group(
    function () {
        Route::get('/body-shop/sync/users', [\App\Http\Controllers\Api\BodyShop\SyncController::class, 'users'])
            ->name('bs.sync.users');
    });
