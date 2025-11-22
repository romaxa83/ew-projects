<?php

namespace App\Events\Listeners\Inventories\Features;

use App\Events\Events\Inventories\Features\DeleteFeatureEvent;
use App\Foundations\Enums\LogKeyEnum;
use App\Services\Requests\ECom\Commands\Feature\FeatureDeleteCommand;

class SyncEComDeleteFeatureListener
{
    public function __construct()
    {}

    public function handle(DeleteFeatureEvent $event): void
    {
        try {
            /** @var $command FeatureDeleteCommand */
            $command = resolve(FeatureDeleteCommand::class);
            $command->exec(['id' => $event->getModel()->id]);

            logger_sync(LogKeyEnum::SyncECom->value." SUCCESS - ". __CLASS__ . " [{$event->getModel()->name}]");
        } catch (\Throwable $e) {
            logger_sync( LogKeyEnum::SyncECom->value. " FAILED - " . __CLASS__, [
                'message' => $e->getMessage()
            ]);
        }
    }
}
