<?php

namespace App\DTO\Catalog\Car;

use App\Traits\AssetData;

class ModelEditDTO
{
    use AssetData;

    private null|bool $active;
    private null|bool $forCredit;
    private null|bool $forCalc;
    private null|string $name;
    private null|int $sort;
    private null|int $brandId;

    private bool $changeName;
    private bool $changeActive;
    private bool $changeForCredit;
    private bool $changeForCalc;
    private bool $changeSort;
    private bool $changeBrandId;

    private function __construct(array $data)
    {
        $this->changeName = static::checkFieldExist($data, 'name');
        $this->changeBrandId = static::checkFieldExist($data, 'brandId');
        $this->changeActive = static::checkFieldExist($data, 'active');
        $this->changeSort = static::checkFieldExist($data, 'sort');
        $this->changeForCalc = static::checkFieldExist($data, 'forCalc');
        $this->changeForCredit = static::checkFieldExist($data, 'forCredit');
    }

    public static function byArgs(
        array $args,
    ): self
    {
        $self = new self($args);

        $self->brandId = $args['brandId'] ?? null;
        $self->name = $args['name'] ?? null;
        $self->active = $args['active'] ?? null;
        $self->forCalc = $args['forCalc'] ?? null;
        $self->forCredit = $args['forCredit'] ?? null;
        $self->sort = $args['sort'] ?? null;

        return $self;
    }

    public function getBrandId(): null|int
    {
        return $this->brandId;
    }

    public function getName(): null|string
    {
        return $this->name;
    }

    public function getActive(): null|bool
    {
        return $this->active;
    }

    public function getForCalc(): null|bool
    {
        return $this->forCalc;
    }

    public function getForCredit(): null|bool
    {
        return $this->forCredit;
    }

    public function getSort(): null|int
    {
        return $this->sort;
    }

    public function changeBrandId(): bool
    {
        return $this->changeBrandId;
    }

    public function changeSort(): bool
    {
        return $this->changeSort;
    }

    public function changeActive(): bool
    {
        return $this->changeActive;
    }

    public function changeForCalc(): bool
    {
        return $this->changeForCalc;
    }

    public function changeForCredit(): bool
    {
        return $this->changeForCredit;
    }

    public function changeName(): bool
    {
        return $this->changeName;
    }
}
