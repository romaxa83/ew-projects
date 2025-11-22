<?php

namespace App\DTO\History;

final class OrderDispatcherDto
{
    public null|string $fio;
    public null|string $date;
    public null|string $name;
    public null|string $number;
    public null|string $position;

    private function __construct()
    {}

    public static function byRequest(array $data): self
    {
        $self = new self();

        $self->fio = $data['FIO'] ?? null;
        $self->date = $data['date'] ?? null;
        $self->name = $data['name'] ?? null;
        $self->number = $data['number'] ?? null;
        $self->position = $data['position'] ?? null;

        return $self;
    }
}
