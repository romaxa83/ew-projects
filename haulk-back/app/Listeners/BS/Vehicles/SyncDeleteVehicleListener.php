<?php

namespace App\Listeners\BS\Vehicles;

use App\Events\BS\Vehicles\DeleteVehicleEvent;
use App\Services\BodyShop\Sync\Commands\Vehicles\DeleteVehicleCommand;

class SyncDeleteVehicleListener
{
    public function handle(DeleteVehicleEvent $event): void
    {
        if(config('bodyshop.enable_sync')){
            try {
                if($event->vehicle->getCompany()->use_in_body_shop){
                    /** @var $command DeleteVehicleCommand */
                    $command = resolve(DeleteVehicleCommand::class);
                    $command->handler(['vehicle' => $event->vehicle]);

                    logger_info('SyncDeleteVehicleListener [DeleteVehicleCommand - exec]');
                }

            } catch (\Exception $e){
                logger_info('SyncDeleteVehicleListener FAIL', [$e->getMessage()]);
            }
        }
    }
}
