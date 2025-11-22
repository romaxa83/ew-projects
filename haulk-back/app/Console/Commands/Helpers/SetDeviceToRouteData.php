<?php

namespace App\Console\Commands\Helpers;

use App\Models\GPS\Route;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Truck;
use Illuminate\Console\Command;

class SetDeviceToRouteData extends Command
{
    protected $signature = 'helper:set_device_to_route_data';

    protected $count = 0;

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        try {
            $start = microtime(true);

            $this->exec();

            $time = microtime(true) - $start;

            logger_info("[helper] ".__CLASS__." [time = {$time}], [req = $this->count]");
            $this->info("[helper] ".__CLASS__." [time = {$time}], [req = $this->count]");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            logger_info('[helper] '.__CLASS__, [
                'msg' => $e->getMessage()
            ]);
            return self::FAILURE;
        }
    }

    private function exec(): void
    {
        Route::query()
            ->whereNull('device_id')
            ->get()
            ->each(function (Route $item) {
                if($item->truck_id){
                    /** @var $truck Truck */
                    $truck = Truck::find($item->truck_id);
                    $device_id = $truck->gps_device_id;
                } else {
                    /** @var $trailer Trailer */
                    $trailer = Trailer::find($item->trailer_id);
                    $device_id = $trailer->gps_device_id;
                }

                if($device_id){
                    $item->device_id = $device_id;
                    $item->save();
                    $this->count++;
                }
            })
        ;
    }
}



