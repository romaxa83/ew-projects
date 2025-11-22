<?php

namespace App\Listeners\BS\Vehicles;

use App\Events\BS\Vehicles\ToggleUseBSEvent;
use App\Services\BodyShop\Sync\Commands\Vehicles\SetVehicleCommand;
use App\Services\BodyShop\Sync\Commands\Vehicles\UnsetVehicleCommand;

class ToggleUseBSListener
{
    public function handle(ToggleUseBSEvent $event): void
    {
        if(config('bodyshop.enable_sync')){
            try {
                if(
                    array_key_exists('use_in_body_shop', $event->company->getChanges())
                    && $event->oldValue == false
                    && $event->company->use_in_body_shop
                ){
                    /** @var $command SetVehicleCommand */
                    $command = resolve(SetVehicleCommand::class);
                    $command->handler(['company_id' => $event->company->id]);

                    logger_info('ToggleUseBSListener [SetVehicleCommand - exec]');
                } elseif (
                    array_key_exists('use_in_body_shop', $event->company->getChanges())
                    && $event->oldValue
                    && $event->company->use_in_body_shop == false
                ){
                    /** @var $command UnsetVehicleCommand */
                    $command = resolve(UnsetVehicleCommand::class);
                    $command->handler(['company_id' => $event->company->id]);

                    logger_info('ToggleUseBSListener [UnsetVehicleCommand - exec]');
                }
            } catch (\Exception $e){
                logger_info('ToggleUseBSListener FAIL', [$e->getMessage()]);
            }
        }
    }
}
