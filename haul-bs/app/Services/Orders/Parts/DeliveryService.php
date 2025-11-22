<?php

namespace App\Services\Orders\Parts;

use App\Dto\Orders\Parts\DeliveryDto;
use App\Enums\Orders\Parts\DeliveryStatus;
use App\Foundations\Modules\History\Services\OrderPartsHistoryService;
use App\Models\Orders\Parts\Delivery;
use App\Models\Orders\Parts\Order;
use App\Services\Events\EventService;

class DeliveryService
{
    public function __construct()
    {}

    public function create(
        DeliveryDto $dto,
        Order $order,
    ): Delivery
    {
        $model = $this->fill(new Delivery(), $dto);

        $model->order_id = $order->id;
        $model = $this->setStatus($model, DeliveryStatus::Sent);

        $model->save();

        return $model;
    }

    public function update(
        Delivery $model,
        DeliveryDto $dto,
    ): Delivery
    {
        $old = $model->dataForUpdateHistory();

        $model = $this->fill($model, $dto);

        $model->save();

        EventService::partsOrder($model->order)
            ->initiator(auth_user())
            ->custom(OrderPartsHistoryService::ACTION_UPDATE_DELIVERY)
            ->setHistory([
                'old_value' => $old,
                'change_fields' => $model->getChanges(),
            ])
            ->exec()
        ;

        return $model;
    }

    private function fill(Delivery $model, DeliveryDto $dto): Delivery
    {
        $model->method = $dto->method;
        $model->cost = $dto->cost;
        $model->sent_at = $dto->sentAt;
        $model->tracking_number = $dto->trackingNumbers;

        return $model;
    }

    public function setStatus(
        Delivery $model,
        DeliveryStatus $status,
        bool $save = false
    ): Delivery
    {
        $model->status = $status;

        if ($save) $model->save();

        return $model;
    }
}

