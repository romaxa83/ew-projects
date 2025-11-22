<?php

use App\Http\Controllers\ApiController;
use App\Http\Controllers\V1\Saas\Admins\AdminController;
use App\Http\Controllers\V1\Saas\Admins\AdminForgotPasswordController;
use App\Http\Controllers\V1\Saas\Admins\ProfileController;
use App\Http\Controllers\V1\Saas\Admins\ResetPasswordController;
use App\Http\Controllers\V1\Saas\AuthController;
use App\Http\Controllers\V1\Saas\Companies\CompanyController;
use App\Http\Controllers\V1\Saas\Companies\CompanyRegistrationController;
use App\Http\Controllers\V1\Saas\GPS\DeviceController;
use App\Http\Controllers\V1\Saas\GPS\DeviceRequestController;
use App\Http\Controllers\V1\Saas\GPS\FlespiWebhookController;
use App\Http\Controllers\V1\Saas\GPS\HistoryController;
use App\Http\Controllers\V1\Saas\Intl\LanguageController;
use App\Http\Controllers\V1\Saas\Invoices\InvoicesController;
use App\Http\Controllers\V1\Saas\Permissions\RoleController;
use App\Http\Controllers\V1\Saas\Support\SupportBackofficeController;
use App\Http\Controllers\V1\Saas\Support\SupportController;
use App\Http\Controllers\V1\Saas\Support\SupportCrmController;
use App\Http\Controllers\V1\Saas\TextBlocks\TextBlockController;
use App\Http\Controllers\V1\Saas\Translations\TranslationController;

Route::get('', [ApiController::class, 'api'])
    ->name('saas');

Route::post('login', [AuthController::class, 'login'])
    ->name('login');

Route::post('refresh-token', [AuthController::class, 'refreshToken'])
    ->name('refreshToken');

Route::middleware('auth:api_admin')
    ->post('logout', [AuthController::class, 'logout'])
    ->name('logout');

Route::post('password-forgot', [AdminForgotPasswordController::class, 'sendResetLinkEmail'])
    ->name('password.forgot');

Route::post('password-set', [ResetPasswordController::class, 'reset'])
    ->name('password.reset');

Route::prefix('admins')
    ->name('admins.')
    ->middleware('auth:api_admin')
    ->group(
        function () {
            Route::apiResource('', AdminController::class)
                ->parameters(['' => 'admin']);
        }
    );

Route::prefix('profile')
    ->name('profile.')
    ->middleware('auth:api_admin')
    ->group(
        function () {
            Route::put('change-password', [ProfileController::class, 'changePassword'])
                ->name('change-password');

            Route::post('upload-photo', [ProfileController::class, 'uploadPhoto'])
                ->name('upload-photo');

            Route::delete('delete-photo', [ProfileController::class, 'deletePhoto'])
                ->name('delete-photo');

            Route::get('', [ProfileController::class, 'show'])
                ->name('profile');

            Route::put('', [ProfileController::class, 'update'])
                ->name('profile-update');
        }
    );

Route::prefix('roles')
    ->name('roles.')
    ->middleware('auth:api_admin')
    ->group(
        function () {
            Route::get('permissions', [RoleController::class, 'permissions'])
                ->name('permissions');

            Route::apiResource('', RoleController::class)
                ->parameters(['' => 'role']);
        }
    );

// company registration request
Route::prefix('company-registration')
    ->name('company-registration.')
    ->group(
        function () {
            Route::post('registration-request', [CompanyRegistrationController::class, 'registrationRequest'])
                ->name('registration-request');
            Route::post(
                'confirm-registration-email',
                [CompanyRegistrationController::class, 'confirmRegistrationEmail']
            )
                ->name('confirm-registration-email');

            Route::middleware('auth:api_admin')
                ->group(
                    function () {
                        Route::put('{company_registration}/approve', [CompanyRegistrationController::class, 'approve'])
                            ->name('approve');
                        Route::put('{company_registration}/decline', [CompanyRegistrationController::class, 'decline'])
                            ->name('decline');
                        Route::apiResource('', CompanyRegistrationController::class)
                            ->parameters(['' => 'company_registration'])
                            ->only(
                                [
                                    'index',
                                    'show',
                                ]
                            );
                    }
                );
        }
    );

Route::prefix('companies')
    ->name('companies.')
    ->middleware('auth:api_admin')
    ->group(
        function () {
            Route::get('shortlist', [CompanyController::class, 'shortlist'])
                ->name('shortlist');
            Route::put('{company}/status', [CompanyController::class, 'changeActiveStatus'])
                ->name('status');
            Route::post('{company}/send-destroy-notification', [CompanyController::class, 'sendDestroyNotification'])
                ->name('send-destroy-notification');
            Route::post('company/set-destroy', [CompanyController::class, 'setDestroy'])
                ->name('set-destroy');

            Route::get('{id}/devices-info', [CompanyController::class, 'devicesInfo'])
                ->name('devices-info');
            Route::get('{id}/admins', [CompanyController::class, 'admins'])
                ->name('admins');

            Route::delete('{company}', [CompanyController::class, 'delete'])
                ->name('delete');

            Route::apiResource('', CompanyController::class)
                ->parameters(['' => 'company'])
                ->except(['store','destroy']);
        }
    );

Route::prefix('invoices')
    ->name('invoices.')
    ->middleware('auth:api_admin')
    ->group(
        function () {
            Route::get('companies-list', [InvoicesController::class, 'companiesList'])
                ->name('companies-list');
            Route::apiResource('', InvoicesController::class)
                ->parameters(['' => 'invoice'])
                ->except(['store', 'update', 'destroy']);
        }
    );

Route::prefix('translates')
    ->name('translates.')
    ->middleware('auth:api_admin')
    ->group(
        function () {
            Route::post('sync', [TranslationController::class, 'sync'])
                ->name('sync');
            Route::apiResource('', TranslationController::class)
                ->parameters(['' => 'translate'])
                ->middleware('throttleIp:1200,1');
        }
    );

Route::prefix('languages')
    ->name('languages.')
    ->middleware('auth:api_admin')
    ->group(
        function () {
            Route::get('selected', [LanguageController::class, 'getSelected']);
            Route::put('change-language', [LanguageController::class, 'changeLanguage']);
            Route::apiResource('', LanguageController::class)
                ->parameters(['' => 'language'])
                ->except('store', 'update', 'destroy');
        }
    );

Route::prefix('text-blocks')
    ->name('text-blocks.')
    ->group(
        function () {
            Route::get('render', [TextBlockController::class, 'render']);

            Route::middleware('auth:api_admin')->group(
                function () {
                    Route::get('groups', [TextBlockController::class, 'groups'])
                        ->name('groups');
                    Route::get('scopes', [TextBlockController::class, 'scopes'])
                        ->name('scopes');

                    Route::apiResource('', TextBlockController::class)
                        ->parameters(['' => 'textBlock']);
                }
            );
        }
    );

Route::prefix('support')
    ->name('support.')
    ->group(
        function () {
            Route::prefix('back-office')
                ->name('back-office.')
                ->middleware('auth:api_admin')
                ->group(
                    function () {
                        Route::apiResource('', SupportBackofficeController::class)
                            ->except('store');
                        Route::get('{supportRequest}', [SupportBackofficeController::class, 'show']);
                        Route::put('{supportRequest}/take', [SupportBackofficeController::class, 'take'])
                            ->name('take');
                        Route::put('{supportRequest}/set-label', [SupportBackofficeController::class, 'setLabel'])
                            ->name('set-label');
                        Route::put('{supportRequest}/change-manager', [SupportBackofficeController::class, 'changeManager'])
                            ->name('change-manager');
                        Route::put('{supportRequest}/close', [SupportBackofficeController::class, 'close'])
                            ->name('close');

                        Route::get('{supportRequest}/messages', [SupportBackofficeController::class, 'indexMessages'])
                            ->name('index-messages');
                        Route::post('{supportRequest}/message', [SupportBackofficeController::class, 'storeMessage'])
                            ->name('store-message');
                        Route::get('{supportRequest}/message/{supportRequestMessage}', [SupportBackofficeController::class, 'showMessage'])
                            ->name('show-message');
                    }
            );

            Route::prefix('crm')
                ->name('crm.')
                ->middleware('auth:api')
                ->group(
                    function () {
                        Route::apiResource('', SupportCrmController::class)
                            ->except('store')->names(['index' => 'index']);
                        Route::get('{supportRequest}', [SupportCrmController::class, 'show']);
                        Route::put('{supportRequest}/close', [SupportCrmController::class, 'close'])
                            ->name('close');
                        Route::get('{supportRequest}/messages', [SupportCrmController::class, 'indexMessages'])
                            ->name('index-messages');
                        Route::post('{supportRequest}/message', [SupportCrmController::class, 'storeMessage'])
                            ->name('store-message');
                        Route::get('{supportRequest}/message/{supportRequestMessage}', [SupportCrmController::class, 'showMessage'])
                            ->name('show-message');
                        Route::post('', [SupportCrmController::class, 'store'])
                            ->name('store')->withoutMiddleware('auth:api');
                    }
            );

            Route::middleware('auth:api,api_admin')
                ->group(
                    function () {
                        Route::get('statuses', [SupportController::class, 'getStatusesList'])
                            ->name('statuses');
                        Route::get('labels', [SupportController::class, 'getLabelsList'])
                            ->name('labels');
                        Route::get('sources', [SupportController::class, 'getSourcesList'])
                            ->name('sources');
                    }
            );

        }
    );

Route::prefix('gps-devices')
    ->name('gps-devices.')
    ->middleware('auth:api_admin')
    ->group(
        function () {
            Route::apiResource('', DeviceController::class)
                ->except(['destroy', 'update']);

            Route::put('{id}', [DeviceController::class, 'update'])
                ->name('update');
            Route::put('{id}/approve-request', [DeviceController::class, 'approveRequest'])
                ->name('approve-request');
            Route::put('{id}/deactivate', [DeviceController::class, 'deactivate'])
                ->name('deactivate');

            Route::get('flespi', [DeviceController::class, 'listFromFlespi'])
                ->name('flespi_list');

            Route::get('/requests', [DeviceRequestController::class, 'index'])
                ->name('request');
            Route::post('/requests', [DeviceRequestController::class, 'create'])
                ->name('request-create');

            Route::put('/requests/{id}', [DeviceRequestController::class, 'update'])
                ->name('request-update');
        }
    );

Route::prefix('gps')
    ->name('gps.')
    ->middleware('auth:api_admin')
    ->group(
        function () {
            Route::get('history', [HistoryController::class, 'index'])
                ->name('history');

//            Route::put('speed-limit', 'SettingsController@updateSpeedLimit')
//                ->name('update-speed-limit');
//
//            Route::get('alerts', [AlertController::class, 'index'])
//                ->name('alerts-index');
        }
    );

Route::prefix('flespi-webhook')
    ->name('flespi-webhook.')
    ->middleware('flespi.auth')
    ->group(
        function () {
            Route::put('update', [FlespiWebhookController::class, 'update'])
                ->name('update');

            Route::post('connected', [FlespiWebhookController::class, 'connectDevice'])
                ->name('connect-device');

            Route::put('disconnected', [FlespiWebhookController::class, 'disconnectDevice'])
                ->name('disconnect-device');

            Route::delete('delete', [FlespiWebhookController::class, 'delete'])
                ->name('delete');
        }
    );

Route::get('notifications', [
    \App\Http\Controllers\V1\Saas\Notifications\NotificationController::class,
    'index'
])
    ->name('notifications');
Route::put('notifications/read', [
    \App\Http\Controllers\V1\Saas\Notifications\NotificationController::class,
    'read'
])
    ->name('notifications-read');
Route::get('notifications/count', [
    \App\Http\Controllers\V1\Saas\Notifications\NotificationController::class,
    'count'
])
    ->name('notifications-count');
