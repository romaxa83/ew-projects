<?php

namespace App\Enums\Orders\Dealer;

use Core\Enums\BaseEnum;

/**
 * @method static static DRAFT()
 * @method static static SENT()
 * @method static static APPROVED()
 * @method static static PROCESSING()
 * @method static static SHIPPED()
 * @method static static CANCELED()
 * @method static static BACKORDER()
 */
final class OrderStatus extends BaseEnum
{
    public const DRAFT     = 'draft';
    public const SENT      = 'sent';
    public const APPROVED  = 'approved';
    public const PROCESSING = 'processing';
    public const SHIPPED   = 'shipped';
    public const CANCELED  = 'canceled';
    public const BACKORDER = 'backorder';   // нет товара на данный момент, но он будет позже и клиент согласен подождать

    public function isDraft(): bool
    {
        return $this->is(self::DRAFT());
    }

    public function isSent(): bool
    {
        return $this->is(self::SENT());
    }

    public function isApproved(): bool
    {
        return $this->is(self::APPROVED());
    }

    public function isShipped(): bool
    {
        return $this->is(self::SHIPPED());
    }
}
