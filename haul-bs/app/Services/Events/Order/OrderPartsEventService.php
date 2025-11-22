<?php

namespace App\Services\Events\Order;

use App\Events\Events\Orders\Parts\RequestToEcom;
use App\Foundations\Modules\History\Contracts\HistoryServiceInterface;
use App\Foundations\Modules\History\Services\OrderPartsHistoryService;
use App\Models\Orders\Parts\Order;
use App\Services\Events\EventService;

class OrderPartsEventService extends EventService
{
    protected bool $sendToEcomm = false;

    public function __construct(
        protected Order $model,
        protected array $additional = []
    )
    {}

    public function setHistory(array $additional = []): self
    {
        $this->setHistory = true;
        $this->historyAdditional = $additional;
        return $this;
    }

    public function getHistoryService(): HistoryServiceInterface
    {
        return resolve(OrderPartsHistoryService::class);
    }

    public function sendToEcomm(string $action = null): self
    {
        if($this->model->source->isHaulkDepot()){
            $action = $action ?? $this->action;
            event(new RequestToEcom($this->model, $action));
        }

        return $this;
    }
}
