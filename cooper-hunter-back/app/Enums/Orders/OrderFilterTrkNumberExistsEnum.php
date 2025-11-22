<?php

namespace App\Enums\Orders;

use Core\Enums\BaseEnum;

/**
 * Class OrderStatusEnum
 * @package App\Enums\Orders
 *
 * @method static static WITH_NUMBER()
 * @method static static NOT_ASSIGNED()
 */
final class OrderFilterTrkNumberExistsEnum extends BaseEnum
{
    public const WITH_NUMBER = 'with_number';
    public const NOT_ASSIGNED = 'not_assigned';

}
