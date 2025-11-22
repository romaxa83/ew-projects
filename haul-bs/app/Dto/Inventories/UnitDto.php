<?php

namespace App\Dto\Inventories;

class UnitDto
{
    public string $name;
    public bool $acceptDecimals;

    public static function byArgs(array $data): self
    {
        $self = new self();

        $self->name = data_get($data, 'name');
        $self->acceptDecimals = data_get($data, 'accept_decimals', false);

        return $self;
    }
}
