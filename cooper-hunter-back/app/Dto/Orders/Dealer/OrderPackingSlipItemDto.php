<?php

namespace App\Dto\Orders\Dealer;

class OrderPackingSlipItemDto
{
    public string $guid;
    public float $qty;
    public ?string $description;

    public static function byArgs(array $args): self
    {
        $dto = new self();

        $dto->guid = data_get($args, 'guid');
        $dto->qty = data_get($args, 'qty');
        $dto->description = data_get($args, 'description');

        return $dto;
    }
}
