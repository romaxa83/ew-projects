<?php

namespace App\Console\Commands\Workers;

use App\Enums\Saas\GPS\DeviceRequestStatus;
use App\Models\Saas\GPS\Device;
use App\Services\Saas\GPS\Devices\DeviceService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
// убираем у gps девайса статус запроса closed на none, через день после получение данного статуса
class ChangeDeviceRequestStatus extends Command
{
    protected $signature = 'worker:change-device-request-status';

    protected DeviceService $service;

    public function __construct(DeviceService $service)
    {
        parent::__construct();

        $this->service = $service;
    }

    public function handle()
    {
        try {
            $this->service->removeClosedDeviceRequestStatus();

            echo PHP_EOL;
            $this->info('Done');

            return self::SUCCESS;
        } catch (\Exception $e){
            $this->error($e->getMessage());
            return self::FAILURE;
        }
    }
}


