<?php

namespace App\Enums\Orders;

use Core\Enums\BaseEnum;

/**
 * Class OrderStatusEnum
 * @package App\Enums\Orders
 *
 * @method static static ACTIVE()
 * @method static static HISTORY()
 */
final class OrderFilterTabEnum extends BaseEnum
{
    public const ACTIVE = 'active';
    public const HISTORY = 'history';

}
