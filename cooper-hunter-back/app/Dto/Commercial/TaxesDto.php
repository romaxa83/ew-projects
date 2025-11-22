<?php

namespace App\Dto\Commercial;

class TaxesDto
{
    public array $items = [];

    public static function byArgs(array $args): self
    {
        $dto = new self();

        foreach ($args['data'] ?? [] as $item){
            $dto->items[] = TaxDto::byArgs($item);
        }

        return $dto;
    }
}
