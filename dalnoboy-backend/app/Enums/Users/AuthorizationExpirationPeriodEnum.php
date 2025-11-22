<?php

namespace App\Enums\Users;

use Core\Enums\BaseEnum;

/**
 * Class AuthorizationExpirationPeriodsEnum
 * @package App\Enums\Users
 *
 * @method static static UNLIMITED()
 * @method static static EVERYDAY()
 */
class AuthorizationExpirationPeriodEnum extends BaseEnum
{
    public const UNLIMITED = 'unlimited';
    public const EVERYDAY = 'everyday';
}
