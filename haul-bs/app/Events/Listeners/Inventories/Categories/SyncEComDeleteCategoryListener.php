<?php

namespace App\Events\Listeners\Inventories\Categories;

use App\Events\Events\Inventories\Categories\DeleteCategoryEvent;
use App\Foundations\Enums\LogKeyEnum;
use App\Services\Requests\ECom\Commands\Category\CategoryDeleteCommand;
use App\Services\Requests\ECom\Commands\Category\CategoryExistsCommand;

class SyncEComDeleteCategoryListener
{
    public function __construct(
        protected CategoryDeleteCommand $commandDelete,
        protected CategoryExistsCommand $commandExists
    )
    {}

    public function handle(DeleteCategoryEvent $event): void
    {
        try {
            if(!$this->commandExists->exec($event->getModel())) return;

            $res = $this->commandDelete->exec(['id' => $event->getModel()->id]);

            logger_sync(LogKeyEnum::SyncECom->value." SUCCESS - ". __CLASS__ . " [{$event->getModel()->name}] ", ['res' => $res]);
        } catch (\Throwable $e) {
            logger_sync( LogKeyEnum::SyncECom->value. " FAILED - " . __CLASS__, [
                'message' => $e->getMessage()
            ]);
        }
    }
}
