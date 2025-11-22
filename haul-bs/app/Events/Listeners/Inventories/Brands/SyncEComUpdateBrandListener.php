<?php

namespace App\Events\Listeners\Inventories\Brands;

use App\Events\Events\Inventories\Brands\UpdateBrandEvent;
use App\Foundations\Enums\LogKeyEnum;
use App\Services\Requests\ECom\Commands\Brand\BrandUpdateCommand;

class SyncEComUpdateBrandListener
{
    public function __construct()
    {}

    public function handle(UpdateBrandEvent $event): void
    {
        try {
            /** @var $command BrandUpdateCommand */
            $command = resolve(BrandUpdateCommand::class);
            $res = $command->exec($event->getModel());

            logger_sync(LogKeyEnum::SyncECom->value." SUCCESS - ". __CLASS__ . " [{$event->getModel()->name}] ", ['res' => $res]);
        } catch (\Throwable $e) {
            logger_sync( LogKeyEnum::SyncECom->value. " FAILED - " . __CLASS__, [
                'message' => $e->getMessage()
            ]);
        }
    }
}
