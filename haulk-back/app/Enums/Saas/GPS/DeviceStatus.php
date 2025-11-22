<?php

namespace App\Enums\Saas\GPS;

use App\Enums\BaseEnum;

/**
 * @method static static ACTIVE()
 * @method static static INACTIVE()
 * @method static static DELETED()
 */

class DeviceStatus extends BaseEnum
{
    public const ACTIVE   = 'active';
    public const INACTIVE = 'inactive';
    public const DELETED  = 'deleted';

    public function isActive(): bool
    {
        return $this->is(self::ACTIVE());
    }

    public function isInactive(): bool
    {
        return $this->is(self::INACTIVE());
    }

    public function isDeleted(): bool
    {
        return $this->is(self::DELETED());
    }
}

