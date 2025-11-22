<?php

namespace App\Dto\Orders\Dealer;

use App\Enums\Orders\Dealer\OrderStatus;

class OrderPackingSlipOnecDto
{
    public string $guid;
    public string $number;
    public OrderStatus $status;
    public ?string $trackingNumber;
    public ?string $trackingCompany;
    public ?string $shippedAt;

    /** @var array<OrderPackingSlipDimensionsOnecDto> */
    public array $dimensions = [];

    /** @var array<OrderPackingSlipItemDto> */
    public array $items = [];

    public static function byArgs(array $args): self
    {
        $dto = new self();

        $dto->guid = data_get($args, 'guid');
        $dto->status = OrderStatus::fromValue($args['status']);
        $dto->number = data_get($args, 'number');
        $dto->trackingNumber = data_get($args, 'tracking_number');
        $dto->trackingCompany = data_get($args, 'tracking_company');
        $dto->shippedAt = data_get($args, 'shipped_date');

        foreach (data_get($args, 'products', []) as $item){
            $dto->items[] = OrderPackingSlipItemDto::byArgs($item);
        }

        foreach (data_get($args, 'dimensions', []) as $dims){
            $dto->dimensions[] = OrderPackingSlipDimensionsOnecDto::byArgs($dims);
        }

        return $dto;
    }
}
