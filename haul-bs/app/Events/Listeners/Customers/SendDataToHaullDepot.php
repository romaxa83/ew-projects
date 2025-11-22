<?php

namespace App\Events\Listeners\Customers;

use App\Events\Events\Customers\CustomerGiveEcommTag;
use App\Foundations\Enums\LogKeyEnum;
use App\Notifications\Customers\RegisterHaulkDepot;
use App\Services\Customers\CustomerNotificationService;
use App\Services\Requests\ECom\Commands\Customer\CustomerSetTagEcomCommand;

class SendDataToHaullDepot
{
    public function __construct(
        protected CustomerNotificationService $notificationService
    )
    {}

    public function handle(CustomerGiveEcommTag $event): void
    {
        try {
            /** @var $command CustomerSetTagEcomCommand */
            $command = resolve(CustomerSetTagEcomCommand::class);
            $res = $command->exec($event->getModel());

            logger_sync(LogKeyEnum::SyncECom->value. ' ' . __CLASS__ ." to [{$event->getModel()->email->getValue()}] SUCCESS");
        } catch (\Throwable $e) {
            logger_sync( LogKeyEnum::SyncECom->value. " FAILED -" . __CLASS__, [
                'message' => $e->getMessage()
            ]);
        }
    }
}

