<?php

namespace App\DTO\Catalog\Calc;

use App\Traits\AssetData;

final class SparesEditDTO
{
    use AssetData;

    private null|int|string $groupId;
    private null|bool $active;
    private null|string $name;
    private null|float $price;
    private null|float $priceDiscount;

    private bool $changeGroupId;
    private bool $changeActive;
    private bool $changeName;
    private bool $changePrice;
    private bool $changePriceDiscount;

    private function __construct(array $data)
    {
        $this->changeGroupId = static::checkFieldExist($data, 'groupId');
        $this->changeActive = static::checkFieldExist($data, 'active');
        $this->changeName = static::checkFieldExist($data, 'name');
        $this->changePrice = static::checkFieldExist($data, 'price');
        $this->changePriceDiscount = static::checkFieldExist($data, 'priceDiscount');
    }

    public static function byArgs(
        array $args,
    ): self
    {
        $self = new self($args);

        $self->groupId = $args['groupId'] ?? null;
        $self->active = $args['active'] ?? null;
        $self->name = $args['name'] ?? null;
        $self->price = $args['price'] ?? null;
        $self->priceDiscount = $args['priceDiscount'] ?? null;

        return $self;
    }

    public function getGroupId(): null|int|string
    {
        return $this->groupId;
    }

    public function changeGroupId(): bool
    {
        return $this->changeGroupId;
    }

    public function getActive(): null|bool
    {
        return $this->active;
    }

    public function changeActive(): bool
    {
        return $this->changeActive;
    }

    public function getName(): null|string
    {
        return $this->name;
    }

    public function changeName(): bool
    {
        return $this->changeName;
    }

    public function getPrice(): null|string
    {
        return $this->price;
    }

    public function changePrice(): bool
    {
        return $this->changePrice;
    }

    public function getPriceDiscount(): null|string
    {
        return $this->priceDiscount;
    }

    public function changePriceDiscount(): bool
    {
        return $this->changePriceDiscount;
    }
}

