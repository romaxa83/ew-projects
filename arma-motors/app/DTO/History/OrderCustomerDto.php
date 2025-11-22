<?php

namespace App\DTO\History;

final class OrderCustomerDto
{
    public null|string $fio;
    public null|string $date;
    public null|string $email;
    public null|string $name;
    public null|string $number;
    public null|string $phone;

    private function __construct()
    {}

    public static function byRequest(array $data): self
    {
        $self = new self();

        $self->fio = $data['FIO'] ?? null;
        $self->date = $data['date'] ?? null;
        $self->email = $data['email'] ?? null;
        $self->name = $data['name'] ?? null;
        $self->number = $data['number'] ?? null;
        $self->phone = $data['phone'] ?? null;

        return $self;
    }
}
