<?php

namespace App\DTO\History;

final class OrderPartDto
{
    public null|string $name;
    public null|float $amount_including_vat;
    public null|float $amount_without_vat;
    public null|float $price;
    public null|float $price_with_vat;
    public null|float $price_without_vat;
    public null|string $producer;
    public null|float $quantity;
    public null|string $unit;
    public null|string $ref;
    public null|float $rate;

    private function __construct()
    {}

    public static function byRequest(array $data): self
    {
        $self = new self();

        $self->name = $data['name'] ?? null;
        $self->amount_including_vat = $data['amountIncludingVAT'] ?? null;
        $self->amount_without_vat = $data['amountWithoutVAT'] ?? null;
        $self->price = $data['price'] ?? null;
        $self->price_with_vat = $data['priceWithVAT'] ?? null;
        $self->price_without_vat = $data['priceWithoutVAT'] ?? null;
        $self->producer = $data['producer'] ?? null;
        $self->quantity = $data['quantity'] ?? null;
        $self->unit = $data['unit'] ?? null;
        $self->ref = $data['ref'] ?? null;
        $self->rate = $data['rate'] ?? null;

        return $self;
    }
}
