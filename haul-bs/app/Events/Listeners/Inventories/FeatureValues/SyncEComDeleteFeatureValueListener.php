<?php

namespace App\Events\Listeners\Inventories\FeatureValues;

use App\Foundations\Enums\LogKeyEnum;
use App\Events\Events\Inventories\FeatureValues\DeleteFeatureValueEvent;
use App\Services\Requests\ECom\Commands\FeatureValue\FeatureValueDeleteCommand;

class SyncEComDeleteFeatureValueListener
{
    public function __construct()
    {}

    public function handle(DeleteFeatureValueEvent $event): void
    {
        try {
            /** @var $command FeatureValueDeleteCommand */
            $command = resolve(FeatureValueDeleteCommand::class);
            $command->exec(['id' => $event->getModel()->id]);

            logger_sync(LogKeyEnum::SyncECom->value." SUCCESS - ". __CLASS__ . " [{$event->getModel()->name}] ");
        } catch (\Throwable $e) {
            logger_sync( LogKeyEnum::SyncECom->value. " FAILED - " . __CLASS__, [
                'message' => $e->getMessage()
            ]);
        }
    }
}
