<?php

namespace App\Dto\Orders\Dealer;

class OrderSerialNumbersOnecDto
{
    /** @var array<OrderSerialNumberOnecDto> */
    public array $items = [];

    public static function byArgs(array $args): self
    {
        $dto = new self();

        foreach ($args as $item){
            $dto->items[] = OrderSerialNumberOnecDto::byArgs($item);
        }

        return $dto;
    }
}
