<?php

namespace App\Enums\Payments\PaymentCard;

use Core\Enums\BaseEnum;

/**
 * @method static static COMPANY()
 * @method static static DEALER()
 */
final class MorphSupportedType extends BaseEnum
{
    public const COMPANY = 'company';
    public const DEALER  = 'dealer';
}

