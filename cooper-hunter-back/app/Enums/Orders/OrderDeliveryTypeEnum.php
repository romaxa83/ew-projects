<?php

namespace App\Enums\Orders;

use BenSampo\Enum\Contracts\LocalizedEnum;
use Core\Enums\BaseEnum;

/**
 * Class OrderDeliveryTypeEnum
 * @package App\Enums\Orders
 *
 * @method static static GROUND()
 * @method static static OVERNIGHT()
 * @method static static NEXT_DAY()
 */
final class OrderDeliveryTypeEnum extends BaseEnum implements LocalizedEnum
{
    public const GROUND = 'ground';
    public const OVERNIGHT = 'overnight';
    public const NEXT_DAY = 'next_day';

}
