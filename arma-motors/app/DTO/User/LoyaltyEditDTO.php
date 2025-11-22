<?php

namespace App\DTO\User;

use App\Traits\AssetData;

class LoyaltyEditDTO
{
    use AssetData;

    private $discount;
    private $active;

    private bool $changeActive;
    private bool $changeDiscount;

    private function __construct(array $data)
    {
        $this->changeActive = static::checkFieldExist($data, 'active');
        $this->changeDiscount = static::checkFieldExist($data, 'discount');
    }

    public static function byArgs(array $args): self
    {
        $self = new self($args);

        $self->discount = $args['discount'] ?? null;
        $self->active = $args['active'] ?? null;

        return $self;
    }

    public function getDiscount()
    {
        return $this->discount;
    }

    public function getActive()
    {
        return $this->active;
    }

    public function changeActive(): bool
    {
        return $this->changeActive;
    }

    public function changeDiscount(): bool
    {
        return $this->changeDiscount;
    }
}
