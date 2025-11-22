<?php

namespace App\Events\Listeners\Inventories\Categories;

use App\Events\Events\Inventories\Categories\UpdateCategoryEvent;
use App\Foundations\Enums\LogKeyEnum;
use App\Jobs\Categories\CategorySyncJob;
use App\Services\Requests\ECom\Commands\Category\CategoryCreateCommand;
use App\Services\Requests\ECom\Commands\Category\CategoryExistsCommand;
use App\Services\Requests\ECom\Commands\Category\CategoryUpdateCommand;

class SyncEComUpdateCategoryListener
{
    public function __construct(
        protected CategoryUpdateCommand $command,
        protected CategoryCreateCommand $commandCreate,
        protected CategoryExistsCommand $commandExists,
    )
    {}

    public function handle(UpdateCategoryEvent $event): void
    {
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
