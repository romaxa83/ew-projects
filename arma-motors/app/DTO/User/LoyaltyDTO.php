<?php

namespace App\DTO\User;

use App\Models\User\Car;
use App\Traits\AssetData;
use App\ValueObjects\CarNumber;
use App\ValueObjects\CarVin;

class LoyaltyDTO
{
    use AssetData;

    private $brandId;
    private $type;
    private $age;
    private $discount;
    private bool $active;

    private function __construct()
    {}

    public static function byArgs(array $args): self
    {
        self::assetFieldAll($args, 'brandId');
        self::assetFieldAll($args, 'type');
        self::assetFieldAll($args, 'discount');

        $self = new self();

        $self->brandId = $args['brandId'];
        $self->type = $args['type'];
        $self->age = $args['age'] ?? null;
        $self->discount = $args['discount'];
        $self->active = $args['active'] ?? true;

        return $self;
    }

    public function getBrandId()
    {
        return $this->brandId;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getDiscount()
    {
        return $this->discount;
    }

    public function getAge()
    {
        return $this->age;
    }

    public function getActive(): bool
    {
        return $this->active;
    }
}
