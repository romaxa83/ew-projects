<?php

namespace App\DTO\Catalog\Car;

use App\Casts\MoneyCast;
use App\Traits\AssetData;
use App\ValueObjects\Money;

class BrandEditDTO
{
    use AssetData;

    private null|int $color;
    private null|bool $isMain;
    private null|bool $active;
    private null|int $sort;
    private null|string $name;
    private null|Money $hourlyPayment;
    private null|Money $discountHourlyPayment;
    private array $workIds = [];
    private array $mileageIds = [];

    private bool $changeIsMain;
    private bool $changeActive;
    private bool $changeSort;
    private bool $changeColor;
    private bool $changeName;
    private bool $changeHourlyPayment;
    private bool $changeDiscountHourlyPayment;

    private function __construct(array $data)
    {
        $this->changeIsMain = static::checkFieldExist($data, 'isMain');
        $this->changeColor = static::checkFieldExist($data, 'color');
        $this->changeActive = static::checkFieldExist($data, 'active');
        $this->changeSort = static::checkFieldExist($data, 'sort');
        $this->changeName = static::checkFieldExist($data, 'name');
        $this->changeHourlyPayment = static::checkFieldExist($data, 'hourlyPayment');
        $this->changeDiscountHourlyPayment = static::checkFieldExist($data, 'discountHourlyPayment');
    }

    public static function byArgs(
        array $args,
    ): self
    {
        $self = new self($args);

        $self->isMain = $args['isMain'] ?? null;
        $self->color = $args['color'] ?? null;
        $self->active = $args['active'] ?? null;
        $self->sort = $args['sort'] ?? null;
        $self->name = $args['name'] ?? null;
        $self->hourlyPayment = isset($args['hourlyPayment']) && !empty($args['hourlyPayment'])
            ? new Money($args['hourlyPayment'])
            : null;
        $self->discountHourlyPayment = isset($args['discountHourlyPayment']) && !empty($args['discountHourlyPayment'])
            ? new Money($args['discountHourlyPayment'])
            : null;

        $self->workIds = $args['workIds'] ?? [];
        $self->mileageIds = $args['mileageIds'] ?? [];

        return $self;
    }

    public function getIsMain(): null|bool
    {
        return $this->isMain;
    }

    public function getColor(): null|int
    {
        return $this->color;
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

    public function getHourlyPayment(): null|Money
    {
        return $this->hourlyPayment;
    }

    public function getDiscountHourlyPayment(): null|Money
    {
        return $this->discountHourlyPayment;
    }

    public function getWorkIds(): array
    {
        return $this->workIds;
    }

    public function emptyWorkIds(): bool
    {
        return empty($this->workIds);
    }

    public function getMileageIds(): array
    {
        return $this->mileageIds;
    }

    public function emptyMileageIds(): bool
    {
        return empty($this->mileageIds);
    }

    public function changeIsMain(): bool
    {
        return $this->changeIsMain;
    }

    public function changeSort(): bool
    {
        return $this->changeSort;
    }

    public function changeActive(): bool
    {
        return $this->changeActive;
    }

    public function changeColor(): bool
    {
        return $this->changeColor;
    }

    public function changeName(): bool
    {
        return $this->changeName;
    }

    public function changeHourlyPayment(): bool
    {
        return $this->changeHourlyPayment;
    }

    public function changeDiscountHourlyPayment(): bool
    {
        return $this->changeDiscountHourlyPayment;
    }
}
