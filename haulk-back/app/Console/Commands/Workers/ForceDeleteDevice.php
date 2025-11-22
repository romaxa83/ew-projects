<?php

namespace App\Console\Commands\Workers;

use App\Models\Saas\GPS\Device;
use App\Services\Saas\GPS\Devices\DeviceService;
use Illuminate\Console\Command;

// полное удаление gps девайса и связанных данных, через месяц после фактического удаления
class ForceDeleteDevice extends Command
{
    protected $signature = 'worker:force_delete_device';

    protected $description = 'Complete deletion of the gps device and its data, after days '. Device::DAYS_TO_FORCE_DELETE .', as it was deleted from the fleshpi';

    protected DeviceService $service;

    public function __construct(DeviceService $service)
    {
        parent::__construct();

        $this->service = $service;
    }


    public function handle()
    {
        try {
            $this->service->forceDelete();

            echo PHP_EOL;
            $this->info('Done');

            return self::SUCCESS;
        } catch (\Exception $e){
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}

