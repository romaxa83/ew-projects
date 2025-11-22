<?php

namespace App\Console\Commands\Workers;

use App\Enums\Saas\GPS\DeviceRequestStatus;
use App\Models\Saas\GPS\Device;
use App\Services\Saas\GPS\Devices\DeviceService;
use App\Services\Saas\GPS\Devices\DeviceSubscriptionService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
// При отмене gps подписки, за день до конца активного периода, отправлять сообщение
class WarningGpsSubscriptionCanceled extends Command
{
    protected $signature = 'worker:warning_gps_subscription';

    protected DeviceSubscriptionService $service;

    public function __construct(DeviceSubscriptionService $service)
    {
        parent::__construct();

        $this->service = $service;
    }

    public function handle()
    {
        try {
            $this->service->createWarningNotification();

            echo PHP_EOL;
            $this->info('Done');

            return self::SUCCESS;
        } catch (\Exception $e){
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}



