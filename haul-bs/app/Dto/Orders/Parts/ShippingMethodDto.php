<?php

namespace App\Dto\Orders\Parts;

class ShippingMethodDto
{
    public string $name;
    public float $cost;
    public string|null $terms;
    public array $itemsIds;

    public static function byArgs(array $data): self
    {
        $self = new self();

        $self->name = $data['name'];
        $self->cost = $data['cost'];
        $self->terms = $data['terms'] ?? null;
        $self->itemsIds = $data['items_ids'];

        return $self;
    }
}
