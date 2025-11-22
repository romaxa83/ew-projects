<?php

namespace App\Enums\Orders;

use Core\Enums\BaseEnum;

/**
 * Class OrderStatusEnum
 * @package App\Enums\Orders
 *
 * @method static static NOT_FORMED()
 * @method static static PAID()
 * @method static static WAITING_TO_PAY()
 * @method static static PAYMENT_IN_PROCESS()
 * @method static static REFUND_IN_PROCESS()
 * @method static static REFUND_COMPLETE()
 */
final class OrderCostStatusEnum extends BaseEnum
{
    public const NOT_FORMED = 'not_formed';
    public const PAID = 'paid';
    public const WAITING_TO_PAY = 'waiting_to_pay';
    public const PAYMENT_IN_PROCESS = 'payment_in_process';
    public const REFUND_IN_PROCESS = 'refund_in_process';
    public const REFUND_COMPLETE = 'refund_complete';

    public static function getFilterValues(): array
    {
        return array_filter(
            self::getValues(),
            fn(string $item) => !in_array(
                $item,
                [
                    self::REFUND_IN_PROCESS,
                    self::PAYMENT_IN_PROCESS
                ]
            )
        );
    }
}
