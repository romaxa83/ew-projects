<?php

namespace App\Events\Listeners\Orders\Parts;

use App\Events\Events\Orders\Parts\RequestToEcom;
use App\Foundations\Enums\LogKeyEnum;
use App\Foundations\Modules\History\Services\OrderPartsHistoryService;
use App\Services\Requests\ECom\Commands\Order\Parts\OrderChangeStatusCommand;
use App\Services\Requests\ECom\Commands\Order\Parts\OrderChangeStatusPaidCommand;
use App\Services\Requests\ECom\Commands\Order\Parts\OrderDeleteCommand;
use App\Services\Requests\ECom\Commands\Order\Parts\OrderUpdateCommand;

class RequestToEcomListener
{
    public function __construct(
        protected OrderChangeStatusCommand $changeStatusCommand,
        protected OrderChangeStatusPaidCommand $changeStatusPaidCommand,
        protected OrderUpdateCommand $updateCommand,
        protected OrderDeleteCommand $deleteCommand,
    )
    {}

    public function handle(RequestToEcom $event): void
    {
        if(!$event->getModel()->source->isHaulkDepot()) return;

        try {

            $res = match ($event->getAction()) {
                OrderPartsHistoryService::ACTION_STATUS_CHANGED => $this->changeStatusCommand->exec($event->getModel()),
                OrderPartsHistoryService::ACTION_IS_PAID => $this->changeStatusPaidCommand->exec($event->getModel()),
                OrderPartsHistoryService::ACTION_REFUNDED => $this->changeStatusPaidCommand->exec($event->getModel()),
                OrderPartsHistoryService::ACTION_UPDATE => $this->updateCommand->exec($event->getModel()),
                OrderPartsHistoryService::ACTION_DELETE => $this->deleteCommand->exec(['id' => $event->getModel()->id]),
                default => [],
            };

            logger_info(LogKeyEnum::SyncECom->value." SUCCESS - ". __CLASS__ . " [{$event->getModel()->order_number}] ", [$res]);
        } catch (\Throwable $e) {
            logger_info( LogKeyEnum::SyncECom->value. " FAILED - " . __CLASS__, [
                'message' => $e->getMessage()
            ]);
        }
    }
}
