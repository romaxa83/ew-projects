<?php

namespace App\Dto\Orders\Parts;

class ItemDto
{
    public int|string|null $inventoryId;
    public float $qty;
    public float $discount;

    public static function byArgs(array $data): self
    {
        $self = new self();

        $self->inventoryId = $data['inventory_id'] ?? null;
        $self->qty = $data['quantity'];
        $self->discount = $data['discount'] ?? 0;

        return $self;
    }
}
