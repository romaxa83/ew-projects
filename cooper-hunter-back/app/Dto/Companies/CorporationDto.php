<?php

namespace App\Dto\Companies;

class CorporationDto
{
    public string $guid;
    public string $name;

    public static function byArgs(array $args): self
    {
        $dto = new self();
        $dto->guid = data_get($args, 'guid');
        $dto->name = data_get($args, 'name');

        return $dto;
    }
}
