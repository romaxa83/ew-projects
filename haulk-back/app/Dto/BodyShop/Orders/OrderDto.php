<?php

namespace App\Dto\BodyShop\Orders;

use App\Models\BodyShop\Orders\Order;

class OrderDto
{
    private array $orderData;

    private array $typeOfWorkData;

    private ?array $attachments;

    private bool $needToUpdatePrices;

    public static function byParams(array $data): self
    {
        $dto = new self();

        $dto->orderData = [
            'truck_id' => $data['truck_id'] ?? null,
            'trailer_id' => $data['trailer_id'] ?? null,
            'discount' => $data['discount'] ?? null,
            'tax_labor' => $data['tax_labor'] ?? null,
            'tax_inventory' => $data['tax_inventory'] ?? null,
            'implementation_date' => fromBSTimezone('Y-m-d H:i', $data['implementation_date']),
            'mechanic_id' => $data['mechanic_id'],
            'notes' => $data['notes'],
            'due_date' => $data['due_date'],
        ];

        $dto->typeOfWorkData = [];

        foreach ($data['types_of_work'] ?? [] as $typeOfWork) {
            $dto->typeOfWorkData[] = TypeOfWorkDto::byParams($typeOfWork);
        }

        $dto->attachments = $data[Order::ATTACHMENT_FIELD_NAME] ?? null;

        $dto->needToUpdatePrices = $data['need_to_update_prices'] ?? false;

        return $dto;
    }

    public function getTypeOfWorkData(): array
    {
        return $this->typeOfWorkData;
    }

    public function getOrderData(): array
    {
        return $this->orderData;
    }

    public function getAttachments(): array
    {
        return $this->attachments ?? [];
    }

    public function isNeedToUpdatePrices(): bool
    {
        return $this->needToUpdatePrices;
    }
}
