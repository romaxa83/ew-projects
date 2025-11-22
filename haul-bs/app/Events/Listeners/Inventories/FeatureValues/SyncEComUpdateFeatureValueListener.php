<?php

namespace App\Events\Listeners\Inventories\FeatureValues;

use App\Foundations\Enums\LogKeyEnum;
use App\Events\Events\Inventories\FeatureValues\UpdateFeatureValueEvent;
use App\Services\Requests\ECom\Commands\FeatureValue\FeatureValueUpdateCommand;

class SyncEComUpdateFeatureValueListener
{
    public function __construct()
    {}

    public function handle(UpdateFeatureValueEvent $event): void
    {
        try {
            /** @var $command FeatureValueUpdateCommand */
            $command = resolve(FeatureValueUpdateCommand::class);
            $res = $command->exec($event->getModel());

            logger_sync(LogKeyEnum::SyncECom->value." SUCCESS - ". __CLASS__ . " [{$event->getModel()->name}] ", ['res' => $res]);
        } catch (\Throwable $e) {
            logger_sync( LogKeyEnum::SyncECom->value. " FAILED - " . __CLASS__, [
                'message' => $e->getMessage()
            ]);
        }
    }
}
