<?php

namespace App\DTO\Catalog\Car;

use App\Traits\AssetData;

final class DriveUnitDTO
{
    use AssetData;

    private int $sort = 0;
    private string $name;
    private bool $active = true;

    private function __construct(){}

    public static function byArgs(array $args): self
    {
        self::assetFieldAll($args, 'name');

        $self = new self();

        $self->name = $args['name'];
        $self->sort = $args['sort'] ?? 0;
        $self->active = $args['active'] ?? true;

        return $self;
    }

    public function getSort(): int
    {
        return $this->sort;
    }

    public function getActive(): bool
    {
        return $this->active;
    }

    public function getName(): string
    {
        return $this->name;
    }
}


