<?php

namespace App\Events\Listeners\Settings;

use App\Events\Events\Settings\RequestToEcom;
use App\Foundations\Enums\LogKeyEnum;
use App\Services\Requests\ECom\Commands\Settings\SettingsUpdateCommand;

class RequestToEcomListener
{
    public function __construct(protected SettingsUpdateCommand $command)
    {}

    public function handle(RequestToEcom $event): void
    {
        try {
            $res = $this->command->exec($event->getData());

            logger_info(LogKeyEnum::SyncECom->value." SUCCESS - ". __CLASS__ , [$res]);
        } catch (\Throwable $e) {
            logger_info( LogKeyEnum::SyncECom->value. " FAILED - " . __CLASS__, [
                'message' => $e->getMessage()
            ]);
        }
    }
}
