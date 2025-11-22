<?php

namespace App\DTO\Catalog\Car;

use App\Traits\AssetData;

class DriveUnitEditDTO
{
    use AssetData;

    private null|bool $active;
    private null|string $name;
    private null|int $sort;

    private bool $changeSort;
    private bool $changeActive;
    private bool $changeName;

    private function __construct(array $data)
    {
        $this->changeActive = static::checkFieldExist($data, 'active');
        $this->changeSort = static::checkFieldExist($data, 'sort');
        $this->changeName = static::checkFieldExist($data, 'name');
    }

    public static function byArgs(
        array $args,
    ): self
    {
        $self = new self($args);

        $self->active = $args['active'] ?? null;
        $self->sort = $args['sort'] ?? null;
        $self->name = $args['name'] ?? null;

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

    public function getName(): null|string
    {
        return $this->name;
    }

    public function changeSort(): bool
    {
        return $this->changeSort;
    }

    public function changeName(): bool
    {
        return $this->changeName;
    }

    public function changeActive(): bool
    {
        return $this->changeActive;
    }
}

