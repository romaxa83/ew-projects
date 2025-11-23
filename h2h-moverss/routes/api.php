<?php

use App\Http\Controllers\API;
use App\Http\Controllers\InfoController;
use App\Http\Controllers\TasksController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1', 'as' => 'api.'], function () {

    Route::post('mobile-login', [API\MobileAppController::class, 'login'])->name('login'); // Deprecated
    Route::group([
        'prefix' => 'mobile',
        'as' => 'mobile.',
        'middleware' => [
            'auth:sanctum',
            'ability:mobile'
        ]
    ],
        function () {

            Route::get('/info', [InfoController::class, 'getInfo'])
                ->name('info.test.project');
            Route::get('/statistics', [TasksController::class, 'statistics'])
                ->name('statistics');
            Route::post('/viewed-all', [TasksController::class, 'viewedAll'])
                ->name('viewed-all');

            Route::post('login', [API\AuthController::class, 'login'])
                ->withoutMiddleware(['auth:sanctum', 'ability:mobile'])
                ->name('login');
            Route::get('/profile', [API\AuthController::class, 'profile'])
                ->name('profile');

            Route::get('/', [API\MobileAppController::class, 'getOrders'])->name('orders');

            Route::get('/{id}', [API\MobileAppController::class, 'getOrder'])->name('get.order');
            Route::post('/{id}/estimate', [API\MobileAppController::class, 'storeEstimate'])->name('save.estimate');
            Route::post('/{id}/bol', [API\MobileAppController::class, 'storeBol'])->name('save.bol');
            Route::post('/{id}/signatures', [API\MobileAppController::class, 'storeSignatures'])->name('save.signatures');
            Route::post('/{id}/update', [API\MobileAppController::class, 'update'])
                ->name('update.mobile-estimate');
            Route::delete('/{id}/bol', [API\MobileAppController::class, 'deleteBol'])
                ->name('delete.bol');
            Route::delete('/{id}/estimate', [API\MobileAppController::class, 'deleteEstimate'])
                ->name('delete.estimate');

            // payroll
            Route::post('/{id}/payroll', [API\PayrollController::class, 'storePayroll'])
                ->name('save.payroll');
            Route::get('/payrolls', [API\PayrollController::class, 'getPayrolls'])
                ->name('payrolls');
            Route::get('/transactions', [API\PayrollController::class, 'getTransactions'])
                ->name('transactions');

            Route::post('/{id}/reject', [API\MobileAppController::class, 'rejectOrder'])
                ->name('reject.order');

            Route::post('/{id}/note', [API\MobileAppController::class, 'storeNote'])
                ->name('note.order');

            Route::get('/view/estimate/{id}', [API\MobileAppController::class, 'viewEstimatePdf'])
                ->withoutMiddleware(['auth:sanctum', 'ability:mobile'])
                ->name('view.estimate');
            Route::get('/print/estimate/{id}', [API\MobileAppController::class, 'printEstimatePdf'])
                ->withoutMiddleware(['auth:sanctum', 'ability:mobile'])
                ->name('print.estimate');

            Route::get('/view/bol/{id}', [API\MobileAppController::class, 'viewBolPdf'])
                ->withoutMiddleware(['auth:sanctum', 'ability:mobile'])
                ->name('view.bol');
            Route::get('/print/bol/{id}', [API\MobileAppController::class, 'printBolPdf'])
                ->withoutMiddleware(['auth:sanctum', 'ability:mobile'])
                ->name('print.bol');

            Route::get('/view/inspection/{id}', [API\MobileAppController::class, 'viewInspectionPdf'])
                ->withoutMiddleware(['auth:sanctum', 'ability:mobile'])
                ->name('view.inspection');
            Route::get('/print/inspection/{id}', [API\MobileAppController::class, 'printInspectionPdf'])
                ->withoutMiddleware(['auth:sanctum', 'ability:mobile'])
                ->name('print.inspection');

            Route::get('/view/waiver/{id}', [API\MobileAppController::class, 'viewWaiverPdf'])
                ->withoutMiddleware(['auth:sanctum', 'ability:mobile'])
                ->name('view.waiver');
            Route::get('/print/waiver/{id}', [API\MobileAppController::class, 'printWaiverPdf'])
                ->withoutMiddleware(['auth:sanctum', 'ability:mobile'])
                ->name('print.waiver');
        });

});

Route::group([
    'prefix' => 'vapi',
    'as' => 'vapi.',
    'middleware' => [
        'auth.vapi',
    ]
],  function () {

    Route::post('call-data', [API\VapiController::class, 'callData'])
        ->name('call-data');
    Route::post('request-for-client', [API\VapiController::class, 'requestForClient'])
        ->name('request-for-client');

    Route::post('client-by-number', [API\VapiController::class, 'getClientByPhone'])
        ->name('client-by-number');
    Route::post('employee-to-transfer', [API\VapiController::class, 'getEmployeeToTransfer'])
        ->name('employee-to-transfer');
    Route::post('webhook', [API\VapiController::class, 'webhook'])
        ->name('webhook');
});


