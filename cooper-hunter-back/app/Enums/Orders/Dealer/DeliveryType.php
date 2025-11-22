<?php

namespace App\Enums\Orders\Dealer;

use Core\Enums\BaseEnum;

/**
 * @method static static NONE()
 * @method static static LTL()
 * @method static static PICK_UP()
 * @method static static TRAIN()
 * @method static static TRUCK()
 */
final class DeliveryType extends BaseEnum
{
    public const NONE    = 'none';
    public const LTL     = 'ltl';
    public const PICK_UP = 'pickUp';
    public const TRAIN   = 'train';
    public const TRUCK   = 'truck';

    public function isNone(): bool
    {
        return $this->is(self::NONE());
    }

    public function isLtl(): bool
    {
        return $this->is(self::LTL());
    }

    public function isPickUp(): bool
    {
        return $this->is(self::PICK_UP());
    }

    public function isTrain(): bool
    {
        return $this->is(self::TRAIN());
    }

    public function isTruck(): bool
    {
        return $this->is(self::TRUCK());
    }

    public function asString(): string
    {
        if($this->isNone()){
            return 'NONE';
        }
        if($this->isLtl()){
            return 'LTL';
        }
        if($this->isPickUp()){
            return 'Pick up';
        }
        if($this->isTruck()){
            return 'Truck';
        }
        if($this->isTrain()){
            return 'Train';
        }
    }
}
