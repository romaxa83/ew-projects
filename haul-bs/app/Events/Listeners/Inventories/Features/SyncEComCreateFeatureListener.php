<?php

namespace App\Events\Listeners\Inventories\Features;

use App\Events\Events\Inventories\Features\CreateFeatureEvent;
use App\Foundations\Enums\LogKeyEnum;
use App\Services\Requests\ECom\Commands\Feature\FeatureCreateCommand;

class SyncEComCreateFeatureListener
{
    public function __construct()
    {}

    public function handle(CreateFeatureEvent $event): void
    {
        try {
            /** @var $command FeatureCreateCommand */
            $command = resolve(FeatureCreateCommand::class);
            $res = $command->exec($event->getModel());

            logger_sync(LogKeyEnum::SyncECom->value." SUCCESS - ". __CLASS__ . " [{$event->getModel()->name}] ", ['res' => $res]);
        } catch (\Throwable $e) {
            logger_sync( LogKeyEnum::SyncECom->value. " FAILED - " . __CLASS__, [
                'message' => $e->getMessage()
            ]);
        }
    }
}
