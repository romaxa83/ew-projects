<?php

namespace App\Enums\Payments;

use Core\Enums\BaseEnum;

/**
 * Class PayPalCheckoutStatusEnum
 * @package App\Enums\Payments
 * @link https://developer.paypal.com/api/orders/v2/#orders_create
 *
 * @method static static CREATED()
 * @method static static SAVED()
 * @method static static APPROVED()
 * @method static static VOIDED()
 * @method static static COMPLETED()
 * @method static static PAYER_ACTION_REQUIRED()
 */
final class PayPalCheckoutStatusEnum extends BaseEnum
{
    public const CREATED = 'CREATED';
    public const SAVED = 'SAVED';
    public const APPROVED = 'APPROVED';
    public const VOIDED = 'VOIDED';
    public const COMPLETED = 'COMPLETED';
    public const PAYER_ACTION_REQUIRED = 'PAYER_ACTION_REQUIRED';

    public static function canCreate(): array
    {
        return [
            self::CREATED,
            self::VOIDED,
            self::PAYER_ACTION_REQUIRED
        ];
    }
}
