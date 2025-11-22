<?php

namespace App\Console\Commands\GPS;

use App\Enums\Saas\GPS\DeviceStatus;
use App\Models\Saas\GPS\Device;
use App\Services\GPS\GPSDataService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class ProcessMessages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gps:gps-process-messages {limit?}';

    protected $description = 'Process GPS Messages';

    protected GPSDataService $service;

    public function __construct(GPSDataService $service)
    {
        parent::__construct();

        $this->service = $service;
    }


    public function handle(): void
    {
        $start = microtime(true);

        $limit = $this->argument('limit');

        $this->exec($limit);

        $time = microtime(true) - $start;

        $this->info("Messages Processed ... [time = {$time}]");
    }

    private function exec($limit):void
    {
        $this->service->processMessages($limit);

        Artisan::call('gps:ping_devices');


//        Device::query()
//            ->with([
//                'truck.lastGPSHistory',
//                'trailer.lastGPSHistory',
//            ])
//            ->where('status', DeviceStatus::ACTIVE())
//            ->get()
//            ->each(function(Device $device){
//                $this->service->checkDeviceConnectionAlert($device);
//            })
//        ;
    }
}
