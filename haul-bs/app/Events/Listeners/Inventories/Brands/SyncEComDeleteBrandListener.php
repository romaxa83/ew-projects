<?php

namespace App\Events\Listeners\Inventories\Brands;

use App\Events\Events\Inventories\Brands\CreateBrandEvent;
use App\Foundations\Enums\LogKeyEnum;
use App\Services\Requests\ECom\Commands\Brand\BrandDeleteCommand;

class SyncEComDeleteBrandListener
{
    public function __construct()
    {}

    public function handle(CreateBrandEvent $event): void
    {
        try {
            /** @var $command BrandDeleteCommand */
            $command = resolve(BrandDeleteCommand::class);
            $res = $command->exec(['id' => $event->getModel()->id]);

            logger_sync(LogKeyEnum::SyncECom->value." SUCCESS - ". __CLASS__ . " [{$event->getModel()->name}] ", ['res' => $res]);
        } catch (\Throwable $e) {
            logger_sync( LogKeyEnum::SyncECom->value. " FAILED - " . __CLASS__, [
                'message' => $e->getMessage()
            ]);
        }
    }
}
