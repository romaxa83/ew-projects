<?php

namespace App\DTO\Catalog\Calc\Model;

final class CalModelWorkDTO
{
    private int|string $id;
    private float $minutes;

    private function __construct(){}

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->id = $args['id'];
        $self->minutes = $args['minutes'];

        return $self;
    }

    public function getId(): int|string
    {
        return $this->id;
    }

    public function getMinutes(): float
    {
        return $this->minutes;
    }
}
