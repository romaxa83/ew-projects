<?php

namespace App\Enums\Commercial\Commissioning;

use Core\Enums\BaseEnum;

/**
 * @method static static DRAFT()
 * @method static static ACTIVE()
 * @method static static INACTIVE()
 */
class QuestionStatus extends BaseEnum
{
    public const DRAFT    = 'draft';
    public const ACTIVE   = 'active';
    public const INACTIVE = 'inactive';

    public function isDraft(): bool
    {
        return $this->is(self::DRAFT());
    }

    public function isActive(): bool
    {
        return $this->is(self::ACTIVE());
    }

    public function isInactive(): bool
    {
        return $this->is(self::INACTIVE());
    }
}
