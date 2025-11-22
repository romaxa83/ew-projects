<?php

namespace App\Enums\Companies;

use Core\Enums\BaseEnum;

/**
 * @method static static CORPORATION()
 * @method static static SOLE_PROPRIETORSHIP()
 * @method static static PARTNERSHIP()
 * @method static static OTHER()
 */
class CompanyType extends BaseEnum
{
    public const CORPORATION         = 'corporation';
    public const SOLE_PROPRIETORSHIP = 'sole_proprietorship';
    public const PARTNERSHIP         = 'partnership';
    public const OTHER               = 'other';
}


