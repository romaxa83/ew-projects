<?php

namespace App\Enums\Saas\GPS;

use App\Enums\BaseEnum;

/**
 * @method static static DRAFT()
 * @method static static ACTIVE()
 * @method static static ACTIVE_TILL()
 * @method static static CANCELED()
 */

class DeviceSubscriptionStatus extends BaseEnum
{
    public const DRAFT   = 'draft';
    public const ACTIVE   = 'active';
    public const ACTIVE_TILL   = 'active_till';
    public const CANCELED = 'canceled';

    public function isDraft(): bool
    {
        return $this->is(self::DRAFT());
    }

    public function isActive(): bool
    {
        return $this->is(self::ACTIVE());
    }

    public function isActiveTill(): bool
    {
        return $this->is(self::ACTIVE_TILL());
    }

    public function isCanceled(): bool
    {
        return $this->is(self::CANCELED());
    }

    public static function forBilling(): array
    {
        return [
            self::ACTIVE,
            self::ACTIVE_TILL,
        ];
    }
}
