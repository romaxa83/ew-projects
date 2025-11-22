<?php

namespace App\Dto\Orders\Dealer;

class OrderPackingSlipsOnecDto
{
    /** @var array<OrderPackingSlipOnecDto> */
    public array $items = [];

    public static function byArgs(array $args): self
    {
        $dto = new self();

        foreach ($args as $item){
            $dto->items[] = OrderPackingSlipOnecDto::byArgs($item);
        }

        return $dto;
    }
}

