<?php

namespace App\Enums\Payments;

use Core\Enums\BaseEnum;

/**
 * Class PayPalRefundStatusEnum
 * @package App\Enums\Payments
 * @link https://developer.paypal.com/api/payments/v2/#captures_refund
 *
 * @method static static CANCELLED()
 * @method static static PENDING()
 * @method static static COMPLETED()
 */
final class PayPalRefundStatusEnum extends BaseEnum
{
    public const CANCELLED = 'CANCELLED';
    public const PENDING = 'PENDING';
    public const COMPLETED = 'COMPLETED';

}
