<?php

namespace App\Enums\Orders\Dealer;

use Core\Enums\BaseEnum;
use Illuminate\Validation\Rule;

/**
 * @method static static NONE()
 * @method static static CARD()
 * @method static static PAYPAL()
 * @method static static BANK()
 * @method static static CHECK()
 * @method static static FLOORING()
 */
final class PaymentType extends BaseEnum
{
    public const NONE   = 'none';
    public const CARD   = 'card';
    public const PAYPAL = 'paypal';
    public const BANK   = 'wiredTransfer';
    public const CHECK  = 'check';
    public const FLOORING  = 'flooring';

    public function isNone(): bool
    {
        return $this->is(self::NONE());
    }

    public function isCard(): bool
    {
        return $this->is(self::CARD());
    }

    public static function ruleInDesc(): string
    {
        return Rule::in([
            self::BANK,
            self::CARD,
            self::PAYPAL,
            self::CHECK,
            self::FLOORING,
        ]);
    }
}
