<?php

namespace App\Dto\Orders\Dealer;

class OrderItemDto
{
    public string $guid;
    public float $discount;
    public float $discount_total;
    public float $qty;
    public float $total;
    public float $price;
    public ?string $description;

    public static function byArgs(array $args): self
    {
        $dto = new self();

        $dto->guid = data_get($args, 'guid');
        $dto->discount = data_get($args, 'discount');
        $dto->discount_total = data_get($args, 'discount_total', 0);
        $dto->qty = data_get($args, 'qty');
        $dto->total = data_get($args, 'total');
        $dto->price = data_get($args, 'price');
        $dto->description = data_get($args, 'description');

        return $dto;
    }
}
