<?php

namespace App\Events\Listeners\Inventories\Inventories;

use App\Foundations\Enums\LogKeyEnum;
use App\Events\Events\Inventories\Inventories\DeleteInventoryEvent;
use App\Services\Requests\ECom\Commands\Inventory\InventoryDeleteCommand;
use App\Services\Requests\ECom\Commands\Inventory\InventoryExistsCommand;

class SyncEComDeleteInventoryListener
{
    public function __construct(
        protected InventoryDeleteCommand $commandDelete,
        protected InventoryExistsCommand $commandExists,
    )
    {}

    public function handle(DeleteInventoryEvent $event): void
    {
        if(!$event->sendToEcomm()) return;

        try {
            if(!$this->commandExists->exec($event->getModel())) return;

            $this->commandDelete->exec(['id' => $event->getModel()->id]);

            logger_sync(LogKeyEnum::SyncECom->value." SUCCESS - ". __CLASS__ . " [{$event->getModel()->name}]");
        } catch (\Throwable $e) {
            logger_sync( LogKeyEnum::SyncECom->value. " FAILED - " . __CLASS__, [
                'message' => $e->getMessage()
            ]);
        }
    }
}
