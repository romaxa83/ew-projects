<?php

namespace App\Events\Listeners\Inventories\Inventories;

use App\Foundations\Enums\LogKeyEnum;
use App\Events\Events\Inventories\Inventories\CreateInventoryEvent;
use App\Services\Requests\ECom\Commands\Inventory\InventoryCreateCommand;

class SyncEComCreateInventoryListener
{
    public function __construct(
        protected InventoryCreateCommand $command,
    )
    {}

    public function handle(CreateInventoryEvent $event): void
    {
        if(!$event->sendToEcomm()) return;

        try {
            $res = $this->command->exec($event->getModel());

            logger_sync(LogKeyEnum::SyncECom->value." SUCCESS - ". __CLASS__ . " [{$event->getModel()->name}] ", ['res' => $res]);
        } catch (\Throwable $e) {
            logger_sync( LogKeyEnum::SyncECom->value. " FAILED - " . __CLASS__, [
                'message' => $e->getMessage()
            ]);
        }
    }
}
