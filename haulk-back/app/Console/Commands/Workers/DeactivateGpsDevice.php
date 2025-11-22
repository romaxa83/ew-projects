<?php

namespace App\Console\Commands\Workers;

use App\Services\Saas\GPS\Devices\DeviceService;
use Illuminate\Console\Command;

// устанавливаем gps девайсу статус inactive, после того как у него ранее был запрос на деактивацию,
// и он деактивирован, но билинг у него еще продолжался
class DeactivateGpsDevice extends Command
{
    protected $signature = 'worker:deactivate_device';

    protected DeviceService $service;

    public function __construct(DeviceService $service)
    {
        parent::__construct();

        $this->service = $service;
    }

    public function handle()
    {
        try {
            $this->service->deactivatingProcess();

            echo PHP_EOL;
            $this->info('Done');

            return self::SUCCESS;
        } catch (\Exception $e){
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}



