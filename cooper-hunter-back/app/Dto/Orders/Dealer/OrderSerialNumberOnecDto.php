<?php

namespace App\Dto\Orders\Dealer;

class OrderSerialNumberOnecDto
{
    public string $guid;
    public array $serialNumbers = [];

    public static function byArgs(array $args): self
    {
        $dto = new self();

        $dto->guid = data_get($args, 'guid');
        $dto->serialNumbers = data_get($args, 'serial_numbers');

        return $dto;
    }
}

