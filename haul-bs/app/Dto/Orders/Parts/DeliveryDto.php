<?php

namespace App\Dto\Orders\Parts;

class DeliveryDto
{
    public string $method;
    public float $cost;
    public ?string $sentAt;
    public ?string $trackingNumbers;

    public static function byArgs(array $data): self
    {
        $self = new self();

        $self->method = $data['delivery_method'];
        $self->cost = $data['delivery_cost'];
        $self->sentAt = $data['date_sent'] ?? null;
        $self->trackingNumbers = $data['tracking_number'] ?? null;

        return $self;
    }
}
