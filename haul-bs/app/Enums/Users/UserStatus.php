<?php

namespace App\Enums\Users;

use App\Foundations\Enums\BaseEnum;

/**
 * @method static static ACTIVE()
 * @method static static PENDING()
 * @method static static INACTIVE()
 */

class UserStatus extends BaseEnum
{
    public const ACTIVE = 'active';
    public const PENDING = 'pending';
    public const INACTIVE = 'inactive';

    public function isActive(): bool
    {
        return $this->is(self::ACTIVE());
    }

    public function isPending(): bool
    {
        return $this->is(self::PENDING());
    }

    public function isInactive(): bool
    {
        return $this->is(self::INACTIVE());
    }
}
