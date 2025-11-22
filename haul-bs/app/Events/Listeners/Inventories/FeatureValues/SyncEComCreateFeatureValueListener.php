<?php

namespace App\Events\Listeners\Inventories\FeatureValues;

use App\Foundations\Enums\LogKeyEnum;
use App\Events\Events\Inventories\FeatureValues\CreateFeatureValueEvent;
use App\Services\Requests\ECom\Commands\FeatureValue\FeatureValueCreateCommand;

class SyncEComCreateFeatureValueListener
{
    public function __construct()
    {}

    public function handle(CreateFeatureValueEvent $event): void
    {
        try {
            /** @var $command FeatureValueCreateCommand */
            $command = resolve(FeatureValueCreateCommand::class);
            $res = $command->exec($event->getModel());

            logger_sync(LogKeyEnum::SyncECom->value." SUCCESS - ". __CLASS__ . " [{$event->getModel()->name}] ", ['res' => $res]);
        } catch (\Throwable $e) {
            logger_sync( LogKeyEnum::SyncECom->value. " FAILED - " . __CLASS__, [
                'message' => $e->getMessage()
            ]);
        }
    }
}
