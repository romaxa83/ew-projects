<?php

namespace App\Events\Listeners\Inventories\Inventories;

use App\Events\Events\Inventories\Inventories\UpdateInventoryEvent;
use App\Foundations\Enums\LogKeyEnum;
use App\Services\Requests\ECom\Commands\Inventory\InventoryCreateCommand;
use App\Services\Requests\ECom\Commands\Inventory\InventoryExistsCommand;
use App\Services\Requests\ECom\Commands\Inventory\InventoryUpdateCommand;

class SyncEComUpdateInventoryListener
{
    public function __construct(
        protected InventoryUpdateCommand $command,
        protected InventoryCreateCommand $commandCreate,
        protected InventoryExistsCommand $commandExists,
    )
    {}

    public function handle(UpdateInventoryEvent $event): void
    {
        if(!$event->sendToEcomm()) return;

        try {
            if($this->commandExists->exec($event->getModel())){
                $res = $this->command->exec($event->getModel());
            } else {
                $res = $this->commandCreate->exec($event->getModel());
            }

            logger_sync(LogKeyEnum::SyncECom->value." SUCCESS - ". __CLASS__ . " [{$event->getModel()->name}] ", ['res' => $res]);
        } catch (\Throwable $e) {
            logger_sync( LogKeyEnum::SyncECom->value. " FAILED - " . __CLASS__, [
                'message' => $e->getMessage()
            ]);
        }
    }
}
