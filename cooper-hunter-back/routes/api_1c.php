<?php

use App\Http\Controllers\Api\OneC\Auth\AuthController;
use App\Http\Controllers\Api\OneC\Catalog\CategoriesController;
use App\Http\Controllers\Api\OneC\Catalog\CertificatesController;
use App\Http\Controllers\Api\OneC\Catalog\FeaturesController;
use App\Http\Controllers\Api\OneC\Catalog\ManualsController;
use App\Http\Controllers\Api\OneC\Catalog\ProductsController;
use App\Http\Controllers\Api\OneC\Catalog\TroubleshootingGroupsController;
use App\Http\Controllers\Api\OneC\Catalog\ValuesController;
use App\Http\Controllers\Api\OneC\Catalog\VideoLinkController;
use App\Http\Controllers\Api\OneC\Commercial\ProjectController;
use App\Http\Controllers\Api\OneC\Commercial\TaxController;
use App\Http\Controllers\Api\OneC\Companies\CompanyController;
use App\Http\Controllers\Api\OneC\Orders\Categories\OrderCategoryController;
use App\Http\Controllers\Api\OneC\Orders\DeliveryTypes\OrderDeliveryTypeController;
use App\Http\Controllers\Api\OneC\Orders\OrderController;
use App\Http\Controllers\Api\OneC\Permissions\PermissionsController;
use App\Http\Controllers\Api\OneC\Products\SerialNumbersController;
use App\Http\Controllers\Api\OneC\Products\TicketController;
use App\Http\Controllers\Api\OneC\Technicians\TechniciansController;
use App\Http\Controllers\Api\OneC\Users\UsersController;
use App\Http\Controllers\Api\OneC\Warranty\WarrantyRegistrationController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\OneC\Dealers;

Route::name('auth.')
    ->prefix('auth')
    ->group(
        static function () {
            Route::post('login', [AuthController::class, 'login'])
                ->name('login')
                ->middleware('api_guest:graph_1c_moderator');

            Route::post('refresh/{refresh_token}', [AuthController::class, 'refreshToken'])
                ->name('refresh')
                ->middleware('api_guest:graph_1c_moderator');

            Route::post('logout', [AuthController::class, 'logout'])
                ->name('logout');
        }
    );

Route::middleware('auth:graph_1c_moderator')
    ->group(
        static function () {
            Route::name('categories.')
                ->prefix('categories')
                ->group(
                    static function () {
                        Route::post('update/guid', [CategoriesController::class, 'updateGuid'])->name('update.guid');
                    }
                );

            Route::apiResource('categories', CategoriesController::class)
                ->parameter('categories', 'category:guid');

            Route::name('features.')
                ->prefix('features')
                ->group(
                    static function () {
                        Route::post('update/guid', [FeaturesController::class, 'updateGuid'])->name('update.guid');
                    }
                );

            Route::apiResource('features', FeaturesController::class)
                ->parameter('features', 'feature:guid');

            Route::apiResource('values', ValuesController::class)
                ->only(['store', 'update', 'destroy']);

            Route::name('orders.')
                ->prefix('orders')
                ->group(
                    static function () {
                        Route::name('delivery_types.')
                            ->prefix('delivery_types')
                            ->group(
                                static function () {
                                    Route::get('', [OrderDeliveryTypeController::class, 'index'])->name('index');
                                }
                            );

                        Route::apiResource('', OrderController::class)
                            ->parameter('', 'order:id')
                            ->only(['index', 'update']);
                    }
                );

            Route::get('tickets/new', [TicketController::class, 'new'])
                ->name('tickets.new');

            Route::get('tickets/without-code', [TicketController::class, 'withoutCode'])
                ->name('tickets.withoutCode');

            Route::get('tickets/exists/{guid}', [TicketController::class, 'exists'])
                ->name('tickets.exists');

            Route::post('tickets/update-code/{ticket:guid}', [TicketController::class, 'updateCode'])
                ->name('tickets.update-code');

            Route::apiResource('tickets', TicketController::class)
                ->parameter('tickets', 'ticket:guid')
                ->only(['store', 'update', 'destroy']);

            Route::name('products.')
                ->prefix('products')
                ->group(
                    static function () {
                        Route::post('update/guid', [ProductsController::class, 'updateGuid'])->name('update.guid');
                    }
                );

            Route::apiResource('products', ProductsController::class)
                ->parameter('products', 'product:guid');

            Route::get('video_links', [VideoLinkController::class, 'index'])->name('video_links');
            Route::get('certificates', [CertificatesController::class, 'index'])->name('certificates');
            Route::get('troubleshooting_groups', [TroubleshootingGroupsController::class, 'index'])
                ->name('troubleshooting_groups');
            Route::get('manuals', [ManualsController::class, 'index'])->name('manuals');

            Route::name('permissions.')
                ->prefix('permissions')
                ->group(
                    static function () {
                        Route::get('roles', [PermissionsController::class, 'roles'])->name('roles');
                        Route::get('list', [PermissionsController::class, 'list'])->name('list');
                    }
                );

            Route::name('users.')
                ->prefix('users')
                ->group(
                    static function () {
                        Route::get('new', [UsersController::class, 'new'])->name('new');
                        Route::post('update/guid', [UsersController::class, 'updateGuid'])->name('update.guid');
                        Route::post('import', [UsersController::class, 'import'])->name('import');
                    }
                );
            Route::apiResource('users', UsersController::class)
                ->parameter('users', 'user:guid')
                ->except(['store', 'update']);

            Route::name('technicians.')
                ->prefix('technicians')
                ->group(
                    static function () {
                        Route::get('new', [TechniciansController::class, 'new'])->name('new');
                        Route::post('import', [TechniciansController::class, 'import'])->name('import');
                    }
                );
            Route::apiResource('technicians', TechniciansController::class)
                ->parameter('technicians', 'technician:guid')
                ->except(['store', 'update']);

            Route::name('serialNumbers.')
                ->prefix('serialNumbers')
                ->group(
                    static function () {
                        Route::post('delete', [SerialNumbersController::class, 'delete'])->name('delete');
                        Route::post('import', [SerialNumbersController::class, 'import'])->name('import');
                    }
                );

            Route::name('orderParts.')
                ->prefix('orderParts')
                ->group(
                    static function () {
                        Route::post('update/guid', [OrderCategoryController::class, 'updateGuid'])->name('update.guid');
                    }
                );

            Route::apiResource('orderParts', OrderCategoryController::class)
                ->parameter('orderParts', 'orderPart:guid');

            Route::name('warranty.')
                ->prefix('warranty')
                ->group(
                    static function () {
                        Route::get('index', [WarrantyRegistrationController::class, 'index'])->name('index');
                        Route::get('pending', [WarrantyRegistrationController::class, 'pending'])->name('pending');
                        Route::post('create', [WarrantyRegistrationController::class, 'create'])->name('create');
                        Route::post('process/{warranty}', [WarrantyRegistrationController::class, 'process'])
                            ->name('process');
                    }
                );

            Route::post('tax/create-or-update', [TaxController::class, 'createOrUpdate'])
                ->name('tax.create-or-update');
            Route::post('tax/remove', [TaxController::class, 'remove'])
                ->name('tax.remove');

            Route::get('commercial-project/{guid}/start-commissioning', [ProjectController::class, 'startCommissioning'])
                ->name('commercial-project.start-commissioning');
            Route::post('commercial-project/{guid}/add-units', [ProjectController::class, 'addUnits'])
                ->name('commercial-project.add-units');
            Route::post('commercial-project/{guid}/remove-units', [ProjectController::class, 'removeUnits'])
                ->name('commercial-project.remove-units');

            Route::post('companies/{guid}/approve', [CompanyController::class, 'approve'])
                ->name('companies.approve');
            Route::post('companies/{guid}', [CompanyController::class, 'update'])
                ->name('companies.update');
            Route::post('companies/{guid}/add-prices', [CompanyController::class, 'addPrices'])
                ->name('companies.add-prices');
            Route::get('companies/{guid}/shipping-addresses', [CompanyController::class, 'shippingAddressList'])
                ->name('companies.shipping-addresses.list');

            Route::get('dealer-orders', [Dealers\OrderController::class, 'list'])
                ->name('dealer-order.list');
            Route::post('dealer-orders', [Dealers\OrderController::class, 'create'])
                ->name('dealer-order.create');
            Route::post('dealer-orders/{guid}', [Dealers\OrderController::class, 'update'])
                ->name('dealer-order.update');
            Route::post('dealer-orders/{guid}/add-serial-number', [Dealers\OrderController::class, 'addSerialNumber'])
                ->name('dealer-order.add-serial-number');
            Route::post('dealer-orders/packing-slip/{guid}/add-serial-number', [Dealers\OrderController::class, 'addSerialNumberToPackingList'])
                ->name('dealer-order.packing-slip.add-serial-number');
            Route::post('dealer-orders/{guid}/add-or-update-packing-slip', [Dealers\OrderController::class, 'addOrUpdatePackingSlip'])
                ->name('dealer-order.add-or-update-packing-slip');
            Route::post('dealer-orders/{guid}/upload', [Dealers\OrderController::class, 'upload'])
                ->name('dealer-order.upload');
            Route::post('dealer-orders/packing-slip/{guid}/upload', [Dealers\OrderController::class, 'uploadToPackingSlip'])
                ->name('dealer-order.packing-slip.upload');
            Route::post('dealer-orders/add/invoice-data', [Dealers\OrderController::class, 'addInvoiceData'])
                ->name('dealer-order.add-invoice-data');
        }
    );
