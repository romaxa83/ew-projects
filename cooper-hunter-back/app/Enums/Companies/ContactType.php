<?php

namespace App\Enums\Companies;

use Core\Enums\BaseEnum;

/**
 * @method static static ACCOUNT()
 * @method static static ORDER()
 */
class ContactType extends BaseEnum
{
    public const ACCOUNT   = 'account';
    public const ORDER     = 'order';
}
