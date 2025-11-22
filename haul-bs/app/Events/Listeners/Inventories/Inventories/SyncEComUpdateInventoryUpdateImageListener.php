<?php

namespace App\Events\Listeners\Inventories\Inventories;

use App\Events\Events\Inventories\Inventories\UpdateImageInventoryEvent;
use App\Foundations\Enums\LogKeyEnum;
use App\Services\Requests\ECom\Commands\Inventory\InventoryExistsCommand;
use App\Services\Requests\ECom\Commands\Inventory\InventoryUpdateImagesCommand;

class SyncEComUpdateInventoryUpdateImageListener
{
    public function __construct(
        protected InventoryUpdateImagesCommand $command,
        protected InventoryExistsCommand $commandExists,
    )
    {}

    public function handle(UpdateImageInventoryEvent $event): void
    {
        $model = $event->getModel();

        try {
            if($this->commandExists->exec($model)){
                $res = $this->command->exec($model);
            }

            logger_sync(LogKeyEnum::SyncECom->value." SUCCESS - ". __CLASS__ . " [{$model->name}] ", ['res' => $res]);
        } catch (\Throwable $e) {
            logger_sync( LogKeyEnum::SyncECom->value. " FAILED - " . __CLASS__, [
                'message' => $e->getMessage()
            ]);
        }
    }
}
