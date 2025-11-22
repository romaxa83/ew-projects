<?php

namespace App\Enums\Orders;

use Core\Enums\BaseEnum;

/**
 * Class OrderStatusEnum
 * @package App\Enums\Orders
 *
 * @method static static CREATED()
 * @method static static PENDING_PAID()
 * @method static static PAID()
 * @method static static SHIPPED()
 * @method static static CANCELED()
 */
final class OrderStatusEnum extends BaseEnum
{
    public const CREATED = 'created';
    public const PENDING_PAID = 'pending_paid';
    public const PAID = 'paid';
    public const SHIPPED = 'shipped';
    public const CANCELED = 'canceled';
}
