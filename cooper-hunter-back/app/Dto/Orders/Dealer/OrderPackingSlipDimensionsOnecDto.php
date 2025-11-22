<?php

namespace App\Dto\Orders\Dealer;

class OrderPackingSlipDimensionsOnecDto
{
    public int $pallet;
    public int $boxQty;
    public string $type;
    public float $weight;
    public float $width;
    public float $depth;
    public float $height;
    public int $classFreight;

    public static function byArgs(array $args): self
    {
        $dto = new self();

        $dto->pallet = data_get($args, 'pallet');
        $dto->boxQty = data_get($args, 'box_qty');
        $dto->type = data_get($args, 'type');
        $dto->weight = data_get($args, 'weight');
        $dto->width = data_get($args, 'width');
        $dto->depth = data_get($args, 'depth');
        $dto->height = data_get($args, 'height');
        $dto->classFreight = data_get($args, 'class_freight');

        return $dto;
    }
}
