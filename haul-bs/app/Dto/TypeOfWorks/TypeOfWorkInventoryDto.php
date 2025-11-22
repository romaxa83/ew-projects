<?php

namespace App\Dto\TypeOfWorks;

class TypeOfWorkInventoryDto
{
    public string|int $inventoryId;
    public float $quantity;

    public static function byArgs(array $data): self
    {
        $self = new self();

        $self->inventoryId = data_get($data, 'id');
        $self->quantity = data_get($data, 'quantity');

        return $self;
    }
}
