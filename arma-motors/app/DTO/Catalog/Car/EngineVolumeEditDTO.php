<?php

namespace App\DTO\Catalog\Car;

use App\DTO\NameTranslationDTO;
use App\Traits\AssetData;
use App\ValueObjects\Volume;

class EngineVolumeEditDTO
{
    use AssetData;

    private null|bool $active;
    private null|int $sort;
    private null|Volume $volume;

    private bool $changeSort;
    private bool $changeActive;
    private bool $changeVolume;

    private function __construct(array $data)
    {
        $this->changeActive = static::checkFieldExist($data, 'active');
        $this->changeSort = static::checkFieldExist($data, 'sort');
        $this->changeVolume = static::checkFieldExist($data, 'volume');
    }

    public static function byArgs(
        array $args,
    ): self
    {
        $self = new self($args);

        $self->active = $args['active'] ?? null;
        $self->sort = $args['sort'] ?? null;
        $self->volume = isset($args['volume']) && !empty($args['volume']) ? new Volume($args['volume']) : null;

        return $self;
    }

    public function getActive(): null|bool
    {
        return $this->active;
    }

    public function getSort(): null|int
    {
        return $this->sort;
    }

    public function getVolume(): null|Volume
    {
        return $this->volume;
    }

    public function changeSort(): bool
    {
        return $this->changeSort;
    }

    public function changeActive(): bool
    {
        return $this->changeActive;
    }

    public function changeVolume(): bool
    {
        return $this->changeVolume;
    }
}

