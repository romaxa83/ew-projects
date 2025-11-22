<?php

namespace App\Enums\Saas\GPS;

use App\Enums\BaseEnum;

/**
 * @method static static NONE()
 * @method static static ACTIVATE()
 * @method static static DEACTIVATE()
 */

class DeviceStatusActivateRequest extends BaseEnum
{
    public const NONE       = 'none';
    public const ACTIVATE   = 'activate';
    public const DEACTIVATE = 'deactivate';

    public function isActivate(): bool
    {
        return $this->is(self::ACTIVATE());
    }

    public function isDeactivate(): bool
    {
        return $this->is(self::DEACTIVATE());
    }

    public function isNone(): bool
    {
        return $this->is(self::NONE());
    }
}
