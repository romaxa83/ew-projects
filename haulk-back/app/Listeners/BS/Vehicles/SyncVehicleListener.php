<?php

namespace App\Listeners\BS\Vehicles;

use App\Events\BS\Vehicles\SyncVehicleEvent;
use App\Services\BodyShop\Sync\Commands\Vehicles\SyncVehicleCommand;

class SyncVehicleListener
{
    public function handle(SyncVehicleEvent $event): void
    {
        if(config('bodyshop.enable_sync')){
            try {
                if($event->vehicle->getCompany()->use_in_body_shop){
                    /** @var $command SyncVehicleCommand */
                    $command = resolve(SyncVehicleCommand::class);
                    $data = $command->fill($event->vehicle);
                    $command->handler($data);

                    logger_info('SyncVehicleListener [SyncVehicleCommand - exec]');
                }

            } catch (\Exception $e){
                logger_info('SyncVehicleListener FAIL', [$e->getMessage()]);
            }
        }
    }
}
