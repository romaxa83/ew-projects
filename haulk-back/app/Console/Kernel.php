<?php

namespace App\Console;

use App\Console\Commands\AddLanguage;
use App\Console\Commands\AddLocation;
use App\Console\Commands\Billing\CancelSubscriptionAfterMonthUnpaid;
use App\Console\Commands\Billing\ChargeInvoices;
use App\Console\Commands\Billing\CreateInvoices;
use App\Console\Commands\Billing\DeleteOldInvoices;
use App\Console\Commands\Billing\GpsSubscriptionCanceled;
use App\Console\Commands\Billing\TrackActiveDriverHistory;
use App\Console\Commands\Billing\TrackActiveGPSDeviceForPayment;
use App\Console\Commands\Billing\UpdatePendingTransactionStatus;
use App\Console\Commands\BodyShop\ProcessFinishedPaidOrders;
use App\Console\Commands\BodyShop\PurgeDeletedOrders as BSPurgeDeletedOrders;
use App\Console\Commands\DeleteLostMedia;
use App\Console\Commands\DeleteOldAlerts;
use App\Console\Commands\DeleteOldSignatureTokens;
use App\Console\Commands\DownloadFileZipCode;
use App\Console\Commands\Email\BeforeTrialEnd;
use App\Console\Commands\Email\NotConfirmSignup;
use App\Console\Commands\Email\NotLoginFreeTrial;
use App\Console\Commands\Email\NotPaid;
use App\Console\Commands\Email\NotPaymentCard;
use App\Console\Commands\FixOldTimeZones;
use App\Console\Commands\Fueling\FuelCardOldDelete;
use App\Console\Commands\GPS\AddCoordsToRoute;
use App\Console\Commands\GPS\GoogleRoadApiWorker;
use App\Console\Commands\GPS\ProcessMessages;
use App\Console\Commands\Helpers\Telescope\ClearTelescopeTables;
use App\Console\Commands\Logs\ClearDbLogs;
use App\Console\Commands\ParserHelpers\ParserRenameFilesCommand;
use App\Console\Commands\ProcessPaidOrders;
use App\Console\Commands\PurgeDeletedOrders;
use App\Console\Commands\PurgePaidPayrolls;
use App\Console\Commands\SendPushNotifications;
use App\Console\Commands\Storage\CreateAppRoot;
use App\Console\Commands\UpdateVehicleMakes;
use App\Console\Commands\UpdateVehicleModels;
use App\Console\Commands\Workers\ChangeDeviceRequestStatus;
use App\Console\Commands\Workers\DeactivateGpsDevice;
use App\Console\Commands\Workers\DeleteOldExcelFile;
use App\Console\Commands\Workers\ForceDeleteDevice;
use App\Console\Commands\Workers\NormalizeGpsMsg;
use App\Console\Commands\Workers\OrderDocumentReindex;
use App\Console\Commands\Workers\SendCompanyToAddressbook;
use App\Console\Commands\Workers\WarningGpsSubscriptionCanceled;
use App\Services\Language\LanguageService;
use Clockwork\Support\Laravel\ClockworkCleanCommand;
use Illuminate\Console\Application;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        AddLocation::class,
        ProcessPaidOrders::class,
        PurgeDeletedOrders::class,
        UpdateVehicleMakes::class,
        UpdateVehicleModels::class,
        SendPushNotifications::class,
        PurgePaidPayrolls::class,

        CreateAppRoot::class,

        ClearDbLogs::class,

        TrackActiveDriverHistory::class,

        DeleteLostMedia::class,

        DeleteOldInvoices::class,

        CancelSubscriptionAfterMonthUnpaid::class,

        UpdatePendingTransactionStatus::class,
        FixOldTimeZones::class,
        DeleteOldSignatureTokens::class,

        AddLanguage::class,
        ParserRenameFilesCommand::class,

        BSPurgeDeletedOrders::class,

        ProcessMessages::class,
        GpsSubscriptionCanceled::class,
        DeactivateGpsDevice::class,
        TrackActiveGPSDeviceForPayment::class,
        GoogleRoadApiWorker::class,
        OrderDocumentReindex::class,
    ];

    protected function schedule(Schedule $schedule): void
    {
        // email send (marketing)
        $schedule->command(NotConfirmSignup::class)
            ->everyMinute()
            ->withoutOverlapping();
        $schedule->command(NotLoginFreeTrial::class)
            ->everyMinute()
            ->withoutOverlapping();
        $schedule->command(NotPaymentCard::class)
            ->everyMinute()
            ->withoutOverlapping();
        $schedule->command(BeforeTrialEnd::class)
            ->everyMinute()
            ->withoutOverlapping();
        $schedule->command(NotPaid::class)
            ->everyMinute()
            ->withoutOverlapping();
        //--------------------------------

        $schedule->command(FuelCardOldDelete::class)
            ->daily()
            ->withoutOverlapping();

        $schedule->command(ProcessPaidOrders::class)
            ->daily()
            ->withoutOverlapping();

        $schedule->command(PurgeDeletedOrders::class)
            ->daily()
            ->withoutOverlapping();

        $schedule->command(UpdateVehicleMakes::class)
            ->monthly()
            ->withoutOverlapping();

        $schedule->command(UpdateVehicleModels::class)
            ->everyMinute()
            ->withoutOverlapping();

        $schedule->command(SendPushNotifications::class)
            ->everyMinute()
            ->withoutOverlapping();

        $schedule->command(PurgePaidPayrolls::class)
            ->daily()
            ->withoutOverlapping();

        $schedule->command('websockets:clean')
            ->daily();

        $schedule->command('passport:purge')
            ->dailyAt('03:00');

        $schedule->command(ClearDbLogs::class)
            ->dailyAt('04:00');

        $schedule->command(TrackActiveDriverHistory::class)
            ->everyThirtyMinutes()
            ->withoutOverlapping();

        $schedule->command(CreateInvoices::class)
            ->hourly()
            ->withoutOverlapping();

        $schedule->command(DownloadFileZipCode::class)
            ->monthly()
            ->withoutOverlapping();

        $schedule->command(DeleteLostMedia::class)
            ->everyFiveMinutes()
            ->withoutOverlapping();

        $schedule->command(ChargeInvoices::class)
            ->everyMinute()
            ->withoutOverlapping();

        $schedule->command(DeleteOldInvoices::class)
            ->daily()
            ->withoutOverlapping();

        $schedule->command(DeleteOldAlerts::class)
            ->daily()
            ->withoutOverlapping();

        $schedule->command(CancelSubscriptionAfterMonthUnpaid::class)
            ->hourly()
            ->withoutOverlapping();

        $schedule->command(UpdatePendingTransactionStatus::class)
            ->hourly()
            ->withoutOverlapping();

        $schedule->command(DeleteOldSignatureTokens::class)
            ->daily()
            ->withoutOverlapping();

//        $schedule->command(OrderDocumentReindex::class)
//            ->everyTenMinutes()
//            ->withoutOverlapping();
        $schedule->command(SendCompanyToAddressbook::class)
            ->everyFiveMinutes()
            ->withoutOverlapping();

        //BodyShop
        $schedule->command(BSPurgeDeletedOrders::class)
            ->daily()
            ->withoutOverlapping();

        $schedule->command(ProcessFinishedPaidOrders::class)
            ->daily()
            ->withoutOverlapping();

        $schedule->command(ClockworkCleanCommand::class, ['--all' => true])
            ->weekly()
            ->withoutOverlapping();

        $schedule->command(ClearTelescopeTables::class)
            ->everyThreeHours()
            ->withoutOverlapping();

        //GPS
        $schedule->command(NormalizeGpsMsg::class)
            ->dailyAt('23:55')
            ->withoutOverlapping();

        $schedule->command(ProcessMessages::class)
            ->everyMinute()
            ->withoutOverlapping();

        $schedule->command(GoogleRoadApiWorker::class)
            ->hourly()
            ->withoutOverlapping();

        $schedule->command(AddCoordsToRoute::class)
            ->hourly()
            ->withoutOverlapping();

        $schedule->command(ForceDeleteDevice::class)
            ->daily()
            ->withoutOverlapping();
        $schedule->command(DeleteOldExcelFile::class)
            ->daily()
            ->withoutOverlapping();
        $schedule->command(ChangeDeviceRequestStatus::class)
            ->everyFiveMinutes()
            ->withoutOverlapping();

        $schedule->command(GpsSubscriptionCanceled::class)
            ->everyThirtyMinutes()
            ->withoutOverlapping();
        $schedule->command(DeactivateGpsDevice::class)
            ->everyThirtyMinutes()
            ->withoutOverlapping();
        $schedule->command(TrackActiveGPSDeviceForPayment::class)
            ->everyThirtyMinutes()
            ->withoutOverlapping();
        $schedule->command(WarningGpsSubscriptionCanceled::class)
            ->everyThirtyMinutes()
            ->withoutOverlapping();
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }

    protected function getArtisan(): Application
    {
        $languageService = resolve(LanguageService::class);

        if ($languageService->hasTable()) {
            $languageService->load();
        }

        if (is_null($this->artisan)) {
            $this->artisan = parent::getArtisan();
        }

        return $this->artisan;
    }
}
