<?php

namespace App\DTO\History;

final class OrderOrganizationDto
{
    public null|string $address;
    public null|string $phone;
    public null|string $name;

    private function __construct()
    {}

    public static function byRequest(array $data): self
    {
        $self = new self();

        $self->address = $data['address'] ?? null;
        $self->phone = $data['phone'] ?? null;
        $self->name = $data['name'] ?? null;

        return $self;
    }
}
