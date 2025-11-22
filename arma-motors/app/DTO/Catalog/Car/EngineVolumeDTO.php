<?php

namespace App\DTO\Catalog\Car;

use App\Traits\AssetData;
use App\ValueObjects\Volume;

final class EngineVolumeDTO
{
    use AssetData;

    private int $sort = 0;
    private bool $active = true;
    private Volume $volume;

    private function __construct(){}

    public static function byArgs(array $args): self
    {
        self::assetFieldAll($args, 'volume');

        $self = new self();

        $self->volume = new Volume($args['volume']);
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

    public function getVolume(): Volume
    {
        return $this->volume;
    }
}


