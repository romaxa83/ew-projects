<?php

namespace App\DTO\History;

final class InvoicePartDto
{
    public null|string $name;
    public null|string $ref;
    public null|string $unit;
    public null|float $discounted_price;
    public null|float $price;
    public null|float $quantity;
    public null|float $rate;
    public null|float $sum;

    private function __construct()
    {}

    public static function byRequest(array $data): self
    {
        $self = new self();

        $self->name = $data['name'] ?? null;
        $self->ref = $data['ref'] ?? null;
        $self->unit = $data['unit'] ?? null;
        $self->discounted_price = $data['discountedPrice'] ?? null;
        $self->price = $data['price'] ?? null;
        $self->quantity = $data['quantity'] ?? null;
        $self->rate = $data['rate'] ?? null;
        $self->sum = $data['sum'] ?? null;

        return $self;
    }
}


