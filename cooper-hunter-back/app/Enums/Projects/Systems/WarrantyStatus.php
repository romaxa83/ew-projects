<?php

namespace App\Enums\Projects\Systems;

use BenSampo\Enum\Contracts\LocalizedEnum;
use Core\Enums\BaseEnum;

/**
 * @method static static WARRANTY_NOT_REGISTERED()
 * @method static static PENDING()
 * @method static static ON_WARRANTY()
 * @method static static VOIDED()
 * @method static static DENIED()
 * @method static static DELETE()
 * @method static static EXPIRED()
 */
class WarrantyStatus extends BaseEnum implements LocalizedEnum
{
    public const WARRANTY_NOT_REGISTERED = 'warranty_not_registered';
    public const PENDING = 'pending';
    public const ON_WARRANTY = 'on_warranty';
    public const VOIDED = 'voided';
    public const DENIED = 'denied';
    public const DELETE = 'delete';
    public const EXPIRED = 'expired';

    public function notRegistered(): bool
    {
        return $this->is(self::WARRANTY_NOT_REGISTERED);
    }

    public function requestSent(): bool
    {
        return $this->isNot(self::WARRANTY_NOT_REGISTERED);
    }

    public function onWarranty(): bool
    {
        return $this->is(self::ON_WARRANTY);
    }

    public function isPending(): bool
    {
        return $this->is(self::PENDING);
    }

    public function isDelete(): bool
    {
        return $this->is(self::DELETE);
    }

    public function isExpired(): bool
    {
        return $this->is(self::EXPIRED);
    }

    public function hasNotice(): bool
    {
        return $this->isDenied();
    }

    public function isDenied(): bool
    {
        return $this->is(self::DENIED);
    }
}
