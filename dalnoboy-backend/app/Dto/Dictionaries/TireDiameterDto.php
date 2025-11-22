<?php

namespace App\Dto\Dictionaries;

class TireDiameterDto
{
    private bool $active;
    private string $value;

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

    public function getValue(): string
    {
        return $this->value;
    }
}
