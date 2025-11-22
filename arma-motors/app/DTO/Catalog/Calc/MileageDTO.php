<?php

namespace App\DTO\Catalog\Calc;

use App\Traits\AssetData;

final class MileageDTO
{
    use AssetData;

    private int $value = 0;
    private bool $active = true;

    private function __construct(){}

    public static function byArgs(array $args): self
    {
        self::assetFieldAll($args, 'value');

        $self = new self();

        $self->value = $args['value'];
        $self->active = $args['active'] ?? true;

        return $self;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function getActive(): bool
    {
        return $this->active;
    }
}


