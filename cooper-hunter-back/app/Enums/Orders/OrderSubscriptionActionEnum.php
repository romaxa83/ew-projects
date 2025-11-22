<?php

namespace App\Enums\Orders;

use Core\Enums\BaseEnum;

/**
 * Class OrderSubscriptionActionEnum
 * @package App\Enums\Orders
 *
 * @method static static CREATED()
 * @method static static UPDATED()
 * @method static static DELETED()
 */
final class OrderSubscriptionActionEnum extends BaseEnum
{
    public const CREATED = 'created';
    public const UPDATED = 'updated';
    public const DELETED = 'deleted';

}
