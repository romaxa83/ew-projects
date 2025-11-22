<?php

namespace App\Enums\Saas\GPS;

use App\Enums\BaseEnum;

/**
 * @method static static NONE()
 * @method static static PENDING()
 * @method static static CLOSED()
 * @method static static CANCEL_SUBSCRIPTION()
 */

// статус запроса на активирование/деактивирование девайса
class DeviceRequestStatus extends BaseEnum
{
    public const NONE    = 'none';
    public const PENDING = 'pending';
    public const CLOSED  = 'closed';
    public const CANCEL_SUBSCRIPTION  = 'cancel_subscription';

    public function isPending(): bool
    {
        return $this->is(self::PENDING());
    }

    public function isClosed(): bool
    {
        return $this->is(self::CLOSED());
    }

    public function isNone(): bool
    {
        return $this->is(self::NONE());
    }

    public function isCancelSubscription(): bool
    {
        return $this->is(self::CANCEL_SUBSCRIPTION());
    }
}


