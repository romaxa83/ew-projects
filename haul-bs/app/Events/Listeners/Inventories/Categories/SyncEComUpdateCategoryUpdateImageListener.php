<?php

namespace App\Events\Listeners\Inventories\Categories;

use App\Events\Events\Inventories\Categories\UpdateImageCategoryEvent;
use App\Foundations\Enums\LogKeyEnum;
use App\Services\Requests\ECom\Commands\Category\CategoryExistsCommand;
use App\Services\Requests\ECom\Commands\Category\CategoryUpdateImagesCommand;

class SyncEComUpdateCategoryUpdateImageListener
{
    public function __construct(
        protected CategoryUpdateImagesCommand $command,
        protected CategoryExistsCommand $commandExists,
    )
    {}

    public function handle(UpdateImageCategoryEvent $event): void
    {
        try {
            if($this->commandExists->exec($event->getModel())){
                $res = $this->command->exec($event->getModel());
            }

            logger_sync(LogKeyEnum::SyncECom->value." SUCCESS - ". __CLASS__ . " [{$event->getModel()->name}] ", ['res' => $res]);
        } catch (\Throwable $e) {
            logger_sync( LogKeyEnum::SyncECom->value. " FAILED - " . __CLASS__, [
                'message' => $e->getMessage()
            ]);
        }
    }
}
