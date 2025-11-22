<?php

namespace App\Dto\Orders\Dealer;

use App\Enums\Orders\Dealer\OrderStatus;
use App\Traits\SimpleHasher;

class OrderOnecDto
{
    use SimpleHasher;

    public ?OrderStatus $status;
    public ?string $trackingNumber;
    public ?string $trackingCompany;
    public ?string $shippedDate;

    public ?string $term;
    public ?float $tax;
    public ?float $shippingPrice;
    public ?float $total;
    public ?float $totalDiscount;
    public ?float $totalWithDiscount;

    /** @var array<OrderItemDto> */
    public array $items = [];

    public ?string $hash;

    public static function byArgs(array $args): self
    {
        $dto = new self();
        $dto->status = isset($args['status'])
            ? OrderStatus::fromValue($args['status'])
            : null
        ;
        $dto->trackingNumber = data_get($args, 'tracking_number');
        $dto->trackingCompany = data_get($args, 'tracking_company');
        $dto->shippedDate = data_get($args, 'shipped_date');
        $dto->term = data_get($args, 'term');
        $dto->tax = data_get($args, 'tax');
        $dto->shippingPrice = data_get($args, 'shipping_price');
        $dto->total = data_get($args, 'total');
        $dto->totalDiscount = data_get($args, 'total_discount');
        $dto->totalWithDiscount = data_get($args, 'total_with_discount');
        $dto->hash = self::getHash($args);

        foreach (data_get($args, 'products', []) as $item){
            $dto->items[] = OrderItemDto::byArgs($item);
        }
        return $dto;
    }

    private static function getHash(array $data): string
    {
        return self::hash(data_get($data, 'products', []));
    }
}
