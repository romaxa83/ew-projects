<?php

namespace App\DTO\History;

final class OrderPayerDto
{
    public null|string $name;
    public null|string $date;
    public null|string $number;
    public null|string $contract;

    private function __construct()
    {}

    public static function byRequest(array $data): self
    {
        $self = new self();

        $self->name = $data['name'] ?? null;
        $self->date = $data['date'] ?? null;
        $self->number = $data['number'] ?? null;
        $self->contract = $data['contract'] ?? null;

        return $self;
    }
}
