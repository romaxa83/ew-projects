<?php

namespace App\Events\Listeners\Inventories\Brands;

use App\Events\Events\Inventories\Brands\CreateBrandEvent;
use App\Foundations\Enums\LogKeyEnum;
use App\Services\Requests\ECom\Commands\Brand\BrandCreateCommand;

class SyncEComCreateBrandListener
{
    public function __construct()
    {}

    public function handle(CreateBrandEvent $event): void
    {
        try {
            /** @var $command BrandCreateCommand */
            $command = resolve(BrandCreateCommand::class);
            $res = $command->exec($event->getModel());

            logger_sync(LogKeyEnum::SyncECom->value." SUCCESS - ". __CLASS__ . " [{$event->getModel()->name}] ", ['res' => $res]);
        } catch (\Throwable $e) {
            logger_sync( LogKeyEnum::SyncECom->value. " FAILED - " . __CLASS__, [
                'message' => $e->getMessage()
            ]);
        }
    }
}
