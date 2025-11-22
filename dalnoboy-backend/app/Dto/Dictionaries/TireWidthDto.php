<?php

namespace App\Dto\Dictionaries;

class TireWidthDto
{
    private bool $active;
    private float $value;

    public static function byArgs(array $args): self
    {
        $dto = new self();
        $dto->active = $args['active'];
        $dto->value = $args['value'];

        return $dto;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function getValue(): float
    {
        return $this->value;
    }
}
