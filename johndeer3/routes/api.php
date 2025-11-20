<?php

use App\Http\Controllers\Api\Admin;
use App\Http\Controllers\Api\Auth;
use App\Http\Controllers\Api\Site;
use App\Http\Controllers\Api\Imports;
use App\Http\Controllers\Api\Common;

Route::middleware('set.locale')->group(function() {
    // Auth
    Route::post('login',[Auth\LoginController::class, 'login'])
        ->name('api.login')
        ->middleware('header.local');
    Route::post('reset-password',[Auth\LoginController::class, 'resetPassword'])
        ->name('api.reset-password')
        ->middleware('header.local');
    Route::post('refresh-token',[Auth\LoginController::class, 'refreshToken'])
        ->name('api.refresh.token')
        ->middleware('header.local');

    Route::get('language',[Common\TranslateController::class, 'getLang'])
        ->name('api.language');
    // Translation
    Route::get('translate', [Common\TranslateController::class, 'getTranslate'])
        ->name('api.translate.get');
    Route::get('translate/version', [Common\TranslateController::class, 'version'])
        ->name('api.translate.version');
    Route::get('translate/version-check', [Common\TranslateController::class, 'checkVersion'])
        ->name('api.translate.version-check');

    Route::get('report/download-video/{report}', [Site\ReportController::class, 'downloadVideo'])
        ->name('api.download-video');

    Route::get('hash/{type}',[Common\HashController::class, 'hash'])
        ->name('api.catalog.hash');

    Route::get('pages',[Site\PageController::class, 'index'])
        ->name('api.pages');

    Route::middleware('auth:api')->group(function () {

        // Auth
        Route::get('me',[Auth\LoginController::class, 'me'])
            ->name('api.user');
        Route::get('logout',[Auth\LoginController::class, 'logout'])
            ->name('api.logout');

        // Catalog
        Route::get('dealers',[Site\JD\DealerController::class, 'list'])
            ->name('api.dealers');
        Route::get('manufacturers',[Site\JD\ManufactureController::class, 'list'])
            ->name('api.manufacturers.list');
        Route::get('model-descriptions', [Site\JD\ModelDescriptionController::class, 'list'])
            ->name('api.model-descriptions');
        Route::get('model-descriptions/types', [Site\JD\ModelDescriptionController::class, 'types'])
            ->name('api.model-descriptions.types');
        Route::get('model-descriptions/sizes', [Site\JD\ModelDescriptionController::class, 'sizes'])
            ->name('api.model-descriptions.sizes');
        Route::get('clients',[Site\JD\ClientController::class, 'list'])
            ->name('api.clients');
        Route::get('equipment-groups', [Site\JD\EquipmentGroupController::class, 'list'])
            ->name('api.equipment-groups.list');

        // Report
        Route::post('report/create',[Site\ReportController::class, 'create'])
            ->name('api.report.create')
            ->middleware('can:create-report');
        Route::post('report/update-ps/{report}',[Site\ReportController::class, 'update'])
            ->name('api.report.update.ps');
        Route::post('report/attach-video/{report}',[Site\ReportController::class, 'attachVideo'])
            ->name('api.report.attach-video')
            ->middleware('can:create-report');
        Route::get('report/search',[Site\ReportController::class, 'search'])
            ->name('api.report.search');
        Route::get('report/{report}', [Site\ReportController::class, 'show'])
            ->name('api.report.show')
            ->middleware('can:show-report,report');

        Route::get('v2/reports',[Site\V2\ReportController::class, 'index'])
            ->name('api.v2.reports');

        Route::get('report-feature',[Site\ReportController::class, 'allFeature'])
            ->name('api.report.features-field-all');

        Route::get('report-feature/{equipmentGroup}',[Site\ReportController::class, 'feature'])
            ->name('api.report.features-field');

        // User
        Route::post('user/change-language',[Site\UserController::class, 'changeLanguage'])
            ->name('api.user.change-language');
        Route::post('user/change-password',[Site\UserController::class, 'changePassword'])
            ->name('api.user.change-password');
        Route::post('user/set-fcm-token',[Site\UserController::class, 'setFcmToken'])
            ->name('api.user.set-fcm-token');

        // Translate
        Route::post('translate', [Common\TranslateController::class, 'setTranslate'])
            ->name('api.translate.set');

        Route::get('admin/user', [Admin\UserController::class, 'index'])
            ->name('admin.user.index')
            ->middleware('can:show-list-user');

        Route::get('v2/admin/user', [Admin\V2\UserController::class, 'index'])
            ->name('v2.admin.user.index')
            ->middleware('can:show-list-user');

        // Admin
        Route::middleware('check.admin')->group(function () {
            // Equipment group
            Route::post('admin/equipment-group/{EquipmentGroup}/attach', [Admin\EquipmentGroupController::class, 'attach'])
                ->name('admin.equipment-group.attach');

            // User
            Route::post('admin/user/edit/{user}', [Admin\UserController::class, 'update'])
                ->name('admin.user.edit');
            Route::get('admin/user/{user}', [Admin\UserController::class, 'show'])
                ->name('admin.user.show');
            Route::put('admin/user/{user}/generate-password', [Admin\UserController::class, 'generatePassword'])
                ->name('admin.user.generate-password');
            Route::get('admin/role', [Admin\RoleController::class, 'list'])
                ->name('admin.role.list');
            Route::post('admin/user/create', [Admin\UserController::class, 'create'])
                ->name('admin.create.user');
            Route::post('admin/user/change-status/{user}', [Admin\UserController::class, 'changeStatus'])
                ->name('admin.change-status.user');
            Route::post('admin/user/attach-egs/{user}', [Admin\UserController::class, 'attachEgs'])
                ->name('admin.attach-egs.user');
            Route::put('admin/user/{user}/send-ios-link', [Admin\UserController::class, 'sendIosLink'])
                ->name('admin.user.send-ios-link');

            // Countries
            Route::get('admin/countries', [Admin\CountryController::class, 'index'])
                ->name('admin.country.list');
            Route::post('admin/country-toggle-active/{country}', [Admin\CountryController::class, 'toggleActive'])
                ->name('admin.country.toggle-active');
            Route::get('admin/nationalities',[Admin\CountryController::class, 'nationalities'])
                ->name('api.nationalities.list');

            // Feature
            Route::get('admin/feature/list', [Admin\FeatureController::class, 'list'])
                ->name('admin.feature.list');
            Route::post('admin/feature/create', [Admin\FeatureController::class, 'create'])
                ->name('admin.feature.create');
            Route::get('admin/feature/{feature}',[Admin\FeatureController::class, 'show'])
                ->name('admin.feature.show');
            Route::post('admin/feature/update/{feature}', [Admin\FeatureController::class,'update'])
                ->name('admin.feature.update');
            Route::delete('admin/feature/{feature}', [Admin\FeatureController::class, 'delete'])
                ->name('admin.feature.delete');
            Route::get('admin/feature/{feature}/toggle-active', [Admin\FeatureController::class, 'toggleActive'])
                ->name('admin.feature.toggle-active');

            Route::post('admin/feature/batchUpdate', ['uses' => 'Api\Admin\FeatureController@batchUpdate', 'as' => 'admin.feature.batch-update']);

            // Feature value
            Route::post('admin/feature/{feature}/value', [Admin\FeatureController::class, 'addValue'])
                ->name('admin.feature.add-value');
            Route::get('admin/feature/{feature}/values', [Admin\FeatureController::class, 'getValues'])
                ->name('admin.feature.get-value');
            Route::delete('admin/feature/value/{value}', [Admin\FeatureController::class, 'removeValue'])
                ->name('admin.feature.remove-value');
            Route::post('admin/update/value/{value}', [Admin\FeatureController::class, 'updateValue'])
                ->name('admin.feature.update-value');

            // Dealers
            Route::get('admin/dealers', [Admin\JdDataController::class, 'dealers'])
                ->name('admin.dealers.list');
            Route::post('admin/dealers/edit/{dealer}', [Admin\JdDataController::class, 'dealersEdit'])
                ->name('admin.dealers.edit');

            // Report
            Route::post('admin/{report}/comment', [Admin\CommentController::class, 'create'])
                ->name('admin.comment.create');
            Route::get('admin/{report}/verify', [Admin\ReportController::class, 'verify'])
                ->name('admin.report.verify');
            Route::get('report/export/excel',[Admin\ReportController::class, 'exportExcel'])
                ->name('api.report.export-excel');
            Route::get('report/export/pdf/{report}',[Site\ReportController::class, 'exportPdf'])
                ->name('api.report.export-pdf');

            Route::post('report/edit/{report}',[Admin\ReportController::class, 'update'])
                ->name('api.report.update');
            Route::get('admin/report-list-filter', [Admin\ReportController::class, 'listLocationDataForFilter'])
                ->name('admin.report.list-filter');

            // Statistic filter v1
            // todo candidate for remove
            Route::get('statistic/filter/country',[Admin\StatisticController::class, 'filterCountry'])
                ->name('api.statistic.filter.country');
            Route::get('statistic/filter/dealer',[Admin\StatisticController::class, 'filterDealer'])
                ->name('api.statistic.filter.dealer');
            Route::get('statistic/filter/eg',[Admin\StatisticController::class, 'filterEg'])
                ->name('api.statistic.filter.eg');
            Route::get('statistic/filter/md',[Admin\StatisticController::class, 'filterMd'])
                ->name('api.statistic.filter.md');

            // Statistic filter for machine v2
            Route::get('v2/statistic/filter/country',[Admin\V2\Stats\StatsMachineFilterController::class, 'country'])
                ->name('api.v2.statistic.filter.country');
            Route::get('v2/statistic/filter/dealer',[Admin\V2\Stats\StatsMachineFilterController::class, 'dealer'])
                ->name('api.v2.statistic.filter.dealer');
            Route::get('v2/statistic/filter/eg',[Admin\V2\Stats\StatsMachineFilterController::class, 'eg'])
                ->name('api.v2.statistic.filter.eg');
            Route::get('v2/statistic/filter/md',[Admin\V2\Stats\StatsMachineFilterController::class, 'md'])
                ->name('api.v2.statistic.filter.md');

            // Statistic filter for report v2
            Route::get('v2/statistic/reports/filter/status',[Admin\V2\Stats\StatsReportFilterController::class, 'status'])
                ->name('api.v2.statistic.report.filter.status');
            Route::get('v2/statistic/reports/filter/country',[Admin\V2\Stats\StatsReportFilterController::class, 'country'])
                ->name('api.v2.statistic.report.filter.country');
            Route::get('v2/statistic/reports/filter/dealer',[Admin\V2\Stats\StatsReportFilterController::class, 'dealer'])
                ->name('api.v2.statistic.report.filter.dealer');
            Route::get('v2/statistic/reports/filter/eg',[Admin\V2\Stats\StatsReportFilterController::class, 'eg'])
                ->name('api.v2.statistic.report.filter.eg');
            Route::get('v2/statistic/reports/filter/md',[Admin\V2\Stats\StatsReportFilterController::class, 'md'])
                ->name('api.v2.statistic.report.filter.md');
            Route::get('v2/statistic/type/filter',[Admin\V2\Stats\StatsReportFilterController::class, 'type'])
                ->name('api.v2.statistic.type.filter');
            Route::get('v2/statistic/size/filter',[Admin\V2\Stats\StatsReportFilterController::class, 'size'])
                ->name('api.v2.statistic.size.filter');
            Route::get('v2/statistic/crop/filter',[Admin\V2\Stats\StatsReportFilterController::class, 'crop'])
                ->name('api.v2.statistic.crop.filter');

            // Statistic v2
            Route::get('v2/statistic/machines',[Admin\V2\Stats\StatsController::class, 'machines'])
                ->name('api.v2.statistic.machines');
            Route::get('v2/statistic/machine',[Admin\V2\Stats\StatsController::class, 'machine'])
                ->name('api.v2.statistic.machine');
            Route::get('v2/statistic/reports',[Admin\V2\Stats\StatsController::class, 'reports'])
                ->name('api.v2.statistic.report.filter');
            Route::get('v2/statistic/type/reports',[Admin\V2\Stats\StatsController::class, 'type'])
                ->name('api.v2.statistic.type.report');
            Route::get('v2/statistic/size/reports',[Admin\V2\Stats\StatsController::class, 'size'])
                ->name('api.v2.statistic.size.report');
            Route::get('v2/statistic/crop/reports',[Admin\V2\Stats\StatsController::class, 'crop'])
                ->name('api.v2.statistic.crop.report');

            // Statistic machine  todo candidate for remove
            Route::get('statistic/machines',[Admin\StatisticController::class, 'forMachines'])
                ->name('api.statistic.machines');
            Route::get('statistic/machine',[Admin\StatisticController::class, 'forMachine'])
                ->name('api.statistic.machine');
            Route::get('statistic/types', [Admin\StatisticController::class, 'forTypes'])
                ->name('api.statistic.types');

            // Statistic report count todo candidate for remove
            Route::get('statistic/reports/filter/status',[Admin\Statistics\StatisticCountController::class, 'filterStatus'])
                ->name('api.statistic.report.filter.status');
            Route::get('statistic/reports/filter/country',[Admin\Statistics\StatisticCountController::class, 'filterCountry'])
                ->name('api.statistic.report.filter.country');
            Route::get('statistic/reports/filter/dealer',[Admin\Statistics\StatisticCountController::class, 'filterDealer'])
                ->name('api.statistic.report.filter.dealer');
            Route::get('statistic/reports/filter/eg',[Admin\Statistics\StatisticCountController::class, 'filterEg'])
                ->name('api.statistic.report.filter.eg');
            Route::get('statistic/reports/filter/md',[Admin\Statistics\StatisticCountController::class, 'filterMd'])
                ->name('api.statistic.report.filter.md');
            Route::get('statistic/reports',[Admin\Statistics\StatisticCountController::class, 'forReports'])
                ->name('api.statistic.report.filter');

            // Statistic size for modelDescription todo candidate for remove
            Route::get('statistic/size/filter',[Admin\Statistics\SizeController::class, 'filterSize'])
                ->name('api.statistic.size.filter');
            Route::get('statistic/size/filter/md',[Admin\Statistics\SizeController::class, 'filterMd'])
                ->name('api.statistic.size.filter.md');
            Route::get('statistic/size/reports',[Admin\Statistics\SizeController::class, 'forReports'])
                ->name('api.statistic.size.report');

            // Statistic type for modelDescription todo candidate for remove
            Route::get('statistic/type/filter',[Admin\Statistics\TypeController::class, 'filterType'])
                ->name('api.statistic.type.filter');
            Route::get('statistic/type/filter/md',[Admin\Statistics\TypeController::class, 'filterMd'])
                ->name('api.statistic.type.filter.md');
            Route::get('statistic/type/reports',[Admin\Statistics\TypeController::class, 'forReports'])
                ->name('api.statistic.type.report');

            // Statistic crop for modelDescription todo candidate for remove
            Route::get('statistic/crop/filter',[Admin\Statistics\CropController::class, 'filterCrop'])
                ->name('api.statistic.crop.filter');
            Route::get('statistic/crop/filter/md',[Admin\Statistics\CropController::class, 'filterMd'])
                ->name('api.statistic.crop.filter.md');
            Route::get('statistic/crop/reports',[Admin\Statistics\CropController::class, 'forReports'])
                ->name('api.statistic.crop.report');

            // Page
            Route::post('admin/page/edit/{alias}',[Site\PageController::class, 'update'])
                ->name('api.page.update');
            Route::get('/admin/page/alias-list',[Site\PageController::class, 'getPageAlias'])
                ->name('api.page.alias-list');

            // Fcm Notification Template
            Route::get('/admin/fcm-notification-templates',[Admin\FcmNotificationController::class, 'index'])
                ->name('api.notification.template.list');
            Route::get('/admin/fcm-notification-templates/{template}',[Admin\FcmNotificationController::class, 'show'])
                ->name('api.notification.template.show');
            Route::post('/admin/fcm-notification-templates/update/{template}',
                [Admin\FcmNotificationController::class, 'edit'])
                ->name('api.notification.template.edit');

            // Import ios link
            Route::get('/admin/ios-links-imports', [Imports\IosLinkImportController::class, 'index'])
                ->name('admin.ios-links.import.index');
            Route::post('/admin/ios-links-imports', [Imports\IosLinkImportController::class, 'process'])
                ->name('admin.ios-links.import.process');
            Route::get('/admin/ios-links-imports-can-use-import', [Imports\IosLinkImportController::class, 'canUseImport'])
                ->name('admin.ios-links.import.can-use-import');
            Route::get('/admin/ios-links-imports/{iosLinkImport}', [Imports\IosLinkImportController::class, 'show'])
                ->name('admin.ios-links.import.show');
            // Ios link
            Route::get('admin/ios-links', [Admin\IosLinkController::class, 'index'])
                ->name('admin.ios-links.index');
            Route::get('admin/ios-links-count', [Admin\IosLinkController::class, 'count'])
                ->name('admin.ios-links.count');
        });
    });
});

