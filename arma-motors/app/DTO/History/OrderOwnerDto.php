<?php

namespace App\DTO\History;

final class OrderOwnerDto
{
    public null|string $address;
    public null|string $email;
    public null|string $name;
    public null|string $certificate;
    public null|string $phone;
    public null|string $etc;

    private function __construct()
    {}

    public static function byRequest(array $data): self
    {
        $self = new self();

        $self->address = $data['address'] ?? null;
        $self->email = $data['email'] ?? null;
        $self->name = $data['name'] ?? null;
        $self->certificate = $data['certificate'] ?? null;
        $self->phone = $data['phone'] ?? null;
        $self->etc = $data['etc'] ?? null;

        return $self;
    }
}
