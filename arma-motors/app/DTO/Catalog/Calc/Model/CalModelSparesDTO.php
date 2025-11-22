<?php

namespace App\DTO\Catalog\Calc\Model;

final class CalModelSparesDTO
{
    private int|string $id;
    private float $qty;

    private function __construct(){}

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->id = $args['id'];
        $self->qty = $args['qty'];

        return $self;
    }

    public function getId(): int|string
    {
        return $this->id;
    }

    public function getQty(): float
    {
        return $this->qty;
    }
}

