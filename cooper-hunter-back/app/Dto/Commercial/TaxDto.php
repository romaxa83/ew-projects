<?php

namespace App\Dto\Commercial;

class TaxDto
{
    public string $guid;
    public string $name;
    public float $value;

    public static function byArgs(array $args): self
    {
        $dto = new self();

        $dto->guid = data_get($args, 'guid');
        $dto->name = data_get($args, 'name');
        $dto->value = (float)data_get($args, 'value');

        return $dto;
    }
}


