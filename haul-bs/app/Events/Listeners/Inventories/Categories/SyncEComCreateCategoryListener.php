<?php

namespace App\Events\Listeners\Inventories\Categories;

use App\Events\Events\Inventories\Categories\CreateCategoryEvent;
use App\Foundations\Enums\LogKeyEnum;
use App\Services\Requests\ECom\Commands\Category\CategoryCreateCommand;

class SyncEComCreateCategoryListener
{
    public function __construct(
        protected CategoryCreateCommand $command
    )
    {}

    public function handle(CreateCategoryEvent $event): void
    {
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
