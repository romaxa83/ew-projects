<?php

namespace App\DTO\Catalog\Calc;

use App\Traits\AssetData;

class MileageEditDTO
{
    use AssetData;

    private null|bool $active;
    private null|int $value;

    private bool $changeValue;
    private bool $changeActive;

    private function __construct(array $data)
    {
        $this->changeActive = static::checkFieldExist($data, 'active');
        $this->changeValue = static::checkFieldExist($data, 'value');
    }

    public static function byArgs(
        array $args,
    ): self
    {
        $self = new self($args);

        $self->active = $args['active'] ?? null;
        $self->value = $args['value'] ?? null;

        return $self;
    }

    public function getActive(): null|bool
    {
        return $this->active;
    }

    public function getValue(): null|int
    {
        return $this->value;
    }

    public function changeValue(): bool
    {
        return $this->changeValue;
    }

    public function changeActive(): bool
    {
        return $this->changeActive;
    }
}

