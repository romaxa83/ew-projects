<?php

namespace App\Events\Listeners\Inventories\Inventories;

use App\Foundations\Enums\LogKeyEnum;
use App\Events\Events\Inventories\Inventories\ChangeQuantityInventory;
use App\Services\Requests\ECom\Commands\Inventory\InventoryChangeQuantityCommand;
use Illuminate\Contracts\Queue\ShouldQueue;

class SyncEComChangeQuantityInventoryListener implements ShouldQueue
{
    public function __construct(
        protected InventoryChangeQuantityCommand $command
    )
    {}

    public function handle(ChangeQuantityInventory $event): void
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
