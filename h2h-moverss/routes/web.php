<?php

use App\Http\Controllers\{API\ExportController,
    API\MobileAppController,
    AttachmentController,
    CalendarController,
    CashRegistries\CashRegistryController,
    ClientController,
    Communications\RecordController,
    CommunicationsController,
    Company\EmployeeController,
    Company\PeakDateController,
    Company\SalesTeamController,
    Company\TrucksController,
    CustomerController,
    DispatchController,
    Emails\MailchimpController,
    EstimateController,
    HomeController,
    Import\AngiController,
    Import\AuthorizePaymentController,
    Import\EquateMediaController,
    Import\HomeAdvisorController,
    Import\ImportController,
    Mailbox\Gmail\GMailController,
    MailboxController,
    MaterialController,
    OrderController,
    OrderNoteController,
    Orders\InventoryController,
    PaymentController,
    Reports\ActivityAuditReportController,
    Reports\ByManagersAndCompanyReportController,
    Reports\ByManagersReportController,
    Reports\EffectiveActionsReportController,
    Reports\EfficiencyReportController,
    Reports\FinancialCheckController,
    Reports\ForemanCashReportController,
    Reports\SalesFunelReportController,
    Reports\SalesReportController,
    Ringostat\WebhookController as RingostatWebhookController,
    Settings\DataTablesController,
    Settings\DivisionFooterTextController,
    Settings\EmailTemplateController,
    Settings\ItemController,
    Settings\Rate\EmployeeRateController,
    Settings\Rate\InterstateController,
    Settings\Rate\IntrastateController,
    Settings\Rate\LocalController,
    Settings\TagsController,
    Settings\UserController as UserSettingsController,
    Signature\HelloSignController,
    SizingController,
    Tasks\TypeController as TasksStatusController,
    TasksController,
    Twilio\TwilioWebhookController,
    UserController,
    WaypointController,
    WorkController,
    Zadarma\PBXController,
    Zadarma\WebhookController};
use App\Http\Controllers;
use App\Http\Controllers\Settings\DivisionController;
use App\Http\Middleware\EnsureTwilioWebhook;
use Illuminate\Support\Facades\Route;

/*
  |--------------------------------------------------------------------------
  | Web Routes
  |--------------------------------------------------------------------------
  |
  | Here is where you can register web routes for your application. These
  | routes are loaded by the RouteServiceProvider within a group which
  | contains the "web" middleware group. Now create something great!
  |
 */

Auth::routes(['register' => false]);

//Route::get('/ring', [RingCentralController::class, 'foo'])->middleware(['auth']);
//Route::get('/ring/wh', [RingCentralController::class, 'webhook']);


Route::group(['prefix' => 'customer', 'as' => 'customer.'], function () {
    Route::get('/order/{hash}', [CustomerController::class, 'orderPublicView'])
//        ->middleware('throttle:10,1') // 10 запросов в мин
        ->name('orderPublicView');

    Route::get('/order/{hash}/inventories', [CustomerController::class, 'getInventories'])
        ->middleware('throttle:10,1') // 10 запросов в мин
        ->name('orderPublicView.inventories');

    Route::post('/order/{hash}/inventories/autocomplete',
        [InventoryController::class, 'ajaxSearch'])->name('inventory.search');
    Route::post('/order/{hash}/inventories/save',
        [InventoryController::class, 'ajaxSave'])->name('inventory.save');
});

Route::group(['prefix' => 'webhook', 'as' => 'webhook.'], function () {
    Route::post('/mailjet', [EmailTemplateController::class, 'mailJetWebHook'])->name('mailjet');
    Route::post('/angi-ch', [AngiController::class, 'webHook'])->name('AngiCh');
    Route::post('/angi-la', [AngiController::class, 'webHook'])->name('AngiLa');
    Route::post('/HomeAdvisor', [HomeAdvisorController::class, 'webHook'])->name('HomeAdvisor');

    //Route::post('/EquateMedia', [EquateMediaController::class, 'webHook'])->name('EquateMediaCh');

    Route::post('/EquateMedia-IL', [EquateMediaController::class, 'webHook'])->name('EquateMediaCh');
    Route::post('/EquateMedia-CA', [EquateMediaController::class, 'webHook'])->name('EquateMediaLa');

    Route::post('/gmail', [GMailController::class, 'webhook'])->name('gmail');
    Route::group(['prefix' => 'twilio', 'as' => 'twilio.', 'middleware' => [EnsureTwilioWebhook::class]], function () {
        Route::group(['prefix' => 'sms', 'as' => 'sms.'], function () {
            Route::post('/', [TwilioWebhookController::class, 'handleSms'])->name('handleSms');
            Route::post('/statusCallback', [TwilioWebhookController::class, 'handleSmsStatus'])->name('handleSmsStatus');
        });
    });
//    Route::post('/ring', [RingCentralController::class, 'webhookSave']);
});


Route::group(['prefix' => 'import', 'as' => 'import.'], function () {
    Route::post('/Top10-IL', [ImportController::class, 'webHook'])->name('Top10-IL');
    Route::post('/Top10-CA', [ImportController::class, 'webHook'])->name('Top10-CA');
});


Route::get('/export/appGetOrders', [ExportController::class, 'export'])->name('export');

Route::group(['middleware' => ['auth']], function () {

    Route::get('/info', [Controllers\InfoController::class, 'getInfo'])
        ->name('info.project');

    Route::group(['prefix' => 'mailchimp', 'as' => 'mailchimp.'], function () {
        Route::get('/mandrillTemplates', [MailchimpController::class, 'getTemplates']);
        Route::post('/renderMandrill', [MailchimpController::class, 'renderMandrillTemplate']);
        Route::post('/sendMandrill', [MailchimpController::class, 'sendMandrillTemplate']);
    });

    Route::group(['prefix' => 'pbx', 'as' => 'pbx.'], function () {
        Route::post('/initPBX', [PBXController::class, 'initPBX']);
        Route::post('/callback', [PBXController::class, 'callback']);
        Route::get('/activeCalls', [PBXController::class, 'getActiveCalls']);
        Route::post('/sms', [PBXController::class, 'sendSMS'])->name('send-sms');
    });

    Route::get('/', [HomeController::class, 'index'])->name('home');

    Route::group(['prefix' => 'company', 'as' => 'company.'], function () {
        Route::post('/choose-division', [HomeController::class, 'chooseDivision'])->name('choose-division');

        Route::group(['prefix' => 'sales-team', 'as' => 'sales-team.'], function () {
            Route::post('/', [SalesTeamController::class, 'index'])->name('index');
            Route::post('/update', [SalesTeamController::class, 'update'])->name('update');
        });

        Route::group(['prefix' => 'employees', 'as' => 'employees.'], function () {
            Route::get('/', [EmployeeController::class, 'records'])->name('records');
            Route::view('/{id}', 'layouts.company.employee.record')->name('record');
            Route::post('/{id}', [EmployeeController::class, 'ajaxInfo'])->name('record.info');
            Route::post('/{id}/save', [EmployeeController::class, 'save'])->name('record.save');
            Route::post('/create-empty', [EmployeeController::class, 'createEmpty'])->name('record.create');
            Route::post('/autocomplete', [EmployeeController::class, 'employeesAutocompleteAjax']);
        });

        Route::get('/trucks', [TrucksController::class, 'records'])->name('trucks.records');
        Route::view('/trucks/{id}', 'layouts.company.trucks.record')->name('trucks.record');
        Route::post('/trucks/{id}', [TrucksController::class, 'ajaxInfo'])->name('trucks.record.info');
        Route::post('/trucks/{id}/save', [TrucksController::class, 'save'])->name('trucks.record.save');
        Route::post('/trucks/create-empty',
            [TrucksController::class, 'createEmpty'])->name('trucks.record.create');


        Route::view('/peak-dates', 'layouts.company.peak_date')->name('peak_dates');
        Route::post('/peak-dates', [PeakDateController::class, 'ajaxInfo'])->name('peak_dates.info');
        Route::post('/peak-dates/save', [PeakDateController::class, 'save'])->name('peak_dates.save');
    });

    Route::group(['prefix' => 'partners', 'as' => 'partner.'], function () {
        Route::get('/', [Controllers\Partners\CrudController::class, 'index'])->name('index');
        Route::view('/{id}', 'layouts.partners.show')->name('show');
        Route::post('/{id}', [Controllers\Partners\CrudController::class, 'ajaxInfo'])->name('info');
        Route::post('/{id}/save', [Controllers\Partners\CrudController::class, 'update'])->name('update');
        Route::post('/create', [Controllers\Partners\CrudController::class, 'create'])->name('create');
    });

    Route::group(['prefix' => 'client', 'as' => 'client.'], function () {
        Route::post('/profile', [ClientController::class, 'profile'])->name('record.profile');
        Route::post('/profile/save', [ClientController::class, 'profileSave'])->name('record.profile.save');
        Route::post('/profile/autocomplete',
            [ClientController::class, 'ajaxProfileAutocomplete'])->name('record.autocomplete');
    });

    Route::group(['prefix' => 'dispatch', 'as' => 'dispatch.'], function () {
        Route::get('/', [DispatchController::class, 'schedule'])->name('schedule');
        Route::post('/info', [DispatchController::class, 'ajaxInfo'])->name('schedule.info');
        Route::post('/logs', [DispatchController::class, 'ajaxLogs'])->name('schedule.logs');
        Route::post('/save', [DispatchController::class, 'save'])->name('schedule.save');
        Route::post('/notify', [DispatchController::class, 'sendNotify'])->name('schedule.notify');
        Route::post('/notifyAll', [DispatchController::class, 'sendNotifyToAll'])->name('schedule.notifyAll');
    });

    Route::group(['prefix' => 'calendar', 'as' => 'calendar.'], function () {
        Route::get('/', [CalendarController::class, 'schedule'])->name('schedule');
        Route::post('/cellInfo', [CalendarController::class, 'cellInfo'])->name('schedule.cell.info');
    });

    Route::group(['prefix' => 'communications', 'as' => 'communications.'], function () {
//        Route::view('/', 'layouts.render.component', [
//            'component' => 'app-communications-list',
//            'title' => null,
//            //'breadcrumbs' => null,
//            'mixed' => [
////                '/js/flatpicker-plugins.js'
//            ],
//            'assets' => [
////                '/css/flatpicker.css'
//            ],
//        ])->name('list');
        Route::post('/records', [CommunicationsController::class, 'recordsAjax'])->name('records');

        Route::get('/recordsUnanswered', [CommunicationsController::class, 'recordsUnanswered'])->name('recordsUnanswered');
        Route::post('/flow', [CommunicationsController::class, 'flow'])->name('flow');
        Route::post('/markConversation', [CommunicationsController::class, 'markConversation'])->name('markConversation');
        Route::post('/markStarred', [CommunicationsController::class, 'markStarred'])->name('markStarred');

        Route::post('/addIgnoreRecord', [CommunicationsController::class, 'addIgnoreContactAjax']);
        Route::post('/addClientRelationRecord', [CommunicationsController::class, 'addClientRelationRecordAjax']);
        Route::post('/createOrderToClient',
            [CommunicationsController::class, 'createOrderToClientAjax']);
        Route::post('/createClientOrderRelationRecord',
            [CommunicationsController::class, 'createClientOrderRelationRecordAjax']);

//        Route::post('communications/v2/records', [RecordController::class, 'index'])
//            ->name('v2.records')
//        ;

        Route::get('/filter-data',
            [RecordController::class, 'dataForFilter'])
            ->name('filter-data');

        Route::get('/employees',
            [Controllers\Communications\EmployeeController::class, 'index'])
            ->name('employees');

        Route::get('/incoming-calls',
            [Controllers\Communications\CallController::class, 'incomingList'])
            ->name('incoming-calls');

    });

    Route::group(['prefix' => 'communications.v2', 'as' => 'communications.'], function () {
        Route::view('/', 'layouts.render.component', [
            'component' => 'app-communications-list-new',
            'title' => null,
            'mixed' => [],
            'assets' => [],
        ])->name('v2.list');

        Route::post('/records', [RecordController::class, 'index'])
            ->name('v2.records');
        Route::post('/flow', [RecordController::class, 'flow'])
            ->name('v2.flow');
        Route::post('/email/{id}', [RecordController::class, 'emailData'])
            ->name('v2.emailData');
//
//        Route::get('/recordsUnanswered', [CommunicationsController::class, 'recordsUnanswered'])->name('recordsUnanswered');
//        Route::post('/flow', [CommunicationsController::class, 'flow'])->name('flow');
//        Route::post('/markConversation', [CommunicationsController::class, 'markConversation'])->name('markConversation');
//        Route::post('/markStarred', [CommunicationsController::class, 'markStarred'])->name('markStarred');
//
//        Route::post('/addIgnoreRecord', [CommunicationsController::class, 'addIgnoreContactAjax']);
//        Route::post('/addClientRelationRecord', [CommunicationsController::class, 'addClientRelationRecordAjax']);
//        Route::post('/createOrderToClient',
//            [CommunicationsController::class, 'createOrderToClientAjax']);
//        Route::post('/createClientOrderRelationRecord',
//            [CommunicationsController::class, 'createClientOrderRelationRecordAjax']);
//
//        Route::post('communications/v2/records', [RecordController::class, 'index'])
//            ->name('v2.records')
//        ;
    });

    Route::group(['prefix' => 'orders', 'as' => 'orders.'], function () {

        Route::group(['prefix' => 'pipeline', 'as' => 'pipeline.'], function () {
            Route::view('/', 'layouts.render.component', [
                'component' => 'app-orders-pipeline',
                'title' => 'Orders Pipeline',
                'mixed' => [
//                '/js/flatpicker-plugins.js'
                ],
                'assets' => [
//                '/css/flatpicker.css'
                ],
            ])->name('list');
            Route::post('/settings', [OrderController::class, 'pipelineRecordsSettingsAjax']);
            Route::post('/records', [OrderController::class, 'pipelineRecordsAjax']);
        });
        Route::post('/hellosign/signature_request/{Order}', [HelloSignController::class, 'signatureRequest'])->name('hellosign.signature.request');


        Route::get('/', [OrderController::class, 'records'])->name('records');
        Route::post('/', [OrderController::class, 'recordsAjaxDT'])->name('recordsDT');
        Route::post('/create', [OrderController::class, 'create'])->name('create');
        Route::post('/change-client', [OrderController::class, 'changeClient'])->name('changeClient');
        Route::post('/autocomplete', [OrderController::class, 'ajaxAutocomplete'])->name('autocomplete');

        Route::post('/view-preset-save', [OrderController::class, 'savePreset'])->name('preset.save');

        Route::post('/{id}', [OrderController::class, 'recordAjaxDT'])->name('recordDT');
        Route::get('/{id}', [OrderController::class, 'record'])->name('record');
        Route::post('/info', [OrderController::class, 'ajaxInfo'])->name('record.info');
        Route::post('/logs', [OrderController::class, 'ajaxLogs'])->name('record.logs');
        Route::post('/copy', [OrderController::class, 'ajaxCopy'])->name('record.copy');
        Route::post('/activity', [OrderController::class, 'ajaxActivityCommunications'])->name('record.activity');
        Route::post('/communicationsPanelHistory', [OrderController::class, 'ajaxOrderPanelCommunications']);
        Route::post('/communicationsPanelHistoryNew', [RecordController::class, 'forOrder'])
            ->name('v2.records-for-order')
        ;
//        Route::post('/calls', [RingCentralController::class, 'ajaxActivityCalls'])->name('calls');
        Route::get('/recordZadarma', [PBXController::class, 'ajaxZadarmaRecord'])->name('zadaramaRecord');
        Route::get('/callsZadarma', [PBXController::class, 'ajaxOrderCalls'])->name('callsZadarama');
//        Route::get('/calls/record/{id}', [RingCentralController::class, 'ajaxProxyMedia'])->name('calls.record');
        Route::post('/info-statuses', [OrderController::class, 'ajaxInfoOrder'])->name('record.info-statuses');
        Route::get('/order-statuses', [OrderController::class, 'ajaxOrderStatusesInfo'])->name('order.statuses-info');

        //================================================================================
        // ORDER INVENTORY
        Route::post('/inventory/autocomplete', [InventoryController::class, 'ajaxSearch'])->name('inventory.search');
        Route::post('/inventory/save', [InventoryController::class, 'ajaxSave'])->name('inventory.save');

        Route::post('{id}/inventory', [
            InventoryController::class, 'add'
        ])->name('inventory.add');

        Route::post('{id}/inventory/sort', [
            InventoryController::class, 'sort'
        ])->name('orders.inventory.sort');

        Route::post('{orderId}/inventory/{inventoryId}', [
            InventoryController::class, 'edit'
        ])->name('inventory.edit');

        Route::delete('{orderId}/inventory/{inventoryId}', [
            InventoryController::class, 'delete'
        ])->name('inventory.delete');
    //================================================================================

        Route::post('/materials', [MaterialController::class, 'records'])->name('materials.records');
        Route::post('/materials/save', [MaterialController::class, 'saveRecords'])->name('materials.save');
        Route::post('/works/save', [WorkController::class, 'save'])->name('works.save');
        Route::post('/works/remove', [WorkController::class, 'remove'])->name('works.remove');
        Route::post('/works/remove-assignments',
            [WorkController::class, 'removeAssignments'])->name('works.remove.assignments');
        Route::post('/works/peaks-days', [WorkController::class, 'peaksRecords'])->name('works.peaks');
        Route::get('/waypoints/states', [WaypointController::class, 'states'])->name('waypoints.states');
        Route::post('/waypoints/save', [WaypointController::class, 'save'])->name('waypoints.save');
        Route::post('/waypoints/remove', [WaypointController::class, 'remove'])->name('waypoints.remove');
        Route::post('/waypoints/save-sort', [WaypointController::class, 'saveSort'])->name('waypoints.save-sort');
        Route::post('/waypoints/zipGeoInfo', [WaypointController::class, 'zipGeoInfo'])->name('waypoints.zipAutocomplete');
        Route::post('/estimates/set-type', [EstimateController::class, 'saveType'])->name('estimates.saveType');
        Route::post('/estimates/save', [EstimateController::class, 'save'])->name('estimates.save');
        Route::post('/notes', [OrderNoteController::class, 'records'])->name('notes.records');
        Route::post('/notes/save', [OrderNoteController::class, 'save'])->name('notes.save');
        Route::post('/notes/remove', [OrderNoteController::class, 'remove'])->name('notes.remove');
        Route::post('/notes/update', [OrderNoteController::class, 'update'])->name('notes.update');
        Route::post('/payments', [PaymentController::class, 'records'])->name('payments.records');
        Route::post('/payments/create', [PaymentController::class, 'create'])->name('payments.create');
        Route::post('/payments/toggle-in-total', [PaymentController::class, 'toggleInTotal'])->name('payments.toggleInTotal');

        // PAYROLL
        Route::post('/payroll/{id}/toggle-process', [Controllers\Orders\PayrollController::class, 'markAsProcess'])
            ->name('payroll.toggle-process');
        Route::post('/payroll/{id}', [Controllers\Orders\PayrollController::class, 'update'])
            ->name('payroll.update');

        Route::post('/{id?}/order', [OrderController::class, 'saveOrder'])->name('record.order');
        Route::post('/{id?}/order/save-sizing',
            [SizingController::class, 'saveOrderSizing'])->name('record.save-sizing');
        Route::post('/{id?}/order/set-status', [OrderController::class, 'setStatus'])->name('record.setStatus');
        Route::post('/{id?}/order/set-status-closed',
            [OrderController::class, 'setStatusClosed'])->name('record.setStatusClosed');


    });

    Route::group(['prefix' => 'attachments', 'as' => 'attachments.'], function () {
        Route::post('/', [AttachmentController::class, 'records'])->name('records');
        Route::get('/dl/{hash}', [AttachmentController::class, 'dl'])->name('dl');
        Route::post('/create', [AttachmentController::class, 'create'])->name('create');
        Route::post('/remove', [AttachmentController::class, 'remove'])->name('remove');
    });

    Route::group(['prefix' => 'clients', 'as' => 'clients.'], function () {
        Route::get('/', [ClientController::class, 'clientsPage'])->name('records');
        Route::post('/', [ClientController::class, 'recordsAjaxDT'])->name('recordsDT');
        Route::post('/findDuplicates', [ClientController::class, 'findDuplicatesAjax'])->name('findDuplicates');
        Route::post('/mergeDuplicates', [ClientController::class, 'mergeDuplicatesAjax'])->name('mergeDuplicates');
    });

    Route::group(['prefix' => 'settings', 'as' => 'settings.'], function () {

        Route::group(['prefix' => 'divisions', 'as' => 'divisions.'], function () {
            Route::resource('/divisions', DivisionController::class)->only([
                'index', 'store'
            ]);
            Route::resource('/divisions-footer-texts', DivisionFooterTextController::class)->only([
                'index', 'store'
            ]);
            Route::post('/clear-divisions-cache', [DivisionController::class, 'clearCache']);
        });


        Route::get('/orders-statuses', [DataTablesController::class, 'statusHome'])->name('orders.statuses');
        Route::post('/orders-statuses', [DataTablesController::class, 'statusSave'])->name('orders.statuses.save');

        Route::get('/orders-statuses-routes',
            [DataTablesController::class, 'statusRouteHome'])->name('orders.statuses.routes');
        Route::post('/orders-statuses-routes',
            [DataTablesController::class, 'statusRouteSave'])->name('orders.statuses.routes.save');

        Route::get('/orders-statuses-groups',
            [DataTablesController::class, 'statusGroupHome'])->name('orders.statuses.groups');
        Route::post('/orders-statuses-groups',
            [DataTablesController::class, 'statusGroupSave'])->name('orders.statuses.groups.save');

        Route::get('/orders-sources', [DataTablesController::class, 'sourceHome'])->name('orders.sources');
        Route::post('/orders-sources', [DataTablesController::class, 'sourceSave'])->name('orders.sources.save');

        Route::group(['prefix' => 'materials', 'as' => 'materials.'], function () {
            Route::get('/', [MaterialController::class, 'homeDT'])->name('home');

            Route::get('/groups', [MaterialController::class, 'groupDT'])->name('group');
            Route::post('/groups-editor', [MaterialController::class, 'groupDT_EDITOR'])
                ->name('group.editor');

            Route::get('/records', [MaterialController::class, 'recordsDT'])->name('records');
            Route::post('/records-editor', [MaterialController::class, 'goodsDT_EDITOR'])
                ->name('records.editor');
            Route::post('/records/reOrder', [MaterialController::class, 'reOrder'])->name('records.records');
        });

        Route::get('/items', [ItemController::class, 'home'])->name('items');
        Route::get('/items/groups', [ItemController::class, 'groupDT'])->name('items.group');
        Route::post('/items/groups-editor', [ItemController::class, 'groupDT_EDITOR'])->name('items.group.editor');
        Route::get('/items/records', [ItemController::class, 'goodsDT'])->name('items.records');
        Route::post('/items/records-editor', [ItemController::class, 'goodsDT_EDITOR'])->name('items.records.editor');

        Route::get('/rates/local', [LocalController::class, 'records'])->name('rates.local');
        Route::post('/rates/local', [LocalController::class, 'dtEditor'])->name('rates.local.editor');

        Route::get('/rates/employee', [EmployeeRateController::class, 'records'])->name('rates.employee');
        Route::post('/rates/employee', [EmployeeRateController::class, 'dtEditor'])->name('rates.employee.editor');

        Route::get('/rates/intrastate', [IntrastateController::class, 'view'])->name('rates.intrastate');
        Route::post('/rates/intrastate-coefficients-datatable', [IntrastateController::class, 'coefficientsDatatable'])
            ->name('rates.intrastate.coefficients.datatable');
        Route::post('/rates/intrastate-coefficients-datatable-editor', [IntrastateController::class, 'coefficientsDatatableEditor'])
            ->name('rates.intrastate.coefficients.datatable.editor');

        Route::post('/rates/intrastate-coefficients-matrix', [IntrastateController::class, 'coefficientsMatrix'])
            ->name('rates.intrastate.coefficients.matrix');
        Route::post('/rates/intrastate-coefficients-matrix-editor', [IntrastateController::class, 'coefficientsMatrixEditor'])
            ->name('rates.intrastate.coefficients.matrix.editor');

        Route::get('/rates/interstate', [InterstateController::class, 'view'])->name('rates.interstate');
        Route::post('/rates/interstate-coefficients-matrix', [InterstateController::class, 'coefficientsMatrix'])
            ->name('rates.interstate.coefficients.matrix');
        Route::post('/rates/interstate-coefficients-matrix-editor', [InterstateController::class, 'coefficientsMatrixEditor'])
            ->name('rates.interstate.coefficients.matrix.editor');

        Route::post('/rates/interstate-ranges-datatable', [InterstateController::class, 'rangesDatatable'])
            ->name('rates.interstate.ranges.datatable');
        Route::post('/rates/interstate-ranges-datatable-editor', [InterstateController::class, 'rangesDatatableEditor'])
            ->name('rates.interstate.ranges.datatable.editor');

        Route::post('/rates/interstate-shuttle-datatable', [InterstateController::class, 'shuttleDatatable'])
            ->name('rates.interstate.shuttle.datatable');
        Route::post('/rates/interstate-shuttle-datatable-editor', [InterstateController::class, 'shuttleDatatableEditor'])
            ->name('rates.interstate.shuttle.datatable.editor');


        Route::post('/rates/intrastate-miles-datatable', [IntrastateController::class, 'milesDatatable'])
            ->name('rates.intrastate.miles.datatable');
        Route::post('/rates/intrastate-miles-datatable-editor', [IntrastateController::class, 'milesDatatableEditor'])
            ->name('rates.intrastate.miles.datatable.editor');


        Route::post('/rates/intrastate-volumes-datatable', [IntrastateController::class, 'volumesDatatable'])
            ->name('rates.intrastate.volumes.datatable');
        Route::post('/rates/intrastate-weights-datatable', [IntrastateController::class, 'weightsDatatable'])
            ->name('rates.intrastate.weights.datatable');
        Route::post('/rates/intrastate-weights-datatable-editor', [IntrastateController::class, 'weightsDatatableEditor'])
            ->name('rates.intrastate.weights.datatable.editor');


        Route::get('/users', [UserSettingsController::class, 'usersRecords'])->name('users.records');
        Route::post('/users', [UserSettingsController::class, 'usersDtEditor'])->name('users.records.editor');
        Route::post('/users-list', [UserSettingsController::class, 'usersAjax'])->name('users.records.ajax');

        Route::view('/routes2roles', 'layouts.settings.users.route2role')->name('users.routes2roles');
        Route::post('/routes2roles', [UserSettingsController::class, 'ajaxInfo']);
        Route::post('/routes2roles/save', [UserSettingsController::class, 'save'])->name('users.routes2roles.save');

        Route::group(['prefix' => 'email-templates', 'as' => 'email-templates.'], function () {
            Route::view('/', 'layouts.settings.email_templates')->name('records');
            Route::post('/', [EmailTemplateController::class, 'ajaxInfo']);
            Route::post('/save', [EmailTemplateController::class, 'save'])->name('save');
            Route::post('/sender', [EmailTemplateController::class, 'send'])->name('send');
            Route::get('/mailjet/list', [EmailTemplateController::class, 'ajaxMailjetTemplates']);
            Route::get('/mandrill/list', [EmailTemplateController::class, 'ajaxMandrillTemplates']);
        });

//        Route::view('/email-templates', 'layouts.settings.email_templates')->name('email-templates.records');
//        Route::post('/email-templates', [EmailTemplateController::class, 'ajaxInfo'])->name('email-templates');
//        Route::post('/email-templates/save', [EmailTemplateController::class, 'save'])->name('email-templates.save');
//        Route::post('/email-templates/sender', [EmailTemplateController::class, 'send'])->name('email-templates.send');


        Route::view('/clients-tags', 'layouts.render.with-container', [
            'component' => 'settings-tags',
            'params' => 'section="clients"',
            'title' => 'Manage Client Tags',
            'h2' => 'Records',
        ])->name('client.tags');
        Route::post('/clients-tags', [TagsController::class, 'getRecords'])->name('client.tags.records');
        Route::post('/clients-tags/save', [TagsController::class, 'saveRecords'])->name('client.tags.records.save');


        Route::view('/orders-tags', 'layouts.render.with-container', [
            'component' => 'settings-tags',
            'params' => 'section="orders"',
            'title' => 'Manage Order Tags',
            'h2' => 'Records',
        ])->name('orders.tags');
        Route::post('/orders-tags', [TagsController::class, 'getRecords'])->name('orders.tags.records');
        Route::post('/orders-tags/save', [TagsController::class, 'saveRecords'])->name('orders.tags.records.save');

        Route::view('/teams-plans', 'layouts.render.clean', [
            'component' => 'settings-teams-plans',
        ])->name('teams_plans');
    });

    Route::group(['prefix' => 'cash-registry', 'as' => 'cash-registry.'], function () {
        Route::get('/foremans', [CashRegistryController::class, 'renderFormans'])
            ->name('foremans.index');
        Route::get('/operations', [CashRegistryController::class, 'renderOperation'])
            ->name('operations.index');
        Route::get('/get-foremans', [CashRegistryController::class, 'records'])
            ->name('foremans');
        Route::post('/add-operation', [CashRegistryController::class, 'addOperation'])
            ->name('add-operation');
        Route::get('/get-operations', [CashRegistryController::class, 'getOperations'])
            ->name('operations');
        Route::get('/operation-types', [CashRegistryController::class, 'operationTypes'])
            ->name('type.operations');
        Route::get('/operation-excel', [CashRegistryController::class, 'exportExcel'])
            ->name('excel.operations');

    });

    Route::group(['prefix' => 'reports', 'as' => 'reports.'], function () {

        Route::get('/export-orders', [OrderController::class, 'export'])->name('export.orders');

        Route::get('/activity-audit-report', [ActivityAuditReportController::class, 'view'])->name('activity-audit-report');
        Route::post('/activity-audit-report-datatable', [ActivityAuditReportController::class, 'datatable'])
            ->name('audit.datatable');


        Route::view('/report-1', 'layouts.render.with-container', [
            'component' => 'report-one',
            'title' => 'Report By Managers',
        ])->name('report1');
        Route::post('/report-1', [ByManagersReportController::class, 'report'])->name('report1.records');

        Route::view('/report-effective-actions', 'layouts.render.with-container', [
            'component' => 'report-effective-actions',
            'title' => 'Analytics of effective actions',
        ])->name('effective-actions');
        Route::post('/report-effective-actions',
            [EffectiveActionsReportController::class, 'report'])->name('effective.generate');

        Route::view('/report-by-managers', 'layouts.render.with-container', [
            'component' => 'report-by-managers',
            'title' => 'Analytics by Managers and Company',
        ])->name('by-managers');
        Route::post('/report-by-managers',
            [ByManagersAndCompanyReportController::class, 'report'])->name('by-managers.generate');
        Route::post('/report-by-managers/users-with-orders',
            [ByManagersAndCompanyReportController::class, 'usersWithOrders'])->name('report-users-with-orders');

        Route::get('/calls-report', [CommunicationsController::class, 'callLog'])->name('callLog');

        Route::get('efficiency-report', [
            EfficiencyReportController::class, 'view'
        ])->name('efficiency-report.view');
        Route::post('efficiency-datatable', [
            EfficiencyReportController::class, 'datatable'
        ])->name('efficiency.datatable');
//        Route::get('efficiency-report', [\App\Http\Controllers\Reports\EfficiencyReportController::class, 'view'])->name('efficiency-report.view');

        //---------------------Sales report
        Route::get('sales-report', [
            SalesReportController::class, 'view'
        ])->name('sales-report.view');

        Route::post('sales-datatable', [
            SalesReportController::class, 'datatable'
        ])->name('sales.datatable');

        Route::post('sales-datatable/export-csv', [
            SalesReportController::class, 'exportCsv'
        ])->name('sales.datatable.export.csv');

        Route::post('sales-datatable/export-excel', [
            SalesReportController::class, 'exportExcel'
        ])->name('sales.datatable.export.excel');
        //----------------------------------

        //---------------------Financial Check report
        Route::post('/financial-check-report/managers', [
            FinancialCheckController::class, 'managers'
        ])->name('financial.check.report.managers');

        Route::get('/financial-check-report', [
            FinancialCheckController::class, 'index'
        ])->name('financial.check.report.index');
        Route::post('/financial-check-report', [
            FinancialCheckController::class, 'report'
        ])->name('financial.check.report.data');

        Route::post('/financial-check-report/export-csv', [
            FinancialCheckController::class, 'exportCsv'
        ])->name('financial.datatable.export.csv');
        Route::post('financial-check-report/export-excel', [
            FinancialCheckController::class, 'exportExcel'
        ])->name('financial.datatable.export.excel');
        //----------------------------------

        //---------------------Foreman Cash Report
        Route::post('/foreman-cash-report/foremans', [
            ForemanCashReportController::class, 'foremans'
        ])->name('foreman.cash.report.foremans');

        Route::get('/foreman-cash-report', [
            ForemanCashReportController::class, 'index'
        ])->name('foreman.cash.report.index');
        Route::post('/foreman-cash-report', [
            ForemanCashReportController::class, 'report'
        ])->name('foreman.cash.report.data');

        Route::post('/foreman-cash-report/export-csv', [
            ForemanCashReportController::class, 'exportCsv'
        ])->name('foreman.cash.report.export.csv');
        Route::post('foreman-cash-report/export-excel', [
            ForemanCashReportController::class, 'exportExcel'
        ])->name('foreman.cash.report.export.excel');
        //----------------------------------

        //---------------------Sales funel report
        Route::get('/sales-funel-report', [
            SalesFunelReportController::class, 'index'
        ])->name('sales.funel.report.index');
        Route::post('/sales-funel-report', [
            SalesFunelReportController::class, 'report'
        ])->name('sales.funel.report.data');
        Route::get('/sales-funel-report/filter/sales-team', [
            SalesFunelReportController::class, 'salesTeam'
        ])->name('sales.funel.report.data.filter.sale-team');
        //----------------------------------

        Route::get('/report-authorize', [AuthorizePaymentController::class, 'home'])->name('authorize');
        Route::post('/report-authorize', [AuthorizePaymentController::class, 'report'])->name('authorize.report');
        Route::post('/report-authorize/save',
            [AuthorizePaymentController::class, 'saveManagerData'])->name('authorize.report.save');
        Route::post('/report-authorize/status-autocomplete',
            [AuthorizePaymentController::class, 'statusesAutocomplete'])->name('authorize.report.status-autocomplete');
        Route::post('/report-authorize/order-autocomplete',
            [AuthorizePaymentController::class, 'orderIDAutocomplete'])->name('report-authorize.order-autocomplete');
    });

    Route::group(['prefix' => 'authorize', 'as' => 'authorize.'], function () {
        Route::post('/process-payment', [AuthorizePaymentController::class, 'paymentProcess'])->name('payment-process');
    });

    Route::group(['prefix' => 'tasks', 'as' => 'tasks.'], function () {
        Route::view('/calendar', 'layouts.render.component', [
            'component' => 'app-tasks-calendar',
            'title' => 'Tasks Calendar',
            'mixed' => [
                '/js/flatpicker-plugins.js'
            ],
            'assets' => [
                '/css/flatpicker.css'
            ],
        ])->name('tasks-calendar');

        Route::post('/', [TasksController::class, 'session'])->name('session');
        Route::post('/create', [TasksController::class, 'create'])->name('create');
        Route::post('/view', [TasksController::class, 'view'])->name('view');
        Route::post('/modifyTask', [TasksController::class, 'modify'])->name('modify');
        Route::post('/removeTask', [TasksController::class, 'remove'])->name('remove');

        Route::post('/pipeline', [TasksController::class, 'pipeline'])->name('pipeline');
        Route::get('/environment', [TasksController::class, 'environment'])->name('environment');
        Route::post('/viewed-all', [TasksController::class, 'viewedAll'])->name('viewed-all');
        Route::get('/statistics', [TasksController::class, 'statistics'])->name('statistics');

        Route::view('/settings-types', 'layouts.render.with-container', [
            'component' => 'settings-tasks-types',
            'title' => 'Manage Tasks Types',
            'h2' => 'Records',
        ])->name('settings.types');
        Route::post('/settings-types', [TasksStatusController::class, 'records'])->name('settings.types.records');
        Route::post('/settings-types/save',
            [TasksStatusController::class, 'save'])->name('settings.types.records.save');
    });

    Route::group(['prefix' => 'mailbox', 'as' => 'mailbox.'], function () {
        Route::get('/', [MailboxController::class, 'home'])->name('home');
        Route::post('/', [GMailController::class, 'sync'])->name('sync');
        Route::post('/open', [GMailController::class, 'open'])->name('open');
        Route::post('/accounts', [GMailController::class, 'accounts'])->name('accounts');
        Route::post('/account-set-permissions', [GMailController::class, 'accountSetPermissions'])->name('setPermissions');
        Route::post('/account-status-toggle', [GMailController::class, 'accountStatusToggle'])->name('changeStatusPermissions');
        Route::post('/send', [GMailController::class, 'send'])->name('send');
        Route::get('/join', [GMailController::class, 'join'])->name('join');
        Route::get('/join-callback', [GMailController::class, 'joinAccount'])->name('join.cb');
        Route::get('/logout/{id}', [GMailController::class, 'logoutAccount'])->name('logout');
    });

    Route::get('/userEnvironment', [UserController::class, 'getUserEnvironment']);

});


if (env('APP_ENV') == 'local') {
    Route::group(['prefix' => 'test', 'as' => 'test.'], function () {
        // сделать middleware на ip + Signature validation
        Route::get('/call',
            [WebhookController::class, 'testCall']);
    });
}


Route::group(['prefix' => 'zadarma', 'as' => 'zadarma.'], function () {
    // сделать middleware на ip + Signature validation
    Route::post('/webhook/{division_id}',
        [WebhookController::class, 'catchEvents'])
        ->name('zadarmaWebhook')
        ->where('division_id', '[0-9]+');
    Route::get('/webhook/{division_id}', [WebhookController::class, 'echo'])
        ->name('zadarmaEcho')
        ->where('division_id', '[0-9]+');
});

Route::group(['prefix' => 'ringostat', 'as' => 'ringostat.'], function () {
    // check
    Route::post('/webhook/after_call',
        [RingostatWebhookController::class, 'handleAfterCall'])
        ->name('handleAfterCall');

    Route::post('/webhook/before_call',
        [RingostatWebhookController::class, 'handleBeforeCall'])
        ->name('handleBeforeCall');

    Route::post('/webhook/taking_call',
        [RingostatWebhookController::class, 'handleTakingCall'])
        ->name('handleTakingCall');

    Route::post('/webhook/after_out_call',
        [RingostatWebhookController::class, 'handleAfterOutCall'])
        ->name('handleAfterOutCall');

    Route::post('/webhook/before_out_call',
        [RingostatWebhookController::class, 'handleBeforeOutCall'])
        ->name('handleBeforeOutCall');

    Route::post('/webhook/location_forwarding',
        [RingostatWebhookController::class, 'handleLocationForwarding'])
        ->name('handleLocationForwarding');

    Route::post('/webhook/processed_call_ai',
        [RingostatWebhookController::class, 'handleCallProcessedAi'])
        ->name('handleCallProcessedAi');
});

//Route::post('communications/v2/records', [RecordController::class, 'index'])->name('v2.records');
Route::get('/employees.r',
    [Controllers\Communications\EmployeeController::class, 'index'])
    ->name('employees.r');

Route::post('/test/sales-datatable/export-csv', [
    SalesReportController::class, 'exportCsv'
])->name('test.sales.datatable.export.csv');
Route::post('/test/sales-datatable/export-excel', [
    SalesReportController::class, 'exportExcel'
])->name('test.sales.datatable.export.excel');

Route::post('/test/sales-datatable', [
    SalesReportController::class, 'datatable'
])->name('test.sales.datatable');


Route::post('/test/cash-registry/add-operation', [CashRegistryController::class, 'addOperation'])
    ->name('test.add-operation');
Route::get('/test/cash-registry/operations', [CashRegistryController::class, 'getOperations'])
    ->name('test.get-operation');
Route::get('/test/operation-types', [CashRegistryController::class, 'operationTypes'])
    ->name('test.type.operations');
Route::get('/test/operation-excel', [CashRegistryController::class, 'exportExcel'])
    ->name('test.excel.operations');

Route::get('/test/pdf/inspection/{id}', [MobileAppController::class, 'viewInspectionPdf'])
    ->name('test.pdf.inspection');
Route::get('/test/pdf/waiver/{id}', [MobileAppController::class, 'viewWaiverPdf'])
    ->name('test.pdf.waiver');

Route::post('/test/com-panel/message/{id}', [RecordController::class, 'emailData'])
    ->name('test.com-panel.emailData');
