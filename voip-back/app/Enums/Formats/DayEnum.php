<?php

namespace App\Enums\Formats;

use Core\Enums\BaseGqlEnum;

/**
 * @method static static MONDAY()
 * @method static static TUESDAY()
 * @method static static WEDNESDAY()
 * @method static static THURSDAY()
 * @method static static FRIDAY()
 * @method static static SATURDAY()
 * @method static static SUNDAY()
 */
class DayEnum extends BaseGqlEnum
{
    public const MONDAY    = 'monday';
    public const TUESDAY   = 'tuesday';
    public const WEDNESDAY = 'wednesday';
    public const THURSDAY  = 'thursday';
    public const FRIDAY    = 'friday';
    public const SATURDAY  = 'saturday';
    public const SUNDAY    = 'sunday';

    public function isMonday(): bool
    {
        return $this->is(self::MONDAY());
    }
}
